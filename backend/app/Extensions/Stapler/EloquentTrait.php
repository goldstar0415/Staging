<?php

namespace App\Extensions\Stapler;

use Codesleeve\Stapler\ORM\EloquentTrait as EloquentTr;
use App\Extensions\Stapler\AttachmentFactory;

trait EloquentTrait
{
    use EloquentTr;

    /**
     * Add a new file attachment type to the list of available attachments.
     * This function acts as a quasi constructor for this trait.
     *
     * @param string $name
     * @param array  $options
     */
    public function hasAttachedFile($name, array $options = [])
    {
        $attachment = AttachmentFactory::create($name, $options);
        $attachment->setInstance($this);
        $this->attachedFiles[$name] = $attachment;
    }
}
