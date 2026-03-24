<div style="display: flex; align-items: center; gap: 12px; padding: 4px 0;">
    <img 
        src="{{ $url }}" 
        alt="{{ $title }}"
        style="width: 50px; height: 50px; object-fit: cover; flex-shrink: 0;"
    />
    <div style="flex: 1; min-width: 0;">
        <div style="font-weight: 500; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ $title }}
        </div>
    </div>
</div>