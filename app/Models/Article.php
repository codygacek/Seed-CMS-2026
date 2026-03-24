<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cviebrock\EloquentSluggable\Sluggable;

use App\Traits\HasSeo;

class Article extends Model
{
    use HasSeo;
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
        'image',
        'media_asset_id',
        'content',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'index',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public static $rules = [
        'title' => 'required',
        'image' => 'nullable',
        'content' => 'nullable',
        'slug' => 'nullable',
        'is_published' => 'required'
    ];

    public function excerpt(int $limit = 110): string
    {
        return substr(strip_tags($this->content), 0, $limit);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}