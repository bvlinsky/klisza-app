<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestAuthRequest;
use App\Models\Event;
use App\Models\Guest;

class GuestApiController
{
    public function authenticate(GuestAuthRequest $request, Event $event): array
    {
        $guest = Guest::create([
            'event_id' => $event->id,
            'name' => $request->input('name'),
        ]);

        $token = $guest->createToken('guest-token')->plainTextToken;

        return [
            'access_token' => $token,
            'quota_remaining' => $guest->getRemainingQuota(),
        ];
    }
}
