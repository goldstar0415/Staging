<?php

use App\ChatMessage;
use App\User;
use Illuminate\Database\Seeder;

class ChatMessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ChatMessage::class, 100)->create()->each(
            function (ChatMessage $message) {
                $users = User::random(2)->get();
                $message->receiver()->attach($users->pop(), ['sender_id' => $users->pop()->id]);
            }
        );
    }
}
