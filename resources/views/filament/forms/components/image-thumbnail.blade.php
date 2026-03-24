@php
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Storage;

    $state = $getState();
    $url = null;

    if (is_string($state) && $state !== '') {
        $image = urldecode($state);

        // Full URL already
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $url = $image;
        }

        // OLD CMS: /storage/media/...
        elseif (str_starts_with($image, '/storage/')) {
            $url = url($image);
        }

        // NEW/OTHER: /media/...
        elseif (str_starts_with($image, '/media/')) {
            // If the file truly exists in public/media, use it directly.
            $publicCandidate = public_path(ltrim($image, '/')); // public/media/...
            if (File::exists($publicCandidate)) {
                $url = url($image); // /media/...
            } else {
                // Otherwise it's probably stored on the public disk under "media/"
                $url = Storage::disk('public')->url(ltrim($image, '/')); // becomes /storage/media/...
            }
        }

        // Disk-relative: media/...
        elseif (str_starts_with($image, 'media/')) {
            $url = Storage::disk('public')->url($image); // /storage/media/...
        }

        // Legacy: storage/media/... (no leading slash)
        elseif (str_starts_with($image, 'storage/')) {
            $url = url('/' . $image);
        }

        // Just a filename fallback
        else {
            // try /storage/media/<filename> first (old convention)
            $storageUrl = url('/storage/media/' . ltrim($image, '/'));
            $publicCandidate = public_path('storage/media/' . ltrim($image, '/'));

            $url = File::exists($publicCandidate)
                ? $storageUrl
                : url('/media/' . ltrim($image, '/'));
        }
    }
@endphp

@if ($url)
    <div class="mb-3">
        <img
            src="{{ $url }}"
            alt="Image"
            class="rounded-lg shadow-sm w-full"
            style="max-height: 150px; object-fit: contain;"
        />
    </div>
@endif