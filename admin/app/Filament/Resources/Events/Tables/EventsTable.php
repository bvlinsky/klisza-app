<?php

namespace App\Filament\Resources\Events\Tables;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa wydarzenia')
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Data wydarzenia')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('upload_status')
                    ->label('Status przesyłania')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'gray',
                        'open' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->getStateUsing(fn ($record) => $record->isUploadWindowOpen() ? 'open' : ($record->date->isFuture() ? 'upcoming' : 'closed')),

                IconColumn::make('gallery_published')
                    ->label('Galeria opublikowana')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('guests_count')
                    ->label('Goście')
                    ->counts('guests')
                    ->sortable(),

                TextColumn::make('photos_count')
                    ->label('Zdjęcia')
                    ->counts('photos')
                    ->sortable(),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ] + EventResource::getRecordActions())
            ->defaultSort('date', 'desc');
    }
}
