<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cviebrock\EloquentSluggable\Sluggable;

class Slider extends Model
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
        'sliderable_id',
        'sliderable_type'
    ];

    public static $rules = [
        'title' => 'required',
        'slug' => 'nullable',
        'sliderable_id' => 'required',
        'sliderable_type' => 'required',
    ];

    public function sliderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class)->orderBy('position');
    }
}
