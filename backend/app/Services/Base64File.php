<?php

namespace App\Services;

use File;

/**
 * Class Base64File
 * Provides to parse string like base64 file
 * @package App\Services
 */
class Base64File
{
    /**
     * @var null|string
     */
    protected $name;

    /**
     * @var string base64 file string
     */
    protected $base64_code;

    /**
     * @var string Mime type
     */
    protected $mime;

    /**
     * @var string File charset
     */
    protected $charset = 'utf-8';

    /**
     * @var string Base64 file data
     */
    protected $file_data;

    /**
     * @var string Base64 decoded data
     */
    protected $decoded_data;

    /**
     * @var float file size
     */
    protected $size = null;

    /**
     * @var null|string file path
     */
    protected $path = null;

    /**
     * Base64File constructor.
     * @param $base64_code
     * @param null $name
     */
    public function __construct($base64_code, $name = null)
    {
        $this->base64_code = $base64_code;
        $this->name = $name;
        $this->parse();
    }

    /**
     * Get file name
     *
     * @return null|string
     */
    public function getName()
    {
        $ext = explode('/', $this->getMime())[1];
        if (!$this->name) {
            return str_random() . '.' . $ext;
        }
        return $this->name . '.' . $ext;
    }

    /**
     * Get decoded data
     *
     * @return mixed
     */
    public function getDecodedData()
    {
        if (empty($this->decoded_data)) {
            $this->decoded_data = base64_decode($this->getFileData());
        }

        return $this->decoded_data;
    }

    /**
     * Get file data
     *
     * @return mixed
     */
    public function getFileData()
    {
        return $this->file_data;
    }

    /**
     * Get mime type
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Get file size
     *
     * @return null|float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get file path
     *
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Pare base64 format data:[<MIME-type>][;charset=<encoding>][;base64],<data>
     */
    protected function parse()
    {
        list($properties, $data) = explode(',', $this->base64_code);

        $this->file_data = $data;

        $properties = explode(';', substr($properties, 5));

        $mime = '';
        if (count($properties) === 3) {
            list($mime, $charset) = $properties;
            $this->charset = $charset;

        } else {
            list($mime) = $properties;
        }
        $this->mime = $mime;
    }

    /**
     * Save file
     *
     * @param null|string $path
     * @return bool
     */
    public function save($path = null)
    {
        if ($path === null) {
            $path = '/tmp/php' . str_random(6) . '.' . explode('/', $this->getMime())[1];
        }
        if (File::put($path, $this->getDecodedData()) === false) {
            return false;
        }

        $this->path = $path;
        $this->size = File::size($path);

        return true;
    }
}
