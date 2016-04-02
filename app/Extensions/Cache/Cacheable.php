<?php

namespace App\Extensions\Cache;

use Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait Cacheable
{
    protected $cacheFull = false;
    protected $cacheTags = [];
    protected $cacheTime = 60;

    public static function bootCacheable()
    {
        self::updating(function ($model) {
            $cache = Cache::driver();
            $tags = $model->getCacheTags();
            $cache = $tags ? $cache->tags($tags) : $cache;
            if (method_exists($model, 'acceptCacheFlush')) {
                if ($model->acceptCacheFlush()) {
                    $cache->flush();
                    Cache::tags(array_merge($tags, ['attributes']))->flush();
                }
            } else {
                $cache->flush();
            }
        });
    }

    protected function mutateAttribute($key, $value)
    {
        return Cache::tags(array_merge($this->getCacheTags(), ['attributes']))->remember(
            $key, 
            $this->cacheTime, 
            function () use ($key, $value) {
            return parent::mutateAttribute($key, $value);
        });        
    }

    /**
     * @return mixed
     */
    public function getCacheTags()
    {
        $cache_tags = (array)$this->cacheTags;
        array_push($cache_tags, $this->getTable());
        if (!$this->isCacheFull() and $this->exists) {
            array_push($cache_tags, $this->getCacheKey());
        }
        
        return $cache_tags;
    }

    /**
     * @return boolean
     */
    public function isCacheFull()
    {
        return !$this->cacheFull and !$this->timestamps ?: false;
    }
    
    /**
     * Calculate a unique cache key for the model instance.
     */
    public function getCacheKey()
    {
        return sprintf("%s/%s-%s",
            get_class($this),
            $this->getKey(),
            $this->updated_at->timestamp
        );
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();
        $grammar = $conn->getQueryGrammar();
        $builder = new Builder($conn, $grammar, $conn->getPostProcessor());
        if (!empty($tags = $this->getCacheTags())) {
            $builder->cacheTags($tags);
        }

        return $builder->remember($this->cacheTime);
    }
}
