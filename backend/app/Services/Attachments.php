<?php

namespace App\Services;

use App\Link;
use Illuminate\Http\Request;

/**
 * Class Attachments
 * @package App\Services
 */
class Attachments
{
    /**
     * @var Request Request with attachments
     */
    protected $request;

    /**
     * @var array Validation rules for attachments
     */
    protected static $rules = [
        'attachments.album_photos' => [
            'required_without_all:' .
            '{message},' .
            'attachments.spots,' .
            'attachments.areas,' .
            'attachments.plans,' .
            'attachments.links',
            'array',
            'count_max:10'
        ],
        'attachments.spots' => [
            'required_without_all:' .
            '{message},' .
            'attachments.album_photos,' .
            'attachments.areas,attachments.plans,' .
            'attachments.links',
            'array',
            'count_max:10'
        ],
        'attachments.areas' => [
            'required_without_all:' .
            '{message},' .
            'attachments.album_photos,' .
            'attachments.spots,' .
            'attachments.plans,' .
            'attachments.links',
            'array',
            'count_max:10'
        ],
        'attachments.plans' => [
            'required_without_all:' .
            '{message},' .
            'attachments.album_photos,' .
            'attachments.spots,' .
            'attachments.areas,' .
            'attachments.links',
            'array',
            'count_max:10'
        ],
        'attachments.links' => [
            'required_without_all:' .
            '{message},' .
            'attachments.album_photos,' .
            'attachments.spots,' .
            'attachments.areas,' .
            'attachments.plans',
            'array',
            'count_max:10'
        ]
    ];

    /**
     * Attachments constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Save attachments for the model
     *
     * @param $model
     */
    public function make($model)
    {
        if ($this->request->has('attachments.album_photos')) {
            $model->albumPhotos()->sync($this->request->input('attachments.album_photos'));
        }
        if ($this->request->has('attachments.spots')) {
            $model->spots()->sync($this->request->input('attachments.spots'));
        }
        if ($this->request->has('attachments.areas')) {
            $model->areas()->sync($this->request->input('attachments.areas'));
        }
        if ($this->request->has('attachments.plans')) {
            $model->plans()->sync($this->request->input('attachments.plans'));
        }
        if ($this->request->has('attachments.links')) {
            $model->links()->delete();
            $links = $this->request->input('attachments.links');
            foreach ($links as $link) {
                $link_model = new Link($link);
                $model->links()->save($link_model);
            }
        }
    }

    /**
     * Generate validation rules for attachments
     *
     * @param string $message_field
     * @return array
     */
    public static function rules($message_field = 'message')
    {
        $rules = self::$rules;
        foreach ($rules as &$rule) {
            $rule[0] = str_replace('{message}', $message_field, $rule[0]);
        }

        return $rules;
    }
}
