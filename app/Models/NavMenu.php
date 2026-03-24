<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cviebrock\EloquentSluggable\Sluggable;

class NavMenu extends Model
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
        'slug'
    ];

    public static $rules = [
        'title' => 'required'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(NavMenuItem::class)->orderBy('position');
    }

    public function top_level_items(): HasMany
    {
        return $this->hasMany(NavMenuItem::class)->where('parent_id', 0)->orderBy('position');
    }

    protected static function booted(): void
    {
        static::deleted(function (NavMenu $nav_menu) {
            $nav_menu->items()->delete();
        });
    }
}
