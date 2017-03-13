<?php

namespace App;

use App\Extensions\Attachments;
use App\Scopes\NewestScopeTrait;

/**
 * Model Comment
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $commentable_id
 * @property string $commentable_type
 * @property object $commentable
 * @property string $body
 *
 * Relation properties
 * @property AlbumPhoto $photo
 * @property User $sender
 */
class Comment extends BaseModel
{
    use Attachments, NewestScopeTrait;

    protected $fillable = ['body'];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->addAttachments();
    }

    /**
     * Get the user that sent the comment
     */
    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get feeds where attached this comment
     */
    public function feeds()
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    /**
     * Get all of the owning commentable models.
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}
