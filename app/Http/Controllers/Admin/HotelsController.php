<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Admin\HotelFilterRequest;
use App\Http\Requests\Admin\SpotsBulkDeleteRequest;
use App\SpotHotel;
use App\SpotTypeCategory;
use App\RemotePhoto;
use App\SpotPoint;
use App\Spot;

use App\Http\Controllers\Controller;
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
        return view('admin.hotels.index')->with('hotels', $this->paginatealbe($request, Spot::hotels(),15));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param HotelFilterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function filter(HotelFilterRequest $request)
    {
        $query = $this->getFilterQuery($request, Spot::hotels());

        return view('admin.hotels.index')->with('hotels', $this->paginatealbe($request, $query,15));
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
        $updateExisting     = (int)$request->update;
        $result['update']   = $updateExisting;
        $result['rows_added'] = 0;
        $result['rows_updated'] = 0;
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
            config([
                'excel.cache.enable'  => false
                ]);
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
                    if($rows_parsed_now < $stepCount)
                    {
                        $result['file_offset'] = $reader->getFilePointerOffset();
                    }
                    if($rows_parsed_now >= $stepCount)
                    {
                        break;
                    }
                    $item = [];
                    foreach($headers as $title => $index) {
                        $item[$title] = false;
                        foreach(mb_detect_order() as $encoding)
                        {
                            $str = mb_convert_encoding($row[$index], "UTF-8", $encoding);
                            if(stristr($str, '?') === FALSE) {
                                $item[$title] = $str;
                                break;
                            }
                        }
                        if( !$item[$title] )
                        {
                            $item[$title] = mb_convert_encoding($row[$index], "UTF-8", "ISO-8859-16");
                        }
                    }
                    
                    
                    if(isset($item['latitude']) && isset($item['longitude']))
                    $item['location'] = [
                        'lat' => $item['latitude'],
                        'lng' => $item['longitude']
                    ];
                    $picture = isset($item['photo_url'])?$item['photo_url']:null;
                    unset($item['latitude'], $item['longitude'], $item['photo_url']);
                    
                    if(isset($item['booking_id']) && !empty($item['booking_id']) )
                    {
                        $spotExists = Spot::where('remote_id', 'bk_' . $item['booking_id'])->exists();
                        
                        if($spotExists && $updateExisting)
                        {
                            $hotel = Spot::where('remote_id', 'bk_' . $item['booking_id'])->first();
                            if(isset($item['homepage_url']) && !empty($item['homepage_url']))
                            {
                                $hotel->web_sites = [$item['homepage_url']];
                            }
                            unset($item['homepage_url']);
                            foreach($this->spotFields as $column => $value)
                            {
                                if(isset($item[$value]))
                                {
                                    $hotel->$column = $item[$value];
                                }
                            }
                            $hotel->save();
                            $result['rows_updated']++;
                        }
                        elseif(!$spotExists)
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
                            $result['rows_added']++;
                        }
                        
                        if($updateExisting || !$spotExists){
                            $hotelExists = SpotHotel::where('booking_id',  $item['booking_id'])->first();

                            $hotelObj = ($hotelExists) ? $hotelExists: (new SpotHotel);
                            foreach( $this->hotelFields as $field) {
                                if(isset($item[$field]))
                                    $hotelObj->$field = $item[$field];
                            }
                            $hotelObj->spot_id = $hotel->id;
                            $hotelObj->save();

                            if( isset($item['location']) && isset($item['address']) )
                            {
                                $locationExists = SpotPoint::where('spot_id', $hotel->id)->exists();
                                if($locationExists)
                                {
                                    $hotel->points()->delete();
                                }
                                $point = new SpotPoint();
                                $point->location = $item['location'];
                                $point->address = $item['address'];
                                $hotel->points()->save($point);
                            }

                            if( !empty($picture) )
                            {
                                $pictureExists = RemotePhoto::where('associated_id', $hotel->id)->where('associated_type', Spot::class)->exists();
                                if($pictureExists)
                                {
                                    $hotel->remotePhotos()->delete();
                                }
                                $pic = new RemotePhoto([
                                    'url' => $picture,
                                    'image_type' => 0,
                                    'size' => 'original',
                                ]);
                                $hotel->remotePhotos()->save($pic);
                            }
                        }
                        $rows[] = $item;
                    }
                    else {
                        $result['messages'][] = 'Booking.com ID missed in string #' . ($rows_parsed_before + $rows_parsed_now + 1);
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
        Spot::hotels()->delete();
        return back();
    }
    
    public function bulkDestroy(SpotsBulkDeleteRequest $request) {
        $spots = Spot::whereIn('id', $request->spots)->delete();
        return back();
    }
    
    public function getEdit(Spot $hotel) {
        $spotFields = array_keys($this->spotFields);
        $hotelFields = array_diff($this->hotelFields, ['booking_id']);
        
        return view('admin.hotels.item')->with([
            'hotel' => $hotel,
            'spotFields' => $spotFields,
            'hotelFields' => $hotelFields,
        ]);
    }
    
    public function postEdit(Request $request, Spot $hotel) {
        
        $rules = [
            'title' => 'required|max:255',
            'description' => 'required|max:2000',
            'web_sites' => 'sometimes|array',
            
            'class' => 'max:50',
            'hotelscom_url' => 'max:255',
            'booking_url' => 'max:255',
            'booking_num_reviews' => 'max:255',
            'booking_rating' => 'max:255',
            'booking_rating_10' => 'max:255',
            'hotelscom_num_reviews' => 'max:255',
            'hotelscom_rating' => 'max:255',
            'facebook_url' => 'max:255',
            'twitter_url' => 'max:255',
            'trip_advisor_url' => 'max:255',
            'google_pid' => 'max:255',
            'google_rating' => 'max:20',
            'maxrate' => 'max:20',
            'minrate' => 'max:20',
            'nr_rooms' => 'max:20',
            'continent_id' => 'max:20',
            'country_code' => 'max:20',
            'city_hotel' => 'max:100',
            'zip' => 'max:20',
            'currencycode' => 'max:20'
        ];
        
        
        $this->validate($request, $rules);
        $newValues = $request->all();
        foreach(array_keys($this->spotFields) as $field)
        {
            if(isset($newValues[$field])) $hotel->$field = $newValues[$field];
        }
        $hotel->save();
        $hotelAttrObj = $hotel->hotel;
        if(!empty($hotelAttrObj))
        {
            foreach(array_diff($this->hotelFields, ['booking_id']) as $field)
            {
                if(isset($newValues[$field])) $hotelAttrObj->$field = $newValues[$field];
            }
            $hotelAttrObj->save();
        }
        
        return back();
    }
    
}
