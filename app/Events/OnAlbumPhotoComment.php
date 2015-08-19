<?php

namespace App\Events;

use App\PhotoComment;
use Illuminate\Queue\SerializesModels;

class OnAlbumPhotoComment extends Event implements Feedable
{
    use SerializesModels;
    /**
     * @var PhotoComment
     */
    public $comment;

    /**
     * Create a new event instance.
     *
     * @param PhotoComment $comment
     */
    public function __construct(PhotoComment $comment)
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
