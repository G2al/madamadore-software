<?php

namespace App\Filament\Resources\DressResource\Pages;

use App\Filament\Resources\DressResource;
use App\Filament\Widgets\DressesOverview;
use App\Filament\Widgets\DressesEconomics;
use App\Models\Dress;
use Carbon\Carbon;
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

            // 🗑️ Cestino
            Actions\Action::make('open_trash')
                ->label('Cestino')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->modalHeading('Abiti archiviati')
                ->modalWidth('3xl')
                ->form(function () {
                    $archived = Dress::withoutGlobalScopes()
                        ->whereNotNull('archived_at')
                        ->latest('archived_at')
                        ->get(['id', 'customer_name', 'ceremony_type', 'archived_at']);

                    if ($archived->isEmpty()) {
                        return [
                            Forms\Components\Placeholder::make('empty')
                                ->label('')
                                ->content('🗑️ Nessun abito archiviato al momento.'),
                        ];
                    }

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

                    Dress::withoutGlobalScopes()
                        ->whereIn('id', $ids)
                        ->update(['archived_at' => null]);

                    Notification::make()
                        ->title('Ripristino completato')
                        ->body(count($ids) . ' abito/i ripristinato/i.')
                        ->success()
                        ->send();

                    $this->dispatch('refresh');
                }),

            // 🖨️ Stampa mensile
            Actions\Action::make('print_monthly')
                ->label('Stampa abiti per mese')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->modalHeading('Stampa abiti per mese di consegna')
                ->form([
                    Forms\Components\DatePicker::make('month')
                        ->label('Mese di consegna')
                        ->native(false)
                        ->displayFormat('m/Y')
                        ->required()
                        ->closeOnDateSelection(),
                ])
                ->modalSubmitActionLabel('Stampa PDF')
                ->action(function (array $data) {
                    if (empty($data['month'])) {
                        Notification::make()
                            ->title('Seleziona un mese')
                            ->warning()
                            ->send();

                        return;
                    }

                    $date = Carbon::parse($data['month']);
                    $year = $date->year;
                    $month = $date->month;

                    return redirect()->route('pdf.dresses.monthly', [
                        'year'  => $year,
                        'month' => $month,
                    ]);
                }),
        ];
    }

    // 👇 Widget SOPRA la tabella
    protected function getHeaderWidgets(): array
    {
        return [
            DressesOverview::class,
            DressesEconomics::class,
        ];
    }
}
