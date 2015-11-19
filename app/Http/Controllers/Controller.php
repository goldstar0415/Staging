<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Get paginateable data
     *
     * @param Request $request
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return mixed
     */
    public function paginatealbe(Request $request, $query, $limit = null)
    {
        if ($request->has('page') or $request->has('limit') or $limit) {
            if ($limit and !$request->has('limit')) {
                return $query->paginate($limit);
            }

            return $query->paginate((int)$request->get('limit', 10));
        }

        return $query->get();
    }
}
