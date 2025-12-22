<?php

namespace App\Filament\Resources\SpecialDressResource\Pages;

use App\Filament\Resources\SpecialDressResource;
use App\Filament\Widgets\SpecialDressesEconomics;
use App\Filament\Widgets\SpecialDressesOverview;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSpecialDresses extends ListRecords
{
    protected static string $resource = SpecialDressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // Stampa mensile
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

                    return redirect()->route('pdf.special.dresses.monthly', [
                        'year'  => $year,
                        'month' => $month,
                    ]);
                }),
        ];
    }

    // Widget SOPRA la tabella (Overview + Economics)
    protected function getHeaderWidgets(): array
    {
        return [
            SpecialDressesOverview::class,
            SpecialDressesEconomics::class,
        ];
    }
}
