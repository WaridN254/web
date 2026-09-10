<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasUuids;

    public function getRouteKeyName(): string
    {
        return 'name';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->whereRaw('LOWER(name) = ?', [strtolower((string) $value)])
            ->first();
    }

    protected $table = 'roles';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'can_manage_products' => 'boolean',
        'can_manage_variants' => 'boolean',
        'can_manage_services' => 'boolean',
        'can_manage_stock' => 'boolean',
        'can_view_reports' => 'boolean',
        'can_manage_discounts' => 'boolean',
        'can_manage_payment_methods' => 'boolean',
        'can_manage_tax_efris' => 'boolean',
        'can_manage_document_vault' => 'boolean',
        'can_manage_compliance' => 'boolean',
        'can_manage_customers' => 'boolean',
        'can_manage_users' => 'boolean',
        'can_cash_in_out' => 'boolean',
        'can_process_refunds' => 'boolean',
        'can_delete_sales' => 'boolean',
        'can_view_sales_history' => 'boolean',
        'can_settle_credit_payments' => 'boolean',
        'can_manage_quotations' => 'boolean',
        'can_manage_warranties' => 'boolean',
        'can_end_day' => 'boolean',
        'can_backup_data' => 'boolean',
        'can_restore_data' => 'boolean',
        'can_run_cloud_resync' => 'boolean',
        'can_edit_settings' => 'boolean',
        'can_manage_configurations' => 'boolean',
        'can_override_serial_verification' => 'boolean',
        'can_override_credit_limit' => 'boolean',
        'can_access_admin_dashboard' => 'boolean',
        'can_access_sales_dashboard' => 'boolean',
        'can_access_dashboard_2' => 'boolean',
        'can_view_email' => 'boolean',
        'can_compose_email' => 'boolean',
        'can_send_email' => 'boolean',
        'can_delete_email' => 'boolean',
        'can_manage_email_accounts' => 'boolean',
        'can_manage_email_labels' => 'boolean',
        'can_manage_email_templates' => 'boolean',
        'can_sync_email' => 'boolean',
        'can_view_localization_settings' => 'boolean',
        'can_manage_localization_settings' => 'boolean',
        'can_manage_languages' => 'boolean',
        'can_manage_translations' => 'boolean',
        'can_manage_currencies' => 'boolean',
        'can_manage_exchange_rates' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
