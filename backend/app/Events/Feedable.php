<?php

namespace App\Events;

interface Feedable
{
    /**
     * Get feedable model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getFeedable();

    /**
     * Get user who made an action
     *
     * @return \App\User
     */
    public function getFeedSender();
}
