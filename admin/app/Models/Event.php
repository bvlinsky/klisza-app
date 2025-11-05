<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'date',
        'gallery_published',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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

    /**
     * Check if the upload window is currently open.
     * Upload window: ±12h from midnight (noon day before to noon day after).
     */
    public function isUploadWindowOpen(): bool
    {
        $now = now();
        $uploadWindowStart = $this->date->copy()->subHours(12);
        $uploadWindowEnd = $this->date->copy()->addDay()->addHours(12);

        return $now->isBetween($uploadWindowStart, $uploadWindowEnd);
    }

    /**
     * Get the upload window start time.
     * Returns noon of the day before the event.
     */
    public function getUploadWindowStart(): \Carbon\Carbon
    {
        return $this->date->copy()->subHours(12);
    }

    /**
     * Get the upload window end time.
     * Returns noon of the day after the event.
     */
    public function getUploadWindowEnd(): \Carbon\Carbon
    {
        return $this->date->copy()->addDay()->addHours(12);
    }
}
