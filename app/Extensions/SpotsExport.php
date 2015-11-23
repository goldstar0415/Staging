<?php

namespace App\Extensions;

use Maatwebsite\Excel\Files\NewExcelFile;

class SpotsExport  extends NewExcelFile
{
    protected $data;
    /**
     * @inheritDoc
     */
    public function getFilename()
    {
        return 'spots-exported';
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}
