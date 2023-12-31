<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class School extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at','pivot'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class);
    }
}
