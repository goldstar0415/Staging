<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
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
    private $updateExisting = false;
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
    
    private $result = [
        'success'       => true, 
        'end_of_parse'    => false, 
        'messages'      => [],
        'rows_added'    => 0,
        'rows_updated'  => 0,
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
        $fieldsArr = $this->getFieldsForFront();
        return view('admin.parser.index', ['fields' => $fieldsArr, 'categories' => SpotType::categoriesList()]);
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
        $total_rows         = $request->total_rows;
        $this->updateExisting = (int)$request->update;
        $this->field        = $request->field;
        $this->mode         = $request->mode;
        $rows_parsed_before = $request->rows_parsed;
        $file_offset        = $request->file_offset;
        $this->headers      = $request->input('headers', []);
        $this->categoryId   = $request->input('category', null);
        $this->date         = date('Y-m-d H:i:s');
        if(!empty($this->categoryId))
        {
            $this->setTypeName();
        }
        $pref = $this->prefix;
        $this->result['update'] = $this->updateExisting;
        $this->result['old_offset'] = $file_offset;
        $reader             = new Reader();
        $reader->setOffset($file_offset);
        $reader->open($path);
        $isFirstRow         = ($file_offset == 0)?true:false;
        $rows_parsed_now    = 0;
        if(empty($pref))
        {
            $this->result['messages'][] = 'Category not selected';
            return $this->result;
        }
        if($total_rows == $rows_parsed_before)
        {
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
                    if($isFirstRow && $this->mode === 'parsing')
                    {
                        $this->headers = array_change_key_case(array_flip($row));
                        $isFirstRow = false;
                        continue;
                    }
                    elseif($isFirstRow && $this->mode === 'update')
                    {
                        $this->headers = array_flip([
                            'remote_id',
                            $this->field
                        ]);
                        $isFirstRow = false;
                    }
                    // Getting current file pointer
                    if($rows_parsed_now < $this->stepCount)
                    {
                        $this->result['file_offset'] = $reader->getFilePointerOffset();
                    }
                    // Checking for step items counter
                    if($rows_parsed_now >= $this->stepCount)
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
                        $this->result['messages'][] = 'Remote service ID missed in string #' . ($rows_parsed_before + $rows_parsed_now + 1);
                    }
                    $rows_parsed_now++;
                }
            }
            if(!empty($this->spotsRows))
            {
                $this->saveSpots();
            }
            $this->saveRelations();
            $this->result['end_of_parse'] = ($rows_parsed_now == 0) ? true : false;
            $reader->close();
        }

        $this->result['rows_parsed']          = $rows_parsed_before + $rows_parsed_now;
        $this->result['rows_parsed_now']      = $rows_parsed_now;
        $this->result['headers'] = $this->headers;
        header('Content-Type: text/html;charset=utf-8');
        $this->result = json_encode($this->result);
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
                    $attrArr[$column] = Carbon::createFromFormat('m/d/Y', trim($value));
                }
                else
                {
                    $attrArr[$column] = trim($value);
                }
            }
        }
        if($this->mode === 'parsing')
        {
            $attrArr['is_approved'] = true;
            $attrArr['is_private'] = false;
            $attrArr['spot_type_category_id'] = $this->categoryId;
            $attrArr['created_at'] = $this->date;
            $attrArr['updated_at'] = $this->date;
        }
        if($this->typeName != 'event')
        {
            $attrArr['remote_id'] = $remote_id;
        }
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
                    if($this->updateExisting || $this->mode === 'update')
                    {
                        $this->existingIds[$value->remote_id] = $value->id;
                        $this->spotsToUpdate[$value->remote_id] = $this->spotsRows[$value->remote_id];
                    }
                    unset($this->spotsRows[$value->remote_id]);
                }
                if($this->mode === 'update')
                {
                    
                }
            }
            if(!empty($this->spotsToUpdate))
            {
                $this->updateSpots();
            }
            if(!empty($this->spotsRows) && $this->mode === 'parsing')
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
                DB::table('spots')->where('id', $existingIds[$remote_id])->update($row);
                $this->result['rows_updated']++;
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
                $insertedIds[$remote_id] = DB::table('spots')->insertGetId($row);
                $this->result['rows_added']++;
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
                $this->tags[$remote_id][] = [
                    'tag_id' => $id];
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
     * Returning list of field available for update
     * 
     * @return array
     */
    protected function getFieldsForFront() 
    {
        $fieldsArr = array_merge(
                Fields::$spot,
                Fields::$mass
        );
        $fields = [];
        foreach($fieldsArr as $value)
        {
            $fields[$value] = $value;
        }
        return $fields;
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
