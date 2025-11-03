<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuestAuthRequest;
use App\Models\Event;
use App\Models\Guest;

class GuestApiController extends Controller
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
