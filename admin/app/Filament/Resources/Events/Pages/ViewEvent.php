<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Infolists\EventInfolist;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    public function infolist(Schema $schema): Schema
    {
        return EventInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish_gallery')
                ->label('Opublikuj galerię')
                ->icon('heroicon-o-eye')
                ->color('gray')
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
                ->color('gray')
                ->visible(fn (Event $record): bool => $record->gallery_published)
                ->action(function (Event $record): void {
                    $record->update(['gallery_published' => false]);

                    Notification::make()
                        ->title('Publikacja galerii cofnięta')
                        ->body('Publikacja galerii została cofnięta.')
                        ->warning();
                }),

            EditAction::make()->color('gray'),
        ];
    }
}
