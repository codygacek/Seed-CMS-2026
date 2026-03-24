@php
    use Illuminate\Support\Facades\Storage;

    $record = $getRecord();

    $url = null;

    /**
     * 1. Prefer media_asset relationship (new CMS)
     */
    if ($record?->media_asset_id && $record->mediaAsset) {
        // mediaAsset->file is stored as filename.jpg (legacy) or media/filename.jpg
        $file = ltrim($record->mediaAsset->file, '/');

        if (str_starts_with($file, 'media/')) {
            $url = Storage::disk('public')->url($file);
        } else {
            // legacy basename-only storage
            $url = Storage::disk('public')->url('media/' . $file);
        }
    }

    /**
     * 2. Fallback to legacy image column
     */
    elseif (! empty($record?->image)) {
        $image = trim($record->image);

        if (str_starts_with($image, 'http')) {
            $url = $image;
        } elseif (str_starts_with($image, '/storage/')) {
            $url = asset($image);
        } elseif (str_starts_with($image, 'media/')) {
            $url = Storage::disk('public')->url($image);
        } elseif (str_starts_with($image, '/media/')) {
            $url = Storage::disk('public')->url(ltrim($image, '/'));
        } else {
            // basename-only legacy case
            $url = Storage::disk('public')->url('media/' . ltrim($image, '/'));
        }
    }
@endphp

@if ($url)
    <div class="rounded-lg border bg-white p-4">
        <div class="text-sm font-medium mb-2">Current Featured Image</div>

        <img
            src="{{ $url }}"
            alt="Featured image"
            class="max-h-64 rounded-md border object-contain bg-gray-50"
        />
    </div>
@endif