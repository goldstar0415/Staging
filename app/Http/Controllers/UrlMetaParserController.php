<?php

namespace App\Http\Controllers;

use Exception;
use Htmldom;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
use Session;

class UrlMetaParserController extends Controller
{
    /**
     * Get content from site
     * @param Request $request
     * @return mixed
     */
    public function getContentFromSite(Request $request)
    {
        $links = $request->get('links');

        $result = [];
        foreach ($links as $link) {
            $error = null;
            if (!$url = self::getUrl($link)) {
                $error = ['result' => false, 'messages' => 'Bad URL'];
            } else {
//                $linkModel = LinkModel::where('url', $url)->first();
//                if (!$linkModel) {
//                    $result[] = [
//                        'title' => $linkModel->title,
//                        'description' => $linkModel->body,
//                        'images' => [$linkModel->image],
//                        'url' => $linkModel->url,
//                        'default_url' => $link
//                    ];
//                    continue;
//                }
            }

            if (!empty($error) || !$content = self::getPageFromUrl($url)) {
                $error = ['result' => false, 'messages' => 'bad url!'];
            }

            if (empty($error)) {
                if (!$title = self::getTitle($content)) {
                    $title = $url;
                }

                $description = self::getDescription($content);

                $images = self::getAllImages($content, $url);

                $result[] = [
                    'title' => str_limit(strip_tags($title), config('restrictions.link.title.length')),
                    'description' => strip_tags($description),
                    'images' => $images,
                    'url' => $url,
                    'default_url' => $link
                ];
            } else {
                $result[] = [
                    'error' => $error,
                    'url' => $link,
                    'default_url' => $link,
                    '_token' => Session::token()
                ];
            }

        }

        return response()->json(['result' => true, 'data' => $result]);
    }

    /**
     * Get page from URL
     * @param string $url
     * @param string $proxy
     * @param array $headers
     * @return bool|Htmldom|null
     */
    public static function getPageFromUrl($url = '', $proxy = '', $headers = [])
    {
        if (!$url) {
            $url = 'google.com';
        }

        if (!$headers) {
            $headers = ['User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2) Gecko/20100115 Firefox/3.6'];
        }
        $c = curl_init($url);
        if ($proxy) { // Если задана переменная с прокси-сервером, то приказываем использовать его.
            curl_setopt($c, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers); // Передаем массив с HTTP-заголовками.
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); // Это для того, что бы cURL возвращал текст сраницы, а не выводил его на экран.
        curl_setopt($c, CURLOPT_TIMEOUT, 5);
        $page = curl_exec($c); // Запускаем сам процесс и записываем скачанную страницу в $page;
        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c); // Освобождаем задействованные ресурсы, т.к. мы все сделали, cURL нам больше не нужен.
        if (!$page || $code != 200) {
            return false;
        }

        $htmlDom = null;
        try {
            $htmlDom = new Htmldom($page);
        } catch (Exception $ex) { Log::error('PARSE URL: ' . $ex->getMessage()); }

        return $htmlDom;
    }

    /**
     * Get title from html
     * @param $html
     * @return bool
     */
    public static function getTitle($html)
    {
        $elem = $html->find('title', 0);
        if ($elem) {
            return $elem->plaintext;
        }
        return false;
    }

    /**
     * Get all images from html
     * @param $html
     * @param string $url
     * @return array
     */
    public static function getAllImages($html, $url = '')
    {
        $images = [];
        $formatUrl = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);

        //Parse <img src=""></meta>
        foreach ($html->find('img') as $item) {
            if (preg_match('(.*\.(?:png|jpg|svg))', $item->src)) {
                if (parse_url($item->src, PHP_URL_HOST)) {
                    $images[] = $item->src;
                } else {
                    $url = (substr($item->src, 0, 1) == '/') ? $formatUrl : $url;
                    $images[] = $url . '/' . $item->src;
                }
            }
        }

        //Parse <meta property="og:image"></meta>
        if (!$images) {
            foreach ($html->find('meta[property]') as $item) {
                if (strtolower($item->property) == 'og:image') {
                    if (preg_match('(.*\.(?:png|jpg|svg))', $item->content)) {
                        if (parse_url($item->content, PHP_URL_HOST)) {
                            $images[] = $item->content;
                        } else {
                            $url = (substr($item->content, 0, 1) == '/') ? $formatUrl : $url;
                            $images[] = $url . '/' . $item->content;
                        }
                    }
                }
            }
        }

        //Parse <meta itemprop="image"></meta>
        if (!$images) {
            foreach ($html->find('meta[itemprop]') as $item) {
                if (strtolower($item->itemprop) == 'image') {
                    if (preg_match('(.*\.(?:png|jpg|svg))', $item->content)) {
                        if (parse_url($item->content, PHP_URL_HOST)) {
                            $images[] = $item->content;
                        } else {
                            $url = (substr($item->content, 0, 1) == '/') ? $formatUrl : $url;
                            $images[] = $url . '/' . $item->content;
                        }
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Get description from html
     * @param $html
     * @return string
     */
    public static function getDescription($html)
    {
        $description = '';
        foreach ($html->find('meta[name]') as $elem) {
            if (strtolower($elem->name) == 'description') {
                $description = $elem->content;
            }
        }
        if (!$description) {
            foreach ($html->find('meta[property]') as $elem) {
                if (strtolower($elem->property) == 'og:description') {
                    $description = $elem->content;
                }
            }
        }
        return $description;
    }

    /**
     * Get URL
     * @param $url
     * @return bool|string
     */
    public static function getUrl($url)
    {
        $url = trim($url);
        if (strlen($url) == 0) {
            return false;
        }
        if (!preg_match("~^(?:(?:https?)://(?:[a-z0-9_-]{1,32}" . "(?::[a-z0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9-]{1,128}\.)+(?:com|net|" . "org|mil|edu|arpa|gov|biz|info|aero|inc|travel|name|mobi|[a-z]{2})|(?!0)(?:(?" . "!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:/[a-z0-9.,_@%&" . "?+=\~/-]*)?(?:#[^ '\"&<>]*)?$~i", $url, $ok)) {
            return false;
        }

        if (!strstr($url, "://")) {
            $url = "http://" . $url;
        }
        //$url = preg_replace("~^[a-z]+~iA", "strtolower('\\0')", $url);

        return $url;
    }
}
