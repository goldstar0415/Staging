<?php


namespace App\Services;


use App\AlbumPhoto;
use App\BaseModel;
use App\Friend;
use App\Spot;
use App\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    const USER_AVATAR = 1;
    const USER_ALBUM_PHOTO = 2;
    const FRIEND_AVATAR = 3;
    const SPOT_PHOTO = 4;
    const SPOT_COVER = 5;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile|array
     */
    protected $file;

    protected $mode;

    protected $model;

    public function __construct($file, $mode, BaseModel $model)
    {
        $this->file = $file;
        $this->mode = $mode;
        $this->model = $model;
    }

    private function saveUploadedFile()
    {
        $file_path = '';
        $file_name = '';
        switch ($this->mode) {
            case self::USER_AVATAR:
                if ($this->model instanceof User) {
                    $file_path = storage_path("app/user_files/user_{$this->model->id}");
                    $file_name = "avatar." . $this->file->getExtension();
                }
                break;
            case self::USER_ALBUM_PHOTO:
                if ($this->model instanceof AlbumPhoto) {
                    $file_path = storage_path(
                        "app/user_files/user_{$this->model->album->user->id}/albums/{$this->model->id}"
                    );
                    $file_name = "photo_{$this->model->id}." . $this->file->getExtension();
                }
                break;
            case self::FRIEND_AVATAR:
                if ($this->model instanceof Friend) {
                    $file_path = storage_path(
                        "app/user_files/user_{$this->model->user->id}/friends"
                    );
                    $file_name = "friend_{$this->model->id}." . $this->file->getExtension();
                }
                break;
            case self::SPOT_COVER:
                if ($this->model instanceof Spot) {
                    $file_path = storage_path(
                        "app/spot_files/spot_{$this->model->id}"
                    );
                    $file_name = "cover." . $this->file->getExtension();
                }
                break;
            case self::SPOT_PHOTO:
                if ($this->model instanceof Spot) {
                    $file_path = storage_path(
                        "app/spot_files/spot_{$this->model->id}"
                    );
                    $file_name = str_random() . '.' . $this->file->getExtension();
                }
                break;
            default:
                break;
        }
        $this->file->move($file_path, $file_name);
    }
}