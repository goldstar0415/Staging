<?php

namespace App\Http\Middleware;

use App\Services\Privacy;
use App\User;
use Closure;

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
        if ($request->is('spots', 'spots/index') and $request->isMethod('get')) {
            $target = User::find($request->get('user_id'));

            if ($this->privacy->hasPermission($target, $target->privacy_events)) {
                $allow = true;
            }
        } elseif ($request->is('spots/*/') and $request->isMethod('get')
            or $request->is('spots/*/comments') and $request->isMethod('get') || $request->isMethod('post')
            or $request->is('spots/*/photos/*') and $request->isMethod('get') || $request->isMethod('post')
            or $request->is('spots/*/rate') and $request->isMethod('post')
            or $request->is('spots/*/favorite') and $request->isMethod('get')
            or $request->is('spots/*/unfavorite') and $request->isMethod('get')
        ) {
            $target = $request->route('spots')->user;

            if ($this->privacy->hasPermission($target, $target->privacy_events)) {
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

            if ($this->privacy->hasPermission($target, $target->privacy_wall)) {
                $allow = true;
            }
        } elseif ($request->is('wall/*', 'wall/*/like', 'wall/*/dislike') and $request->isMethod('get')) {
            $target = $request->route('wall')->user;

            if ($this->privacy->hasPermission($target, $target->privacy_wall)) {
                $allow = true;
            }
        } elseif ($request->is('users/*/albums')) {
            $target = $request->route('users');

            if ($this->privacy->hasPermission($target, $target->privacy_photo_map)) {
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
