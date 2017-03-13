<?php

namespace App\Http\Middleware;

use App\Services\Privacy;
use App\User;
use Closure;

/**
 * Class PrivacyCheck
 * @package App\Http\Middleware
 */
class PrivacyCheck
{
    /**
     * @var Privacy
     */
    private $privacy;

    /**
     * PrivacyCheck constructor.
     * @param Privacy $privacy
     */
    public function __construct(Privacy $privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allow = false;

        /**
         * @var User $target
         */
        if ($request->is('spots/*/') and $request->isMethod('get')
            or $request->is('spots/*/comments') and $request->isMethod('get') || $request->isMethod('post')
            or $request->is('spots/*/photos/*') and $request->isMethod('get') || $request->isMethod('post')
            or $request->is('spots/*/rate') and $request->isMethod('post')
            or $request->is('spots/*/favorite') and $request->isMethod('get')
            or $request->is('spots/*/unfavorite') and $request->isMethod('get')
        ) {
            $spot = $request->route('spots');
            $target = $spot->user;

            if (!$target or !$spot->is_private
                or $spot->is_private and $this->privacy->hasPermission($target, $target->privacy_events)
            ) {
                $allow = true;
            }
        } elseif ($request->is('followers/*')) {
            $target = $request->route('users');

            if ($this->privacy->hasPermission($target, $target->privacy_followers)) {
                $allow = true;
            }
        } elseif ($request->is('followings/*')) {
            $target = $request->route('users');

            if ($this->privacy->hasPermission($target, $target->privacy_followings)) {
                $allow = true;
            }
        } elseif ($request->is('wall')) {
            $target = User::find($request->get('user_id'));

            if ($target and $this->privacy->hasPermission($target, $target->privacy_wall)) {
                $allow = true;
            }
        } elseif ($request->is('wall/*', 'wall/*/like', 'wall/*/dislike') and $request->isMethod('get')) {
            $target = $request->route('wall')->receiver;

            if ($this->privacy->hasPermission($target, $target->privacy_wall)) {
                $allow = true;
            }
        } elseif ($request->is('spots/favorites') and $request->has('user_id')) {
            $target = User::find($request->user_id);

            if ($this->privacy->hasPermission($target, $target->privacy_favorites)) {
                $allow = true;
            }
        } else {
            $allow = true;
        }

        if (!$allow) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
