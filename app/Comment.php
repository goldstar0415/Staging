<?php

namespace App;
use App\Extensions\Attachments;
use App\Scopes\NewestScopeTrait;

/**
 * Class Comment
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
 * @property User $user
 */
class Comment extends BaseModel
{
    use Attachments, NewestScopeTrait;

    protected $fillable = ['body'];

    protected $with = ['sender', 'commentable'];


    /**
     * @inheritdoc
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->addAttachments();
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }
}
