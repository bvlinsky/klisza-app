<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nazwa wydarzenia')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Wprowadź nazwę wydarzenia'),

                DatePicker::make('date')
                    ->label('Data wydarzenia')
                    ->required()
                    ->minDate(now()->startOfDay())
                    ->displayFormat('d.m.Y'),

                Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }
}
