<?php

namespace App\Http\Middleware;

use App\Services\Base64File;
use Closure;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Base64Upload
 * Middleware provide to upload files in base64 format like simple files from multipart/form-data
 *
 * @package App\Http\Middleware
 */
class Base64Upload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param mixed... $fields
     *
     * @return mixed
     */
    public function handle($request, Closure $next, ...$fields)
    {
        //Walk through fields with base64 data
        //Fields got from middleware parameters
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $base64_data = $request->input($field);
                if (!empty($base64_data)) {
                    $request->files->add([$field => $this->formatFile($base64_data)]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Make UploadedFile from base64 data
     *
     * @param string $data Base64 data
     * @return UploadedFile
     */
    protected function formatFile($data)
    {
        $file = null;

        if (is_array($data)) {
            $file = new Base64File($data['data'], $data['name']);
        } else {
            $file = new Base64File($data);
        }

        $file->save();

        return new UploadedFile($file->getPath(), $file->getName(), $file->getMime(), $file->getSize(), 0, true);
    }
}
