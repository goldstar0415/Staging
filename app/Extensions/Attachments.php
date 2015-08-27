<?php

namespace App\Extensions;

use App\AlbumPhoto;
use App\Area;
use App\Plan;
use App\Spot;

trait Attachments
{
    public function addAttachments()
    {
        $attachments_relations = [
            'spots',
            'plans',
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
            'plans' => $this->plans,
            'album_photos' => $this->albumPhotos,
            'areas' => $this->areas
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function spots()
    {
        return $this->morphToMany(Spot::class, 'spotable', 'spot_attachable')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function plans()
    {
        return $this->morphToMany(Plan::class, 'planable', 'plan_attachable')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function albumPhotos()
    {
        return $this->morphToMany(AlbumPhoto::class, 'album_photoable', 'album_photo_attachable')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function areas()
    {
        return $this->morphToMany(Area::class, 'areable', 'area_attachable')->withTimestamps();
    }
}
