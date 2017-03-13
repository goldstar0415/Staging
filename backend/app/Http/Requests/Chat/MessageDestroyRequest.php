<?php

namespace App\Http\Requests\Chat;

use App\Http\Requests\Request;

class MessageDestroyRequest extends Request
{
    private $is_receiver = true;

    /**
     * @return boolean
     */
    public function isReceiver()
    {
        return $this->is_receiver;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $sender_id = $this->route('message')->sender()->first()->id;
        $receiver_id = $this->route('message')->receiver()->first()->id;
        $user_id = $this->user()->id;

        if ($sender_id === $user_id) {
            $this->is_receiver = false;

            return true;
        } elseif ($receiver_id === $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
