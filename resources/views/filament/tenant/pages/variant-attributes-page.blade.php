<x-filament-panels::page>
    <style>
        .va-create-btn { display:inline-flex; align-items:center; gap:4px; padding:6px 12px; background:#2563eb; color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; margin-bottom:16px; width:fit-content; }
        .va-create-btn:hover { background:#1d4ed8; }
        .va-modal-overlay { position:fixed; inset:0; z-index:9999; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); }
        .va-modal { background:#fff; border-radius:14px; width:92%; max-width:550px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,0.25); overflow:hidden; }
        .va-modal-head { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #e2e8f0; }
        .va-modal-head h3 { margin:0; font-size:16px; font-weight:700; color:#0f172a; }
        .va-modal-close { background:none; border:none; cursor:pointer; color:#94a3b8; font-size:22px; padding:2px 6px; border-radius:6px; }
        .va-modal-close:hover { color:#475569; }
        .va-modal-body { flex:1; overflow-y:auto; padding:20px; }
        .va-modal-footer { padding:14px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; }
        .va-field { margin-bottom:14px; }
        .va-field label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px; }
        .va-field input, .va-field textarea { width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; font-family:inherit; }
        .va-field textarea { resize:vertical; min-height:60px; }
        .va-field .helper { font-size:11px; color:#6b7280; margin-top:2px; }
        .va-btn { padding:8px 18px; border-radius:8px; border:none; font-size:13px; font-weight:600; cursor:pointer; }
        .va-btn-primary { background:#2563eb; color:#fff; }
        .va-btn-primary:hover { background:#1d4ed8; }
        .va-btn-secondary { background:#f1f5f9; color:#475569; }
        .va-btn-secondary:hover { background:#e2e8f0; }
        .va-values-list { list-style:none; padding:0; margin:0; }
        .va-values-list li { display:flex; align-items:center; justify-content:space-between; padding:8px 12px; border-bottom:1px solid #f1f5f9; font-size:13px; }
        .va-values-list li:last-child { border-bottom:none; }
        .va-val-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; }
        .va-val-active { background:#dcfce7; color:#16a34a; }
        .va-val-inactive { background:#f3f4f6; color:#6b7280; }
    </style>

    <div style="margin-bottom:16px;">
        <button class="va-create-btn" wire:click="openCreateModal">
            <x-heroicon-o-plus style="width:16px;height:16px;" /> Add Attribute
        </button>
    </div>

    {{ $this->table }}

    {{-- CREATE MODAL --}}
    @if ($showCreateModal)
        <div class="va-modal-overlay" wire:click.self="closeModals">
            <div class="va-modal">
                <div class="va-modal-head">
                    <h3>Add Variant Attribute</h3>
                    <button type="button" class="va-modal-close" wire:click="closeModals">&times;</button>
                </div>
                <div class="va-modal-body">
                    <div class="va-field">
                        <label>Attribute Name</label>
                        <input type="text" wire:model.live="attributeName" placeholder="e.g. Color, Size, Storage...">
                    </div>
                    <div class="va-field">
                        <label>Values (comma separated)</label>
                        <textarea wire:model.live="attributeValues" placeholder="e.g. Red, Blue, Green, Black"></textarea>
                        <div class="helper">Enter each value separated by a comma</div>
                    </div>
                </div>
                <div class="va-modal-footer">
                    <button type="button" class="va-btn va-btn-secondary" wire:click="closeModals">Cancel</button>
                    <button type="button" class="va-btn va-btn-primary" wire:click="saveAttribute">Save Attribute</button>
                </div>
            </div>
        </div>
    @endif

    {{-- EDIT MODAL --}}
    @if ($showEditModal)
        <div class="va-modal-overlay" wire:click.self="closeModals">
            <div class="va-modal">
                <div class="va-modal-head">
                    <h3>Edit Attribute</h3>
                    <button type="button" class="va-modal-close" wire:click="closeModals">&times;</button>
                </div>
                <div class="va-modal-body">
                    <div class="va-field">
                        <label>Attribute Name</label>
                        <input type="text" wire:model.live="editAttributeName" placeholder="e.g. Color, Size, Storage...">
                    </div>
                    <div class="va-field">
                        <label>Values (comma separated)</label>
                        <textarea wire:model.live="editAttributeValues" placeholder="e.g. Red, Blue, Green, Black"></textarea>
                        <div class="helper">Existing values are pre-filled. Add or remove as needed.</div>
                    </div>
                </div>
                <div class="va-modal-footer">
                    <button type="button" class="va-btn va-btn-secondary" wire:click="closeModals">Cancel</button>
                    <button type="button" class="va-btn va-btn-primary" wire:click="updateAttribute">Update</button>
                </div>
            </div>
        </div>
    @endif

    {{-- VIEW VALUES MODAL --}}
    @if ($showValuesModal)
        <div class="va-modal-overlay" wire:click.self="closeModals">
            <div class="va-modal">
                <div class="va-modal-head">
                    <h3>{{ $viewingAttributeName }} — Values</h3>
                    <button type="button" class="va-modal-close" wire:click="closeModals">&times;</button>
                </div>
                <div class="va-modal-body">
                    @if (count($viewingValues) > 0)
                        <ul class="va-values-list">
                            @foreach ($viewingValues as $val)
                                <li>
                                    <span>{{ $val['value'] }}</span>
                                    <span class="va-val-badge {{ $val['is_active'] ? 'va-val-active' : 'va-val-inactive' }}">
                                        {{ $val['is_active'] ? 'Active' : 'Inactive' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div style="text-align:center;padding:30px;color:#94a3b8;">
                            <p>No values defined for this attribute.</p>
                        </div>
                    @endif
                </div>
                <div class="va-modal-footer">
                    <button type="button" class="va-btn va-btn-primary" wire:click="closeModals">Done</button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
