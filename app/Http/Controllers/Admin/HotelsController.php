<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Admin\HotelFilterRequest;
use App\Hotel;
use App\RemotePhoto;

use App\Http\Requests;
use App\Http\Controllers\Controller;
//use Box\Spout\Reader\ReaderFactory;
//use Box\Spout\Common\Type;
use App\Services\Csv\Reader;

class HotelsController extends Controller
{
    
    private $stepCount = 1000;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PaginateRequest $request)
    {
        return view('admin.hotels.index')->with('hotels', $this->paginatealbe($request, Hotel::query(), 50));
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

        return view('admin.hotels.index')->with('hotels', $this->paginatealbe($request, $query, 50));
    }
    
    /**
     * @param HotelFilterRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getFilterQuery(HotelFilterRequest $request, $query)
    {
        if ($request->has('filter.hotel_name')) {
            $query->where('hotel_name', 'ilike', '%' . $request->filter['hotel_name'] . '%');
        }
        if ($request->has('filter.desc_en')) {
            $query->where('desc_en', 'ilike', '%' . $request->filter['desc_en'] . '%');
        }
        if ($request->has('filter.created_at')) {
            $query->where(DB::raw('hotels.created_at::date'), $request->filter['created_at']);
        }
        $request->flash();

        return $query;
    }
    
    public function hotelsCsvParser()
    {
        return view('admin.hotels.parser');
    }
    
    public function exportUpload(Request $request)
    {
        $rules = ['csv' => 'required']; //|mimetypes:text/csv|mimes:csv
        $result = ['success' => true];
        $messages = [
            'csv.required'  => 'CSV file required',
            'csv.mimetypes' => 'File should be of text/csv mime type'
        ];
        
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) 
        {
            $result['success'] = false;
            $result['data']    = $validator->messages()->get('csv');
        }
        else 
        {
            $filename =  $request->csv->getClientOriginalName();
            $path     = $request->csv->move(storage_path() . '/csvs/', $filename );
            if( $request->csv->getClientOriginalExtension() != 'csv' )
            {
                unlink($path->getPathName());
                $result['success'] = false;
                $result['data'][] = 'File should be .csv';
            }
            else 
            {
                $result['data']['path'] = $path->getPathName();
                $result['data']['filename'] = $filename;
                config([
                    'excel.csv.delimiter' => ',',
                    'excel.cache.enable'  => false
                    ]);
                $startRow = 1;
                
                $headers = [];
                
                $reader = new Reader();
                $reader->open($result['data']['path']);
                $count = 0;
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $count++;
                    }
                }
                $reader->close();
                    
                $result['data']['count'] = $count;
            }
        }
        
        return json_encode($result);
        
    }
    
    public function export( Request $request ) 
    {    
        
        $result             = ['success' => true, 'endOfParse' => false];
        $path               = $request->path;
        $stepCount          = $this->stepCount;
        $total_rows         = $request->total_rows;
        $rows_parsed_before = $request->rows_parsed;
        $file_offset        = $request->file_offset;
        $headers            = $request->input('headers', []);
        $result['old_offset'] = $file_offset;
        $reader             = new Reader();
        $reader->setOffset($file_offset);
        $reader->open($path);
        $isFirstRow         = ($file_offset == 0)?true:false;
        $rows               = [];
        $rows_parsed_now    = 0;
        if($total_rows == $rows_parsed_before)
        {
            $result['endOfParse']       = true;
        }
        else
        {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if($isFirstRow)
                    {
                        $headers = array_flip($row);
                        $result['headers'] = $headers;
                        $isFirstRow = false;
                        continue;
                    }
                    if($rows_parsed_now == $stepCount-1)
                    {
                        $result['file_offset'] = $reader->getFilePointerOffset();
                    }
                    if($rows_parsed_now >= $stepCount)
                    {
                        break;
                    }
                    $item = [];
                    foreach($headers as $title => $index) {
                        $item[$title] = mb_convert_encoding($row[$index], "UTF-8", "ISO-8859-16");
                        //$item[$title] = $row[$index];
                    }
                    $rows[] = $item;
                    
                    $item['location'] = [
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude']
                    ];
                    $picture = $item['photo_url'];
                    unset($item['latitude']);
                    unset($item['longitude']);
                    unset($item['photo_url']);
                    
                    if( !Hotel::where('booking_id', $item['booking_id'])->exists() )
                    {
                        $hotel = Hotel::create($item);
                    }
                    else
                    {
                        $hotel = Hotel::where('booking_id', $item['booking_id'])->first();
                        foreach($item as $column => $value)
                        {
                            $hotel->$column = $value;
                        }
                        $hotel->save();
                    }
                    if(!empty($picture))
                    {
                        $pic = new RemotePhoto([
                            'url' => $picture,
                            'image_type' => 0,
                            'size' => 'original',
                        ]);
                        $hotel->remotePhotos()->save($pic);
                        unset($pic);
                    }
                    unset($picture);
                    $rows_parsed_now++;
                }
            }
            if($rows_parsed_now == 0)
            {
                $result['endOfParse']       = true;
            }
            $reader->close();
        }
        
        $result['rows']                 = $rows;
        $result['rows_parsed']          = $rows_parsed_before + $rows_parsed_now;
        $result['rows_parsed_now']      = $rows_parsed_now;
                
        header('Content-Type: text/html;charset=utf-8');
        $result = json_encode($result);
        return $result;
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
