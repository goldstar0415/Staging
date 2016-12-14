<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use ForceUTF8\Encoding;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Admin\RestaurantFilterRequest;
use App\Http\Requests\Admin\SpotsBulkDeleteRequest;
use App\SpotRestaurant;
use App\SpotAmenity;
use App\SpotTypeCategory;
use App\RemotePhoto;
use App\SpotPoint;
use App\Spot;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\Csv\Reader;

class RestaurantsController extends Controller
{
    
    private $stepCount = 1000;
    
    private $spotFields = [
        'title' => 'Restaurant name',
        'description' => 'description',
        'web_sites' => 'website',
    ];
    
    private $restaurantFields = [
        'Rest_id' => 'remote_id',
        'trip_id' => 'tripadvisor_id',
        'Tripadvisor url' => 'tripadvisor_url',
        'Email' => 'email',
        'Phone Number' => 'phone_number',
        'Trip_Rating' => 'tripadvisor_rating',
        'Price_level' => 'price_level',
        'num_trip_reviews' => 'tripadvisor_reviews_count',
        'Category' => 'category',
        'meals_served' => 'meals_served',
        'country' => 'country',
        'city' => 'city',
        'state' => 'state',
        'Yelp' => 'yelp_url',
        'yelp_rating' => 'yelp_rating',
        'Zomato' => 'zomato_url',
        'Zomato_id' => 'zomato_id',
        'ZomatoRating' => 'zomato_rating',
        'Facebook_URL' => 'facebook_url',
        'facebook_rating' => 'facebook_rating',
        'OpenTableURL' => 'open_table_url',
        'google_pid' => 'google_pid',
        'google_rating' => 'google_rating',
    ];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PaginateRequest $request)
    {
        return view('admin.restaurants.index')->with('restaurants', $this->paginatealbe($request, Spot::restaurants(),15));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param RestaurantFilterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function filter(RestaurantFilterRequest $request)
    {
        $query = $this->getFilterQuery($request, Spot::restaurants());

        return view('admin.restaurants.index')->with('restaurants', $this->paginatealbe($request, $query,15));
    }
    
    /**
     * @param RestaurantFilterRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getFilterQuery(RestaurantFilterRequest $request, $query)
    {
        if ($request->has('filter.title')) {
            $query->where('title', 'ilike', '%' . $request->filter['title'] . '%');
        }
        if ($request->has('filter.description')) {
            $query->where('description', 'ilike', '%' . $request->filter['description'] . '%');
        }
        if ($request->has('filter.created_at')) {
            $query->where(DB::raw('restaurants.created_at::date'), $request->filter['created_at']);
        }
        $request->flash();

        return $query;
    }
    
    public function restaurantsCsvParser()
    {
        return view('admin.restaurants.parser');
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
            $result['endOfParse'] = true;
        }
        else
        {
            config([
                'excel.cache.enable'  => false
                ]);
            $spotTypeCategory = SpotTypeCategory::where('name', 'restaurants')->first();
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
                    
                    if(isset($item['Latitude']) && isset($item['Longitude']))
                    {
                        $item['location'] = [
                            'lat' => $item['Latitude'],
                            'lng' => $item['Longitude']
                        ];
                    }
                    $pictures = isset($item['all_images'])?$item['all_images']:null;
                    unset($item['Latitude']);
                    unset($item['Longitude']);
                    unset($item['all_images']);
                    
                    if(isset($item['Rest_id']) && !empty($item['Rest_id']) )
                    {
                        $spotExists = Spot::where('remote_id', 'yp_' . $item['Rest_id'])->exists();
                        
                        if($spotExists && $updateExisting)
                        {
                            $restaurant = Spot::where('remote_id', 'yp_' . $item['Rest_id'])->first();
                            if(isset($item['website']) && !empty($item['website']))
                            {
                                $restaurant->web_sites = [$item['website']];
                            }
                            unset($item['website']);
                            foreach($this->spotFields as $column => $value)
                            {
                                if(isset($item[$value]))
                                {
                                    $restaurant->$column = $item[$value];
                                }
                            }
                            $restaurant->save();
                            $result['rows_updated']++;
                        }
                        elseif(!$spotExists)
                        {
                            $restaurant = Spot::create([
                                'spot_type_category_id' => $spotTypeCategory->id,
                                'title' => isset($item['Restaurant name']) ? $item['Restaurant name']: '',
                                'web_sites'	=> isset($item['website']) ? [$item['website']] : [],
                                'is_approved' => true,
                                'is_private' => false,
                                'remote_id' => isset($item['Rest_id']) ? 'yp_' . $item['Rest_id']: ''
                            ]);
                            $result['rows_added']++;
                        }
                        
                        if($updateExisting || !$spotExists){
                            $restaurantExists = SpotRestaurant::where('remote_id',  $item['Rest_id'])->first();

                            $restaurantObj = ($restaurantExists) ? $restaurantExists: (new SpotRestaurant);
                            foreach( $this->restaurantFields as $name => $field) {
                                if(isset($item[$name]))
                                    $restaurantObj->$field = $item[$name];
                            }
                            $restaurantObj->spot_id = $restaurant->id;
                            $restaurantObj->save();

                            if( isset($item['location']) && isset($item['Address']) )
                            {
                                $locationExists = SpotPoint::where('spot_id', $restaurant->id)->exists();
                                if($locationExists)
                                {
                                    $restaurant->points()->delete();
                                }
                                $point = new SpotPoint();
                                $point->location = $item['location'];
                                $point->address = $item['Address'];
                                $restaurant->points()->save($point);
                            }

                            if( !empty($pictures) )
                            {
                                
                                $pictureExists = RemotePhoto::where('associated_id', $restaurant->id)->where('associated_type', Spot::class)->exists();
                                if($pictureExists)
                                {
                                    $restaurant->remotePhotos()->delete();
                                }
                                $pictuesObjects = [];
                                $pictures = array_filter(explode(';', $pictures));
                                $needCover = true;
                                foreach($pictures as $picture)
                                {
                                    $image_type = 0;
                                    if($needCover)
                                    {
                                        $image_type = 1;
                                        $needCover = false;
                                    }
                                    $pictuesObjects[] = new RemotePhoto([
                                        'url' => $picture,
                                        'image_type' => $image_type,
                                        'size' => 'original',
                                    ]);
                                }
                                $restaurant->remotePhotos()->saveMany($pictuesObjects);
                            }
                            if(isset($item['features']) && !empty($item['features']))
                            {
                                $features = array_filter(explode(',', $item['features']));
                                foreach($features as $amenity)
                                {
                                    $body = trim($amenity);
                                    if( !SpotAmenity::where('spot_id', $restaurant->id)
                                                     ->where('item', $body)->exists() )
                                    $amenityObject = new SpotAmenity([
                                        'item' => $body,
                                        'spot_id' => $restaurant->id
                                    ]);
                                    $amenityObject->save();
                                }
                                
                            }
                            
                        }
                        $rows[] = $item;
                    }
                    else {
                        $result['messages'][] = 'Restaurant ID missed in string #' . ($rows_parsed_before + $rows_parsed_now + 1);
                    }
                    $rows_parsed_now++;
                }
            }
            if($rows_parsed_now == 0)
            {
                $result['endOfParse'] = true;
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
     * @param  \App\Spot  $restaurant
     * @return \Illuminate\Http\Response
     */
    public function destroy($restaurant)
    {
        $restaurant->delete();
        return back();
    }
    
    public function cleanDb(Request $request)
    {
        Spot::restaurants()->delete();
        return back();
    }
    
    public function bulkDestroy(SpotsBulkDeleteRequest $request) {
        $spots = Spot::whereIn('id', $request->spots)->delete();
        return back();
    }
    
    public function getEdit(Spot $restaurant) {
        $spotFields = array_keys($this->spotFields);
        $restaurantFields = array_diff($this->restaurantFields, ['remote_id']);
        
        return view('admin.restaurants.item')->with([
            'restaurant' => $restaurant,
            'spotFields' => $spotFields,
            'restaurantFields' => $restaurantFields,
        ]);
    }
    
    public function postEdit(Request $request, Spot $restaurant) {
        
        $rules = [
            'title' => 'required|max:255',
            'web_sites' => 'sometimes|array',
            
            'remote_id',
            'tripadvisor_id' => 'max:50',
            'tripadvisor_url' => 'max:255',
            'email' => 'max:50',
            'phone_number' => 'max:50',
            'tripadvisor_rating' => 'max:50',
            'price_level' => 'max:50',
            'num_trip_reviews' => 'max:50',
            'category' => 'max:255',
            'meals_served' => 'max:255',
            'country' => 'max:255',
            'city' => 'max:255',
            'state' => 'max:255',
            'yelp_url' => 'max:255',
            'yelp_rating' => 'max:50',
            'zomato_url' => 'max:255',
            'zomato_id' => 'max:50',
            'zomato_rating' => 'max:50',
            'facebook_url' => 'max:255',
            'zomato_rating' => 'max:50',
            'open_table_url' => 'max:255',
            'google_pid' => 'max:50',
            'google_rating' => 'max:50',
        ];
        
        
        $this->validate($request, $rules);
        $newValues = $request->all();
        foreach(array_keys($this->spotFields) as $field)
        {
            if(isset($newValues[$field])) $restaurant->$field = $newValues[$field];
        }
        $restaurant->save();
        $restaurantAttrObj = $restaurant->restaurant;
        if(!empty($restaurantAttrObj))
        {
            foreach(array_diff($this->restaurantFields, ['remote_id']) as $field)
            {
                if(isset($newValues[$field])) $restaurantAttrObj->$field = $newValues[$field];
            }
            $restaurantAttrObj->save();
        }
        
        return back();
    }
    
}
