<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Nazwa')
                ->sortable(),

            TextColumn::make('date')
                ->label('Data')
                ->date('d.m.Y')
                ->sortable()
                ->grow(),

            TextColumn::make('upload_status')
                ->label('Status przesyłania')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Otwarte' => 'success',
                    default => 'gray',
                })
                ->getStateUsing(fn ($record) => $record->isUploadWindowOpen() ? 'Otwarte' : 'Zamknięte'),

            TextColumn::make('guests_count')
                ->label('Goście')
                ->counts('guests')
                ->sortable(),

            TextColumn::make('photos_count')
                ->label('Zdjęcia')
                ->counts('photos')
                ->sortable(),
        ]);
    }
}
