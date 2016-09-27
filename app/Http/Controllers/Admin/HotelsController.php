<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Admin\HotelFilterRequest;
use App\Hotel;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class HotelsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PaginateRequest $request)
    {
        return view('admin.hotels')->with('hotels', $this->paginatealbe($request, Hotel::query()));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param HotelFilterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function filter(HotelFilterRequest $request)
    {
        $query = $this->getFilterQuery($request, Hotel::query());

        return view('admin.hotels')->with('hotels', $this->paginatealbe($request, $query, 15));
    }
    
    /**
     * @param HotelFilterRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getFilterQuery(HotelFilterRequest $request, $query)
    {
        if ($request->has('filter.title')) {
            $query->where('title', 'ilike', '%' . $request->filter['title'] . '%');
        }
        if ($request->has('filter.description')) {
            $query->where('description', 'ilike', '%' . $request->filter['description'] . '%');
        }
        if ($request->has('filter.created_at')) {
            $query->where(DB::raw('hotels.created_at::date'), $request->filter['created_at']);
        }
        $request->flash();

        return $query;
    }
    
    public function export(Request $request) {
        
        $rules = ['csv' => 'required']; //|mimetypes:text/csv|mimes:csv
        $messages = [
            'csv.mimetypes' => 'File should be of text/csv mime type'
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            abort(422, implode(', ' , $validator->messages()->get('csv')));
        }
        
        $file = $request->file('csv');
        \Excel::filter('chunk')->load($file->getPathName())->chunk(250, function($results) {
            foreach ($results as $row) {
                // 0 - hotels.com url
                // 1 - title
                // 2 - location lat
                // 3 - location lng
                // 4 - booking.com url
                $rowInfo = explode(',',$row->all()[0]);
                if( !empty($rowInfo[1]) && !Hotel::where('title', $rowInfo[1])->exists() )
                
                Hotel::create([
                    'title'       => $rowInfo[1],
                    'hotels_url'  => $rowInfo[0],
                    'booking_url' => $rowInfo[4],
                    'location'    => [
                        'lat' => $rowInfo[2],
                        'lng' => $rowInfo[3]
                    ]
                ]);
            }
        });
        redirect('hotels');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Hotel  $hotel
     * @return \Illuminate\Http\Response
     */
    public function destroy($hotel)
    {
        $hotel->delete();

        return back();
    }
}
