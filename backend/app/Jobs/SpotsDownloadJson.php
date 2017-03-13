<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\JsonParserController;
use App\Jobs\SpotsImportJson;

class SpotsDownloadJson extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue;

    public $offset;

    public function handle()
    {
        $result = App(JsonParserController::class)->getDump();
        
        if($result)
        {
            $newJob = new SpotsImportJson();
            dispatch($newJob);
        }
        else
        {
            $newJob = new SpotsDownloadJson();
            dispatch($newJob);
        }
        
    }
}
