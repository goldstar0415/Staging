<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MailUsersRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Admin\SendMailRequest;
use App\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Mail\Message;

class EmailController extends Controller
{
    /**
     * Display send mail form.
     *
     * @param MailUsersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(MailUsersRequest $request)
    {
        $users = [];
        if ($request->has('users')) {
            $users = User::whereIn('id', $request->users)
                ->get(['id', 'first_name', 'last_name'])->pluck('full_name', 'id')->toArray();
        }

        return view('admin.mail-form')->with('users', $users);
    }

    /**
     * Send mail to chosen users
     *
     * @param SendMailRequest $request
     * @param Mailer $mailer
     * @return \Illuminate\Http\Response
     */
    public function send(SendMailRequest $request, Mailer $mailer)
    {
        User::whereIn('id', $request->users)->get(['email'])->each(function (User $user) use ($request, $mailer) {
            $mailer->send(
                'emails.main',
                ['body' => $request->body],
                function (Message $message) use ($request, $user) {
                    $message->to($user->email);
                    $message->subject($request->subject);
                }
            );
        });

        return back();
    }

    public function users(SearchRequest $request)
    {
        return User::search($request->search_text)->get(['id', 'first_name', 'last_name'])
            ->each(function (User $user) {
                $user->setAppends([]);
        });
    }
}
