<?php

namespace App\Filament\Resources\Events\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make()->columns(2)->schema([
                TextEntry::make('name')
                    ->label('Nazwa wydarzenia')
                    ->weight('bold'),

                TextEntry::make('date')
                    ->label('Data wydarzenia')
                    ->date('d.m.Y'),

                TextEntry::make('frontend_link')
                    ->label('Link do wydarzenia')
                    ->state(fn ($record) => rtrim(config('app.frontend_url'), '/').'/event/'.$record->id)
                    ->copyable(),

                TextEntry::make('gallery_published')
                    ->label('Galeria opublikowana')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Tak' : 'Nie'),

                TextEntry::make('photos_count')
                    ->label('Łączna liczba zdjęć')
                    ->state(fn ($record) => $record->photos()->count()),

                TextEntry::make('guests_count')
                    ->label('Łączna liczba gości')
                    ->state(fn ($record) => $record->guests()->count()),
            ]),
        ]);
    }
}
