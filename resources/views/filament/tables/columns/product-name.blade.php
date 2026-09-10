<div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
    <div style="width: 40px; height: 40px; flex-shrink: 0; overflow: hidden; border-radius: 8px; background-color: rgba(128, 128, 128, 0.18);">
        @if ($getRecord()->image_url)
            <img src="{{ $getRecord()->image_url }}" alt="{{ $getRecord()->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block;" loading="lazy">
        @else
            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; text-transform: uppercase; opacity: 0.5;">
                {{ \Illuminate\Support\Str::limit($getRecord()->name, 1, '') }}
            </div>
        @endif
    </div>
    <div style="min-width: 0;">
        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500;">{{ $getRecord()->name }}</div>
    </div>
</div>