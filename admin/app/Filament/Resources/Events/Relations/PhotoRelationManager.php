<?php

namespace App\Filament\Resources\Events\Relations;

use App\Models\Photo;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;

class PhotoRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    protected static ?string $title = 'Zdjęcia';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Tables\Columns\ImageColumn::make('filename')
                        ->label('Zdjęcie')
                        ->getStateUsing(function (Photo $record): string {
                            // Generate a data URL for the image
                            $path = storage_path("app/private/photos/{$record->filename}");
                            if (file_exists($path)) {
                                $imageData = file_get_contents($path);
                                $base64 = base64_encode($imageData);

                                return "data:image/jpeg;base64,{$base64}";
                            }

                            return '';
                        })
                        ->extraImgAttributes([
                            'style' => 'width: 100%; height: auto; border-radius: 0.25rem; margin-bottom: 1rem',
                        ]),

                    Tables\Columns\TextColumn::make('guest.name')
                        ->label('Autor'),
                ]),
            ])
            ->contentGrid([
                'sm' => 2,
                'lg' => 3,
            ])
            ->paginated([12, 24, 48, 'all'])
            ->defaultPaginationPageOption(24)
            ->defaultSort('taken_at', 'desc');
    }
}
