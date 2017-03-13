<?php

namespace App\Services\Csv;

use Box\Spout\Reader\CSV\RowIterator as ExtRowIterator;
use Box\Spout\Common\Helper\EncodingHelper;

class RowIterator extends ExtRowIterator {
    
    protected $offset;

    public function __construct($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineDelimiter, $globalFunctionsHelper, $offset)
    {
        $this->filePointer = $filePointer;
        $this->fieldDelimiter = $fieldDelimiter;
        $this->fieldEnclosure = $fieldEnclosure;
        $this->encoding = $encoding;
        $this->inputEOLDelimiter = $endOfLineDelimiter;
        $this->globalFunctionsHelper = $globalFunctionsHelper;
        $this->offset = $offset;
        
        /*if($offset)
        {
            echo 'In RowIterator';
            dd($offset);
        }*/

        $this->encodingHelper = new EncodingHelper($globalFunctionsHelper);
    }
    
    public function rewind()
    {
        $this->rewindAndSkipBom();

        $this->numReadRows = 0;
        $this->rowDataBuffer = null;

        $this->next();
    }

    protected function rewindAndSkipBom()
    {
        $byteOffsetToSkipBom = $this->encodingHelper->getBytesOffsetToSkipBOM($this->filePointer, $this->encoding);
        
        if($this->offset)
        {
            $this->globalFunctionsHelper->fseek($this->filePointer, $this->offset);
        }
        else
        {
            $this->globalFunctionsHelper->fseek($this->filePointer, $byteOffsetToSkipBom);
        }
    }
}