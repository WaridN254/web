<?php

namespace App\Filament\Tenant\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    /**
     * Build a consistent permission toggle row.
     */
    private static function perm(string $name, string $label, string $description): Toggle
    {
        return Toggle::make($name)
            ->label($label)
            ->helperText($description)
            ->onColor('success')
            ->inline(false);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Details')
                    ->description('Set up the basic identity of this role.')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Role Name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->placeholder('e.g. Store Manager')
                            ->helperText('This name appears in the sidebar and on user accounts.'),
                    ]),

                Section::make('Permissions')
                    ->description('Toggle each permission to control what this role can access and do.')
                    ->icon('heroicon-o-lock-open')
                    ->columns(2)
                    ->schema([
                        Section::make('Dashboard Access')
                            ->description('Which dashboards this role can open.')
                            ->icon('heroicon-o-home')
                            ->schema([
                                self::perm('can_access_admin_dashboard', 'Admin Dashboard', 'Can open the main admin dashboard (Overview).'),
                                self::perm('can_access_sales_dashboard', 'Sales Dashboard', 'Can open the Sales Dashboard.'),
                                self::perm('can_access_dashboard_2', 'Dashboard 2', 'Can open the second admin dashboard (Finance & Stock).'),
                            ]),

                        Section::make('Catalog & Inventory')
                            ->description('Control of products, services and stock levels.')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                self::perm('can_manage_products', 'Products', 'Create, edit and delete products.'),
                                self::perm('can_manage_services', 'Services', 'Manage billable services.'),
                                self::perm('can_manage_stock', 'Stock & Inventory', 'Manage stock levels and movements.'),
                            ]),

                        Section::make('Sales & Transactions')
                            ->description('Point of sale actions, customers and money handling.')
                            ->icon('heroicon-o-shopping-bag')
                            ->columnSpan(2)
                            ->columns(3)
                            ->schema([
                                self::perm('can_manage_customers', 'Customers', 'Manage the customer directory and balances.'),
                                self::perm('can_cash_in_out', 'Cash In/Out', 'Record cash deposits and withdrawals.'),
                                self::perm('can_process_refunds', 'Refunds', 'Issue refunds for sales.'),
                                self::perm('can_delete_sales', 'Delete Sales', 'Remove completed sales transactions.'),
                                self::perm('can_view_sales_history', 'Sales History', 'View past sales records.'),
                                self::perm('can_settle_credit_payments', 'Credit Payments', 'Settle outstanding customer credit.'),
                                self::perm('can_manage_quotations', 'Quotations', 'Create and manage quotations.'),
                                self::perm('can_manage_warranties', 'Warranties', 'Manage product warranties.'),
                                self::perm('can_manage_discounts', 'Discounts', 'Create and apply discounts.'),
                                self::perm('can_manage_payment_methods', 'Payment Methods', 'Configure accepted payment methods.'),
                                self::perm('can_override_credit_limit', 'Override Credit Limit', 'Allow credit sales above a customer\'s credit limit.'),
                                self::perm('can_end_day', 'End of Day', 'Run the end-of-day close out.'),
                                self::perm('can_override_serial_verification', 'Override Serial Verification', 'Bypass serial number checks at checkout.'),
                            ]),

                        Section::make('Administration & Settings')
                            ->description('Reports, compliance, users and system configuration.')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->columnSpan(2)
                            ->columns(3)
                            ->schema([
                                self::perm('can_view_reports', 'Reports', 'Access all business reports.'),
                                self::perm('can_manage_tax_efris', 'Tax & EFRIS', 'Manage tax categories and EFRIS integration.'),
                                self::perm('can_manage_document_vault', 'Document Vault', 'Manage stored business documents.'),
                                self::perm('can_manage_compliance', 'Compliance', 'Manage compliance reminders.'),
                                self::perm('can_manage_users', 'Users & Security', 'Create and manage user accounts and roles.'),
                                self::perm('can_backup_data', 'Backup Data', 'Run data backups.'),
                                self::perm('can_restore_data', 'Restore Data', 'Restore from backups.'),
                                self::perm('can_run_cloud_resync', 'Cloud Resync', 'Trigger cloud synchronisation.'),
                                self::perm('can_edit_settings', 'General Settings', 'Edit business and receipt settings.'),
                                self::perm('can_manage_configurations', 'Configurations', 'Manage system-wide configuration.'),
                            ]),

                        Section::make('Communication')
                            ->description('Email access, sending, and account management.')
                            ->icon('heroicon-o-envelope')
                            ->columnSpan(2)
                            ->columns(3)
                            ->schema([
                                self::perm('can_view_email', 'View Email', 'Access the email inbox and read emails.'),
                                self::perm('can_compose_email', 'Compose Email', 'Create new email messages.'),
                                self::perm('can_send_email', 'Send Email', 'Send emails via configured accounts.'),
                                self::perm('can_delete_email', 'Delete Email', 'Delete emails from the mailbox.'),
                                self::perm('can_manage_email_accounts', 'Email Accounts', 'Add, edit and configure email accounts.'),
                                self::perm('can_manage_email_labels', 'Email Labels', 'Create and manage email labels/tags.'),
                                self::perm('can_manage_email_templates', 'Email Templates', 'Create and manage email templates.'),
                                self::perm('can_sync_email', 'Sync Email', 'Manually trigger email synchronization.'),
                            ]),

                        Section::make('Localization')
                            ->description('Language, currency, and regional settings.')
                            ->icon('heroicon-o-globe-alt')
                            ->columnSpan(2)
                            ->columns(3)
                            ->schema([
                                self::perm('can_view_localization_settings', 'View Localization', 'View language and currency settings.'),
                                self::perm('can_manage_localization_settings', 'Manage Localization', 'Change tenant language and currency settings.'),
                                self::perm('can_manage_languages', 'Manage Languages', 'Enable, disable and configure languages.'),
                                self::perm('can_manage_translations', 'Manage Translations', 'Edit and manage translation strings.'),
                                self::perm('can_manage_currencies', 'Manage Currencies', 'Enable, disable and configure currencies.'),
                                self::perm('can_manage_exchange_rates', 'Exchange Rates', 'View and manage currency exchange rates.'),
                            ]),

                        Section::make('Branch Management')
                            ->description('Control branch visibility and management.')
                            ->icon('heroicon-o-building-storefront')
                            ->columnSpan(2)
                            ->columns(3)
                            ->schema([
                                self::perm('can_view_all_branches', 'View All Branches', 'See all branches across the business. Without this, users only see their assigned branches.'),
                                self::perm('can_manage_branches', 'Manage Branches', 'Create, edit and deactivate branches.'),
                                self::perm('can_manage_stock_transfers', 'Stock Transfers', 'Create and manage stock transfers between branches.'),
                            ]),
                    ]),
            ]);
    }
}