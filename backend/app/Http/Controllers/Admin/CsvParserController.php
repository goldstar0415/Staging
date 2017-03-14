<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\SpotTypeCategory;
use App\SpotType;
use App\Http\Controllers\Controller;
use App\Services\Csv\Reader;
use App\Services\Csv\Helper;
use App\Services\Csv\Fields;
use App\Spot;
use Carbon\Carbon;
use Phaza\LaravelPostgis\Geometries\Point;
use DB;

class CsvParserController extends Controller
{
    private $stepCount = 1000;
    private $prefix = null;
    private $categoryId = null;
    private $typeName = null;
    private $usePrefix = true;
    private $date = null;
    private $headers = null;
    private $field = null;
    private $mode = null;
    
    private $insertedIds = [];
    private $existingIds = [];
    private $spotsRows = [];
    private $spotsToUpdate = [];
    private $photos = [];
    private $tags = [];
    private $amenities = [];
    private $locations = [];
    private $cachedTags = [];
    
    private $result = [
        'success'       => true, 
        'end_of_parse'    => false, 
        'messages'      => [],
        'rows_added'    => 0,
        'rows_updated'  => 0,
        'rows_parsed_now' => 0,
    ];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    /**
     * Index page
     * 
     * @return View
     */
    public function index()
    {
        return view('admin.parser.index', ['categories' => SpotType::categoriesList()]);
    }
    
    /**
     * Uploading .CSV to host
     * 
     * @param Request $request
     * @return App\Services\Csv\Helper
     */
    public function exportUpload(Request $request)
    {
        return Helper::uploadCsv($request);
    }
    
    /**
     * Export handling
     * 
     * @param Request $request
     * @return json
     */
    public function export( Request $request ) 
    {
        $path               = $request->path;
        $total_rows         = (int)$request->total_rows;
        $this->usePrefix    = (int)$request->use_prefix;
        $this->field        = $request->field;
        $this->mode         = $request->mode;
        $rows_parsed_before = $request->rows_parsed;
        $file_offset        = (int)$request->file_offset;
        $this->headers      = $request->input('headers', []);
        $this->categoryId   = $request->input('category', null);
        $this->date         = date('Y-m-d H:i:s');
        if(!empty($this->categoryId))
        {
            $this->setTypeName();
        }
        $pref = $this->prefix;
        $this->result['old_offset'] = $file_offset;
        $reader             = new Reader();
        $reader->setOffset($file_offset);
        $reader->open($path);
        $isFirstRow         = ($file_offset == 0)?true:false;
        if(empty($pref))
        {
            $this->result['messages'][] = 'Category not selected';
            return $this->result;
        }
        if($total_rows == $rows_parsed_before)
        {
            unlink($path);
            $this->result['end_of_parse']       = true;
        }
        else
        {
            // Starting loop thru CSV
            config([
                'excel.cache.enable'  => false
                ]);
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    // Getting fields headers if it's first row or if field setted in update mode
                    if($isFirstRow)
                    {
                        $this->headers = array_change_key_case(array_flip($row));
                        $isFirstRow = false;
                        continue;
                    }
                    // Getting current file pointer
                    if($this->result['rows_parsed_now'] < $this->stepCount)
                    {
                        $this->result['file_offset'] = $reader->getFilePointerOffset();
                    }
                    // Checking for step items counter
                    if($this->result['rows_parsed_now'] >= $this->stepCount)
                    {
                        break;
                    }
                    $item = $this->associateColumns($row);
                    $remote_id = $this->getRemoteId($item);
                    
                    if( !empty($remote_id))
                    {
                        $this->spotsRows[$remote_id] = $this->prepareSpot($item, $remote_id);
                        $this->addAmenities($remote_id, $item);
                        $this->addLocation($remote_id, $item);
                        $this->addPhoto($remote_id, $item);
                        $this->addTags($remote_id, $item);
                    }
                    else {
                        $this->result['messages'][] = 'Remote service ID missed in string #' . ($rows_parsed_before + $this->result['rows_parsed_now'] + 1);
                    }
                    $this->result['rows_parsed_now']++;
                }
            }
            if(!empty($this->spotsRows))
            {
                $this->saveSpots();
            }
            if($this->result['success'])
            {
                $this->saveRelations();
            }
            $this->result['end_of_parse'] = ($this->result['rows_parsed_now'] == 0) ? true : false;
            $reader->close();
        }

        $this->result['rows_parsed'] = $rows_parsed_before + $this->result['rows_parsed_now'];
        if($this->result['rows_parsed'] === $total_rows || $this->result['end_of_parse'])
        {
            $this->result['end_of_parse'] = true;
            unlink($path);
        }
        $this->result['headers'] = $this->headers;
        header('Content-Type: text/html;charset=utf-8');
        return $this->result;
    }
    
    /**
     * Preparing items to insert/update as spot
     * 
     * @param array $item
     * @param string $remote_id
     * @return array
     */
    protected function prepareSpot($item, $remote_id)
    {
        $attrArr = [];
        foreach($item as $column => $value)
        {
            if( in_array($column, Fields::$spot) && !empty($value))
            {
                if( $column == 'web_sites')
                {
                    $attrArr[$column] = json_encode([trim($value)]);
                }
                elseif($column == 'start_date' || $column == 'end_date')
                {
                    $attrArr[$column] = Carbon::parse(trim($value))->toDateTimeString();
                }
                else
                {
                    $attrArr[$column] = trim($value);
                }
            }
        }
        $attrArr['remote_id'] = $remote_id;
        return $attrArr;
    }
    
    /**
     * Handling for spots insert and update
     * 
     * @return void
     */
    protected function saveSpots()
    {
        if(!empty($this->spotsRows))
        {
            $existingSpots = DB::table('spots')->select(['id', 'remote_id'])->whereIn('remote_id', array_keys($this->spotsRows))->get();
            if(!empty($existingSpots))
            {
                foreach($existingSpots as $value)
                {
                    if($this->mode !== 'insert')
                    {
                        $this->existingIds[$value->remote_id] = $value->id;
                        $this->spotsToUpdate[$value->remote_id] = $this->spotsRows[$value->remote_id];
                    }
                    unset($this->spotsRows[$value->remote_id]);
                }
            }
            if(!empty($this->spotsToUpdate) && $this->mode !== 'insert' )
            {
                $this->updateSpots();
            }
            elseif($this->mode === 'insert')
            {
                $this->existingIds = [];
            }
            if(!empty($this->spotsRows) && $this->mode !== 'update' && $this->result['success'])
            {
                $this->insertSpots();
            }
        }
    }
    
    /**
     * Updating existing spots
     * 
     * @return void
     */
    protected function updateSpots()
    {
        $spots = $this->spotsToUpdate;
        $existingIds = $this->existingIds;
        DB::transaction(function() use ($spots, $existingIds) {
            foreach($spots as $remote_id => $row)
            {
                try
                {
                    DB::table('spots')->where('id', $existingIds[$remote_id])->update($row);
                    $this->result['rows_updated']++;
                }
                catch(QueryException $e)
                {
                    $this->result['messages'][] = "Line with remote_id $remote_id throws SQL error on update:";
                    $this->result['messages'][] = $e->getMessage();
                    $this->result['success'] = false;
                    break;
                }
            }
        });
    }
    
    /**
     * Inserting not existing spots
     * 
     * @return void
     */
    protected function insertSpots()
    {
        $spots = $this->spotsRows;
        $this->insertedIds = DB::transaction(function() use ($spots) {
            $insertedIds = [];
            foreach($spots as $remote_id => $row)
            {
                $row['is_approved'] = true;
                $row['is_private'] = false;
                $row['spot_type_category_id'] = $this->categoryId;
                $row['created_at'] = $this->date;
                $row['updated_at'] = $this->date;
                try
                {
                    $insertedIds[$remote_id] = DB::table('spots')->insertGetId($row);
                    $this->result['rows_added']++;
                }
                catch(QueryException $e)
                {
                    $this->result['messages'][] = "Line with remote_id $remote_id throws SQL error on insert:";
                    $this->result['messages'][] = $e->getMessage();
                    $this->result['success'] = false;
                    break;
                }
            }
            return $insertedIds;
        });
    }

    /**
     * Adding spots points for further saving
     * 
     * @param string $remote_id
     * @param array $item
     * @return void
     */
    protected function addLocation($remote_id, $item)
    {
        if(!empty($item['latitude']) && !empty($item['longitude']) && !empty($item['address']))
        {
            $pointObj = new Point($item['latitude'], $item['longitude']);
            $point = [
                'location' => DB::raw(sprintf("ST_GeogFromText('%s')", $pointObj->toWKT())),
                'address' => $item['address'],
            ];
            $this->locations[$remote_id] = $point;
        }
    }
    
    /**
     * Adding spots remote photos for further saving
     * 
     * @param string $remote_id
     * @param array $item
     * @return void
     */
    protected function addPhoto($remote_id, $item)
    {
        if( !empty($item['photos']) )
        {
            $photos = array_filter(array_map('trim', explode(';', $item['photos'])));
            $needCover = true;
            $i = 1;
            foreach($photos as $photoUrl)
            {
                $image_type = 0;
                if($needCover)
                {
                    $image_type = 1;
                    $needCover = false;
                }
                $this->photos[$remote_id][($image_type === 1)? 'cover':$i] = [
                    'url' => $photoUrl,
                    'image_type' => $image_type,
                    'size' => 'original',
                    'associated_type' => Spot::class,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ];
                $i++;
            }
        }
    }
    
    /**
     * Adding spots tags for further saving
     * 
     * @param string $remote_id
     * @param array $item
     * @return void
     */
    protected function addTags($remote_id, $item)
    {
        if( !empty($item['tags']))
        {
            $tags = array_filter(array_map('trim', explode(';', $item['tags'])));
            $idsArr = [];
            
            foreach($tags as $index => $tag)
            {
                if(isset($this->cachedTags[$tag]))
                {
                    $this->tags[$remote_id][] = [
                        'tag_id' => $this->cachedTags[$tag]
                    ];
                    unset($tags[$index]);
                }
            }
            if(!empty($tags))
            {
                $existingTags = [];
                $tagsCollection = DB::table('tags')->whereIn('name', $tags)->get();
                foreach($tagsCollection as $tagObj)
                {
                    $idsArr[] = $tagObj->id;
                    $this->cachedTags[$tagObj->name] = $tagObj->id;
                    $existingTags[] = $tagObj->name;
                }
                $tags = array_diff($tags, $existingTags);
                foreach($tags as $tag)
                {
                    $tagId = DB::table('tags')->insertGetId(['name' => $tag]);
                    $idsArr[] = $tagId;
                    $this->cachedTags[$tag] = $tagId;
                }
                foreach($idsArr as $id)
                {
                    $this->tags[$remote_id][] = [
                        'tag_id' => $id
                    ];
                }
            }
        }
    }
    
    /**
     * Adding spots amenities for further saving
     * 
     * @param string $remote_id
     * @param array $item
     * @return void
     */
    protected function addAmenities($remote_id, $item)
    {
        if(!empty($item['amenities']))
        {
            $amenities = array_filter(explode(',', $item['amenities']));
            foreach($amenities as $amenity)
            {
                $body = trim($amenity);
                $this->amenities[$remote_id][] = [
                    'item' => $body,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ];
            }
        }
    }
    
    /**
     * Deleting old existing spots dependencies and adding new 
     * related items for inserted and updated spots 
     * 
     * @return void
     */
    protected function saveRelations()
    {
        $spotIdsToRemove = $relationsToAdd = [
            'photos' => [],
            'tags' => [],
            'amenities' => [],
            'locations' => []
        ];
        // Preparing spots remote photos for insert
        if($this->photos != [])
        {
            foreach($this->photos as $remote_id => $items)
            {
                foreach($items as $item)
                {
                    if(isset($this->insertedIds[$remote_id]))
                    {
                        $item['associated_id'] = $this->insertedIds[$remote_id];
                    }
                    if(isset($this->existingIds[$remote_id]))
                    {
                        $item['associated_id'] = $this->existingIds[$remote_id];
                        $spotIdsToRemove['photos'][] = $this->existingIds[$remote_id];
                    }
                    if(isset($item['associated_id']))
                    {
                        $relationsToAdd['photos'][] = $item;
                    }
                }
            }
        }
        // Preparing spots amenities for insert
        if($this->amenities != [])
        {
            foreach($this->amenities as $remote_id => $items)
            {
                foreach($items as $item)
                {
                    if(isset($this->insertedIds[$remote_id]))
                    {
                        $item['spot_id'] = $this->insertedIds[$remote_id];
                    }
                    if(isset($this->existingIds[$remote_id]))
                    {
                        $item['spot_id'] = $this->existingIds[$remote_id];
                        $spotIdsToRemove['amenities'][] = $this->existingIds[$remote_id];
                    }
                    if(isset($item['spot_id']))
                    {
                        $relationsToAdd['amenities'][] = $item;
                    }
                }
            }
        }
        // Preparing spots tags for insert
        if($this->tags != [])
        {
            foreach($this->tags as $remote_id => $items)
            {
                foreach($items as $item)
                {
                    if(isset($this->insertedIds[$remote_id]))
                    {
                        $item['spot_id'] = $this->insertedIds[$remote_id];
                    }
                    if(isset($this->existingIds[$remote_id]))
                    {
                        $item['spot_id'] = $this->existingIds[$remote_id];
                        $spotIdsToRemove['tags'][] = $this->existingIds[$remote_id];
                    }
                    if(isset($item['spot_id']))
                    {
                        $relationsToAdd['tags'][] = $item;
                    }
                }
            }
        }
        // Preparing spots points for insert
        if($this->locations != [])
        {
            foreach($this->locations as $remote_id => $item)
            {
                if(isset($this->insertedIds[$remote_id]))
                {
                    $item['spot_id'] = $this->insertedIds[$remote_id];
                }
                if(isset($this->existingIds[$remote_id]))
                {
                    $item['spot_id'] = $this->existingIds[$remote_id];
                    $spotIdsToRemove['locations'][] = $this->existingIds[$remote_id];
                }
                if(isset($item['spot_id']))
                {
                    $relationsToAdd['locations'][] = $item;
                }
            }
        }
        // Database transaction to remove all old relations of existing spots and add new relations for inserted and updated spots
        DB::transaction(function() use ($spotIdsToRemove, $relationsToAdd) {
            if($spotIdsToRemove['photos'] != [])
            {
                DB::table('remote_photos')->whereIn('associated_id', $spotIdsToRemove['photos'])->where('associated_type', Spot::class)->delete();
            }
            if($spotIdsToRemove['amenities'] != [])
            {
                DB::table('spot_amenities')->whereIn('spot_id', $spotIdsToRemove['amenities'])->delete();
            }
            if($spotIdsToRemove['tags'] != [])
            {
                DB::table('spot_tag')->whereIn('spot_id', $spotIdsToRemove['tags'])->delete();
            }
            if($spotIdsToRemove['locations'] != [])
            {
                DB::table('spot_points')->whereIn('spot_id', $spotIdsToRemove['locations'])->delete();
            }
            if($relationsToAdd['photos'] != [])
            {
                DB::table('remote_photos')->insert($relationsToAdd['photos']);
            }
            if($relationsToAdd['amenities'] != [])
            {
                DB::table('spot_amenities')->insert($relationsToAdd['amenities']);
            }
            if($relationsToAdd['tags'] != [])
            {
                DB::table('spot_tag')->insert($relationsToAdd['tags']);
            }
            if($relationsToAdd['locations'] != [])
            {
                DB::table('spot_points')->insert($relationsToAdd['locations']);
            }
        });
    }

    /**
     * Creating associative array from CSV headers (if they are named properly) and row values
     * 
     * @param array $row
     * @return array
     */
    protected function associateColumns($row)
    {
        $item = [];
        $availableFields = $this->getAvailableFields();
        foreach($this->headers as $title => $index) {
            if(in_array($title, $availableFields))
            {
                $item[$title] = $row[$index];
            }
        }
        return $item;
    }
    
    /**
     * Gettin all possible fields
     * 
     * @return array
     */
    protected function getAvailableFields() 
    {
        return array_merge(
            Fields::$spot,
            Fields::$mass,
            Fields::$location
        );
    } 
    
    /**
     * Setting type name and prefix for remote_id's by category ID
     * 
     * @return void
     */
    protected function setTypeName() 
    {
        $this->typeName = SpotTypeCategory::find($this->categoryId)->type->name;
        $this->prefix = $this->typeName . '_' . $this->categoryId . '_';
    }
    
    /**
     * Generating remote_id with prefix depending on remote_id or booking_id in CSV
     * 
     * @return string
     */
    protected function getRemoteId(&$item)
    {
        $remote_id = null;
        if(!empty($item['booking_id']))
        {
            $remote_id = $item['booking_id'];
            unset($item['booking_id']);
        }
        if(!empty($item['remote_id']))
        {
            $remote_id = $item['remote_id'];
            unset($item['remote_id']);
        }
        return ($this->usePrefix ? $this->prefix: '') . $remote_id;
    }
}
