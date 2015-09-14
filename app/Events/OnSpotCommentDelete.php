<?php

namespace App\Events;

use App\Comment;
use Illuminate\Queue\SerializesModels;

class OnSpotCommentDelete extends Event implements Feedable
{
    use SerializesModels;

    /**
     * @var Comment
     */
    public $comment;

    /**
     * Create a new event instance.
     *
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function getFeedable()
    {
        return $this->comment;
    }

    public function getFeedSender()
    {
        return $this->comment->sender;
    }
}
