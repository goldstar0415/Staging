<?php

namespace App\Http\Controllers;

use App\ChatMessage;
use App\Events\OnMessage;
use App\Events\OnMessageRead;
use App\Http\Requests\Chat\MessageDestroyRequest;
use App\Http\Requests\Chat\MessageListRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Services\Attachments;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Class ChatController
 * @package App\Http\Controllers
 *
 * Chat controller
 */
class ChatController extends Controller
{
    /**
     * ChatController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Send new message
     *
     * @param SendMessageRequest $request
     * @param Attachments $attachments
     * @return ChatMessage
     */
    public function sendMessage(SendMessageRequest $request, Attachments $attachments)
    {
        $user = $request->user();
        $receiver_id = $request->input('user_id');

        $message = new ChatMessage(['body' => $request->input('message')]);
        $user->chatMessagesSend()->save($message, ['receiver_id' => $receiver_id]);
        $attachments->make($message);

        event(new OnMessage($user, $message, User::find($receiver_id)->random_hash));

        return $message;
    }

    /**
     * Get authenticated user dialogs
     *
     * @param Request $request
     * @return static
     */
    public function getDialogs(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $messages = collect(\DB::select(<<< QUERY
SELECT DISTINCT ON(cm.sender_id+cm.receiver_id) cm.*, m.*
FROM "chat_messages" m
inner join "chat_message_user" cm on m."id" = cm."chat_message_id"
WHERE cm.receiver_id = $user_id AND cm."receiver_deleted_at" is null
/* OR cm.sender_id = $user_id AND cm."sender_deleted_at" is null */
order by cm.sender_id+cm.receiver_id, m.created_at desc
QUERY
        ));

        $dialogs = $messages->map(function ($item, $key) use ($user) {
            $last_user_id = null;
            if ($user->id === $item->receiver_id) {
                $last_user_id = $item->sender_id;
            } else {
                $last_user_id = $item->receiver_id;
            }
            return [
                'user' => User::find($last_user_id),
                'last_message' => [
                    'user_id' => $item->sender_id,
                    'message' => $item->body,
                    'created_at' => $item->created_at,
                    'is_read' => $item->is_read
                ]
            ];
        });

        return $dialogs;
    }

    /**
     * Show list of messages with specific user
     *
     * @param MessageListRequest $request
     * @return mixed
     */
    public function getList(MessageListRequest $request)
    {
        $user_id = (int)$request->get('user_id');
        $my_id = $request->user()->id;
        return $request->user()->chatMessagesSend()
            ->wherePivot('receiver_id', '=', $user_id)->wherePivot('sender_deleted_at')
            ->orWherePivot('sender_id', '=', $user_id)->wherePivot('receiver_deleted_at')
            ->wherePivot('receiver_id', '=', $my_id)
            ->paginate($request->get('limit'));
    }

    /**
     * Remove the specified chat message from storage.
     *
     * @param MessageDestroyRequest $request
     * @param ChatMessage $message
     * @return bool|null
     */
    public function destroy(MessageDestroyRequest $request, $message)
    {
        $result = false;

        if ($request->isReceiver()) {
            $result = $message->deleteForReceiver();
        } else {
            $result = $message->deleteForSender();
        }

        return compact('result');
    }

    /**
     * Remove dialog with specified user
     *
     * @param Request $request
     * @param int $target_id
     * @return array
     */
    public function destroyDialog(Request $request, $target_id)
    {
        $user = $request->user();

        DB::table('chat_message_user')->where('sender_id', $user->id)->where('receiver_id', $target_id)
            ->where('sender_deleted_at')
            ->update(['sender_deleted_at' => Carbon::now()]);

        DB::table('chat_message_user')->where('receiver_id', $user->id)->where('sender_id', $target_id)
            ->where('receiver_deleted_at')
            ->update(['receiver_deleted_at' => Carbon::now()]);

        return ['result' => true];
    }

    /**
     * Send read message event
     *
     * @param Request $request
     * @param $user_id
     * @return array
     */
    public function read(Request $request, $user_id)
    {
        $user = $request->user();
        event(new OnMessageRead($user->id, $user_id));

        return [
            'affected_messages' => $user->chatMessagesReceived()
                ->where('sender_id', $user_id)->where('is_read', false)
                ->update(['is_read' => true])
        ];
    }
}
