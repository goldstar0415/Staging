<?php

namespace App\Http\Controllers\Admin;

use App\Spot;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.users.index')->with('users', User::query()->paginate());
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $user)
    {
        return view('admin.users.show', [
            'user' => $user,
            'spots' => $user->spots()->paginate()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $user
     * @return \Illuminate\Http\Response
     * @internal param int $user
     */
    public function edit($user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user)
    {
        $user->fill($request->only(['first_name', 'last_name', 'email', 'ban_reason']));
        $user->banned_at = $request->input('ban');
        $user->roles()->sync($request->input('roles'));
        $user->save();

        return back()->with('status', 1);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($user)
    {
        $user->delete();

        return redirect()->route('admin.users.index');
    }
}
