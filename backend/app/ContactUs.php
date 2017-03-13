<?php

namespace App;

use App\Scopes\NewestScopeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Services\SqlEscape;

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
    use NewestScopeTrait, SqlEscape;

    protected $table = 'contact_us';

    protected $fillable = ['username', 'email', 'message'];

    /**
     * Scope a query to search by user full name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $filter)
    {
    	$filter = self::escapeLike($filter);

        return $query->whereRaw("LOWER(\"message\") like '%$filter%'");
    }
}
