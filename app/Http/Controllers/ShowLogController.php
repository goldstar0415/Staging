<?php

namespace App\Http\Controllers;

use File;
use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * Class ShowLogController
 * @package App\Http\Controllers
 *
 * Spot import log controller
 */
class ShowLogController extends Controller
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * ShowLogController constructor.
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Show log by the type
     *
     * @param $type
     * @return string
     */
    public function show($type)
    {
        $validator = $this->validator->make(compact('type'), ['type' => 'required|in:event,todo,food,shelter']);

        if ($validator->fails()) {
            abort(403, 'Forbidden');
        }

        $path = storage_path('logs/' . $type . '-import.log');
        if (File::exists($path)) {
            return nl2br(File::get($path));
        }

        return 'No log data';
    }
}
