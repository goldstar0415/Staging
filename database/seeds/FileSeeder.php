<?php


namespace Seeds;

use SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileSeeder
{
    public function makeUploadedFile($path, $mime = 'image/jpeg') {
        $file = new SplFileInfo($path);
        return new UploadedFile($file->getPath(), $file->getFilename(), $mime);
    }
}