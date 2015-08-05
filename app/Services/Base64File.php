<?php

namespace App\Services;

use File;

class Base64File
{
    protected $name;

    protected $base64_code;

    protected $mime;

    protected $charset = 'utf-8';

    protected $file_data;

    protected $decoded_data;

    protected $size = null;

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
     * @return null
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
     * @return mixed
     */
    public function getFileData()
    {
        return $this->file_data;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @return null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return null
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

        } else {
            list($mime) = $properties;
        }
        $this->mime = $mime;
    }

    public function save($path = null)
    {
        if ($path === null) {
            $path = '/tmp/php' . str_random(6) . '.' . explode('/', $this->getMime())[1]; //TODO: remove extension
        }
        if (File::put($path, $this->getDecodedData()) === false) {
            return false;
        }

        $this->path = $path;
        $this->size = File::size($path);

        return true;
    }
}
