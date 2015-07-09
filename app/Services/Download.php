<?php



namespace App\Services;


use Auth;
use Session;
use SplFileInfo;

class Download
{

    /**
     * @var \SplFileObject|string
     */
    private $file;

    private $title;

    private $userfiles;

    private $attributes;

    /**
     * @param string $file
     * @param string $tile
     * @param array $attributes
     * @param bool $userfiles
     */
    function __construct($file, $tile = '', $attributes = [], $userfiles = true)
    {
        $this->file = $file;
        $this->title = $tile;
        $this->userfiles = $userfiles;
        $this->attributes = $attributes;
    }


    public function htmLink()
    {
        $file = $this->prepareSession();
        return '<a href="' . url('file?id=' . $file) . '"' . $this->attributes($this->attributes) . '>' . $this->title . '</a>';
    }

    private function prepareSession()
    {
        $file_id = '';
        $file_path = '';
        if ($this->userfiles) {
            $file_path = storage_path('app/userfiles/' . Auth::id() . '/' . $this->file);
        } else {
            $file_path = storage_path('app/userfiles/' . $this->file);
        }
        if (Session::has('files')) {
            foreach (Session::get('files') as $id => $file) {
                if ($file === $file_path) {
                    $file_id = $id;
                    break;
                }
            }
        }
        if (empty($file_id)) {
            $file_id = bcrypt(uniqid(str_random(), true));
            Session::set('files.' . $file_id, $file_path);
        }
        return $file_id;
    }

    public static function getFile($id)
    {
        $file = Session::pull('files.' . $id);
        if (empty($file)) {
            abort(404);
        }
        return response()->download($file);
    }

    public function link()
    {
        return url('file?id=' . $this->prepareSession());
    }

    public function attributes($attributes)
    {
        $html = array();

        // For numeric keys we will assume that the key and the value are the same
        // as this will convert HTML attributes such as "required" to a correct
        // form like required="required" instead of using incorrect numerics.
        foreach ((array)$attributes as $key => $value) {
            $element = $this->attributeElement($key, $value);

            if (!is_null($element)) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param  string $key
     * @param  string $value
     * @return string
     */
    protected function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            $key = $value;
        }

        if (!is_null($value)) {
            return $key . '="' . e($value) . '"';
        }
        return '';
    }

}