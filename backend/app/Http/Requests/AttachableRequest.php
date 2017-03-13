<?php

namespace App\Http\Requests;

use App\Services\Attachments;

/**
 * Class AttachableRequest
 * @package App\Http\Requests
 */
trait AttachableRequest
{
    /**
     * @var string Attachments rule
     */
    protected $message_rule = 'required_without_all:' .
    'attachments.album_photos,' .
    'attachments.spots,' .
    'attachments.areas,' .
    'attachments.links';

    /**
     * Generate rules for request with attachments
     *
     * @param array $rules
     * @param string $message_field
     * @return array
     */
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

    /**
     * Sanitize input data before validation
     *
     * @param array $input
     * @return array
     */
    public function sanitize($input)
    {
        if (isset($input['attachments']['links'])) {
            foreach ($input['attachments']['links'] as &$link) {
                if (starts_with($link['image'], '//')) {
                    $link['image'] = substr_replace($link['image'], 'http://', 0, 2);
                }
            }
        }

        return $input;
    }
}
