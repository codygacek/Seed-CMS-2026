<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Widget extends Model
{
    protected $fillable = [
        'widget_container_id',
        'title',
        'type',
        'options',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
        'options'  => 'array',   // ✅ THIS is the key
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(WidgetContainer::class, 'widget_container_id');
    }

    public function getDecodedOptions()
    {
        // Keep this if you still use it in blade:
        return (object) ($this->options ?? []);
    }
}