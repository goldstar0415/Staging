<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\JsonParserController;


class SpotsImportJson extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue;

    public $offset = 0;

    public function handle()
    {
        $result = App(JsonParserController::class)->importSpots($this->offset);
        
        if(!$result['endOfFile'])
        {
            $newJob = new SpotsImportJson();
            $newJob->offset = $result['offset'];
            dispatch($newJob);
        }
    }
}
