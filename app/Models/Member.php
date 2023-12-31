<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Member extends Model
{
    use HasFactory;

    protected $hidden = ['pivot'];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class);
    }
}
