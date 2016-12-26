<?php

namespace App\Services\Csv;

use App\Services\Csv\Reader;

class Helper 
{
    public static function uploadCsv($request)
    {
        $rules = ['csv' => 'required'];
        $result = ['success' => true];
        $messages = [
            'csv.required'  => 'CSV file required',
            'csv.mimetypes' => 'File should be of text/csv mime type'
        ];
        
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) 
        {
            $result['success'] = false;
            $result['data']    = $validator->messages()->get('csv');
        }
        else
        {
            $filename =  $request->csv->getClientOriginalName();
            $path     = $request->csv->move(storage_path() . '/csvs/', $filename );
            if( $request->csv->getClientOriginalExtension() != 'csv' )
            {
                unlink($path->getPathName());
                $result['success'] = false;
                $result['data'][] = 'File should be .csv';
            }
            else
            {
                $result['data']['path'] = $path->getPathName();
                $result['data']['filename'] = $filename;
                config([
                    'excel.csv.delimiter' => ',',
                    'excel.cache.enable'  => false
                    ]);
                
                $reader = new Reader();
                $reader->open($result['data']['path']);
                $count = 0;
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $count++;
                    }
                }
                $reader->close();
                    
                $result['data']['count'] = $count;
            }
        }
        return json_encode($result);
    }
}