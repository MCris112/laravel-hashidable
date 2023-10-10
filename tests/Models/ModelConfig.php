<?php

namespace Mcris112\LaravelHashidable\Tests\Models;

use Mcris112\LaravelHashidable\Hashidable;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Mcris112\LaravelHashidable\HashidableConfigInterface;

class ModelConfig extends LaravelModel implements HashidableConfigInterface
{
    use Hashidable;

    protected $table = 'models';

    public function hashidableConfig()
    {
        return array_merge(config('hashidable'), ['length' => 64]);
    }
}
