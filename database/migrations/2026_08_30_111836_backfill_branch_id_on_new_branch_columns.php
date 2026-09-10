<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // layaway_plans → from transactions
        DB::statement("
            UPDATE layaway_plans lp
            SET branch_id = t.branch_id
            FROM transactions t
            WHERE lp.transaction_id = t.id
              AND lp.branch_id IS NULL
              AND t.branch_id IS NOT NULL
        ");

        // quotations → from tenant default branch
        DB::statement("
            UPDATE quotations q
            SET branch_id = (
                SELECT b.id::text FROM branches b
                WHERE b.tenant_id::text = q.tenant_id::text
                  AND b.is_default = true
                LIMIT 1
            )
            WHERE q.branch_id IS NULL
        ");

        // quotation_items → from parent quotation
        DB::statement("
            UPDATE quotation_items qi
            SET branch_id = q.branch_id
            FROM quotations q
            WHERE qi.quotation_id::text = q.id::text
              AND qi.branch_id IS NULL
              AND q.branch_id IS NOT NULL
        ");

        // cash_drawer_logs → from sessions
        DB::statement("
            UPDATE cash_drawer_logs cdl
            SET branch_id = s.branch_id
            FROM sessions s
            WHERE cdl.user_id::text = s.user_id::text
              AND cdl.branch_id IS NULL
              AND s.branch_id IS NOT NULL
        ");

        // transaction_items → from parent transaction
        DB::statement("
            UPDATE transaction_items ti
            SET branch_id = t.branch_id
            FROM transactions t
            WHERE ti.transaction_id::text = t.id::text
              AND ti.branch_id IS NULL
              AND t.branch_id IS NOT NULL
        ");

        // purchase_items → from parent purchase_order
        DB::statement("
            UPDATE purchase_items pi
            SET branch_id = po.branch_id
            FROM purchase_orders po
            WHERE pi.purchase_id::text = po.id::text
              AND pi.branch_id IS NULL
              AND po.branch_id IS NOT NULL
        ");

        // stock_audit_items → from parent stock_audit
        DB::statement("
            UPDATE stock_audit_items sai
            SET branch_id = sa.branch_id
            FROM stock_audits sa
            WHERE sai.audit_id::text = sa.id::text
              AND sai.branch_id IS NULL
              AND sa.branch_id IS NOT NULL
        ");

        // stock_transfers → from from_branch_id
        DB::statement("
            UPDATE stock_transfers st
            SET branch_id = st.from_branch_id
            WHERE st.branch_id IS NULL
              AND st.from_branch_id IS NOT NULL
        ");

        // stock_transfer_items → from parent stock_transfer
        DB::statement("
            UPDATE stock_transfer_items sti
            SET branch_id = st.branch_id
            FROM stock_transfers st
            WHERE sti.transfer_id::text = st.id::text
              AND sti.branch_id IS NULL
              AND st.branch_id IS NOT NULL
        ");

        // stock_transfer_serials → from parent stock_transfer
        DB::statement("
            UPDATE stock_transfer_serials sts
            SET branch_id = st.branch_id
            FROM stock_transfers st
            WHERE sts.transfer_id::text = st.id::text
              AND sts.branch_id IS NULL
              AND st.branch_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        // irreversible — nullable columns, data preserved
    }
};
