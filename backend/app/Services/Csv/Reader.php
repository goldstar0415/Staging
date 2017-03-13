<?php

namespace App\Services\Csv;

use Box\Spout\Reader\CSV\Reader as ExtReader;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\EncodingHelper;

class Reader extends ExtReader
{
    public $offset;
    
    protected $fieldDelimiter = ',';

    protected $fieldEnclosure = '"';

    protected $encoding = EncodingHelper::ENCODING_UTF8;

    protected $endOfLineCharacter = "\n";

    public function __construct()
    {
        $this->setGlobalFunctionsHelper(new GlobalFunctionsHelper());
    }
    
    public function getFilePointerOffset()
    {
        return ftell($this->filePointer);
    }
    
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    protected function openReader($filePath)
    {
        $this->autoDetectLineEndings = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');

        $this->filePointer = $this->globalFunctionsHelper->fopen($filePath, 'r');
        if (!$this->filePointer) {
            throw new IOException("Could not open file $filePath for reading.");
        }

        $this->sheetIterator = new SheetIterator(
            $this->filePointer,
            $this->fieldDelimiter,
            $this->fieldEnclosure,
            $this->encoding,
            $this->endOfLineCharacter,
            $this->globalFunctionsHelper,
            $this->offset
        );
    }
    
    
}