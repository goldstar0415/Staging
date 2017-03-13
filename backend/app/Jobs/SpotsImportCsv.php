<?php

namespace App\Jobs;

use App\Mailers\AppMailer;
use App\Services\SpotsImportFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Excel;

/**
 * Class SpotsImport
 * Job for import spot from csv file
 * @package App\Jobs
 */
class SpotsImportCsv extends SpotsImport implements ShouldQueue
{
    use InteractsWithQueue;
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
    public function __construct(array $data, $type = self::EVENT)
    {
        parent::__construct($data, $type);
    }

    public function getSpots()
    {
        return $this->importFile->all();
    }

    public function handle(AppMailer $mailer)
    {
        $this->importFile = app(SpotsImportFile::class, [app(), app(Excel::class), $this->data['document']]);
        
        return parent::handle($mailer);
    }
}
