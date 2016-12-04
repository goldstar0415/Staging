<?php

namespace App\Services\Json;

class ReaderException extends \Exception
{
    const TEXT_OPT = 'Options must be within array';
    const TEXT_CNT = 'Count of items must be integer';
    const TEXT_OFF = 'Offset must be integer';
    const TEXT_FEM = 'File path is empty';
    const TEXT_FNE = 'File not exists';
    const TEXT_NFL = 'Not a file';
    const TEXT_FNS = 'File not set';
    const TEXT_CNS = 'Items count not set';
}