<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Filament\Resources\Events\Relations\GuestRelationManager;
use App\Filament\Resources\Events\Relations\PhotoRelationManager;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $modelLabel = 'Wydarzenie';

    protected static ?string $pluralModelLabel = 'Wydarzenia';

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PhotoRelationManager::class,
            GuestRelationManager::class,
        ];
    }

    public static function getRecordActions(): array
    {
        return [
            Action::make('publish_gallery')
                ->label('Opublikuj galerię')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->visible(fn (Event $record): bool => ! $record->gallery_published)
                ->action(function (Event $record): void {
                    $record->update(['gallery_published' => true]);

                    Notification::make()
                        ->title('Galeria opublikowana')
                        ->body('Galeria została opublikowana.')
                        ->success()
                        ->send();
                }),

            Action::make('unpublish_gallery')
                ->label('Cofnij publikację galerii')
                ->icon('heroicon-o-eye-slash')
                ->color('neutral')
                ->visible(fn (Event $record): bool => $record->gallery_published)
                ->action(function (Event $record): void {
                    $record->update(['gallery_published' => false]);

                    Notification::make()
                        ->title('Publikacja galerii cofnięta')
                        ->body('Publikacja galerii została cofnięta.')
                        ->warning()
                        ->send();
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'view' => ViewEvent::route('/{record}'),
        ];
    }
}
