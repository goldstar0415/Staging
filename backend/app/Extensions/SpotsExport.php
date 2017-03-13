<?php

namespace App\Extensions;

use Maatwebsite\Excel\Files\NewExcelFile;

class SpotsExport  extends NewExcelFile
{
    /**
     * @var array File data
     */
    protected $data = [];

    /**
     * @var array File headers (1st row)
     */
    protected $headers = [];
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

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
}
