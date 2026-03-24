<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasSeo;

class Event extends Model
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
        'has_dates',
        'starts_at',
        'ends_at',
        'venue_name',
        'venue_address',
        'venue_website',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'index',
    ];

    protected $casts = [
        'has_dates' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public static $rules = [
        'title' => 'required',
        'image' => 'nullable',
        'content' => 'nullable',
        'slug' => 'nullable',
        'has_dates' => 'required',
        'starts_at' => 'nullable',
        'ends_at' => 'nullable',
        'venue_name' => 'nullable',
        'venue_address' => 'nullable',
        'venue_website' => 'nullable'
    ];

    public static function getUpcoming(string $location = 'page', int $quantity = 25): Collection|LengthAwarePaginator
    {
        $upcoming = self::query();

        $upcoming = $upcoming->where('starts_at', '>=', now())
            ->orWhere('ends_at', '>=', now())
            ->orWhere(function ($query) {
                $query->whereNull('ends_at');
                $query->where('starts_at', '>', now());
            })
            ->orWhere(function ($query) {
                $query->whereNull('starts_at');
                $query->whereNull('ends_at');
            });

        if ($location == 'widget') {
            return $upcoming->orderBy('starts_at', 'ASC')->limit($quantity)->get();
        }

        return $upcoming->orderBy('starts_at', 'ASC')->paginate($quantity);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}
