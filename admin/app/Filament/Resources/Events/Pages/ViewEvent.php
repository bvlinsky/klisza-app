<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Infolists\EventInfolist;
use App\Models\Event;
use App\Models\Photo;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaraZeus\Qr\Facades\Qr;
use ZipArchive;

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

            Action::make('download')
                ->label('Pobierz zdjęcia')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (Event $record) {
                    $photos = Photo::where('event_id', $record->id)
                        ->with('guest')
                        ->orderBy('taken_at')
                        ->get();
                    Storage::disk('local')->makeDirectory('tmp');
                    $tmpName = (string) Str::uuid().'.zip';
                    $zipPath = Storage::disk('local')->path('tmp/'.$tmpName);

                    $zip = new ZipArchive;
                    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                        abort(500, 'Nie udało się utworzyć archiwum .zip');
                    }

                    foreach ($photos as $photo) {
                        $relativePath = 'photos/'.$photo->filename;
                        if (Storage::disk('local')->exists($relativePath)) {
                            $absolutePath = Storage::disk('local')->path($relativePath);

                            $guestNameSlug = Str::slug($photo->guest?->name ?? 'gosc');
                            $timestamp = $photo->taken_at?->format('dHis') ?? now()->format('dHis');
                            $ext = pathinfo($photo->filename, PATHINFO_EXTENSION) ?: 'jpg';
                            $archiveName = "{$timestamp}-{$guestNameSlug}.{$ext}";

                            $zip->addFile($absolutePath, $archiveName);
                        }
                    }

                    $zip->close();

                    $safeName = Str::slug($record->name);
                    $date = $record->date?->format('Y-m-d') ?? '';
                    $downloadName = "{$safeName}-{$date}.zip";

                    return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
                }),

            EditAction::make()->color('gray'),
        ];
    }
}
