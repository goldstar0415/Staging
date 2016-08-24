<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\CrawlerRun;
use App\Jobs\ParseEvents;
use App\Jobs\TicketMasterEvents;
use App\Services\AppSettings;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Spot;


class SettingsController extends Controller
{
    
    /**
     * @var AppSettings
     */
    private $settings;

    /**
     * SettingsController constructor.
     * @param AppSettings $settings
     */
    public function __construct(AppSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lastTicketMasterSpot = Spot::whereNotNull('remote_id')
                ->orderBy('id', 'desc')
                ->first();
        return view('admin.settings', ['ticketMasterSpot' => $lastTicketMasterSpot])->with('settings', $this->settings);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->settings->parser->aid = $request->aid;
        
        return back();
    }

    public function parserRun()
    {
        $this->dispatch(new ParseEvents);

        return back()->with('run', true);
    }

    public function crawlerRun()
    {
        $this->dispatch(new CrawlerRun);

        return back()->with('run', true);
    }
    
    public function ticketMasterRun()
    {
        $this->dispatch(new TicketMasterEvents);

        return back()->with('run', true);
    }
}
