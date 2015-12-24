<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Request;

class SpotOwnerRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return is_null($this->route('spots')->user_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:128',
            'email' => 'required|email|max:128',
            'phone' => 'required|string|max:128',
            'address' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'text' => 'string|max:5000'
        ];
    }

    public function sanitize(array $data)
    {
        if (isset($data['url'])) {
            $url = parse_url($data['url']);
            if (!isset($url['scheme'])) {
                $url = 'http://' . $url['path'];
            }

            $url = $url['scheme'] . '://' . $url['host'];
            $data['url'] = $url;
        }

        return $data;
    }
}
