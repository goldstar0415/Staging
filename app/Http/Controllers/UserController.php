<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UserListRequest;
use App\Role;
use App\User;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\ThrottlesLogins;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

use App\Services\Registrar;
use Illuminate\Http\Request;

class UserController extends Controller
{

    use Registrar, ThrottlesLogins;

    private $auth;

    private $loginPath = '/users/login';

    /**
     * Create a new authentication controller instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->middleware('guest', ['except' => ['getLogout', 'getMe', 'getIndex', 'getList', 'reviews']]);
        $this->middleware('auth', ['only' => 'getMe']);
        $this->auth = $auth;
    }

    /**
     * @param \App\User $user
     * @return mixed
     */
    public function getIndex($user)
    {
        return $this->appendUserRelations($user);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return User
     */
    public function postIndex(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        $this->auth->login($this->create($request->all()));

        $user = $this->auth->user();
        $user->roles()->attach(Role::take(config('entrust.default')));
        return $user;
    }

    public function getList(UserListRequest $request)
    {
        $users = null;
        $limit = $request->get('limit', 10);
        $filter = '';
        if ($request->has('filter')) {
            $filter = $request->get('filter');
        }

        switch ($request->get('type')) {
            case 'all':
                $users = User::query();
                break;
            case 'followers':
                $users = $request->user()->followers();
                break;
            case 'followings':
                $users = $request->user()->followings();
                break;
        }

        if (!empty($filter)) {
            $users->search($filter);
        }

        return $users->with(['spots' => function ($query) {
            $query->take(3);
        }])->paginate($limit);
    }

    public function getMe()
    {
        return $this->appendUserRelations($this->auth->user());
    }

    /**
     * Show the form for creating a new resource.
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
                return redirect()->back()->with('status', trans($response));

            case Password::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans($response)]);
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
                return redirect($this->redirectPath());

            default:
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => trans($response)]);
        }
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
        $comments = collect();
        /**
         * @var \App\User $user
         */
        $user = $request->user();

        $comments = Comment::whereHas('commentable', function ($query) {
            /**
             * @var Builder
             */
            $query->join('albums', '', '')->where('albums.user_id', '=', 1);
        });

        foreach ($user->spots as $spot) {
            if ($spot_comments = $spot->comments->load('commentable')->all()) {
                $comments = $comments->merge($spot_comments);
            }
        }

        foreach ($user->plans as $album) {
            if ($plan_comments = $album->comments->load('commentable')->all()) {
                $comments = $comments->merge($plan_comments);
            }
        }

        foreach ($user->albums as $album) {
            foreach ($album->photos as $photo) {
                if ($photo_comments = $photo->comments->load('commentable')->all()) {
                    $comments = $comments->merge($photo_comments);
                }
            }
        }

        return $comments;
    }

    protected function authenticated(Request $request, Authenticatable $user)
    {
        return $user;
    }

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

        $user->save();

        Auth::login($user);
    }

    /**
     * @param \App\User $user
     * @return mixed
     */
    protected function appendUserRelations($user)
    {
        $rand_func = config('database.connections.' . config('database.default') . '.rand_func');
        $user->append(['count_favorites', 'count_photos']);
        return $user->load(
            ['followers' => function ($query) use ($rand_func) {
                $query->orderBy(DB::raw($rand_func))
                    ->take(6);
            },
            'followings' => function ($query) use ($rand_func) {
                $query->orderBy(DB::raw($rand_func))
                    ->take(6);
            },
            'spots' => function ($query) use ($rand_func) {
                $query->orderBy(DB::raw($rand_func))
                    ->take(6);
            }]
        );
    }
}
