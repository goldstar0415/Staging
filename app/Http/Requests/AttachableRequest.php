<?php

namespace App\Http\Requests;

use App\Services\Attachments;

trait AttachableRequest
{
    protected $message_rule = 'required_without_all:
    attachments.album_photos,
    attachments.spots,
    attachments.areas,
    attachments.links';

    protected function attachmentsRules($rules, $message_field = 'message')
    {
        return array_merge(
            $rules,
            Attachments::rules($message_field),
            $this->arrayFieldRules('attachments.album_photos', 'integer'),
            $this->arrayFieldRules('attachments.spots', 'integer'),
            $this->arrayFieldRules('attachments.areas', 'integer'),
            $this->arrayFieldRules('attachments.links', [
                'title' => 'required|string|max:255',
                'description' => 'string|max:5000',
                'url' => 'required|url',
                'image' => 'required|url'
            ])
        );
    }
}
