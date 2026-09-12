<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountManagementPage extends Page
{
    use HasPermission;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings');
    }

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('navigation.account_management');
    }

    protected static ?string $slug = 'account-management';

    protected string $view = 'filament.tenant.pages.account-management';

    public string $full_name = '';
    public string $email = '';
    public string $phone = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->full_name = $user->full_name ?? $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
    }

    public function getTitle(): string
    {
        return __('navigation.account_management');
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function save(): void
    {
        $user = auth()->user();

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->getKey() . ',id'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->new_password !== '') {
            $rules['current_password'] = ['required', 'string'];
            $rules['new_password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validator = Validator::make([
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'current_password' => $this->current_password,
            'new_password' => $this->new_password,
            'new_password_confirmation' => $this->new_password_confirmation,
        ], $rules);

        if ($this->new_password !== '') {
            $validator->after(function ($v) use ($user) {
                if (! Hash::check($this->current_password, $user->getAuthPassword())) {
                    $v->errors()->add('current_password', 'Your current password is incorrect.');
                }
            });
        }

        if ($validator->fails()) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Validation Error',
                'description' => collect($validator->errors()->all())->take(3)->implode("\n"),
            ]);
            return;
        }

        $user->full_name = $this->full_name;
        $user->email = $this->email;
        $user->phone = $this->phone ?: null;

        if ($this->new_password !== '') {
            $user->password_hash = Hash::make($this->new_password);
        }

        $user->save();

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this$this->dispatch('gooey-toast', [
            'type' => 'success',
            'title' => 'Account Updated',
            'description' => 'Your account details have been saved.',
        ]);
    }
}