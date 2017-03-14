<?php

namespace App\Services\Csv;

use App\Services\Csv\Reader;
use File;
use PharData;
use ZipArchive;

class Helper 
{
    public static $archiveExtensions = [
        'zip',
        'tar',
        'gz',
        'gzip'
    ];

    public static function uploadCsv($request)
    {
        $csvsDir = storage_path() . '/csvs/';
        $rules = ['csv' => 'required'];
        $result = ['success' => true];
        $messages = [
            'csv.required'  => 'CSV file required',
            'csv.mimetypes' => 'File should be of text/csv mime type'
        ];
        $filesToDelete = [];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) 
        {
            $result['success'] = false;
            $result['data']    = $validator->messages()->get('csv');
        }
        else
        {
            $filename =  $request->csv->getClientOriginalName();
            $path     = $request->csv->move($csvsDir, $filename)->getPathName();
            $extension = $request->csv->getClientOriginalExtension();
            // Checking if uploaded file is archive
            if(in_array($extension, self::$archiveExtensions))
            {
                $existingFiles = scandir($csvsDir);
                $extracted = self::extractArchive($path, $extension, $csvsDir);
                // Adding archive to files for delete
                $filesToDelete[] = $path;
                // If archive extracted successfully and has one .csv file - continue
                if( $extracted && 
                    count($newFiles = array_values(array_diff(scandir($csvsDir), $existingFiles))) === 1 &&
                    File::extension($csvsDir . $newFiles[0]) === 'csv' )
                {
                    $path = $csvsDir . $newFiles[0];
                    $filename = $newFiles[0];
                }
                elseif(!$extracted)
                {
                    $result['success'] = false;
                    $result['data'][] = 'Archive extract failed';
                }
                elseif(count($newFiles = array_values(array_diff(scandir($csvsDir), $existingFiles))) !== 1)
                {
                    $filesToDelete = array_merge($filesToDelete, array_map(function($extractedFile) use ($csvsDir) {
                        return $csvsDir . $extractedFile;
                    }, $newFiles));
                    $result['success'] = false;
                    $result['data'][] = 'Archive must contain one .csv file';
                }
                elseif(File::extension($csvsDir . $newFiles[0]) !== 'csv')
                {
                    $filesToDelete[] = $csvsDir . $newFiles[0];
                    $result['success'] = false;
                    $result['data'][] = "Archive contains file $newFiles[0], but it's not .csv";
                }
            }
            // If not archive or csv setting success to false
            elseif(!in_array($extension, self::$archiveExtensions) && $extension !== 'csv')
            {
                $filesToDelete[] = $path;
                $result['success'] = false;
                $result['data'][] = 'File should be .csv or archive with one .csv';
            }
            // If all is ok - continue
            if($result['success'])
            {
                $result['data']['path'] = $path;
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
        self::deleteFiles($filesToDelete);
        return $result;
    }
    
    public static function deleteFiles($filesToDelete)
    {
        foreach( $filesToDelete as $file)
        {
            if (is_dir($file)) {
                File::deleteDirectory($file);
            }
            else 
            {
                unlink($file);
            }
        }
    }
    public static function extractArchive($path, $extension, $csvsDir)
    {
        $extracted = null;
        if($extension === 'zip')
        {
            $zip = new ZipArchive;
            $zipped = $zip->open($path);
            if ($zipped) {
                $extracted = $zip->extractTo($csvsDir);
            }
            $zip->close();
        }
        else
        {
            $phar = new PharData($path);
            $extracted = $phar->extractTo($csvsDir, null, true);
        }
        return $extracted;
    }
}