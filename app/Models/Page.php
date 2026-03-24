<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cviebrock\EloquentSluggable\Sluggable;

use App\Traits\HasSeo;

class Page extends Model
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
        'content',
        'layout',
        'sidebar_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'index',
    ];

    protected function layout(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ?: 'default',
        );
    }

    public function slider(): MorphOne
    {
        return $this->morphOne(Slider::class, 'sliderable');
    }

    public function token(): MorphOne
    {
        return $this->morphOne(AuthToken::class, 'tokenable');
    }

    public function sidebar(): BelongsTo
    {
        return $this->belongsTo(WidgetContainer::class, 'sidebar_id');
    }
}
