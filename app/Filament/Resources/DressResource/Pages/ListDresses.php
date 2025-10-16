<?php

namespace App\Filament\Resources\DressResource\Pages;

use App\Filament\Resources\DressResource;
use App\Models\Dress;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDresses extends ListRecords
{
    protected static string $resource = DressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('open_trash')
                ->label('Cestino')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->modalHeading('Abiti archiviati')
                ->modalWidth('3xl')
                ->form(function () {
                    // Prendo gli archiviati (senza global scope)
                    $archived = Dress::withoutGlobalScopes()
                        ->whereNotNull('archived_at')
                        ->latest('archived_at')
                        ->get(['id', 'customer_name', 'ceremony_type', 'archived_at']);

                    // Se vuoto, mostro solo un placeholder e niente submit
                    if ($archived->isEmpty()) {
                        return [
                            Forms\Components\Placeholder::make('empty')
                                ->label('')
                                ->content('🗑️ Nessun abito archiviato al momento.'),
                        ];
                    }

                    // Altrimenti mostro la lista selezionabile
                    return [
                        Forms\Components\CheckboxList::make('to_restore')
                            ->label('Seleziona gli abiti da ripristinare')
                            ->bulkToggleable()
                            ->options(
                                $archived->mapWithKeys(fn ($d) => [
                                    $d->id => sprintf(
                                        '%s — %s — archiviato il %s',
                                        $d->customer_name,
                                        ucfirst((string) $d->ceremony_type ?: '-'),
                                        optional($d->archived_at)->format('d/m/Y H:i')
                                    ),
                                ])->all()
                            )
                            ->columns(1),
                    ];
                })
                // Se nel form c'è solo il placeholder, nascondo il bottone submit
                ->modalSubmitActionLabel('Ripristina selezionati')
                ->modalCancelActionLabel('Chiudi')
                ->action(function (array $data): void {
                    $ids = $data['to_restore'] ?? [];

                    if (empty($ids)) {
                        Notification::make()
                            ->title('Nessun abito selezionato')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Ripristino (tolgo archived_at) senza global scopes
                    Dress::withoutGlobalScopes()
                        ->whereIn('id', $ids)
                        ->update(['archived_at' => null]);

                    Notification::make()
                        ->title('Ripristino completato')
                        ->body(count($ids) . ' abito/i ripristinato/i.')
                        ->success()
                        ->send();

                    // ricarico la tabella
                    $this->dispatch('refresh');
                }),
        ];
    }
}
