<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotReport
 * @package App
 *
 * @property integer $id
 * @property integer $spot_id
 * @property string $reason
 * @property string $text
 *
 * @property Spot $spot
 */
class SpotReport extends Model
{
    const WRONG = 'wrong';
    const INAPPROPRIATE = 'inappropriate';
    const DUPLICATE = 'duplicate';
    const SPAM = 'spam';
    const OTHER = 'other';

    protected $fillable = ['reason', 'text'];

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
