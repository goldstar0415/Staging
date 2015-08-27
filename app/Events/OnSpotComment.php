<?php

namespace App\Events;

use App\Spot;
use App\SpotComment;
use Illuminate\Queue\SerializesModels;

class OnSpotComment extends Event implements Feedable
{
    use SerializesModels;

    /**
     * @var SpotComment
     */
    public $comment;

    /**
     * Create a new event instance.
     *
     * @param SpotComment $comment
     */
    public function __construct(SpotComment $comment)
    {
        $this->comment = $comment;
    }

    public function getFeedable()
    {
        return $this->comment;
    }

    public function getFeedSender()
    {
        return $this->comment->user;
    }
}
