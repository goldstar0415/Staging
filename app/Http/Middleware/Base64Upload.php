<?php

namespace App\Http\Middleware;

use App\Services\Base64File;
use Closure;

class Base64Upload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param $fields
     * @return mixed
     * @internal param string $field
     */
    public function handle($request, Closure $next, ...$fields)
    {
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $base64_data = $request->input($field);
                if (!empty($base64_data)) {
                    $request->files->add([$field => $this->formatFile($base64_data, $field)]);
                }
            }
        }

        return $next($request);
    }

    protected function formatFile($data, $field)
    {
        $file = null;

        if (is_array($data)) {
            $file = new Base64File($data['data'], $data['name']);
        } else {
            $file = new Base64File($data);
        }

        $file->save();
        return [
            'name' => $file->getName(),
            'type' => $file->getMime(),
            'tmp_name' => $file->getPath(),
            'error' => 0,
            'size' => $file->getSize()
        ];
    }
}
