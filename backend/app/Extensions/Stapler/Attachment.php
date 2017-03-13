<?php

namespace App\Extensions\Stapler;

use Codesleeve\Stapler\Attachment as AttachmentExt;

class Attachment extends AttachmentExt
{
    /**
     * Generates the url to an uploaded file (or a resized version of it).
     *
     * @param string $styleName
     *
     * @return string
     */
    public function url($styleName = '')
    {
        if ($this->originalFilename()) {
            $url = $this->storageDriver->url($styleName, $this);
            $changedUrl = str_replace('//s3.amazonaws.com/','//', $url);
            return $changedUrl;
        }

        return $this->defaultUrl($styleName);
    }
}
