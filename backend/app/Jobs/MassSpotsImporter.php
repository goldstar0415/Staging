<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Role;
use App\SpotTypeCategory;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MassSpotsImporter extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;
    use InteractsWithQueue;
    
    protected $import_dir = 'spots-imports';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $files = Storage::allFiles($this->import_dir);
        foreach ($files as $file) {
            $file_path = storage_path('app/' . $file);

            list($spot_type, $spot_category) = explode(
                '/',
                str_replace(storage_path("app/$this->import_dir/"), '', $file_path)
            );

            $spot_category = SpotTypeCategory::whereName($spot_category)->first()->id;

            $this->dispatch(new SpotsImportCsv(['admin' => Role::take('admin')->users()->first(), 'spot_category' => $spot_category, 'document' => $file_path], $spot_type));
        }
    }
}
