<?php

namespace App\Contracts;

interface Commentable
{
    /**
     * Get id of comment resource owner
     *
     * @return integer
     */
    public function commentResourceOwnerId();
}
