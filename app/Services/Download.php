<?php



namespace App\Services;


use Auth;
use Session;
use SplFileInfo;

class Download
{

    const USER_AVATAR = 1;
    const USER_ALBUM = 2;

    /**
     * @var \SplFileObject|string
     */
    private $file;

    private $title;


    /**
     * @param string $file
     * @param string $title
     * @param int $mode
     */
    public function __construct($file, $title = '', $mode = self::USER_AVATAR)
    {
        $this->file = $file;
        $this->title = $title;
    }


    private function prepareSession()
    {
        $file_id = '';
        $file_path = '';
        if ($this->userfiles) {
            $file_path = storage_path('app/userfiles/' . Auth::id() . '/' . $this->file);
        } else {
            $file_path = storage_path('app/userfiles/' . $this->file);
        }
        if (Session::has('files')) {
            foreach (Session::get('files') as $id => $file) {
                if ($file === $file_path) {
                    $file_id = $id;
                    break;
                }
            }
        }
        if (empty($file_id)) {
            $file_id = bcrypt(uniqid(str_random(), true));
            Session::set('files.' . $file_id, $file_path);
        }
        return $file_id;
    }

    public static function getFile($id)
    {
        $file = Session::pull('files.' . $id);
        if (empty($file)) {
            abort(404);
        }

        return response()->download($file);
    }

    public function link()
    {
        return url('file?id=' . $this->prepareSession());
    }
}
