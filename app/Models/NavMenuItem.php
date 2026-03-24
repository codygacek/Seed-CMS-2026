<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavMenuItem extends Model
{
    protected $fillable = [
        'nav_menu_id',
        'parent_id',
        'label',
        'link',
        'position',
        'new_window'
    ];

    protected $casts = [
        'new_window' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavMenu::class, 'nav_menu_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavMenuItem::class, 'parent_id')
            ->where('nav_menu_id', $this->nav_menu_id)
            ->orderBy('position');
    }

    protected static function booted(): void
    {
        static::creating(function (NavMenuItem $item) {
            $item->parent_id ??= 0;
            $item->new_window ??= false;

            if (! $item->nav_menu_id && $item->parent_id) {
                $parent = NavMenuItem::query()->select('id', 'nav_menu_id')->find($item->parent_id);
                if ($parent) {
                    $item->nav_menu_id = $parent->nav_menu_id;
                }
            }

            if (! $item->position) {
                $max = NavMenuItem::query()
                    ->where('nav_menu_id', $item->nav_menu_id)
                    ->where('parent_id', $item->parent_id)
                    ->max('position');

                $item->position = ($max ?? 0) + 1;
            }
        });
    }
}
