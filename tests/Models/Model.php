<?php

namespace Mcris112\LaravelHashidable\Tests\Models;

use Mcris112\LaravelHashidable\Hashidable;
use Illuminate\Database\Eloquent\Model as LaravelModel;

class Model extends LaravelModel
{
    use Hashidable;
}
