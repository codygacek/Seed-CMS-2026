<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuthToken extends Model
{
    protected $fillable = [
        'token',
        'tokenable_id',
        'tokenable_type'
    ];

    public static $rules = [
        'token' => 'required',
        'tokenable_id' => 'required',
        'tokenable_type' => 'required',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
