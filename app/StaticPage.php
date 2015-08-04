<?php

namespace App;

/**
 * Class StaticPage
 * @package App
 *
 * @property integer $id
 * @property string $title
 * @property string $body
 */
class StaticPage extends BaseModel
{
    protected $guarded = ['id'];
}
