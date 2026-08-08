<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ViolationType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description'];
}
