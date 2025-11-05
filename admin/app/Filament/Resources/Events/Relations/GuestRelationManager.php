<?php

namespace App\Filament\Resources\Events\Relations;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GuestRelationManager extends RelationManager
{
    protected static string $relationship = 'guests';

    protected static ?string $title = 'Goście';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Imię gościa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('photos_count')
                    ->label('Przesłane zdjęcia')
                    ->counts('photos'),
            ])
            ->defaultSort('name', 'asc');
    }
}
