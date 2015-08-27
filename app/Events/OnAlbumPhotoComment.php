<?php

namespace App\Events;

use App\Comment;
use Illuminate\Queue\SerializesModels;

class OnAlbumPhotoComment extends Event implements Feedable
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

    /**
     * @return \App\User
     */
    public function getFeedSender()
    {
        return $this->comment->user;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getFeedable()
    {
        return $this->comment;
    }
}
