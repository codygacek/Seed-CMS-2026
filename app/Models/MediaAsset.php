<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    protected $fillable = [
        'title',
        'file',
        'extension',
        'alt_text',
        'content'
    ];

    /**
     * Backwards compatible storage:
     * DB stores "filename.jpg"
     * Filament/Storage expects "media/filename.jpg"
     */
    public function getFileAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        // If already normalized
        if (str_starts_with($value, 'media/')) {
            return $value;
        }

        // If someone stored "/storage/media/foo.jpg" or "/media/foo.jpg"
        $value = ltrim($value, '/');
        $value = preg_replace('#^storage/media/#', '', $value);
        $value = preg_replace('#^media/#', '', $value);

        return 'media/' . $value;
    }

    /**
     * Always write only the basename to the DB for legacy compatibility.
     */
    public function setFileAttribute($value): void
    {
        if (! $value) {
            $this->attributes['file'] = null;
            return;
        }

        // Strip common prefixes and store just "foo.jpg"
        $value = urldecode((string) $value);
        $value = ltrim($value, '/');

        $value = preg_replace('#^storage/media/#', '', $value);
        $value = preg_replace('#^media/#', '', $value);

        $this->attributes['file'] = basename($value);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $media): void {
            // If you use soft deletes, keep this guard (so file isn't deleted on soft delete).
            if (method_exists($media, 'isForceDeleting') && ! $media->isForceDeleting()) {
                return;
            }

            $file = $media->getRawOriginal('file') ?: $media->file;

            if (! $file) {
                return;
            }

            // Normalize to "media/<filename>" on the public disk
            $path = ltrim($file, '/');
            if (! str_starts_with($path, 'media/')) {
                $path = 'media/' . $path;
            }

            // Delete original
            Storage::disk('public')->delete($path);

            // OPTIONAL: also delete any cached resized variants if you use public/cache
            // Storage::disk('public')->deleteDirectory('cache'); // too aggressive; see note below
        });
    }
}
