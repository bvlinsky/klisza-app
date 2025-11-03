<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Guest extends Model
{
    use HasApiTokens, HasFactory, HasUuids;

    protected $fillable = [
        'event_id',
        'name',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the current photo count for this guest.
     */
    public function getPhotoCount(): int
    {
        return $this->photos()->count();
    }

    /**
     * Get the remaining quota for this guest.
     */
    public function getRemainingQuota(): int
    {
        return max(0, 15 - $this->getPhotoCount());
    }

    /**
     * Check if the guest has reached the upload quota.
     */
    public function hasReachedQuota(): bool
    {
        return $this->getPhotoCount() >= 15;
    }
}
