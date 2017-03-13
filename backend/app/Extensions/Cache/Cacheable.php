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
    protected $exceptCacheAttributes = [];

    public static function clearCache($model)
    {
        $cache = Cache::driver();
        $tags = $model->getCacheTags();
        $cache = $tags ? $cache->tags($tags) : $cache;
        $method_exists = method_exists($model, 'acceptCacheFlush');
        if (!$method_exists or $method_exists and $model->acceptCacheFlush()) {
            $cache->flush();
            Cache::tags(array_merge($tags, ['attributes']))->flush();
            if ($model->exists and method_exists($model, 'flushRelations') and is_array($relations = $model->flushRelations())) {
                foreach ($relations as $relation) {
                    $relation = $model->$relation();
                    Cache::tags([$relation->getRelated()->getTable(), $relation->getTable() . '-' . $model->getKey()])->flush();
                }
            }
        }
    }

    public static function bootCacheable()
    {
        self::updating([get_called_class(), 'clearCache']);
        self::creating([get_called_class(), 'clearCache']);
        self::deleting([get_called_class(), 'clearCache']);
        self::saving([get_called_class(), 'clearCache']);
    }

    protected function mutateAttribute($key, $value)
    {
        if (!in_array($key, $this->exceptCacheAttributes)) {
            return Cache::tags(array_merge($this->getCacheTags(true), ['attributes']))->remember(
                $key,
                $this->cacheTime,
                function () use ($key, $value) {
                    return parent::mutateAttribute($key, $value);
                });
        }

        return parent::mutateAttribute($key, $value);
    }

    /**
     * @param bool $for_mutated
     * @return mixed
     */
    public function getCacheTags($for_mutated = false)
    {
        $cache_tags = (array)$this->cacheTags;
        array_push($cache_tags, $this->getTable());
        if (!$this->isCacheFull() || $for_mutated and $this->exists) {
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
        return sprintf("%s/%s",
            get_class($this),
            $this->getKey()
        ) . (isset($this->attributes['updated_at']) ? '-' . $this->updated_at->timestamp : '');
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
