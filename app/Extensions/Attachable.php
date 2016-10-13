<?php

namespace App\Extensions;

use App\ChatMessage;
use App\Comment;
use App\Wall;

trait Attachable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attached_walls()
    {
        return $this->morphedByMany(Wall::class, self::snakeName() . 'able', self::snakeName() . '_attachable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attached_comments()
    {
        return $this->morphedByMany(Comment::class, self::snakeName() . 'able', self::snakeName() . '_attachable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attached_chat_messages()
    {
        return $this->morphedByMany(ChatMessage::class, self::snakeName() . 'able', self::snakeName() . '_attachable');
    }

    protected static function snakeName()
    {
        static $name = '';

        if (empty($name)) {
            $name = snake_case(class_basename(get_called_class()));
        }

        return $name;
    }

    public function cleanAttached()
    {
        $collection = $this->attached_chat_messages->merge($this->attached_walls->merge($this->attached_comments));
        $collection->each(function ($message) {
            if (empty($message->body) and $message->countAttachments() === 1) {
                $message->feeds()->delete();
                $message->delete();
            }
        });
    }
}
