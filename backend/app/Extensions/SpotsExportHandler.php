<?php

namespace App\Extensions;

use Maatwebsite\Excel\Files\ExportHandler;

class SpotsExportHandler implements ExportHandler
{
    /**
     * @inheritDoc
     */
    public function handle($file)
    {
        return $file->sheet('sheet', function ($sheet) use ($file) {
            /**
             * @var \Maatwebsite\Excel\Classes\LaravelExcelWorksheet $sheet
             * @var \App\Extensions\SpotsExport $file
             */
            $sheet->row(1, $file->getHeaders());
            $sheet->rows($file->getData());
        })->download('csv');
    }

}