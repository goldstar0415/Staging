<?php

namespace App;

use App\Scopes\NewestScopeTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Model ContactUs
 * @package App
 *
 * @property string $name
 * @property string $email
 * @property string $message
 */
class ContactUs extends Model
{
    use NewestScopeTrait;

    protected $table = 'contact_us';

    protected $fillable = ['username', 'email', 'message'];
}
