<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cviebrock\EloquentSluggable\Sluggable;

class ImageCollection extends Model
{
    use Sluggable;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'collection_type'
    ];

    protected $attributes = [
        'collection_type' => 'photo_album',
    ];

    public static $rules = [
        'title' => 'required',
        'slug' => 'nullable',
        'description' => 'nullable'
    ];

    public function image_items(): HasMany
    {
        return $this->hasMany(ImageCollectionItem::class)->orderBy('position');
    }
}
