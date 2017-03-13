<?php

namespace App\Services;

use stdClass;
use Storage;

class AppSettings implements \ArrayAccess
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
            $this->data = (array)json_decode(Storage::get($this->file_name));
        }
        $this->setDefaults();
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
        if (!isset($this->data[$name])) {
            $this->data[$name] = new stdClass;
        }

        return $this->data[$name];
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }


    public function __destruct()
    {
        Storage::put($this->file_name, json_encode($this->data, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
    }

    protected function setDefaults()
    {
        if (!isset($this->data['parser'])) {
            $this->data['parser'] = new stdClass();
        }
        if (!isset($this->data['crawler'])) {
            $this->data['crawler'] = new stdClass();
        }
    }
}
