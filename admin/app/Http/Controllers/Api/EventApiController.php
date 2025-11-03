<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventApiController extends Controller
{
    public function show(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'date' => $event->date,
            'gallery_published' => $event->gallery_published,
        ];
    }
}
