<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageCollectionItem extends Model
{
    protected $fillable = [
        'image_collection_id',
        'media_asset_id',
        'image',
        'title',
        'content',
        'link',
        'position'
    ];

    public static $rules = [
        'image' => 'required',
        'title' => 'nullable',
        'content' => 'nullable',
        'link' => 'nullable'
    ];

    public function image_collection(): BelongsTo
    {
        return $this->belongsTo(ImageCollection::class);
    }

    public function mediaAsset()
    {
        return $this->belongsTo(\App\Models\MediaAsset::class, 'media_asset_id');
    }
}
