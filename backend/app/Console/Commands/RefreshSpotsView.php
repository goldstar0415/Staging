<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use App\SpotView;
use Log;

class RefreshSpotsView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:refreshspotsview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh materialized view with spots';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		Log::debug('refresh view');
		SpotView::refreshView();
    }
	
	public function fire() {
		$this->handle();
	}
}
