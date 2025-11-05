<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhotoUploadRequest;
use App\Models\Event;
use App\Models\Photo;
use Illuminate\Support\Str;

class PhotoApiController
{
    public function store(PhotoUploadRequest $request, Event $event): array
    {
        $guest = $request->user();

        // Check quota (must be done before creating photo)
        if ($guest->hasReachedQuota()) {
            abort(429, 'Upload quota exceeded');
        }

        // Check upload window (event date ±12 hours)
        if (! $event->isUploadWindowOpen()) {
            abort(403, 'Upload window is closed');
        }

        // Generate UUID v6 filename
        $filename = (string) Str::uuid().'.jpg';

        // Store file
        $request->file('file')->storeAs('photos', $filename);

        // Create photo record
        Photo::create([
            'event_id' => $event->id,
            'guest_id' => $guest->id,
            'filename' => $filename,
            'taken_at' => $request->date('taken_at'),
        ]);

        return [
            'quota_remaining' => $guest->getRemainingQuota(),
        ];
    }
}
