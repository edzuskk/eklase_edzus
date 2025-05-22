<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends Model
{
    protected $fillable = ['user_id', 'subject', 'profile_picture'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
