<?php

namespace App\Extensions\Cache;

use App\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyCache extends BelongsToMany
{
    public function touchIfTouching()
    {
        parent::touchIfTouching();
        
        BaseModel::clearCache($this->getParent());
    }
}
