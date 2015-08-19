<?php


namespace App\Events;

interface Feedable
{
    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getFeedable();

    /**
     * @return \App\User
     */
    public function getFeedSender();
}
