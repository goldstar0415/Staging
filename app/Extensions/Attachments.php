<?php

namespace App\Extensions;

use App\AlbumPhoto;
use App\Area;
use App\Link;
use App\Plan;
use App\Spot;

/**
 * Trait Attachments
 *
 * Use it for models which contains attachments
 *
 * @package App\Extensions
 */
trait Attachments
{
    /**
     * Add attachments field to model
     */
    public function addAttachments()
    {
        $attachments_relations = [
            'spots',
            'plans',
            'albumPhotos',
            'areas',
            'links'
        ];
        $this->addHidden($attachments_relations);
        $this->append('attachments');
    }

    /**
     * Attachments accessor
     *
     * @return array
     */
    public function getAttachmentsAttribute()
    {
        return [
            'spots' => $this->spots,
            'plans' => $this->plans,
            'album_photos' => $this->albumPhotos,
            'areas' => $this->areas,
            'links' => $this->links
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function spots()
    {
        return $this->morphToMany(Spot::class, 'spotable', 'spot_attachable')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function plans()
    {
        return $this->morphToMany(Plan::class, 'planable', 'plan_attachable')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function albumPhotos()
    {
        return $this->morphToMany(AlbumPhoto::class, 'album_photoable', 'album_photo_attachable')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function areas()
    {
        return $this->morphToMany(Area::class, 'areaable', 'area_attachable')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function links()
    {
        return $this->morphMany(Link::class, 'linkable');
    }

    public function countAttachments()
    {
        return $this->spots()->withoutNewest()->count() +
                $this->plans()->count() +
                $this->albumPhotos()->count() +
                $this->areas()->count() +
                $this->links()->count();
    }
}
