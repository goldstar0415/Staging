<?php

namespace App\Http\Controllers;

use App\Services\Json\Reader;
use App\Spot;
use App\SpotType;
use App\SpotTypeCategory;
use App\SpotPoint;
use App\RemotePhoto;
use Log;

class JsonParserController extends Controller {
    
    
    protected $dumpUrl  = "http://23.92.68.169/nkcjsdc/events.json";
    protected $filePath = "json/events.json";
    
    public function getDump()
    {
        $url = $this->dumpUrl;
        $filename = storage_path($this->filePath);
        $result = copy($url, $filename);
        Log::info('Events json download status: ' . ($result)? 'ok' : 'failed');
        return $result;
    }
    
    public function importSpots( $offset = 0 )
    {
        $filename = storage_path($this->filePath);
        
        $reader = new Reader([
            'file_path'   => $filename,
            'items_count' => 1000,
            'offset'      => (integer)$offset
        ]);
        
        $categoryId = $this->getCategoryId();
        $newSpots = [];
        $parsedResults = $reader->getItems();
        $currentOffset = $reader->offset;
        $endOfFile     = $reader->endOfFile;
        $prefix        = 'he_'; 
        foreach($parsedResults as $index => $parsedResult)
        {
            if(!Spot::where('remote_id', '=', $prefix . $parsedResult['hash'])->exists())
            {
                $sites = [];
                if( !empty($parsedResult['url']) )
                {
                    $sites[] = $parsedResult['url'];
                }
                if( !empty($parsedResult['referer']))
                {
                    $sites[] = $parsedResult['referer'];
                }
                $spot = Spot::create([
                    'remote_id'   => $prefix . $parsedResult['hash'],
                    'description' => $this->makeEllipsis($parsedResult['description'], 3000),
                    'web_sites'   => $sites,
                    'title'       => str_limit($parsedResult['title'], 252),
                    'is_approved' => true,
                    'is_private'  => false,
                    'spot_type_category_id' => $categoryId,
                    'start_date'  => $parsedResult['date']
                ]);
                
                $spot->remotePhotos()->create([
                    'url' => $parsedResult['image'],
                    'image_type' => 1,
                    'size' => 'original',
                ]);
                
                $spot->points()->create([
                    'address' => $parsedResult['address'],
                    'location' => [
                        'lat' => $parsedResult['lat'],
                        'lng' => $parsedResult['long']
                    ]
                ]);
                $newSpots[] = $spot;
            }
        }
        $spotsCount = count($newSpots);
        Log::info('EventsJsonParser: new spots count: ' . count($newSpots));
        Log::info('EventsJsonParser: offset: ' . $currentOffset);
        return [
            'offset' => $currentOffset,
            'endOfFile'  => $endOfFile
        ];
    }
    
    public function makeEllipsis($string, $limit)
    {
        $cleanString = strip_tags($string,'<br>');
        $cleanString = nl2br($cleanString);
        if(strlen($cleanString) <= $limit)
        {
            return $cleanString;
        }
        $pos = strpos($cleanString, ' ', $limit);
        return substr($cleanString,0,$pos ) . '...';
    }
    
    public function getCategoryId() {
        $cat = SpotTypeCategory::where('name', 'heyevent');
        if($cat->exists())
        {
            $catObj = $cat->first();
        }
        else
        {
            $type = SpotType::getTypeId('event');
            $catObj = SpotTypeCategory::create([
                'name' => 'heyevent',
                'display_name' => 'Heyevent',
                'spot_type_id' => $type
            ]);
        }
        return $catObj->id;
    }
}