<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Cviebrock\EloquentSluggable\Sluggable;

class ExecutiveCommittee extends Model
{
    use Sluggable;

    protected $table = 'executive_committee';

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
    

    protected $fillable = [
        'name',
        'slug',
        'image',
        'position',
        'date',
        'major',
        'other_position',
        'status',
        'order'
    ];

    public static $rules = [
        'name' => 'required',
        'slug' => 'nullable',
        'image' => 'nullable',
        'position' => 'nullable',
        'date' => 'nullable',
        'major' => 'nullable',
        'other_position' => 'nullable',
        'order' => 'nullable',
        'status' => 'required'
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name,
        );
    }
}
