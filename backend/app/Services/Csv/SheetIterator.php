<?php

namespace App\Services\Csv;

use Box\Spout\Reader\CSV\SheetIterator as ExtSheetIterator;

class SheetIterator extends ExtSheetIterator {
    public function __construct($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper, $offset)
    {
        //dd($offset);
        $this->sheet = new Sheet($filePointer, $fieldDelimiter, $fieldEnclosure, $encoding, $endOfLineCharacter, $globalFunctionsHelper, $offset);
    }
}
