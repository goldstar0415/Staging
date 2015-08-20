<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BlogComment
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $plan_id
 * @property string $body
 */
class PlanComment extends BaseModel
{
    protected $fillable = ['body'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
