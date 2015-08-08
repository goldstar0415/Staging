<?php

namespace App\Http\Controllers;

use App\ChatMessage;
use App\Events\OnMessage;
use App\Events\OnMessageRead;
use App\Http\Requests\Chat\MessageDestroyRequest;
use App\Http\Requests\Chat\MessageListRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $user = $request->user();
        $receiver_id = $request->input('user_id');

        $message = new ChatMessage(['body' => $request->input('message')]);
        $user->chatMessagesSend()->save($message, ['receiver_id' => $receiver_id]);
        if ($request->has('attachments.album_photos')) {
            $message->albumPhotos()->sync($request->input('attachments.album_photos'));
        }
        if ($request->has('attachments.spots')) {
            $message->spots()->sync($request->input('attachments.spots'));
        }
        if ($request->has('attachments.areas')) {
            $message->areas()->sync($request->input('attachments.areas'));
        }

        event(new OnMessage($user, $message, User::find($receiver_id)->random_hash));

        $message->setAttribute(
            'pivot',
            [
                'sender_id' => $user->id,
                'receiver_id' => $receiver_id,
                'chat_message_id' => $message->id
            ]
        );

        return $message;
    }

    public function getDialogs(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $messages = collect(\DB::select(<<< QUERY
SELECT DISTINCT ON(cm.sender_id+cm.receiver_id) cm.*, m.*
FROM "chat_messages" m
inner join "chat_message_user" cm on m."id" = cm."chat_message_id"
WHERE  cm.receiver_id = $user_id OR cm.sender_id = $user_id
order by cm.sender_id+cm.receiver_id, m.created_at desc
QUERY
        ));//TODO: optimize

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

    public function getList(MessageListRequest $request)
    {
        $user_id = $request->get('user_id');
        $my_id = $request->user()->id;
        return $request->user()->chatMessages()->where(function ($query) use ($user_id, $my_id) {
            $query->where(function ($query) use ($user_id, $my_id) {
                $query->where('receiver_id', $user_id)->where('sender_id', $my_id);
            })
                ->orWhere(function ($query) use ($user_id, $my_id) {
                    $query->where('sender_id', $my_id)->where('receiver_id', $user_id);
                });
        })->paginate($request->get('limit'));
    }

    /**
     * @param MessageDestroyRequest $request
     * @param ChatMessage $message
     * @return bool|null
     */
    public function destroy(MessageDestroyRequest $request, $message)
    {
        return ['result' => $message->delete()];
    }

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
