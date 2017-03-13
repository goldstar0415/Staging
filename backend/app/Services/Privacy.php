<?php


namespace App\Services;

use App\User;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class Privacy
 * Checks user privacy
 * @package App\Services
 */
class Privacy
{
    const ALL = 1;
    const FOLLOWERS_FOLLOWINGS = 2;
    const FOLLOWINGS = 3;
    const AUTHORIZED = 4;
    const NOBODY = 5;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected $viewer;
    /**
     * @var Guard
     */
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->viewer = $auth->user();
        $this->auth = $auth;
    }

    /**
     * Check the user permissions to access an information
     *
     * @param User $target
     * @param int $permission
     * @return bool
     */
    public function hasPermission(User $target, $permission = self::ALL)
    {
        $is_permitted = false;

        if ($this->viewer !== null) {
            if ($this->auth->id() === $target->id) {
                return true;
            }

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
                            ->exists()
                        ||
                        $target
                            ->followings()
                            ->where('following_id', $this->viewer->id)
                            ->exists();
                    }
                    break;
                case self::FOLLOWINGS:
                    if (!$this->isGuestViewer()) {
                        $is_permitted = $target
                            ->followings()
                            ->where('following_id', $this->viewer->id)
                            ->exists();
                    }
                    break;
                case self::NOBODY:
                    break;
            }
        } else {
            if ($permission === self::ALL) {
                $is_permitted = true;
            }
        }

        return $is_permitted;
    }

    /**
     * Check viewer is guest
     *
     * @return bool
     */
    protected function isGuestViewer()
    {
        return !isset($this->viewer);
    }
}
