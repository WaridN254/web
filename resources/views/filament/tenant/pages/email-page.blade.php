<x-filament-panels::page>
    <div style="display:flex;height:calc(100vh - 64px);background:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;overflow:hidden;">

        {{-- Sidebar --}}
        @if($showSidebar)
        <div style="width:240px;min-width:240px;background:#fff;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;height:100%;">
            {{-- Compose Button --}}
            <div style="padding:16px;">
                <button wire:click="openCompose" style="width:100%;padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:24px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 1px 3px rgba(0,0,0,0.12);">
                    <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Compose
                </button>
            </div>

            {{-- Folders --}}
            <nav style="flex:1;overflow-y:auto;padding:0 8px;">
                <div style="margin-bottom:16px;">
                    <button wire:click="setFolder('inbox')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'inbox' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'inbox' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'inbox' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        Inbox
                        @if(isset($folderCounts['inbox']) && $folderCounts['inbox'] > 0)
                        <span style="margin-left:auto;background:#2563eb;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;font-weight:600;">{{ $folderCounts['inbox'] }}</span>
                        @endif
                    </button>

                    <button wire:click="setFolder('starred')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'starred' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'starred' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'starred' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="{{ $currentFolder === 'starred' ? '#f59e0b' : 'none' }}" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        Starred
                        @if(isset($folderCounts['starred']) && $folderCounts['starred'] > 0)
                        <span style="margin-left:auto;background:#f59e0b;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;font-weight:600;">{{ $folderCounts['starred'] }}</span>
                        @endif
                    </button>

                    <button wire:click="setFolder('important')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'important' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'important' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'important' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="{{ $currentFolder === 'important' ? '#f59e0b' : 'none' }}" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        Important
                        @if(isset($folderCounts['important']) && $folderCounts['important'] > 0)
                        <span style="margin-left:auto;background:#f59e0b;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;font-weight:600;">{{ $folderCounts['important'] }}</span>
                        @endif
                    </button>

                    <button wire:click="setFolder('sent')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'sent' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'sent' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'sent' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Sent
                    </button>

                    <button wire:click="setFolder('drafts')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'drafts' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'drafts' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'drafts' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Drafts
                        @if(isset($folderCounts['drafts']) && $folderCounts['drafts'] > 0)
                        <span style="margin-left:auto;background:#6b7280;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;font-weight:600;">{{ $folderCounts['drafts'] }}</span>
                        @endif
                    </button>

                    <button wire:click="setFolder('spam')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'spam' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'spam' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'spam' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Spam
                        @if(isset($folderCounts['spam']) && $folderCounts['spam'] > 0)
                        <span style="margin-left:auto;background:#ef4444;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;font-weight:600;">{{ $folderCounts['spam'] }}</span>
                        @endif
                    </button>

                    <button wire:click="setFolder('trash')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'trash' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'trash' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'trash' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Trash
                    </button>

                    <button wire:click="setFolder('archive')" style="width:100%;display:flex;align-items:center;padding:8px 16px;border:none;background:{{ $currentFolder === 'archive' ? '#eff6ff' : 'transparent' }};color:{{ $currentFolder === 'archive' ? '#2563eb' : '#374151' }};font-size:14px;border-radius:8px;cursor:pointer;text-align:left;font-weight:{{ $currentFolder === 'archive' ? '600' : '400' }};">
                        <svg style="width:18px;height:18px;margin-right:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        Archive
                    </button>
                </div>

                {{-- Labels --}}
                <div style="border-top:1px solid #e5e7eb;padding-top:12px;margin-top:8px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0 16px 8px;">
                        <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Labels</span>
                        <button wire:click="openCreateLabel" style="background:none;border:none;cursor:pointer;padding:4px;">
                            <svg style="width:16px;height:16px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>

                    @foreach($labels as $label)
                    <div style="display:flex;align-items:center;padding:6px 16px;cursor:pointer;border-radius:8px;margin:0 8px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $label['color'] ?? '#6b7280' }};margin-right:10px;flex-shrink:0;"></span>
                        <span style="font-size:14px;color:#374151;flex:1;">{{ $label['name'] }}</span>
                        <button wire:click="deleteLabel({{ $label['id'] }})" style="background:none;border:none;cursor:pointer;padding:2px;opacity:0.5;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.5'">
                            <svg style="width:14px;height:14px;color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </nav>
        </div>
        @endif

        {{-- Main Content --}}
        <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;">

            {{-- Top Bar --}}
            <div style="background:#fff;border-bottom:1px solid #e5e7eb;padding:8px 16px;display:flex;align-items:center;gap:12px;min-height:56px;">
                {{-- Sidebar Toggle --}}
                <button wire:click="$set('showSidebar', {{ $showSidebar ? 'false' : 'true' }})" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;flex-shrink:0;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <svg style="width:24px;height:24px;color:#374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                {{-- Current Account --}}
                @if($currentAccountId)
                @php $activeAccount = collect($accounts)->firstWhere('id', $currentAccountId); @endphp
                @if($activeAccount)
                <div style="display:flex;align-items:center;gap:8px;padding:4px 12px;background:#f0f5ff;border:1px solid #bfdbfe;border-radius:8px;flex-shrink:0;">
                    <div style="width:24px;height:24px;border-radius:50%;background:{{ $this->getAvatarColor($activeAccount['email_address'] ?? '') }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:600;">
                        {{ $this->getInitials($activeAccount['display_name'] ?? $activeAccount['email_address'] ?? '') }}
                    </div>
                    <span style="font-size:13px;font-weight:500;color:#1e40af;">{{ $activeAccount['email_address'] ?? '' }}</span>
                </div>
                @endif
                @endif

                {{-- Search Bar --}}
                <form wire:submit.prevent="performSearch" style="flex:1;max-width:600px;position:relative;">
                    <div style="position:relative;">
                        <svg style="width:20px;height:20px;color:#6b7280;position:absolute;left:12px;top:50%;transform:translateY(-50%);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search emails..." style="width:100%;padding:10px 16px 10px 40px;border:1px solid #e5e7eb;border-radius:24px;font-size:14px;background:#f8fafc;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
                        @if($searchQuery)
                        <button type="button" wire:click="clearSearch" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;">
                            <svg style="width:16px;height:16px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        @endif
                    </div>
                </form>

                {{-- Action Buttons --}}
                <div style="display:flex;align-items:center;gap:4px;">
                    @if($currentAccountId)
                    <button wire:click="syncAccount('{{ $currentAccountId }}')" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;flex-shrink:0;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'" title="Sync Account">
                        <svg style="width:20px;height:20px;color:#374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                    @endif

                    <button wire:click="openAccountSettings" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;flex-shrink:0;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'" title="Account Settings">
                        <svg style="width:20px;height:20px;color:#374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </button>

                    <button wire:click="openCompose" style="background:#2563eb;border:none;color:#fff;cursor:pointer;padding:8px 16px;border-radius:8px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:6px;flex-shrink:0;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                        <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Compose
                    </button>
                </div>
            </div>

            {{-- Content Area --}}
            <div style="flex:1;overflow:hidden;display:flex;">

                {{-- Email List --}}
                @if(!$selectedEmail)
                <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;">
                    {{-- Email List Header --}}
                    <div style="background:#fff;border-bottom:1px solid #e5e7eb;padding:10px 16px;display:flex;align-items:center;gap:8px;">
                        <label style="display:flex;align-items:center;cursor:pointer;">
                            <input type="checkbox" style="width:16px;height:16px;cursor:pointer;">
                        </label>
                        <span style="font-size:14px;color:#6b7280;">{{ $emailTotalCount }} emails</span>
                    </div>

                    {{-- Email List --}}
                    <div style="flex:1;overflow-y:auto;">
                        @forelse($emails as $email)
                        <div wire:click="selectEmail({{ $email['id'] }})" style="display:flex;align-items:center;padding:12px 16px;background:{{ $email['is_read'] ? '#f8fafc' : '#fff' }};border-bottom:1px solid #e5e7eb;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='{{ $email['is_read'] ? '#f8fafc' : '#fff' }}'">
                            {{-- Star --}}
                            <button wire:click.stop="toggleStar({{ $email['id'] }})" style="background:none;border:none;cursor:pointer;padding:4px;flex-shrink:0;">
                                @if($email['is_starred'])
                                <svg style="width:18px;height:18px;color:#f59e0b;" fill="#f59e0b" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                @else
                                <svg style="width:18px;height:18px;color:#d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                @endif
                            </button>

                            {{-- Avatar --}}
                            <div style="width:40px;height:40px;border-radius:50%;background:{{ $this->getAvatarColor($email['from_name'] ?? $email['from_address']) }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:600;flex-shrink:0;margin:0 12px;">
                                {{ $this->getInitials($email['from_name'] ?? $email['from_address']) }}
                            </div>

                            {{-- Email Content --}}
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:2px;">
                                    <span style="font-size:14px;color:{{ $email['is_read'] ? '#6b7280' : '#111827' }};font-weight:{{ $email['is_read'] ? '400' : '600' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $email['from_display'] ?? $email['from_name'] ?? $email['from_address'] }}
                                    </span>
                                    <span style="font-size:12px;color:#9ca3af;flex-shrink:0;">{{ $email['received_at'] }}</span>
                                </div>
                                <div style="display:flex;align-items:baseline;gap:8px;">
                                    <span style="font-size:14px;color:{{ $email['is_read'] ? '#6b7280' : '#111827' }};font-weight:{{ $email['is_read'] ? '400' : '600' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $email['subject'] }}
                                    </span>
                                    @if($email['has_attachments'])
                                    <svg style="width:14px;height:14px;color:#6b7280;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    @endif
                                </div>
                                <div style="font-size:13px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                                    {{ strip_tags($email['body_preview'] ?? '') }}
                                </div>
                            </div>

                            {{-- Three-dot Menu --}}
                            <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;flex-shrink:0;margin-left:8px;">
                                <button x-on:click.prevent.stop="open = !open" style="background:none;border:none;cursor:pointer;padding:4px 6px;border-radius:4px;display:flex;align-items:center;justify-content:center;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='transparent'">
                                    <svg style="width:18px;height:18px;color:#6b7280;" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                                </button>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="position:absolute;right:0;top:100%;z-index:50;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.12);min-width:200px;padding:6px 0;">
                                    <button wire:click="openEmail('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#374151;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">Open Email</button>
                                    <button wire:click="openReply('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#374151;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">Reply</button>
                                    <button wire:click="openReplyAll('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#374151;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">Reply All</button>
                                    <button wire:click="openForward('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#374151;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">Forward</button>
                                    <div style="border-top:1px solid #e5e7eb;margin:4px 0;"></div>
                                    <button wire:click="markAsUnread('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#374151;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">Mark As Unread</button>
                                    <button wire:click="moveToJunk('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#374151;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">Move to Junk</button>
                                    <button wire:click="archiveEmail('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#374151;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">Archive</button>
                                    <button wire:click="deleteEmail('{{ $email['id'] }}')" x-on:click="open = false" style="display:block;width:100%;text-align:left;padding:8px 16px;font-size:13px;color:#dc2626;background:none;border:none;cursor:pointer;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">Delete</button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:40px;color:#6b7280;">
                            <svg style="width:64px;height:64px;color:#d1d5db;margin-bottom:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <span style="font-size:16px;font-weight:500;">No emails found</span>
                            <span style="font-size:14px;margin-top:4px;">This folder is empty</span>
                        </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if($emailTotalPages > 1)
                    <div style="background:#fff;border-top:1px solid #e5e7eb;padding:12px 16px;display:flex;align-items:center;justify-content:center;gap:16px;">
                        <button wire:click="prevPage" @if($emailPage <= 1) disabled @endif style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:{{ $emailPage <= 1 ? '#f9fafb' : '#fff' }};color:{{ $emailPage <= 1 ? '#d1d5db' : '#374151' }};font-size:14px;cursor:{{ $emailPage <= 1 ? 'not-allowed' : 'pointer' }};">
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <span style="font-size:14px;color:#374151;">Page {{ $emailPage }} of {{ $emailTotalPages }}</span>
                        <button wire:click="nextPage" @if($emailPage >= $emailTotalPages) disabled @endif style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:{{ $emailPage >= $emailTotalPages ? '#f9fafb' : '#fff' }};color:{{ $emailPage >= $emailTotalPages ? '#d1d5db' : '#374151' }};font-size:14px;cursor:{{ $emailPage >= $emailTotalPages ? 'not-allowed' : 'pointer' }};">
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                    @endif
                </div>

                {{-- Email Thread View --}}
                @else
                <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;background:#fff;">
                    {{-- Thread Header --}}
                    <div style="border-bottom:1px solid #e5e7eb;padding:12px 16px;display:flex;align-items:center;gap:12px;">
                        <button wire:click="deselectEmail" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;flex-shrink:0;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                            <svg style="width:20px;height:20px;color:#374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </button>
                        <h2 style="font-size:18px;font-weight:600;color:#111827;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $selectedEmail['subject'] ?? '' }}</h2>
                    </div>

                    {{-- Thread Messages --}}
                    <div style="flex:1;overflow-y:auto;padding:16px;">
                        @if(!empty($threadEmails))
                            @foreach($threadEmails as $threadIndex => $threadMsg)
                            <div style="border:1px solid #e5e7eb;border-radius:8px;margin-bottom:16px;overflow:hidden;@if($threadIndex > 0) margin-top:8px; @endif">
                                {{-- Message Header --}}
                                <div style="padding:16px;display:flex;align-items:flex-start;gap:12px;">
                                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $this->getAvatarColor($threadMsg['from_name'] ?? $threadMsg['from_address'] ?? '') }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:600;flex-shrink:0;">
                                        {{ $this->getInitials($threadMsg['from_name'] ?? $threadMsg['from_address'] ?? '') }}
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;">
                                            <div>
                                                <span style="font-size:14px;font-weight:600;color:#111827;">{{ $threadMsg['from_display'] ?? $threadMsg['from_name'] ?? $threadMsg['from_address'] ?? '' }}</span>
                                                <span style="font-size:13px;color:#6b7280;margin-left:4px;">&lt;{{ $threadMsg['from_address'] ?? '' }}&gt;</span>
                                            </div>
                                            <span style="font-size:12px;color:#9ca3af;flex-shrink:0;">{{ $threadMsg['received_at'] ?? '' }}</span>
                                        </div>
                                        <div style="font-size:13px;color:#6b7280;margin-top:2px;">
                                            To: {{ is_array($threadMsg['to_recipients'] ?? null) ? implode(', ', $threadMsg['to_recipients']) : ($threadMsg['to_recipients'] ?? '') }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Message Body --}}
                                <div style="padding:0 16px 16px 68px;">
                                    <div style="pointer-events:none;overflow:auto;max-height:400px;font-size:14px;color:#374151;line-height:1.6;">
                                        {!! $threadMsg['body'] ?? $threadMsg['body_preview'] ?? '' !!}
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                @if(!empty($threadMsg['attachments']))
                                <div style="padding:0 16px 16px 68px;">
                                    <div style="border-top:1px solid #e5e7eb;padding-top:12px;">
                                        <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;display:block;">Attachments</span>
                                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                            @foreach($threadMsg['attachments'] as $attachment)
                                            <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f8fafc'">
                                                <svg style="width:16px;height:16px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                <span style="font-size:13px;color:#374151;">{{ $attachment['name'] ?? 'File' }}</span>
                                                <span style="font-size:12px;color:#9ca3af;">{{ $attachment['size'] ?? '' }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Message Actions --}}
                                @if($threadIndex === 0)
                                <div style="padding:8px 16px 16px 68px;display:flex;flex-wrap:wrap;gap:8px;">
                                    <button wire:click="openReply({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        Reply
                                    </button>
                                    <button wire:click="openReplyAll({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        Reply All
                                    </button>
                                    <button wire:click="openForward({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M15 21l4-4-4-4"></path></svg>
                                        Forward
                                    </button>

                                    <div style="width:1px;background:#e5e7eb;margin:0 4px;"></div>

                                    <button wire:click="toggleStar({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:{{ $selectedEmail['is_starred'] ? '#f59e0b' : '#374151' }};font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        @if($selectedEmail['is_starred'])
                                        <svg style="width:14px;height:14px;" fill="#f59e0b" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                        @else
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                        @endif
                                    </button>

                                    <button wire:click="toggleImportant({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:{{ $selectedEmail['is_important'] ? '#f59e0b' : '#374151' }};font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="{{ $selectedEmail['is_important'] ? '#f59e0b' : 'none' }}" stroke="{{ $selectedEmail['is_important'] ? '#f59e0b' : 'currentColor' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                                    </button>

                                    <button wire:click="archiveEmail({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                        Archive
                                    </button>

                                    <button wire:click="moveToSpam({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Spam
                                    </button>

                                    <button wire:click="trashEmail({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #fca5a5;border-radius:6px;background:#fff;color:#ef4444;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Delete
                                    </button>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        @else
                            {{-- Single Email View --}}
                            <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                                <div style="padding:16px;display:flex;align-items:flex-start;gap:12px;">
                                    <div style="width:48px;height:48px;border-radius:50%;background:{{ $this->getAvatarColor($selectedEmail['from_name'] ?? $selectedEmail['from_address'] ?? '') }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;font-weight:600;flex-shrink:0;">
                                        {{ $this->getInitials($selectedEmail['from_name'] ?? $selectedEmail['from_address'] ?? '') }}
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;">
                                            <div>
                                                <span style="font-size:16px;font-weight:600;color:#111827;">{{ $selectedEmail['from_display'] ?? $selectedEmail['from_name'] ?? $selectedEmail['from_address'] ?? '' }}</span>
                                                <span style="font-size:14px;color:#6b7280;margin-left:4px;">&lt;{{ $selectedEmail['from_address'] ?? '' }}&gt;</span>
                                            </div>
                                            <span style="font-size:13px;color:#9ca3af;flex-shrink:0;">{{ $selectedEmail['received_at'] ?? '' }}</span>
                                        </div>
                                        <div style="font-size:13px;color:#6b7280;margin-top:4px;">
                                            To: {{ is_array($selectedEmail['to_recipients'] ?? null) ? implode(', ', $selectedEmail['to_recipients']) : ($selectedEmail['to_recipients'] ?? '') }}
                                        </div>

                                        {{-- Labels --}}
                                        @if(!empty($selectedEmail['labels']))
                                        <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:8px;">
                                            @foreach($selectedEmail['labels'] as $label)
                                            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:500;background:{{ $label['color'] ?? '#6b7280' }}20;color:{{ $label['color'] ?? '#6b7280' }};">
                                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $label['color'] ?? '#6b7280' }};"></span>
                                                {{ $label['name'] }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <div style="padding:0 16px 16px 80px;">
                                    <div style="pointer-events:none;overflow:auto;max-height:400px;font-size:14px;color:#374151;line-height:1.6;">
                                        {!! $selectedEmail['body'] ?? $selectedEmail['body_preview'] ?? '' !!}
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                @if(!empty($selectedEmail['attachments']) || ($selectedEmail['has_attachments'] ?? false))
                                <div style="padding:0 16px 16px 80px;">
                                    <div style="border-top:1px solid #e5e7eb;padding-top:12px;">
                                        <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;display:block;">Attachments</span>
                                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                            @if(!empty($selectedEmail['attachments']))
                                                @foreach($selectedEmail['attachments'] as $attachment)
                                                <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f8fafc'">
                                                    <svg style="width:16px;height:16px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <span style="font-size:13px;color:#374151;">{{ $attachment['name'] ?? 'File' }}</span>
                                                    <span style="font-size:12px;color:#9ca3af;">{{ $attachment['size'] ?? '' }}</span>
                                                </div>
                                                @endforeach
                                            @else
                                                <span style="font-size:13px;color:#6b7280;">{{ $selectedEmail['attachment_count'] ?? 0 }} attachment(s)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div style="padding:8px 16px 16px 80px;display:flex;flex-wrap:wrap;gap:8px;">
                                    <button wire:click="openReply({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        Reply
                                    </button>
                                    <button wire:click="openReplyAll({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        Reply All
                                    </button>
                                    <button wire:click="openForward({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M15 21l4-4-4-4"></path></svg>
                                        Forward
                                    </button>

                                    <div style="width:1px;background:#e5e7eb;margin:0 4px;"></div>

                                    <button wire:click="toggleStar({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:{{ $selectedEmail['is_starred'] ? '#f59e0b' : '#374151' }};font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        @if($selectedEmail['is_starred'])
                                        <svg style="width:14px;height:14px;" fill="#f59e0b" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                        @else
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                        @endif
                                    </button>

                                    <button wire:click="toggleImportant({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:{{ $selectedEmail['is_important'] ? '#f59e0b' : '#374151' }};font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="{{ $selectedEmail['is_important'] ? '#f59e0b' : 'none' }}" stroke="{{ $selectedEmail['is_important'] ? '#f59e0b' : 'currentColor' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                                    </button>

                                    <button wire:click="archiveEmail({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                        Archive
                                    </button>

                                    <button wire:click="moveToSpam({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Spam
                                    </button>

                                    <button wire:click="trashEmail({{ $selectedEmail['id'] }})" style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #fca5a5;border-radius:6px;background:#fff;color:#ef4444;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Compose Modal --}}
    @if($showCompose)
    <div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:50;display:flex;align-items:flex-end;justify-content:flex-end;" x-data="{}">
        {{-- Backdrop --}}
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.3);" wire:click="closeCompose"></div>

        {{-- Compose Panel --}}
        <div style="position:relative;width:560px;max-width:100%;height:520px;background:#fff;border-radius:12px 12px 0 0;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);display:flex;flex-direction:column;overflow:hidden;margin-right:24px;margin-bottom:0;">
            {{-- Compose Header --}}
            <div style="background:#2563eb;color:#fff;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <span style="font-size:14px;font-weight:600;">
                    @if($composeMode === 'reply') Reply
                    @elseif($composeMode === 'reply_all') Reply All
                    @elseif($composeMode === 'forward') Forward
                    @elseif($composeMode === 'draft') Draft
                    @else Compose Email
                    @endif
                </span>
                <button wire:click="closeCompose" style="background:none;border:none;color:#fff;cursor:pointer;padding:4px;border-radius:4px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Compose Fields --}}
            <div style="flex-shrink:0;border-bottom:1px solid #e5e7eb;">
                {{-- To --}}
                <div style="display:flex;align-items:center;padding:8px 16px;border-bottom:1px solid #f3f4f6;">
                    <label style="font-size:13px;color:#6b7280;width:60px;flex-shrink:0;">To:</label>
                    <input type="text" wire:model.live="composeTo" placeholder="recipients@example.com" style="flex:1;border:none;outline:none;font-size:14px;padding:4px 0;background:transparent;">
                </div>

                {{-- CC --}}
                @if($composeShowCc)
                <div style="display:flex;align-items:center;padding:8px 16px;border-bottom:1px solid #f3f4f6;">
                    <label style="font-size:13px;color:#6b7280;width:60px;flex-shrink:0;">CC:</label>
                    <input type="text" wire:model.live="composeCc" placeholder="cc@example.com" style="flex:1;border:none;outline:none;font-size:14px;padding:4px 0;background:transparent;">
                </div>
                @endif

                {{-- BCC --}}
                @if($composeShowBcc)
                <div style="display:flex;align-items:center;padding:8px 16px;border-bottom:1px solid #f3f4f6;">
                    <label style="font-size:13px;color:#6b7280;width:60px;flex-shrink:0;">BCC:</label>
                    <input type="text" wire:model.live="composeBcc" placeholder="bcc@example.com" style="flex:1;border:none;outline:none;font-size:14px;padding:4px 0;background:transparent;">
                </div>
                @endif

                {{-- CC/BCC Toggle --}}
                @if(!$composeShowCc || !$composeShowBcc)
                <div style="padding:4px 16px;display:flex;gap:8px;">
                    @if(!$composeShowCc)
                    <button wire:click="$set('composeShowCc', true)" style="background:none;border:none;color:#2563eb;font-size:12px;cursor:pointer;padding:2px 0;">+ CC</button>
                    @endif
                    @if(!$composeShowBcc)
                    <button wire:click="$set('composeShowBcc', true)" style="background:none;border:none;color:#2563eb;font-size:12px;cursor:pointer;padding:2px 0;">+ BCC</button>
                    @endif
                </div>
                @endif

                {{-- Subject --}}
                <div style="display:flex;align-items:center;padding:8px 16px;">
                    <label style="font-size:13px;color:#6b7280;width:60px;flex-shrink:0;">Subject:</label>
                    <input type="text" wire:model.live="composeSubject" placeholder="Email subject" style="flex:1;border:none;outline:none;font-size:14px;padding:4px 0;background:transparent;">
                </div>
            </div>

            {{-- Compose Body --}}
            <div style="flex:1;overflow:hidden;position:relative;">
                <textarea
                    wire:model.lazy="composeBody"
                    placeholder="Write your email..."
                    style="width:100%;height:100%;padding:16px;font-size:14px;line-height:1.6;color:#374151;outline:none;border:none;resize:none;min-height:200px;box-sizing:border-box;font-family:inherit;background:transparent;"
                ></textarea>
            </div>

            {{-- Compose Footer --}}
                <div style="border-top:1px solid #e5e7eb;padding:12px 16px;flex-shrink:0;">
                    @if($composeSendResult)
                    <div style="margin-bottom:10px;padding:8px 12px;border-radius:6px;font-size:13px;@if($composeSendSuccess) background:#dcfce7;color:#166534;@else background:#fee2e2;color:#991b1b;@endif">
                        {{ $composeSendResult }}
                    </div>
                    @endif
                {{-- Attached Files List --}}
                @if(!empty($composeAttachments))
                <div style="margin-bottom:10px;display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($composeAttachments as $idx => $att)
                    <div style="display:flex;align-items:center;gap:6px;padding:4px 10px;background:#f1f5f9;border-radius:6px;font-size:12px;">
                        <svg style="width:12px;height:12px;color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        <span style="color:#374151;">{{ $att['original_name'] }}</span>
                        <span style="color:#94a3b8;">{{ round(($att['file_size'] ?? 0) / 1024, 1) }}KB</span>
                        <button wire:click="removeComposeAttachment({{ $idx }})" style="background:none;border:none;cursor:pointer;color:#dc2626;padding:0;font-size:14px;line-height:1;">&times;</button>
                    </div>
                    @endforeach
                </div>
                @endif

                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <label style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Attach File
                            <input type="file" multiple style="display:none;" onchange="handleFileSelect(event)" />
                        </label>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        @if($composeSendSuccess)
                        <button wire:click="closeCompose" style="padding:8px 20px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:14px;cursor:pointer;">
                            Close
                        </button>
                        @else
                        <button wire:click="saveDraft" style="padding:8px 16px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:14px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                            Save Draft
                        </button>
                        <button wire:click="sendEmail" wire:loading.attr="disabled" style="padding:8px 20px;border:none;border-radius:6px;background:#2563eb;color:#fff;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span wire:loading.remove wire:target="sendEmail">Send</span>
                            <span wire:loading wire:target="sendEmail">Sending...</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Account Settings Modal --}}
    @if($showAccountSettings)
    <div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:50;display:flex;align-items:center;justify-content:center;" x-data="{ open: true }">
        {{-- Backdrop --}}
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);" wire:click="closeAccountSettings"></div>

        {{-- Modal --}}
        <div style="position:relative;background:#fff;border-radius:12px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);width:640px;max-width:100%;max-height:80vh;overflow:hidden;display:flex;flex-direction:column;">
            {{-- Header --}}
            <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <h2 style="font-size:18px;font-weight:600;color:#111827;margin:0;">Account Settings</h2>
                <button wire:click="closeAccountSettings" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <svg style="width:20px;height:20px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Content --}}
            <div style="flex:1;overflow-y:auto;padding:24px;">
                {{-- Account List --}}
                <div style="margin-bottom:24px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <h3 style="font-size:14px;font-weight:600;color:#374151;margin:0;">Email Accounts</h3>
                        <button wire:click="editAccount('')" style="display:flex;align-items:center;gap:4px;padding:6px 12px;border:1px solid #2563eb;border-radius:6px;background:#fff;color:#2563eb;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add Account
                        </button>
                    </div>

                    @foreach($accounts as $account)
                    <div wire:click="switchAccount('{{ $account['id'] }}')" style="border:1px solid {{ $currentAccountId === $account['id'] ? '#2563eb' : '#e5e7eb' }};border-radius:8px;padding:12px 16px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;background:{{ $currentAccountId === $account['id'] ? '#eff6ff' : '#fff' }};" onmouseover="this.style.borderColor='#2563eb'" onmouseout="this.style.borderColor='{{ $currentAccountId === $account['id'] ? '#2563eb' : '#e5e7eb' }}'">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;border-radius:50%;background:{{ $this->getAvatarColor($account['email_address'] ?? '') }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:600;">
                                {{ $this->getInitials($account['display_name'] ?? $account['email_address'] ?? '') }}
                            </div>
                            <div>
                                <div style="font-size:14px;font-weight:500;color:#111827;">{{ $account['display_name'] ?? '' }}</div>
                                <div style="font-size:13px;color:#6b7280;">{{ $account['email_address'] ?? '' }}</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:4px;">
                            @if($currentAccountId === $account['id'])
                            <span style="width:8px;height:8px;border-radius:50%;background:#2563eb;flex-shrink:0;"></span>
                            @endif
                            <button wire:click.stop="editAccount('{{ $account['id'] }}')" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:4px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                                <svg style="width:16px;height:16px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button wire:click.stop="syncAccount('{{ $account['id'] }}')" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:4px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'" title="Sync">
                                <svg style="width:16px;height:16px;color:#10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Account Form (when editing) --}}
                @if(isset($editingAccountId) && $editingAccountId !== null)
                <div style="border-top:1px solid #e5e7eb;padding-top:24px;">
                    <h3 style="font-size:14px;font-weight:600;color:#374151;margin:0 0 16px;">{{ $editingAccountId === 0 ? 'Add New Account' : 'Edit Account' }}</h3>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div style="grid-column:1/2;">
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">Account Name</label>
                            <input type="text" wire:model.live="accountForm.name" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">Email Address</label>
                            <input type="email" wire:model.live="accountForm.email" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">IMAP Host</label>
                            <input type="text" wire:model.live="accountForm.imap_host" placeholder="imap.example.com" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">IMAP Port</label>
                            <input type="number" wire:model.live="accountForm.imap_port" placeholder="993" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">SMTP Host</label>
                            <input type="text" wire:model.live="accountForm.smtp_host" placeholder="smtp.example.com" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">SMTP Port</label>
                            <input type="number" wire:model.live="accountForm.smtp_port" placeholder="587" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div style="grid-column:1/2;">
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">Username</label>
                            <input type="text" wire:model.live="accountForm.username" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">Password</label>
                            <input type="password" wire:model.live="accountForm.password" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:8px;margin-top:16px;">
                        <button wire:click="testImapConnection" style="padding:8px 16px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                            Test IMAP
                        </button>
                        <button wire:click="testSmtpConnection" style="padding:8px 16px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                            Test SMTP
                        </button>
                        <div style="flex:1;"></div>
                        <button wire:click="closeAccountSettings" style="padding:8px 16px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:13px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                            Cancel
                        </button>
                        <button wire:click="saveAccount" style="padding:8px 20px;border:none;border-radius:6px;background:#2563eb;color:#fff;font-size:13px;font-weight:500;cursor:pointer;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                            Save Account
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Create Label Modal --}}
    @if($showCreateLabel)
    <div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:50;display:flex;align-items:center;justify-content:center;">
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);" wire:click="$set('showCreateLabel', false)"></div>

        <div style="position:relative;background:#fff;border-radius:12px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);width:400px;max-width:100%;padding:24px;">
            <h2 style="font-size:18px;font-weight:600;color:#111827;margin:0 0 20px;">Create Label</h2>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:4px;">Label Name</label>
                <input type="text" wire:model.live="newLabelName" placeholder="e.g., Work, Personal" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;outline:none;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:8px;">Color</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach(['#2563eb', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#6b7280'] as $color)
                    <button wire:click="$set('newLabelColor', '{{ $color }}')" style="width:32px;height:32px;border-radius:50%;background:{{ $color }};border:3px solid {{ $newLabelColor === $color ? '#111827' : 'transparent' }};cursor:pointer;transition:border-color 0.15s;"></button>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button wire:click="$set('showCreateLabel', false)" style="padding:8px 16px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#374151;font-size:14px;cursor:pointer;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                    Cancel
                </button>
                <button wire:click="createLabel" style="padding:8px 20px;border:none;border-radius:6px;background:#2563eb;color:#fff;font-size:14px;font-weight:500;cursor:pointer;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    Create
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Templates Modal --}}
    @if($showTemplates)
    <div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:50;display:flex;align-items:center;justify-content:center;">
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);" wire:click="closeTemplates"></div>

        <div style="position:relative;background:#fff;border-radius:12px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);width:560px;max-width:100%;max-height:80vh;overflow:hidden;display:flex;flex-direction:column;">
            <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <h2 style="font-size:18px;font-weight:600;color:#111827;margin:0;">Email Templates</h2>
                <button wire:click="closeTemplates" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <svg style="width:20px;height:20px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:16px 24px;">
                @forelse($templates as $template)
                <div style="border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:8px;cursor:pointer;transition:border-color 0.15s;" onmouseover="this.style.borderColor='#2563eb'" onmouseout="this.style.borderColor='#e5e7eb'">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                        <div style="flex:1;">
                            <div style="font-size:14px;font-weight:600;color:#111827;margin-bottom:4px;">{{ $template['name'] ?? '' }}</div>
                            <div style="font-size:13px;color:#6b7280;margin-bottom:2px;">Subject: {{ $template['subject'] ?? '' }}</div>
                            <div style="font-size:13px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ strip_tags($template['body'] ?? '') }}</div>
                        </div>
                        <button wire:click="sendTemplateEmail({{ $template['id'] }})" style="margin-left:12px;padding:6px 12px;border:1px solid #2563eb;border-radius:6px;background:#fff;color:#2563eb;font-size:13px;cursor:pointer;flex-shrink:0;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                            Use
                        </button>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:40px 20px;color:#6b7280;">
                    <svg style="width:48px;height:48px;color:#d1d5db;margin:0 auto 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <div style="font-size:14px;">No templates yet</div>
                    <div style="font-size:13px;margin-top:4px;">Create templates to quickly reuse email content</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

<script>
function handleFileSelect(event) {
    const files = event.target.files;
    if (!files || files.length === 0) return;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        fetch('/livewire/upload-file', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Livewire': 'true',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.uuid || data.path) {
                window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                    .call('addAttachmentFromUpload', file.name, data.path || data.uuid, file.size, file.type || 'application/octet-stream');
            }
        })
        .catch(err => {
            console.error('Upload failed:', err);
        });
    }
    event.target.value = '';
}
</script>
</x-filament-panels::page>
