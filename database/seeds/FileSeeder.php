<?php


namespace Seeds;

use App\BaseModel;
use App\Services\Uploader\Upload;
use File;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileSeeder
{
    private $is_randomName = false;

    public function saveModelFile(BaseModel $model, $path, $name = '', $mime = 'image/jpeg') {
        $file = new SplFileInfo($path);
        $file = new UploadedFile($path, $file->getFilename(), $mime);
        /**
         * @var Upload $upload
         */
        $upload = app(Upload::class);
        $upload->make($file, $model, $name);
        if ($this->is_randomName) {
            $upload->randomName();
        }
        $upload_path = $upload->getFullPath();
        if (!File::isDirectory($full_path = dirname($upload_path))) {
            File::makeDirectory($full_path, 493, true);
        }
        File::move($path, $upload_path . '.' . $upload->file->getClientOriginalExtension());
    }

    public function randomName()
    {
        $this->is_randomName = true;

        return $this;
    }
}