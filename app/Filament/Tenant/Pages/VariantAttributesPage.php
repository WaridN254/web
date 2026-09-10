<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\VariantAttribute;
use App\Models\VariantAttributeValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;

class VariantAttributesPage extends Page implements HasTable
{
    use HasPermission;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 1;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.variant_attributes');
    }


    protected static ?string $title = 'Variant Attributes';

    protected string $view = 'filament.tenant.pages.variant-attributes-page';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showValuesModal = false;

    public ?string $editingAttributeId = null;

    public ?string $viewingAttributeName = null;

    public array $viewingValues = [];

    public ?string $attributeName = null;

    public ?string $attributeValues = null;

    public ?string $editAttributeName = null;

    public ?string $editAttributeValues = null;

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;

        return $table
            ->query(
                VariantAttribute::query()
                    ->where('tenant_id', $tenantId)
                    ->with('values')
                    ->withCount('values')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Attribute')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('values_count')
                    ->label('Values')
                    ->numeric()
                    ->sortable()
                    ->color('primary'),
                TextColumn::make('values.value')
                    ->label('Options')
                    ->limit(50)
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->actions([
                Action::make('viewValues')
                    ->label('View Values')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(function (VariantAttribute $record) {
                        $this->viewingAttributeName = $record->name;
                        $this->viewingValues = $record->values()
                            ->orderBy('value')
                            ->get()
                            ->map(fn ($v) => [
                                'id' => $v->id,
                                'value' => $v->value,
                                'is_active' => $v->is_active,
                            ])
                            ->toArray();
                        $this->showValuesModal = true;
                    })
                    ->iconButton(),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->action(function (VariantAttribute $record) {
                        $this->editingAttributeId = $record->id;
                        $this->editAttributeName = $record->name;
                        $this->editAttributeValues = $record->values()->pluck('value')->implode(', ');
                        $this->showEditModal = true;
                    })
                    ->iconButton(),

                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (VariantAttribute $record) {
                        $record->values()->delete();
                        $record->delete();
                        $this->dispatch('toast', [
                            'type' => 'success',
                            'title' => 'Attribute deleted',
                        ]);
                    })
                    ->iconButton(),
            ])
            ->bulkActions([])
            ->searchPlaceholder('Search attributes...')
            ->defaultSort('name', 'asc');
    }

    public function openCreateModal(): void
    {
        $this->attributeName = null;
        $this->attributeValues = null;
        $this->showCreateModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showValuesModal = false;
        $this->editingAttributeId = null;
        $this->attributeName = null;
        $this->attributeValues = null;
        $this->editAttributeName = null;
        $this->editAttributeValues = null;
    }

    public function saveAttribute(): void
    {
        $tenantId = auth()->user()?->tenant_id;

        if (empty($this->attributeName)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Attribute name is required',
            ]);
            return;
        }

        $exists = VariantAttribute::where('tenant_id', $tenantId)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($this->attributeName))])
            ->exists();

        if ($exists) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Attribute already exists',
            ]);
            return;
        }

        $attribute = VariantAttribute::create([
            'tenant_id' => $tenantId,
            'name' => trim($this->attributeName),
            'is_active' => true,
        ]);

        if (!empty($this->attributeValues)) {
            $values = array_filter(array_map('trim', explode(',', $this->attributeValues)));
            foreach ($values as $val) {
                VariantAttributeValue::create([
                    'tenant_id' => $tenantId,
                    'attribute_id' => $attribute->id,
                    'value' => $val,
                    'is_active' => true,
                ]);
            }
        }

        $this->closeModals();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Attribute created successfully',
        ]);
    }

    public function updateAttribute(): void
    {
        $tenantId = auth()->user()?->tenant_id;

        if (empty($this->editAttributeName)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Attribute name is required',
            ]);
            return;
        }

        $exists = VariantAttribute::where('tenant_id', $tenantId)
            ->where('id', '!=', $this->editingAttributeId)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($this->editAttributeName))])
            ->exists();

        if ($exists) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Attribute name already exists',
            ]);
            return;
        }

        $attribute = VariantAttribute::findOrFail($this->editingAttributeId);
        $attribute->update(['name' => trim($this->editAttributeName)]);

        // Sync values
        $newValues = array_filter(array_map('trim', explode(',', $this->editAttributeValues ?? '')));
        $existingValues = $attribute->values->pluck('value')->toArray();

        // Remove values no longer present
        foreach ($existingValues as $ev) {
            if (!in_array($ev, $newValues)) {
                $attribute->values()->where('value', $ev)->delete();
            }
        }

        // Add new values
        foreach ($newValues as $nv) {
            if (!in_array($nv, $existingValues)) {
                VariantAttributeValue::create([
                    'tenant_id' => $tenantId,
                    'attribute_id' => $attribute->id,
                    'value' => $nv,
                    'is_active' => true,
                ]);
            }
        }

        $this->closeModals();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Attribute updated successfully',
        ]);
    }
}
