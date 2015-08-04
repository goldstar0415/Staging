<?php


namespace App\Services;

use App\User;
use Illuminate\Contracts\Auth\Guard;

class Privacy
{

    const ALL = 1;
    const FOLLOWERS_FOLLOWINGS = 2;
    const FOLLOWINGS = 3;
    const AUTHORIZED = 4;
    const NOBODY = 5;

    protected $viewer;
    protected $auth;

    public function __construct(User $viewer, Guard $auth)
    {
        $this->viewer = $viewer;
        $this->auth = $auth;
    }

    public function hasPermission(User $target, $permission = self::ALL)
    {
        $is_permitted = false;

        switch ($permission) {
            case self::ALL:
                $is_permitted = true;
                break;
            case self::AUTHORIZED:
                $is_permitted = $this->auth->check();
                break;
            case self::FOLLOWERS_FOLLOWINGS:
                if (!$this->isGuestViewer()) {
                    $is_permitted = $target
                        ->followers()
                        ->where('follower_id', $this->viewer->id)
                        ->first()
                    or
                    $target
                        ->followings()
                        ->where('following_id', $this->viewer->id)
                        ->first();
                }
                break;
            case self::FOLLOWINGS:
                if (!$this->isGuestViewer()) {
                    $is_permitted = $target
                        ->followings()
                        ->where('following_id', $this->viewer->id)
                        ->first();
                }
                break;
            case self::NOBODY:
                break;
        }

        return $is_permitted;
    }

    protected function isGuestViewer()
    {
        return !isset($this->viewer);
    }
}
