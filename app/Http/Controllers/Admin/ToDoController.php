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
use App\RemotePhoto;
use App\SpotPoint;
use App\SpotToDo;

class ToDoController extends Controller 
{
    
    private $stepCount = 1000;
    private $remote_id_prefix = 'td_';
    
    private $spotFields = [
        'title' => 'todo_name',
        'description' => 'description',
        'web_sites' => 'website',
    ];
    
    private $todoFields = [
        
        'todo_id' => 'remote_id',
        'email' => 'email',
        'phone' => 'phone_number',
        'trip_url' => 'tripadvisor_url',
        'trip_rating' => 'tripadvisor_rating',
        'trip_no_reviews' => 'tripadvisor_reviews_count',
        //'' => 'street_address',
        //'' => 'latitude',
        //'' => 'longitude',
        'city' => 'city',
        'country' => 'country',
        'google_id' => 'google_pid',
        'facebook_url' => 'facebook_url',
        'yelp_id' => 'yelp_id',
        //'' => 'images',
        //'' => 'tags',
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
    
    public function bulkDestroy(SpotsBulkDeleteRequest $request) {
        $spots = Spot::whereIn('id', $request->spots)->delete();
        return back();
    }
    
    public function getEdit(Spot $spot) {
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
        return view('admin.todo.parser');
    }
    
    public function exportUpload(Request $request)
    {
        return Helper::uploadCsv($request);
    }
    
    public function export( Request $request ) 
    {   
        $pref               = $this->remote_id_prefix;
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
                        $query = Spot::where('remote_id', $pref . $item['todo_id']);
                        $spotExists = $query->exists();
                        if( $spotExists && $updateExisting)
                        {
                            $spot = $query->first();
                            if(isset($item['website']) && !empty($item['website']))
                            {
                                $spot->web_sites = [$item['website']];
                            }
                            unset($item['website']);
                            foreach($this->spotFields as $column => $value)
                            {
                                if(isset($item[$value]))
                                {
                                    $spot->$column = $item[$value];
                                }
                            }
                            $spot->save();
                            $result['rows_updated']++;
                        }
                        elseif(!$spotExists)
                        {
                            $spot = $this->createSpot($item, $pref);
                            $result['rows_added']++;
                        }
                        if($updateExisting || !$spotExists)
                        {
                            $this->saveToDoObject($spot, $item);
                            $this->saveLocation($spot, $item);
                            $this->savePhotos($spot, $item);
                            $this->saveTags($spot, $item);
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
    
    protected function saveLocation($spot, $item)
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
            $locationExists = SpotPoint::where('spot_id', $spot->id)->exists();
            if($locationExists)
            {
                $spot->points()->delete();
            }
            $point = new SpotPoint();
            $point->location = $item['location'];
            $point->address = $item['street_address'];
            $spot->points()->save($point);
        }
    }
    
    protected function savePhotos($spot, $item)
    {
        $pictures = isset($item['images'])?$item['images']:null;
        unset($item['images']);
        if( !empty($pictures) )
        {

            $pictureExists = $spot->remotePhotos()->exists();
            if($pictureExists)
            {
                $spot->remotePhotos()->delete();
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
            $spot->remotePhotos()->saveMany($pictuesObjects);
        }
    }
    
    protected function saveTags($spot, $item)
    {
        if( $item['tags'] )
        {
            $spot->tags = explode(';', $item['tags']);
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
    
    protected function createSpot($item, $pref)
    {
        $spotTypeCategory = SpotTypeCategory::where('name', 'todo')->first();
        return Spot::create([
            'spot_type_category_id' => $spotTypeCategory->id,
            'title' => isset($item['todo_name']) ? $item['todo_name']: '',
            'web_sites'	=> isset($item['website']) ? [$item['website']] : [],
            'is_approved' => true,
            'is_private' => false,
            'remote_id' => isset($item['todo_id']) ? $pref . $item['todo_id']: ''
        ]);
    }
    
    protected function saveToDoObject($spot, $item)
    {
        $todoExists = SpotToDo::where('remote_id',  $item['todo_id'])->first();
        $todoObj = ($todoExists) ? $todoExists: (new SpotToDo);
        foreach( $this->todoFields as $name => $field) {
            if(isset($item[$name]))
                $todoObj->$field = $item[$name];
        }
        $todoObj->spot_id = $spot->id;
        $todoObj->save();    
        
        return $todoObj;
    }
}