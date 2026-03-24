<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Cviebrock\EloquentSluggable\Sluggable;

class Member extends Model
{
    use Sluggable;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    protected $fillable = [
        'slug',
        'image',
        'name',
        'date',
        'major',
        'current_position',
        'status',
        'alt_info'
    ];

    public static $rules = [
        'name' => 'required',
        'slug' => 'nullable',
        'image' => 'nullable',
        'date' => 'nullable',
        'major' => 'nullable',
        'current_position' => 'nullable',
        'status' => 'required',
        'alt_info' => 'nullable'
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name,
        );
    }
}
