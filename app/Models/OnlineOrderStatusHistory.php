<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineOrderStatusHistory extends Model
{
    use \App\Models\Traits\BelongsToTenant, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $table = 'online_order_status_history';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }
}
