<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\SpotTypeCategory;
use App\SpotType;
use App\SpotPoint;
use App\Spot;
use App\SpotAmenity;
use App\Http\Controllers\Controller;
use App\Services\Csv\Reader;
use App\Services\Csv\Helper;
use App\Services\Csv\Fields;
use Carbon\Carbon;
use DB;

class CsvParserController extends Controller
{
    private $stepCount = 1000;
    private $prefix = null;
    private $categoryId = null;
    private $typeName = null;
    private $updateExisting = false;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = SpotType::get();
        $firstType = $types->first();
        $typeName = $firstType->name;
        $fieldsArr = $this->getFieldsForFront($typeName);
        return view('admin.parser.index', ['fields' => $fieldsArr, 'categories' => SpotType::categoriesList()]);
    }
    
    public function exportUpload(Request $request)
    {
        return Helper::uploadCsv($request);
    }
    
    public function export( Request $request ) 
    {
        $path               = $request->path;
        $stepCount          = $this->stepCount;
        $total_rows         = $request->total_rows;
        $updateExisting     = $this->updateExisting = (int)$request->update;
        $rows_parsed_before = $request->rows_parsed;
        $file_offset        = $request->file_offset;
        $headers            = $request->input('headers', []);
        $this->categoryId   = $request->input('category', null);
        if(!empty($this->categoryId))
        {
            $this->setTypeName();
        }
        $pref = $this->prefix;
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
        if(empty($pref))
        {
            $result['messages'][] = 'Category not selected';
            return $result;
        }
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
                        $headers = array_change_key_case(array_flip($row));
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
                    $remote_id = $this->getRemoteId($item);
                    if( !empty($remote_id) || ($this->typeName == 'event') )
                    {
                        $spot = ($this->typeName == 'event') ? null : (DB::table('spots')
                                ->select('id')
                                ->where('remote_id', $remote_id)
                                ->first());
                        $spotExists = !empty($spot->id);
                        $spot_id = $spotExists?$spot->id:null;
                        $saveSpot = $this->saveSpot($spot_id, $spotExists, $item, $result, $remote_id);
                        $spot_id = ($saveSpot)?$saveSpot:$spot_id;
                        if($updateExisting || !$spotExists)
                        {
                            $this->saveAmenities($spot_id, $item);
                            $this->saveLocation($spot_id, $item );
                            $this->savePhoto($spot_id, $item);
                            $this->saveTags($spot_id, $item);
                        }
                        $rows[] = $item;
                    }
                    else {
                        $result['messages'][] = 'Remote service ID missed in string #' . ($rows_parsed_before + $rows_parsed_now + 1);
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
        $stepCount          = $this->stepCount;
        $updateExisting     = (int)$request->update;
        $file_offset        = $request->file_offset;
        $path               = $request->path;
        $total_rows         = $request->total_rows;
        $rows_parsed_before = $request->rows_parsed;
        if(!empty($this->categoryId))
        {
            $this->setTypeName();
        }
        $pref = $this->prefix;
        $result             = [
            'success'       => true, 
            'endOfParse'    => false, 
            'messages'      => [],
            'update'        => $updateExisting,
            'rows_added'    => 0,
            'rows_updated'  => 0,
            'old_offset'    => $file_offset
        ];
        if(empty($pref))
        {
            $result['messages'][] = 'Category not selected';
            return $result;
        }
        $availableFields    = $this->getAvailableFields();
        $field              = $availableFields[$request->field];
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
                    $massName = $this->typeName . 'Mass';
                    $fields = Fields::$$this->typeName;
                    $fieldsMass = Fields::$$massName;
                    if(in_array($field, array_values($fields)))
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
                    elseif(in_array($field, array_values($fieldsMass)))
                    {
                        $spot = Spot::where('remote_id', $pref . $remote_id)->first();
                        if($spot)
                        {
                            switch($field)
                            {
                                case 'tags':
                                    $this->saveTags($spot->id, [$field => $value]);
                                    break;
                                case 'photos':
                                    $this->savePhoto($spot->id, [$field => $value]);
                                    break;
                                case 'amenities':
                                    $this->saveAmenities($spot->id, [$field => $value]);
                                    break;
                            }
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
    
    protected function saveSpot($spot_id, $spotExists, $item, &$result, $remote_id)
    {
        $attrArr = [];
        $category_id = $this->getCategoryId();
        $typeName = $this->typeName;
        $spotFields = array_values(Fields::$$typeName);
        foreach($item as $column => $value)
        {
            if( in_array($column, $spotFields) && !empty($value))
            {
                if( $column == 'web_sites')
                {
                    $attrArr[$column] = json_encode(trim($value));
                }
                elseif($column == 'start_date' || $column == 'end_date')
                {
                    $attrArr[$column] = Carbon::createFromFormat('m/d/Y', $value);
                }
                else
                {
                    $attrArr[$column] = trim($value);
                }
            }
        }
        if($spotExists && $this->updateExisting)
        {
            DB::table('spots')
                    ->where('remote_id', $remote_id)
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
            if($this->typeName != 'event')
            {
                $attrArr['remote_id'] = $remote_id;
            }
            $result['rows_added']++;
            return DB::table('spots')
                    ->insertGetId($attrArr);
        }
    }
    
    protected function saveLocation($spot_id, $item)
    {
        if(!empty($item['latitude']) && !empty($item['longitude']) && !empty($item['address']))
        {
            SpotPoint::where('spot_id', $spot_id)->delete();
            $point = new SpotPoint();
            $point->location = [
                'lat' => $item['latitude'],
                'lng' => $item['longitude'],
            ];
            $point->address = $item['address'];
            $point->spot_id = $spot_id;
            $point->save();
            
        }
    }
    
    protected function savePhoto($spot_id, $item)
    {
        if( !empty($item['photos']) )
        {
            $photos = array_filter(array_map('trim', explode(';', $item['photos'])));
            $needCover = true;
            foreach($photos as $photo)
            {
                DB::table('remote_photos')
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $spot_id)
                        ->delete();
                $image_type = 0;
                if($needCover)
                {
                    $image_type = 1;
                    $needCover = false;
                }
                $date = date('Y-m-d H:i:s');
                $pictueArr[] = [
                    'url' => $photo,
                    'image_type' => $image_type,
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
    }
    
    protected function saveTags($spot_id, $item)
    {
        if( !empty($item['tags']) && $spot_id)
        {
            DB::table('spot_tag')->where('spot_id', $spot_id)->delete();
            $tags = array_filter(array_map('trim', explode(';', $item['tags'])));
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
    
    protected function saveAmenities($spot_id, $item)
    {
        if(!empty($item['amenities']) && $spot_id)
        {
            $amenities = array_filter(explode(',', $item['amenities']));
            foreach($amenities as $amenity)
            {
                $body = trim($amenity);
                if( !SpotAmenity::where('spot_id', $spot_id)
                                 ->where('item', $body)->exists() )
                {
                    $date = date('Y-m-d H:i:s');
                    SpotAmenity::insert([
                        'item' => $body,
                        'spot_id' => $spot_id,
                        'created_at' => $date,
                        'updated_at' => $date
                    ]);
                }
            }
        }
    }
    
    protected function convertColumns($headers, $row)
    {
        $item = [];
        $availableFields = $this->getAvailableFields();
        foreach($headers as $title => $index) {
            if(!empty($availableFields[$title]))
            {
                $normalTitle = $availableFields[$title];
                $item[$normalTitle] = null;
                foreach(mb_detect_order() as $encoding)
                {
                    $str = mb_convert_encoding($row[$index], "UTF-8", $encoding);
                    if(stristr($str, '?') === FALSE) {
                        $item[$normalTitle] = $str;
                        break;
                    }
                }
                if( !$item[$normalTitle] )
                {
                    $item[$normalTitle] = mb_convert_encoding($row[$index], "UTF-8", "ISO-8859-16");
                }
            }
        }
        return $item;
    }
    
    protected function getAvailableFields() {
        
        $typeName = $this->typeName;
        $mass = $typeName . 'Mass';
        $location = $typeName . 'Location';
        return array_merge(
            Fields::$$typeName,
            Fields::$$mass,
            Fields::$$location
        );
    }
    
    protected function setTypeName() 
    {
        $this->typeName = SpotTypeCategory::find($this->categoryId)->type->name;
        $this->prefix = $this->typeName . '_' . $this->categoryId . '_';
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
    
    public function getFields($categoryId)
    {
        $category = SpotTypeCategory::find($categoryId);
        $result = [
            'success' => false, 
            'fields' => []
        ];
        if($category)
        {
            $typeName = $category->type->name;
            $result['fields'] = $this->getFieldsForFront($typeName);
            $result['success'] = true;
        }
        return json_encode($result);
    }
    
    protected function getFieldsForFront($typeName) 
    {
        $mass     = $typeName . 'Mass';
        $location = $typeName . 'Location';
        
        $fieldsArr = array_merge(
                array_keys(Fields::$$typeName),
                array_keys(Fields::$$location),
                array_keys(Fields::$$mass)
                );
        $fields = [];
        foreach($fieldsArr as $value)
        {
            $fields[$value] = $value;
        }
        return $fields;
    }
    
    protected function getRemoteId(&$item)
    {
        $remote_id = null;
        if(!empty($item['booking_id']))
        {
            $remote_id = $this->prefix . $item['booking_id'];
            unset($item['booking_id']);
        }
        if(!empty($item['remote_id']))
        {
            $remote_id = $this->prefix . $item['remote_id'];
            unset($item['remote_id']);
        }
        return $remote_id;
    }
}
