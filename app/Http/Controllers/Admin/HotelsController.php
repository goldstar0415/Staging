<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Admin\HotelFilterRequest;
use App\SpotHotel;
use App\SpotTypeCategory;
use App\RemotePhoto;
use App\SpotPoint;
use App\Spot;

use App\Http\Requests;
use App\Http\Controllers\Controller;
//use Box\Spout\Reader\ReaderFactory;
//use Box\Spout\Common\Type;
use App\Services\Csv\Reader;

class HotelsController extends Controller
{
    
    private $stepCount = 1000;
    
    private $spotFields = [
        'title' => 'hotel_name',
        'description' => 'desc_en',
        'web_sites' => 'homepage_url',
    ];
    
    private $hotelFields = [
        'class',
        'hotelscom_url',
        'booking_url',
        'booking_id',
        'booking_num_reviews',
        'booking_rating',
        'booking_rating_10',
        'hotelscom_num_reviews',
        'hotelscom_rating',
        'facebook_url',
        'twitter_url',
        'trip_advisor_url',
        'google_pid',
        'google_rating',
        'maxrate',
        'minrate',
        'nr_rooms',
        'continent_id',
        'country_code',
        'city_hotel',
        'zip',
        'currencycode'
    ];
    
    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PaginateRequest $request)
    {
        $spotTypeCategory = SpotTypeCategory::where('name', 'hotels')->first();
        return view('admin.hotels.index')->with('hotels', $this->paginatealbe($request, Spot::where('spot_type_category_id', $spotTypeCategory->id), 50));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param HotelFilterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function filter(HotelFilterRequest $request)
    {
        $spotTypeCategory = SpotTypeCategory::where('name', 'hotels')->first();
        $query = $this->getFilterQuery($request, Spot::where('spot_type_category_id', $spotTypeCategory->id));

        return view('admin.hotels.index')->with('hotels', $this->paginatealbe($request, $query, 50));
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
        
        $result             = ['success' => true, 'endOfParse' => false, 'messages' => [] ];
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
            $spotTypeCategory = SpotTypeCategory::where('name', 'hotels')->first();
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
                    
                    if(isset($item['latitude']) && isset($item['longitude']))
                    $item['location'] = [
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude']
                    ];
                    $picture = $item['photo_url'];
                    unset($item['latitude']);
                    unset($item['longitude']);
                    unset($item['photo_url']);
                    
                    if(isset($item['booking_id']) && !empty($item['booking_id']) )
                    {
                    
                        if( !SpotHotel::where('booking_id', $item['booking_id'])->exists() )
                        {
                            $hotel = Spot::create([
                                'spot_type_category_id' => $spotTypeCategory->id,
                                'title' => isset($item['hotel_name']) ? $item['hotel_name']: '',
                                'description' => isset($item['desc_en']) ? $item['desc_en']: '',
                                'web_sites'	=> isset($item['homepage_url']) ? [$item['homepage_url']] : [],
                                'is_approved' => true,
                                'is_private' => false,
                                'remote_id' => isset($item['booking_id']) ? 'bk_' . $item['booking_id']: ''
                            ]);

                            $hotelObj = new SpotHotel();
                            foreach( $this->hotelFields as $field) {
                                if(isset($item[$field]))
                                    $hotelObj->$field = $item[$field];
                            }
                            $hotel->hotel()->save($hotelObj);

                            if(isset($item['location']) && isset($item['address']))
                            {
                                $point = new SpotPoint();
                                $point->location = $item['location'];
                                $point->address = $item['address'];
                                $hotel->points()->save($point);
                            }
                        }
                        else
                        {
                            $hotel = Spot::where('remote_id', 'bk_' . $item['booking_id'])->first();
                            if(isset($item['homepage_url']))
                            {
                                $hotel->web_sites = [$item['homepage_url']];
                                unset($item['homepage_url']);
                            }
                            if(isset($item['booking_id']))unset($item['booking_id']);
                            foreach($this->spotFields as $column => $value)
                            {
                                if(isset($item[$value]))
                                {
                                    $hotel->$column = $item[$value];
                                }

                            }
                            $hotel->save();

                            $hotelObj = $hotel->hotel;
                            foreach( $this->hotelFields as $field) {
                                if(isset($item[$field]))
                                    $hotelObj->$field = item[$field];
                            }
                            $hotelObj->save();

                            if(isset($item['location']) && isset($item['address']))
                            {
                                $hotel->points()->delete();
                                $point = new SpotPoint();
                                $point->location = $item['location'];
                                $point->address = $item['address'];
                                $hotel->points()->save($point);
                            }
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
                    }
                    else {
                        $result['messages'][] = 'Booking.com ID missed in string #' . ($rows_parsed_now + 1);
                    }
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
    
    public function cleanDb(Request $request)
    {
        $spotTypeCategory = SpotTypeCategory::where('name', 'hotels')->first();
        
        Spot::where('spot_type_category_id', $spotTypeCategory->id)->delete();
        
        return back();
    }
    
}
