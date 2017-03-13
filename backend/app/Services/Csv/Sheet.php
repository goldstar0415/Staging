<?php

namespace App\Services\Csv;

use Box\Spout\Reader\CSV\Sheet as ExtSheet;

class Sheet extends ExtSheet {
    public function __construct($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper, $offset)
    {
        /*if($offset)
        {
            echo 'In Sheet';
            dd($offset);
        }*/
        $this->rowIterator = new RowIterator($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper, $offset);
    }
}