<?php

namespace App\Services;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\ExcelFile;

/**
 * Class SpotsImportFile
 * @package App\Services
 */
class SpotsImportFile extends ExcelFile
{

    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $lineEnding = '\n';

    /**
     * @var string importing csv file
     */
    protected $importing_file;

    /**
     * @param Application $app
     * @param Excel $excel
     * @param string $file
     */
    public function __construct(Application $app, Excel $excel, $file)
    {
        $this->importing_file = $file;
        parent::__construct($app, $excel);
    }

    /**
     * Get importing file
     * @return string
     */
    public function getFile()
    {
        return $this->importing_file;
    }

    /**
     * Get partitioned dir by id
     *
     * @param $id
     * @return null|string
     */
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
