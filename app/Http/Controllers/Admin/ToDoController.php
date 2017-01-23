<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Csv\Reader;
use App\Services\Csv\Helper;
use App\Http\Requests\PaginateRequest;
use App\Spot;
use App\Http\Requests\Admin\ToDoFilterRequest;
use App\SpotTypeCategory;
use App\SpotType;
use App\RemotePhoto;
use App\SpotPoint;
use App\SpotToDo;
use DB;

class ToDoController extends Controller 
{
    private $stepCount = 1000;
    private $prefix = 'td_';
    
    private $categoryName = 'todo';
    private $categoryId = null;
    private $updateExisting = false;
    
    private $spotFields = [
        'title' => 'todo_name',
        'description' => 'description',
        'web_sites' => 'website',
        'avg_rating' => 'avg_rating',
        'total_reviews' => 'total_reviews'
    ];
    
    private $todoFields = [
        
        'todo_id' => 'remote_id',
        'email' => 'email',
        'phone' => 'phone_number',
        'trip_url' => 'tripadvisor_url',
        'trip_rating' => 'tripadvisor_rating',
        'trip_no_reviews' => 'tripadvisor_reviews_count',
        'city' => 'city',
        'country' => 'country',
        'google_id' => 'google_pid',
        'facebook_url' => 'facebook_url',
        'yelp_id' => 'yelp_id',
    ];
    
    private $massFields = [
        'images',
        'tags'
    ];
    
    private $updateRules = [
        'title' => 'required|max:255',
        'web_sites' => 'sometimes|array',

        'remote_id',
        'email' => 'max:50',
        'phone_number' => 'max:50',
        'tripadvisor_url' => 'max:255',
        'tripadvisor_rating' => 'max:50',
        'tripadvisor_reviews_count' => 'max:50',
        'country' => 'max:255',
        'city' => 'max:255',
        'yelp_id' => 'max:255',
        'facebook_url' => 'max:255',
        'google_pid' => 'max:50',
    ];
    
    public function index(PaginateRequest $request)
    {
        return view('admin.todo.index')->with('todoes', $this->paginatealbe($request, Spot::todoes(),15));
    }
    
    public function filter(ToDoFilterRequest $request)
    {
        $query = $this->getFilterQuery($request, Spot::todoes());
        return view('admin.todo.index')->with('todoes', $this->paginatealbe($request, $query,15));
    }
    
    protected function getFilterQuery(ToDoFilterRequest $request, $query)
    {
        if ($request->has('filter.title')) {
            $query->where('title', 'ilike', '%' . $request->filter['title'] . '%');
        }
        if ($request->has('filter.description')) {
            $query->where('description', 'ilike', '%' . $request->filter['description'] . '%');
        }
        if ($request->has('filter.created_at')) {
            $query->where(DB::raw('created_at::date'), $request->filter['created_at']);
        }
        $request->flash();
        return $query;
    }
    
    public function destroy($todo)
    {
        $todo->delete();
        return back();
    }
    
    public function cleanDb(Request $request)
    {
        Spot::todoes()->delete();
        return back();
    }
    
    public function bulkDestroy(SpotsBulkDeleteRequest $request) 
    {
        $spots = Spot::whereIn('id', $request->spots)->delete();
        return back();
    }
    
    public function getEdit(Spot $spot) 
    {
        $spotFields = array_keys($this->spotFields);
        $todoFields = array_diff($this->todoFields, ['remote_id']);
        
        return view('admin.todo.item')->with([
            'todo' => $spot,
            'spotFields' => $spotFields,
            'todoFields' => $todoFields,
        ]);
    }
    
    public function postEdit(Request $request, Spot $spot) 
    {
        $rules = $this->updateRules;
        $this->validate($request, $rules);
        $newValues = $request->all();
        foreach(array_keys($this->spotFields) as $field)
        {
            if(isset($newValues[$field])) $spot->$field = $newValues[$field];
        }
        $spot->save();
        $todoAttrObj = $spot->todo;
        if(!empty($todoAttrObj))
        {
            foreach(array_diff($this->todoFields, ['remote_id']) as $field)
            {
                if(isset($newValues[$field])) $todoAttrObj->$field = $newValues[$field];
            }
            $todoAttrObj->save();
        }
        return back();
    }
    
    public function csvParser()
    {
        $fieldsArr = array_merge( 
                array_keys($this->spotFields),
                array_diff(array_values($this->todoFields), ['remote_id']),
                $this->massFields
                );
        $fields = [];
        foreach($fieldsArr as $value)
        {
            $fields[$value] = $value;
        }
        return view('admin.todo.parser', ['fields' => $fields, 'categories' => SpotType::categoriesList('todo')]);
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
            foreach ($reader->getSheetIterator() as $sheet)
            {
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
                    if( !empty($item['todo_id']) )
                    {
                        $spot = DB::table('spots')
                                ->select('id')
                                ->where('remote_id', $pref . $item['todo_id'])
                                ->first();
                        $spotExists = !empty($spot->id);
                        $spot_id = $spotExists?$spot->id:null;
                        $saveSpot = $this->saveSpot($spot_id, $spotExists, $item, $result);
                        $spot_id = ($saveSpot)?$saveSpot:$spot_id;
                        
                        if($updateExisting || !$spotExists)
                        {
                            $this->saveToDoObject($spot_id, $item);
                            $this->saveLocation($spot_id, $item);
                            $this->savePhotos($spot_id, $item);
                            $this->saveTags($spot_id, $item);
                        }
                        $rows[] = $item;
                    }
                    else {
                        $result['messages'][] = 'ToDo ID missed in string #' . ($rows_parsed_before + $rows_parsed_now + 1);
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
                    elseif(in_array($field, array_diff(array_values($this->todoFields), ['remote_id'])))
                    {
                        SpotToDo::where('remote_id', $remote_id)->update([$field => $value]);
                    }
                    elseif(in_array($field, $this->massFields))
                    {
                        $spot = Spot::where('remote_id', $pref . $remote_id)->first();
                        if($spot)
                        {
                            if($field == 'tags'){
                                $this->saveTags($spot->id, [$field => $value]);
                            }
                            else
                            {
                                $this->savePhotos($spot->id, [$field => $value]);
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
                    ->where('remote_id', $this->prefix . $item['todo_id'])
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
            $attrArr['remote_id'] = $this->prefix . $item['todo_id'];
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
        if( isset($item['location']) && isset($item['street_address']) )
        {
            SpotPoint::where('spot_id', $spot_id)->delete();
            $point = new SpotPoint();
            $point->location = $item['location'];
            $point->address = $item['street_address'];
            $point->spot_id = $spot_id;
            $point->save();
        }
    }
    
    protected function savePhotos($spot_id, $item)
    {
        $pictures = isset($item['images'])?$item['images']:null;
        unset($item['images']);
        if( !empty($pictures) && $spot_id )
        {
            DB::table('remote_photos')
                    ->where('associated_type', Spot::class)
                    ->where('associated_id', $spot_id)
                    ->delete();
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
    
    protected function saveToDoObject($spot_id, $item)
    {
        $todoObj = DB::table('spot_todoes')
                                ->select('id')
                                ->where('remote_id', $item['todo_id'])
                                ->first();
        $attrArr = [];
        foreach( $this->todoFields as $name => $field) {
            if(isset($item[$name]))
            {
                $attrArr[$field] = $item[$name];
            }
        }
        if(!empty($todoObj->id))
        {
            DB::table('spot_todoes')
                    ->where('remote_id', $item['todo_id'])
                    ->update($attrArr);
        }
        else 
        {
            $date = date('Y-m-d H:i:s');
            $attrArr['remote_id'] = $item['todo_id'];
            $attrArr['spot_id'] = $spot_id;
            $attrArr['created_at'] = $date;
            $attrArr['updated_at'] = $date;
            DB::table('spot_todoes')
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