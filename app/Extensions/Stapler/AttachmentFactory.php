<?php

namespace App\Extensions\Stapler;

use App\Extensions\Stapler\Attachment as Att;
use Codesleeve\Stapler\Factories\Attachment;

use Codesleeve\Stapler\Stapler;
use Codesleeve\Stapler\Factories\Storage as StorageFactory;

class AttachmentFactory extends Attachment
{
    /**
     * Create a new attachment object.
     *
     * @param string $name
     * @param array  $options
     *
     * @return App\Extensions\Stapler\Attachment
     */
    public static function create($name, array $options)
    {
        $options = static::mergeOptions($options);
        Stapler::getValidatorInstance()->validateOptions($options);
        list($config, $interpolator, $resizer) = static::buildDependencies($name, $options);

        $className = Att::class;
        $attachment = new $className($config, $interpolator, $resizer);
        $storageDriver = StorageFactory::create($attachment);
        $attachment->setStorageDriver($storageDriver);
        return $attachment;
    }
}
