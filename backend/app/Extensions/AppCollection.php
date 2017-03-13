<?php

namespace App\Extensions;

use Illuminate\Support\Collection;

class AppCollection extends Collection
{
    /**
     * Dynamically get values
     * @param  string $key
     * @return string
     */
    public function __get($key)
    {
        if ($this->has($key))
            return $this->get($key);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }
}
