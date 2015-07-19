<?php



namespace App\Services\Uploader;


use App\BaseModel;
use Session;

class Download extends Uploader
{
    private function prepareSession()
    {
        $file_path = $this->getFullPath();
        if (!$file_path) {
            return false;
        }
        if (is_array($file_path)) {
            $files = [];
            foreach ($file_path as $file) {
                $files[] = $this->prepareFile($file);
            }
            return $files;
        }
        return $this->prepareFile($file_path);
    }

    public static function getFile($id)
    {
        $file = Session::pull('files.' . $id);
        if (empty($file)) {
            abort(404);
        }

        return response()->download($file);
    }

    public function link(BaseModel $model, $name = '')
    {
        $this->model = $model;
        $this->name = $name;
        $files = $this->prepareSession();
        if (is_array($files)) {
            $links = [];
            foreach ($files as $file) {
                $links[] = url('file?id=' . $file);
            }
            return $links;
        }
        return $files ? url('file?id=' . $files) : false;
    }

    /**
     * @param $file_path
     * @return array|string
     */
    private function prepareFile($file_path)
    {
        $file_id = '';
        if (Session::has('files')) {
            foreach (Session::get('files') as $id => $file) {
                if ($file === $file_path) {
                    return $id;
                }
            }
        }
        $file_id = str_random(24);
        Session::set('files.' . $file_id, $file_path);
        return $file_id;
    }
}
