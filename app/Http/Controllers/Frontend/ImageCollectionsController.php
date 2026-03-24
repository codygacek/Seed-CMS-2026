<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ImageCollection;
use Illuminate\Http\Request;

class ImageCollectionsController extends Controller
{
    public function index()
    {
        // Back-compat: keep variable name $photo_albums for your existing views
        $photo_albums = ImageCollection::query()
            ->where('collection_type', 'photo_album')
            ->with([
                // Only load what you need for the listing view
                'image_items' => fn ($q) => $q->orderBy('position'),
                // If your ImageCollectionItem has mediaAsset relationship:
                'image_items.mediaAsset:id,file,title,alt_text',
            ])
            ->orderByDesc('created_at')
            ->get();

        return view()->first(
            [get_theme() . '.photos', 'default.photos'],
            compact('photo_albums')
        );
    }

    public function show(ImageCollection $image_collection)
    {
        // Ensure items are loaded + in correct order
        $image_collection->load([
            'image_items' => fn ($q) => $q->orderBy('position'),
            'image_items.mediaAsset:id,file,title,alt_text,content,extension',
        ]);

        $photo_album = $image_collection; // back-compat variable name

        return view()->first(
            [get_theme() . '.photo-album', 'default.photo-album'],
            compact('photo_album')
        );
    }
}