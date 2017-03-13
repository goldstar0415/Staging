<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RemotePhoto extends Model {

    protected $fillable = ['image_type', 'url', 'size'];

    public function associated() {
        return $this->morphTo();
    }

}
