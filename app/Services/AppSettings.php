<?php

namespace App\Services;

use Storage;

class AppSettings
{
    /**
     * Settings data
     *
     * @var object
     */
    protected $data;

    /**
     * @var string
     */
    protected $file_name = 'settings.json';

    /**
     * AppSettings constructor.
     */
    public function __construct()
    {
        if (Storage::exists($this->file_name)) {
            $this->data = json_decode(Storage::get($this->file_name), true);
        }
    }

    public function all()
    {
        return $this->data;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __destruct()
    {
        Storage::put($this->file_name, json_encode($this->data, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
    }
}
