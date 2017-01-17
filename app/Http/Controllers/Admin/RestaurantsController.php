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
use App\SpotType;
use App\RemotePhoto;
use App\SpotPoint;
use App\Spot;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\Csv\Reader;
use App\Services\Csv\Helper;

class RestaurantsController extends Controller
{
    
    private $stepCount = 1000;
    private $prefix = 'yp_';
    
    private $categoryName = 'restaurants';
    private $categoryId = null;
    private $updateExisting = false;
    
    private $spotFields = [
        'title' => 'Restaurant name',
        'description' => 'description',
        'web_sites' => 'website',
        'avg_rating' => 'avg_rating',
        'total_reviews' => 'total_reviews'
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
    
    private $massFields = [
        'tags',
        'all_images',
        'features'
    ];
    
    private $updateRules = [
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
    
    public function bulkDestroy(SpotsBulkDeleteRequest $request) 
    {
        $spots = Spot::whereIn('id', $request->spots)->delete();
        return back();
    }
    
    public function getEdit(Spot $restaurant) 
    {
        $spotFields = array_keys($this->spotFields);
        $restaurantFields = array_diff($this->restaurantFields, ['remote_id']);
        return view('admin.restaurants.item')->with([
            'restaurant' => $restaurant,
            'spotFields' => $spotFields,
            'restaurantFields' => $restaurantFields,
        ]);
    }
    
    public function postEdit(Request $request, Spot $restaurant) 
    {
        $rules = $this->updateRules;
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
    
    public function restaurantsCsvParser()
    {
        $fieldsArr = array_merge( 
                array_keys($this->spotFields),
                array_diff(array_values($this->restaurantFields), ['remote_id']),
                $this->massFields
                );
        $fields = [];
        foreach($fieldsArr as $value)
        {
            $fields[$value] = $value;
        }
        return view('admin.restaurants.parser', ['fields' => $fields, 'categories' => SpotType::categoriesList('food')]);
    }
    
    public function exportUpload(Request $request)
    {
        return Helper::uploadCsv($request);
    }
    
    public function export( Request $request ) 
    {    
        $pref               = $this->prefix;
        $path               = $request->path;
        $stepCount          = $this->stepCount;
        $total_rows         = $request->total_rows;
        $updateExisting     = $this->updateExisting = (int)$request->update;
        $rows_parsed_before = $request->rows_parsed;
        $file_offset        = $request->file_offset;
        $headers            = $request->input('headers', []);
        $this->categoryId   = $request->input('category', null);
        $result             = [
            'success'       => true, 
            'endOfParse'    => false, 
            'messages'      => [],
            'update'        => $updateExisting,
            'rows_added'    => 0,
            'rows_updated'  => 0,
            'old_offset'    => $file_offset
        ];
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
                    $item = $this->convertColumns($headers, $row);
                    if( !empty($item['Rest_id']) )
                    {
                        $spot = DB::table('spots')
                                ->select('id')
                                ->where('remote_id', $pref . $item['Rest_id'])
                                ->first();
                        $spotExists = !empty($spot->id);
                        $spot_id = $spotExists?$spot->id:null;
                        $saveSpot = $this->saveSpot($spot_id, $spotExists, $item, $result);
                        $spot_id = ($saveSpot)?$saveSpot:$spot_id;
                        
                        if($updateExisting || !$spotExists)
                        {
                            $this->saveRestaurantObject($spot_id, $item);
                            $this->saveLocation($spot_id, $item);
                            $this->savePhoto($spot_id, $item);
                            $this->saveTags($spot_id, $item);
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
    
    public function updateField( Request $request)
    {
        $pref               = $this->prefix;
        $stepCount          = $this->stepCount;
        $updateExisting     = (int)$request->update;
        $file_offset        = $request->file_offset;
        $path               = $request->path;
        $total_rows         = $request->total_rows;
        $rows_parsed_before = $request->rows_parsed;
        $field              = $request->field;
        $result             = [
            'success'       => true, 
            'endOfParse'    => false, 
            'messages'      => [],
            'update'        => $updateExisting,
            'rows_added'    => 0,
            'rows_updated'  => 0,
            'old_offset'    => $file_offset
        ];
        $reader             = new Reader();
        $reader->setOffset($file_offset);
        $reader->open($path);
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
            foreach ($reader->getSheetIterator() as $sheet)
            {
                foreach ($sheet->getRowIterator() as $row) 
                {
                    if($rows_parsed_now < $stepCount)
                    {
                        $result['file_offset'] = $reader->getFilePointerOffset();
                    }
                    if($rows_parsed_now >= $stepCount)
                    {
                        break;
                    }
                    list($remote_id, $value) = $row;
                    if(in_array($field, array_keys($this->spotFields)))
                    {
                        $query = Spot::where('remote_id', $pref . $remote_id);
                        if($field == 'web_sites')
                        {
                            $query->update([$field => "[\"$value\"]"]);
                        }
                        else
                        {
                            $query->update([$field => $value]);
                        }
                    }
                    elseif(in_array($field, array_diff(array_values($this->restaurantFields), ['remote_id'])))
                    {
                        SpotToDo::where('remote_id', $remote_id)->update([$field => $value]);
                    }
                    elseif(in_array($field, $this->massFields))
                    {
                        $spot = Spot::where('remote_id', $pref . $remote_id)->first();
                        switch($field)
                        {
                            case 'tags':
                                $this->saveTags($spot->id, [$field => $value]);
                                break;
                            case 'all_images':
                                $this->savePhoto($spot->id, [$field => $value]);
                                break;
                            case 'features':
                                $this->saveFeatures($spot->id, [$field => $value]);
                                break;
                        }
                    }
                    $rows_parsed_now++;
                }
            }
        }
        $result['rows']                 = $rows;
        $result['rows_parsed']          = $rows_parsed_before + $rows_parsed_now;
        $result['rows_parsed_now']      = $rows_parsed_now;
        header('Content-Type: text/html;charset=utf-8');
        $result = json_encode($result);
        return $result;
    }
    
    protected function saveSpot($spot_id, $spotExists, $item, $result)
    {
        $attrArr = [];
        $category_id = $this->getCategoryId();
        foreach($this->spotFields as $column => $value)
        {
            if( !empty($item[$value]))
            {
                if( $column == 'web_sites')
                {
                    $attrArr[$column] = json_encode([$item[$value]]);
                }
                else
                {
                    $attrArr[$column] = $item[$value];
                }
            }
        }
        if($spotExists && $this->updateExisting)
        {
            DB::table('spots')
                    ->where('remote_id', $this->prefix . $item['Rest_id'])
                    ->update($attrArr);
            $result['rows_updated']++;
            return $spot_id;
        }
        elseif(!$spotExists)
        {
            $date = date('Y-m-d H:i:s');
            $attrArr['is_approved'] = true;
            $attrArr['is_private'] = false;
            $attrArr['spot_type_category_id'] = $this->getCategoryId();
            $attrArr['created_at'] = $date;
            $attrArr['updated_at'] = $date;
            $attrArr['remote_id'] = $this->prefix . $item['Rest_id'];
            $result['rows_added']++;
            return DB::table('spots')
                    ->insertGetId($attrArr);
        }
    }
    
    protected function saveLocation($spot_id, $item)
    {
        if(isset($item['Latitude']) && isset($item['Longitude']))
        {
            $item['location'] = [
                'lat' => $item['Latitude'],
                'lng' => $item['Longitude'],
            ];
        }
        unset($item['Latitude']);
        unset($item['Longitude']);
        if( isset($item['location']) && isset($item['Address']) )
        {
            SpotPoint::where('spot_id', $spot_id)->delete();
            $point = new SpotPoint();
            $point->location = $item['location'];
            $point->address = $item['Address'];
            $point->spot_id = $spot_id;
            $point->save();
        }
    }
    
    protected function savePhotos($spot_id, $item)
    {
        $pictures = isset($item['all_images'])?$item['all_images']:null;
        unset($item['all_images']);
        if( !empty($pictures) && $spot_id )
        {
            DB::table('remote_photos')
                    ->where('associated_type', Spot::class)
                    ->where('associated_id', $spot_id)
                    ->delete();
            $pictuesObjects = [];
            $pictuesArr = [];
            $pictures = array_filter(explode(';', $pictures));
            $needCover = true;
            $date = date('Y-m-d H:i:s');
            foreach($pictures as $picture)
            {
                $image_type = 0;
                if($needCover)
                {
                    $image_type = 1;
                    $needCover = false;
                }
                $pictuesArr[] = [
                    'url' => $picture,
                    'image_type' => $image_type,
                    'size' => 'original',
                    'associated_type' => Spot::class,
                    'associated_id' => $spot_id,
                    'created_at' => $date,
                    'updated_at' => $date
                ];
            }
            DB::table('remote_photos')
                    ->insert($pictuesArr);
        }
    }
    
    protected function saveTags($spot_id, $item)
    {
        if( !empty($item['tags']) && $spot_id)
        {
            DB::table('spot_tag')->where('spot_id', $spot_id)->delete();
            $tags = explode(';', $item['tags']);
            $idsArr = [];
            $result = [];
            $existingTags = [];
            $tagsCollection = DB::table('tags')->whereIn('name', $tags)->get();
            foreach($tagsCollection as $tagObj)
            {
                $idsArr[] = $tagObj->id;
                $existingTags[] = $tagObj->name;
            }
            $tags = array_diff($tags, $existingTags);
            foreach($tags as $tag)
            {
                $idsArr[] = DB::table('tags')->insertGetId(['name' => $tag]);
            }
            foreach($idsArr as $id)
            {
                $result[] = ['spot_id' => $spot_id, 'tag_id' => $id];
            }
            DB::table('spot_tag')->insert($result);
        }
    }
    
    protected function saveFeatures($spot_id, $item)
    {
        if(!empty($item['features']))
        {
            $features = array_filter(explode(',', $item['features']));
            foreach($features as $amenity)
            {
                $body = trim($amenity);
                if( !SpotAmenity::where('spot_id', $spot_id)
                                 ->where('item', $body)->exists() )
                {
                    SpotAmenity::insert([
                        'item' => $body,
                        'spot_id' => $spot_id
                    ]);
                }
            }
        }
    }
    
    protected function convertColumns($headers, $row)
    {
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
        return $item;
    }
    
    protected function saveRestaurantObject($spot_id, $item)
    {
        $obj = DB::table('spot_restaurants')
                                ->select('id')
                                ->where('remote_id', $item['Rest_id'])
                                ->first();
        $attrArr = [];
        foreach( $this->restaurantFields as $name => $field) {
            if(isset($item[$name]))
            {
                $attrArr[$field] = $item[$name];
            }
        }
        if(!empty($obj->id))
        {
            DB::table('spot_restaurants')
                    ->where('remote_id', $item['Rest_id'])
                    ->update($attrArr);
        }
        else 
        {
            $date = date('Y-m-d H:i:s');
            $attrArr['remote_id'] = $item['Rest_id'];
            $attrArr['spot_id'] = $spot_id;
            $attrArr['created_at'] = $date;
            $attrArr['updated_at'] = $date;
            DB::table('spot_restaurants')
                    ->insert($attrArr);
        }
    }
    
    private function getCategoryId()
    {
        if(!empty($this->categoryId))
        {
            return $this->categoryId;
        }
        $category = DB::table('spot_type_categories')
                ->select('id')
                ->where('name', $this->categoryName)
                ->first();
        return $this->categoryId = (!empty($category->id))?$category->id:null;
    }
    
}
