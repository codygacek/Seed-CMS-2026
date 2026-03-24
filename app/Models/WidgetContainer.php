<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Cviebrock\EloquentSluggable\Sluggable;

class WidgetContainer extends Model
{
    use Sluggable;

    protected $fillable = [
        'title',
        'slug',
        'options',
    ];

    protected $casts = [
        'options' => 'array',   // ✅
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    // Optional helper for blade (if you want object style)
    public function getOptionsObjectAttribute()
    {
        return (object) ($this->options ?? []);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class)->orderBy('position');
    }
}