<?php

namespace App\Http\Requests;

use App\AlbumPhoto;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class CommentsRequest
 * @package App\Http\Requests
 */
class CommentsRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Guard $auth
     * @return bool
     */
    public function authorize(Guard $auth)
    {
        $comment = $this->route('comments');

        return $this->user()->hasRole('admin') or
                $comment->user_id === $auth->id() or
                $comment->commentable->commentResourceOwnerId() === $auth->id();
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
