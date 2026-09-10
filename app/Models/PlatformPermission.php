<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformPermission extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform_permissions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'slug', 'group', 'description'];
}
