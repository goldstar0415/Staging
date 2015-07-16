<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Lang;

class UserController extends Controller
{

    private $auth;
    private $auth_controller;

    /**
     * Create a new authentication controller instance.
     *
     * @param Guard $auth
     * @param AuthController $auth_controller
     */
    public function __construct(Guard $auth, AuthController $auth_controller)
    {
        $this->auth = $auth;
        $this->auth_controller = $auth_controller;
        $this->middleware = $this->auth_controller->getMiddleware();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function login(Request $request)
    {
        //TODO: too many login attempts confirm
        $result = $this->auth_controller->postLogin($request);
        if ($result instanceof RedirectResponse) {
            throw new HttpResponseException($this->buildFailedValidationResponse($request, [
                Lang::has('auth.failed')
                    ? Lang::get('auth.failed')
                    : 'These credentials do not match our records.'
            ]));
        }
        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->auth_controller->postRegister($request);
        $user = $this->auth->user();
        $user->roles()->attach(\App\Role::take(config('entrust.default')));
        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
