<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferSerial extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'stock_transfer_serials';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'serial_id');
    }
}
