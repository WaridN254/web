<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Quotations</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage and track your quotations</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
            {{ $this->table }}
        </div>
    </div>

    @if($showSendEmailModal)
    <div style="position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" wire:click.self="closeSendEmailModal">
        <div style="background:#fff;border-radius:14px;width:92%;max-width:540px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,0.25);overflow:hidden;">

            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">
                    @if($sendEmailDocType === 'invoice') Send Invoice
                    @elseif($sendEmailDocType === 'receipt') Send Receipt
                    @elseif($sendEmailDocType === 'quotation') Send Quotation
                    @elseif($sendEmailDocType === 'statement') Send Statement
                    @elseif($sendEmailDocType === 'payment_reminder') Send Payment Reminder
                    @else Send Email
                    @endif
                </h3>
                <button type="button" wire:click="closeSendEmailModal" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;padding:2px 6px;border-radius:6px;">&times;</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:20px;">
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">To</label>
                    <input type="email" wire:model.live="sendEmailTo" placeholder="customer@example.com" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                    @error('sendEmailTo') <span style="color:#dc2626;font-size:12px;">{{ $message }}</span> @enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Subject</label>
                    <input type="text" wire:model.live="sendEmailSubject" placeholder="Email subject..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:0;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Message (optional)</label>
                    <textarea wire:model.live="sendEmailMessage" rows="3" placeholder="Add a personal note..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
                </div>
            </div>

            @if($sendEmailResult)
            <div style="padding:12px 20px;font-size:13px;@if($sendEmailSuccess) background:#dcfce7;color:#166534;@else background:#fee2e2;color:#991b1b;@endif border-top:1px solid @if($sendEmailSuccess) #bbf7d0;@else #fecaca;@endif">
                {{ $sendEmailResult }}
            </div>
            @endif

            <div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" wire:click="closeSendEmailModal" style="padding:8px 18px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;background:#f1f5f9;color:#475569;">Cancel</button>
                <button type="button" wire:click="sendEmailFromModal" {{ $sendEmailSending ? 'disabled' : '' }} style="padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:{{ $sendEmailSending ? '#94a3b8' : '#2563eb' }};color:#fff;display:flex;align-items:center;gap:6px;">
                    @if($sendEmailSending)
                        <svg style="width:14px;height:14px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:0.75;"></path></svg>
                        Sending...
                    @else
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Send Email
                    @endif
                </button>
            </div>
        </div>
    </div>
    <style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
    @endif
</x-filament-panels::page>
