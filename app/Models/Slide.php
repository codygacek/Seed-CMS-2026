<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slide extends Model
{
    protected $fillable = [
        'slider_id',
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

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class);
    }

    public function mediaAsset(): BelongsTo
        {
            return $this->belongsTo(MediaAsset::class);
        }

}
