<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Infolists\EventInfolist;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use LaraZeus\Qr\Facades\Qr;

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
            Action::make('share')
                ->label('Udostępnij')
                ->icon('heroicon-o-link')
                ->fillForm(fn (Event $record) => [
                    'qr-options' => Qr::getDefaultOptions(),
                    'link' => config('app.frontend_url')."/event/{$record->id}",
                ])
                ->form(Qr::getFormSchema('link', 'qr-options'))
                ->modalSubmitAction(false),

            EditAction::make()->color('gray'),
        ];
    }
}
