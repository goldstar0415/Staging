<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Admin\UsersDeleteRequest;
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
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('sort')) {
            $query->orderBy($request->query('sort'));
        }

        return view('admin.users.index')->with('users', $query->paginate());
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
        $user->fill($request->only(['first_name', 'last_name', 'email']));
        if (!$user->hasRole('admin')) {
            $user->ban_reason = $request->ban_reason;
            $user->banned_at = $request->input('ban');
        }
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

    public function search(SearchRequest $request)
    {
        return view('admin.users.index')->with('users', User::search($request->search_text)->paginate());
    }

    public function bulkDelete(UsersDeleteRequest $request)
    {
        User::destroy($request->users);

        return back();
    }
}
