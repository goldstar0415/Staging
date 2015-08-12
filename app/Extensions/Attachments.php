<?php

namespace App\Extensions;

use App\AlbumPhoto;
use App\Area;
use App\Spot;

trait Attachments
{
    public function addAttachments()
    {
        $attachments_relations = [
            'spots',
            'albumPhotos',
            'areas'
        ];
        $this->addHidden($attachments_relations);
        $this->append('attachments');
    }

    public function getAttachmentsAttribute()
    {
        return [
            'spots' => $this->spots,
            'album_photos' => $this->albumPhotos,
            'areas' => $this->areas
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function spots()
    {
        return $this->belongsToMany(Spot::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function albumPhotos()
    {
        return $this->belongsToMany(AlbumPhoto::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }
}
