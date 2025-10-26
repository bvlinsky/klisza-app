<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Photo extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'event_id',
        'guest_id',
        'filename',
        'uploaded_at',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'taken_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
