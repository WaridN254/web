<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'categories';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Add a global scope to filter out soft-deleted records using the custom is_deleted column.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_deleted', false);
        });
    }

    /**
     * Override the delete method to perform a soft delete.
     */
    public function delete()
    {
        $this->is_deleted = true;
        return $this->save();
    }
}
