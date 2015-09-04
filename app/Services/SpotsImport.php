<?php

namespace App\Services;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\ExcelFile;

class SpotsImport extends ExcelFile
{

    protected $delimiter = ';';
    protected $enclosure = '"';
    protected $lineEnding = '\n';

    protected $importing_file;

    /**
     * @param Application $app
     * @param Excel $excel
     */
    public function __construct(Application $app, Excel $excel, $file)
    {
        $this->importing_file = storage_path('app/' . $file);
        parent::__construct($app, $excel);
    }


    public function getFile()
    {
        return $this->importing_file;
    }

    public function getPartitionedDir($id)
    {
        if (is_numeric($id)) {
            return implode('/', str_split(sprintf('%09d', $id), 3));
        } elseif (is_string($id)) {
            return implode('/', array_slice(str_split($id, 3), 0, 3));
        } else {
            return null;
        }
    }
}
