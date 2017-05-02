<?php

namespace App\Http\Controllers;

use App\Comment;
use App\ContactUs;
use App\Events\UserInviteEvent;
use App\Http\Requests\ContactUsRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UserListRequest;
use App\Mailers\AppMailer;
use App\Role;
use App\Services\EmailChange\EmailChangeBroker;
use App\Services\EmailChange\EmailChangeContract;
use App\Services\Privacy;
use App\User;
use App\Spot;
use App\SpotVote;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

use App\Services\Registrar;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserController
 * @package App\Http\Controllers
 *
 * User resource controller
 */
class UserController extends Controller
{
    use Registrar, ThrottlesLogins;

    /**
     * @var Guard
     */
    private $auth;

    /**
     * @var string
     */
    private $loginPath = '/users/login';

    /**
     * Create a new authentication controller instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->middleware('guest', ['only' => [
            'postLogin',
            'postCreate',
            'postRecovery',
            'postReset',
        ]]);

        $this->middleware('auth', ['only' => [
            'getLogout',
            'getMe',
            'changeEmail',
            'unsubscribe',
            'usersImportInfo',
            'inviteEmail',
            'getListFollowers',
            'getListFollowings',
        ]]);

        $this->auth = $auth;
    }

    /**
     * @param \App\User $user
     * @return mixed
     */
    public function getIndex($user)
    {
        return $this->appendUserRelations($user)->load('roles');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @param AppMailer $mailer
     * @return User
     */
    public function postCreate(Request $request, AppMailer $mailer)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }


        $user = $this->create($request->all());
        $user->roles()->attach(Role::take(config('entrust.default')));
        $mailer->sendEmailVerification($user);

        return $user;
    }

    /**
     * Get all user list
     *
     * @param UserListRequest $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getListAll(UserListRequest $request)
    {
        return $this->getList($request, User::query());
    }

    /**
     * Get user list of all user followers
     *
     * @param UserListRequest $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getListFollowers(UserListRequest $request)
    {
        return $this->getList($request, $request->user()->followers());
    }

    /**
     * Get user list of all user followings
     *
     * @param UserListRequest $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getListFollowings(UserListRequest $request)
    {
        return $this->getList($request, $request->user()->followings());
    }


    /**
     * Get user list by specific condition
     *
     * @param UserListRequest $request
     * @param Builder $users
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function getList(UserListRequest $request, $users)
    {
        $limit = $request->get('limit', 10);

        if(! empty($filter)) {
            $users->search($request->get('filter'));
        }

        return $users->with(['spots' => function ($query) {
            $query->take(3);
        }])->paginate($limit);
    }

    /**
     * Get authenticated user model
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getMe()
    {
        return $this->appendUserRelations($this->auth->user())
                ->append(['new_messages', 'favorites_ids'])
                ->withHidden(['is_hints', 'email'])
                ->load('roles');
    }

    /**
     * Handle a login request to the application.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            $this->loginUsername() => 'required', 'password' => 'required',
        ]);

        // If the class is using the Throttles Logins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        if ($this->auth->attempt($credentials, $request->has('remember'))) {
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        return $this->buildFailedValidationResponse(
            $request,
            [$this->loginUsername() => $this->getFailedLoginMessage()]
        );
    }

    /**
     * Log the user out of the application.
     */
    public function getLogout()
    {
        $this->auth->logout();
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postRecovery(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return response()->json(['status' => trans($response)]);

            case Password::INVALID_USER:
                return response()->json(['email' => trans($response)], 403);
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $credentials = $request->only(
            'email',
            'password',
            'password_confirmation',
            'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                return $this->auth->user();

            default:
                return response()->json(['email' => trans($response)], 403);
        }
    }

    /**
     * Display a listing of the my comments.
     *
     * @param PaginateRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     * @internal param Spot $spot
     */
    public function comments(PaginateRequest $request)
    {
        $comments = Comment::with('commentable','sender')
            ->where('user_id', $request->user()->id)->where('commentable_type', Spot::class);
        return $this->paginatealbe($request, $comments);
    }
    
    /**
     * Display a listing of the my reviews.
     *
     * @param PaginateRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     * @internal param Spot $spot
     */
    public function reviews(PaginateRequest $request)
    {
        $reviews = SpotVote::with('user','spot')
            ->where('user_id', $request->user()->id)->orderBy('created_at', 'desc');
        return $this->paginatealbe($request, $reviews);
    }

    /**
     * Send user feedback
     *
     * @param ContactUsRequest $request
     * @return \App\ContactUs
     */
    public function contactUs(ContactUsRequest $request)
    {
        return ContactUs::create($request->all());
    }

    /**
     * Confirm user email
     *
     * @param $token
     * @return \App\User
     */
    public function confirmEmail($token)
    {
        $user = User::whereToken($token)->firstOrFail()->confirmEmail();

        if ($this->auth->check()) {
            $authUser = $this->auth->user();
            if ($authUser->id != $user->id) {
                $this->auth->logout();
                $this->auth->login($user);
            }
        } else {
            $this->auth->login($user);
        }

        return redirect(frontend_url('email-verified'));
    }

    public function changeEmail(Request $request, $token, EmailChangeBroker $emailChangeBroker)
    {
        $result = $emailChangeBroker->change($request->user(), $token, function ($user, $email) {
            $user->email = $email;
            $user->save();
        });

        switch ($result) {
            case EmailChangeContract::INVALID_TOKEN:
                return redirect(frontend_url('settings', 'token-expired'));
            case EmailChangeContract::EMAIL_CHANGE:
                return redirect(frontend_url('settings', 'email-changed'));
        }

        return redirect(frontend_url('settings', 'email-changed'));
    }

    /**
     * Get authenticated user info
     *
     * @param Request $request
     * @param Authenticatable $user
     * @return $this
     */
    protected function authenticated(Request $request, Authenticatable $user)
    {
        if (!$user->verified) {
            $this->auth->logout();
            abort(406);
        }

        return $this->appendUserRelations($user)->append(['new_messages'])->load('roles');
    }

    /**
     * {@inheritDoc}
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = (int)\Cache::get($this->getLoginLockExpirationKey($request)) - time();

        return response()->json(['message' => $this->getLockoutErrorMessage($seconds)], 503);
    }

    /**
     * Get the e-mail subject line to be used for the reset link email.
     *
     * @return string
     */
    protected function getEmailSubject()
    {
        return isset($this->subject) ? $this->subject : 'Your Password Reset Link';
    }

    /**
     * Reset the given user's password.
     *
     * @param  User $user
     * @param  string $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);
        $user->verified = true;

        $user->save();

        Auth::login($user);
    }

    /**
     * Append some user info to the user model
     *
     * @param \App\User $user
     * @return \App\User
     */
    protected function appendUserRelations($user)
    {
        $rand_func = config('database.connections.' . config('database.default') . '.rand_func');
        $user->append(['count_favorites', 'count_photos']);

        $append = [];
        /**
         * @var Privacy $privacy
         */
        $privacy = app(Privacy::class);
        $rand_callback = function ($query) use ($rand_func) {
            $query->orderBy(DB::raw($rand_func))
                ->take(6);
        };

        if ($privacy->hasPermission($user, $user->privacy_followers)) {
            $append['followers'] = $rand_callback;
        }

        if ($privacy->hasPermission($user, $user->privacy_followings)) {
            $append['followings'] = $rand_callback;
        }

        if ($privacy->hasPermission($user, $user->privacy_events)) {
            $append['spots'] = $rand_callback;
        }

        return $user->load($append);
    }
    
    protected function unsubscribe() {
        
        if($this->auth->check()) {
            $user = $this->auth->user();
            $user->notification_letter = false;
            $user->notification_wall_post = false;
            $user->notification_follow = false;
            $user->notification_new_spot = false;
            $user->notification_coming_spot =false;
            $user->save();
        }
        else {
            abort(401);
        }
        
    }

    /**
     * Get information about people by email if someone is an existing user. If so is he/she a friend or follower of
     * current logged user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function usersImportInfo(Request $request) {
        $usersOut = [];
        $emailMap = [];
        foreach ($request->get('emails') as $email) {
            $usersOut[] = ['email' => $email, 'exists' => false, 'friends' => false];
            $emailMap[$email] = count($usersOut) - 1;
        }
        $users = User::whereIn('email', $request->get('emails'))->get();
        foreach ($users as $user) {
            //Log::debug('found user by email '.$user->email.' with user id '.$user->id);
            $usersOut[$emailMap[$user->email]]['exists']    = true;
            $usersOut[$emailMap[$user->email]]['id']        = $user->id;
            // if users A ($user) and B ($request->user()->id logged user)
            $res = $request->user()->friends()->where('friend_id', $user->id)->get();
            //Log::debug(print_r($res, 1));
            foreach ($res as $_u) {
                $usersOut[$emailMap[$user->email]]['friends'] = true;
                //Log::debug("    friend {$_u->id} of {$user->id}");
            }
        }

        // search existing users
        return response()->json($usersOut);
    }

    /**
     * Invite humans to zoomtivity by sending emails
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inviteEmail(Request $request) {
        Log::debug("UserController inviteEmail");
        event(new UserInviteEvent($request->user(), $request->email));
        return response()->json(['email' => $request->email]);
    }
}
