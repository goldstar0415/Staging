<?php


namespace App\Services\Uploader;


use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;

abstract class Uploader
{

    protected $name;

    protected $random_name = false;

    protected $model;

    protected $file_manager;

    public function __construct(Filesystem $file_manager)
    {
        $this->file_manager = $file_manager;
    }

    public function randomName()
    {
        $this->random_name = true;

        return $this;
    }

    /**
     * @param string $path_pattern
     * @return string
     */
    public function path($path_pattern = '')
    {
        $file_path = preg_replace_callback(
            ['/name/', '/id/', '/[^\/]*_rel/'],
            [$this, 'matchPattern'],
            $path_pattern ?: $this->model->files_dir
        );
        return $file_path;
    }

    /**
     * @param $append
     * @return mixed
     */
    public function fileBaseName($append = '')
    {

        $name = last(explode('_', snake_case(class_basename($this->model))));
        if (is_array($append)) {
            $name .= '_' . implode('_', $append);
        } else if ($append !== '') {
            $name .= '_' . $append;
        }
        return $name;
    }

    public static function rules()
    {
        return [
            'image' => 'required|image|size:1000'
        ];
    }

    /**
     * @param array $matches
     * @return Closure
     */
    public function matchPattern($matches)
    {
        $path = '';
        $name = $this->fileBaseName();
        $main_model = $this->model;
        foreach ($matches as $match) {
            switch ($match) {
                case 'name':
                    $path .= $name . '_files';
                    break;
                case 'id':
                    $path .= $name . '_' . $this->model->id;
                    break;
                default:
                    $relation = explode('_', $match)[0];
                    $this->model = $this->model->$relation;
                    $path .= $this->path();
                    break;
            }
        }
        $this->model = $main_model;
        return $path;
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        $file_path = storage_path('upload/');
        if ($this->model->files_dir !== '') {
            $file_path .= $this->path();
        } else {
            $file_path .= $this->path('name/id');
        }
        return $file_path;
    }

    /**
     * @return mixed|null
     */
    protected function getName()
    {
        $file_name = '';
        if (!$this->name) {
            if ($this->random_name) {
                if ($this instanceof Download) {
                    $file_name = $this->fileBaseName() . '_';
                } else {
                    $file_name = $this->fileBaseName(str_random(24));
                }
            } else {
                $file_name = $this->fileBaseName($this->model->id);
            }
        } else {
            $file_name = $this->name;
        }
        return $file_name;
    }

    public function getFilePath()
    {
        $dir = $this->getPath();
        $name = $this->getName();

        return $this->isFileExists($dir . '/' . $name);
    }

    public function getFullPath()
    {
        $dir = $this->getPath();
        $name = $this->getName();

        return $dir . '/' . $name;
    }

    /**
     * @param $file_path
     * @return string|bool
     */
    protected function isFileExists($file_path)
    {
        $pattern = $file_path . ($this->random_name ? '*.*' : '.*');
        $matches = glob($pattern);
        if (empty($matches)) {
            return false;
        } else if ($this->random_name) {
            return $matches;
        }
        return $matches[0];
    }

}