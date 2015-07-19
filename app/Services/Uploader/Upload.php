<?php


namespace App\Services\Uploader;


use App\BaseModel;
use File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload extends Uploader
{
    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile|array
     */
    protected $file;

    public function save()
    {
        $ext = $this->file->getClientOriginalExtension();
        $file_path = $this->getPath();
        $file_name = $this->getName();
        if ($existed_file = $this->isFileExists($file_path . '/' . $file_name)) {
            File::delete($existed_file);
        }
        $this->file->move($file_path, $file_name . '.' . $ext);
    }

    public function make(UploadedFile $file, BaseModel $model, $name = null)
    {
        $this->file = $file;
        $this->model = $model;
        $this->name = $name;

        return $this;
    }


}