@php
    $image = $getRecord()?->image;
    $url = $image ? Storage::disk('public')->url($image) : null;
@endphp

@if($url)
    <img 
        src="{{ $url }}" 
        alt="{{ $getRecord()?->title ?? 'Image' }}"
        class="rounded-lg shadow-sm w-full h-32 object-cover"
    />
@endif