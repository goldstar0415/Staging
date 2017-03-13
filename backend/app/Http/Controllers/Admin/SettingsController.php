<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\ParseEvents;
use App\Jobs\TicketMasterEvents;
use App\Jobs\SpotsDownloadJson;
use App\Jobs\SpotsImportJson;
use App\Services\AppSettings;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Spot;
use App\SpotTypeCategory;


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
        $ticketMastercategory = SpotTypeCategory::whereName('ticketmaster')->first();
        $lastTicketMasterSpot = $ticketMastercategory
                ?Spot::where('spot_type_category_id', $ticketMastercategory->id)
                ->orderBy('id', 'desc')
                ->first()
                :null;
        
        $seatGeekCategory = SpotTypeCategory::whereName('seatgeek')->first();
        $lastSeatGeekSpot = $seatGeekCategory
                ?Spot::where('spot_type_category_id', $seatGeekCategory->id)
                ->orderBy('id', 'desc')
                ->first()
                :null;
        
        return view('admin.settings', [
            'ticketMasterSpot' => $lastTicketMasterSpot,
            'seatGeekSpot'     => $lastSeatGeekSpot
                ])
                ->with('settings', $this->settings);
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
    
    public function ticketMasterRun()
    {
        $this->dispatch(new TicketMasterEvents);

        return back()->with('run', true);
    }
    
    public function heyeventRun()
    {
        $this->dispatch(new SpotsDownloadJson);

        return back()->with('run', true);
    }
    
    public function heyeventImportRun()
    {
        $this->dispatch(new SpotsImportJson);

        return back()->with('run', true);
    }
    
}
