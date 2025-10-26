<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'date',
        'slug',
        'gallery_published',
        'gallery_slug',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'gallery_published' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
