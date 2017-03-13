<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\SpotView;
use App\Services\SpotsImportFile;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SpotViewUpdater extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;
    use InteractsWithQueue;
    
    protected $spotId = null;
    protected $action = 'save';

    
    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct($id = null, $action = 'save')
    {
        $this->spotId = $id;
        $this->action = $action;
    }
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->spotId)
        {
            switch ($this->action) 
            {
                case 'save':
                    SpotView::saveView($this->spotId);
                    break;
                case 'update':
                    SpotView::updateView($this->spotId);
                    break;
                case 'delete':
                    SpotView::deleteView($this->spotId);
                    break;
            }
        }
        else
        {
            if($this->action === 'refresh')
            {
                SpotView::refreshView();
            }
        }
    }
}