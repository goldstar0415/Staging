<?php

namespace App\Http\Requests;

/**
 * Class WeatherRequest
 * @package App\Http\Requests
 *
 * @deprecated Use app/Http/Requests/Weather/OpenWeatherMapRequest.php instead
 */
class WeatherRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lat' => 'required|latitude',
            'lng' => 'required|longitude'
        ];
    }
}
