<?php

namespace App\Jobs;

use App\GeneratedUser;
use App\Jobs\Job;
use App\Mailers\AppMailer;
use App\Services\SpotsImportFile;
use App\Spot;
use App\SpotTypeCategory;
use App\SpotVote;
use App\User;
use Carbon\Carbon;
use Codesleeve\Stapler\AttachmentConfig;
use Codesleeve\Stapler\Stapler;
use File;
use Illuminate\Contracts\Bus\SelfHandling;
use Log;
use Storage;
use Validator;

/**
 * Class SpotsImport
 * Job for import spot from csv file
 * @package App\Jobs
 */
class SpotsImportCsv extends SpotsImport
{
    /**
     * @var SpotsImportFile
     */
    protected $importFile;

    /**
     * Create a new job instance.
     *
     * @param SpotsImportFile $importFile
     * @param array $data
     * @param string $type
     */
    public function __construct(SpotsImportFile $importFile, array $data, $type = self::EVENT)
    {
        parent::__construct($data, $type);

        $this->importFile = $importFile;
    }

    public function getSpots()
    {
        return $this->importFile->all();
    }
}
