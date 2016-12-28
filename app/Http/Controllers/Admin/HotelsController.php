<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Admin\HotelFilterRequest;
use App\Http\Requests\Admin\SpotsBulkDeleteRequest;
use App\SpotHotel;
use App\SpotTypeCategory;
use App\SpotType;
use App\RemotePhoto;
use App\SpotPoint;
use App\Spot;

use App\Http\Controllers\Controller;
use App\Services\Csv\Reader;
use App\Services\Csv\Helper;
use DB;

class HotelsController extends Controller
{
    private $prefix = 'bk_';
    private $stepCount = 1000;
    
    private $categoryName = 'hotels';
    private $categoryId = null;
    private $updateExisting = false;
    
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
    
    private $massFields = [
        'photo_url',
        'tags'
    ];
    
    private $updateRules = [
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
        $fieldsArr = array_merge( 
                array_keys($this->spotFields),
                $this->hotelFields,
                $this->massFields
                );
        $fields = [];
        foreach($fieldsArr as $value)
        {
            $fields[$value] = $value;
        }
        return view('admin.hotels.parser', ['fields' => $fields, 'categories' => SpotType::categoriesList('shelter')]);
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
    
    public function bulkDestroy(SpotsBulkDeleteRequest $request) 
    {
        $spots = Spot::whereIn('id', $request->spots)->delete();
        return back();
    }
    
    public function getEdit(Spot $hotel) 
    {
        $spotFields = array_keys($this->spotFields);
        $hotelFields = array_diff($this->hotelFields, ['booking_id']);
        
        return view('admin.hotels.item')->with([
            'hotel' => $hotel,
            'spotFields' => $spotFields,
            'hotelFields' => $hotelFields,
        ]);
    }
    
    public function postEdit(Request $request, Spot $hotel) 
    {
        $rules = $this->updateRules;
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
            $result['endOfParse']       = true;
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
                    if( !empty($item['booking_id']) )
                    {
                        $spot = DB::table('spots')
                                ->select('id')
                                ->where('remote_id', $pref . $item['booking_id'])
                                ->first();
                        $spotExists = !empty($spot->id);
                        $spot_id = $spotExists?$spot->id:null;
                        $saveSpot = $this->saveSpot($spot_id, $spotExists, $item, $result);
                        $spot_id = ($saveSpot)?$saveSpot:$spot_id;
                        
                        if($updateExisting || !$spotExists)
                        {
                            $this->saveHotelObject($spot_id, $item);
                            $this->saveLocation($spot_id, $item);
                            $this->savePhoto($spot_id, $item);
                            $this->saveTags($spot_id, $item);
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
                    elseif(in_array($field, array_diff($this->hotelFields, ['remote_id'])))
                    {
                        SpotToDo::where('remote_id', $remote_id)->update([$field => $value]);
                    }
                    elseif(in_array($field, $this->massFields))
                    {
                        $spot = Spot::where('remote_id', $pref . $remote_id)->first();
                        if($field == 'tags'){
                            $this->saveTags($spot->id, [$field => $value]);
                        }
                        else
                        {
                            $this->savePhoto($spot->id, [$field => $value]);
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
                    ->where('remote_id', $this->prefix . $item['booking_id'])
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
            $attrArr['remote_id'] = $this->prefix . $item['booking_id'];
            $result['rows_added']++;
            return DB::table('spots')
                    ->insertGetId($attrArr);
        }
    }
    
    protected function saveLocation($spot_id, $item)
    {
        if(isset($item['latitude']) && isset($item['longitude']))
        {
            $item['location'] = [
                'lat' => $item['latitude'],
                'lng' => $item['longitude'],
            ];
        }
        unset($item['latitude']);
        unset($item['longitude']);
        if( isset($item['location']) && isset($item['address']) )
        {
            SpotPoint::where('spot_id', $spot_id)->delete();
            $point = new SpotPoint();
            $point->location = $item['location'];
            $point->address = $item['address'];
            $point->spot_id = $spot_id;
            $point->save();
        }
    }
    
    protected function savePhoto($spot_id, $item)
    {
        $picture = isset($item['photo_url'])?$item['photo_url']:null;
        unset($item['photo_url']);
        if( !empty($picture) && $spot_id )
        {
            DB::table('remote_photos')
                    ->where('associated_type', Spot::class)
                    ->where('associated_id', $spot_id)
                    ->delete();
            $needCover = true;
            $date = date('Y-m-d H:i:s');
            $pictueArr[] = [
                'url' => $picture,
                'image_type' => 1,
                'size' => 'original',
                'associated_type' => Spot::class,
                'associated_id' => $spot_id,
                'created_at' => $date,
                'updated_at' => $date
            ];
            DB::table('remote_photos')
                    ->insert($pictueArr);
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
    
    protected function saveHotelObject($spot_id, $item)
    {
        $obj = DB::table('spot_hotels')
                                ->select('id')
                                ->where('booking_id', $item['booking_id'])
                                ->first();
        $attrArr = [];
        foreach( array_diff($this->hotelFields, ['booking_id']) as $field) {
            if(isset($item[$field]))
            {
                $attrArr[$field] = $item[$field];
            }
        }
        if(!empty($obj->id))
        {
            DB::table('spot_hotels')
                    ->where('booking_id', $item['booking_id'])
                    ->update($attrArr);
        }
        else 
        {
            $attrArr['booking_id'] = $spot_id;
            DB::table('spot_hotels')
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
