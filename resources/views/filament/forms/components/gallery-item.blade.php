@php
    $record = $getRecord();
    $image = $record?->image;
    $url = $image ? Storage::disk('public')->url($image) : null;
@endphp

<div class="space-y-2">
    @if($url)
        <img 
            src="{{ $url }}" 
            alt="{{ $record?->title ?? 'Image' }}"
            class="rounded-lg shadow-sm w-full h-48 object-cover"
        />
    @endif
    
    <div class="space-y-1">
        <div class="font-medium text-sm">{{ $record?->title }}</div>
        @if($record?->link)
            <div class="text-xs text-gray-500">🔗 Has link</div>
        @endif
        @if($record?->content)
            <div class="text-xs text-gray-500 line-clamp-2">{{ $record->content }}</div>
        @endif
    </div>
</div>