<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventApiController
{
    public function show(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'date' => $event->date->toDateString(),
            'gallery_published' => $event->gallery_published,
        ];
    }
}
