<?php

namespace App\Filament\Resources\CompanyAdjustmentResource\Pages;

use App\Filament\Resources\CompanyAdjustmentResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\AdjustmentCalendarWidget;

class ListCompanyAdjustments extends ListRecords
{
    protected static string $resource = CompanyAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('print_day')
                ->label('Stampa aggiusti aziende per giorno')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->modalHeading('Stampa aggiusti aziende per giorno')
                ->form([
                    Forms\Components\DatePicker::make('date')
                        ->label('Giorno da stampare')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->closeOnDateSelection(),
                ])
                ->modalSubmitActionLabel('Stampa PDF')
                ->action(function (array $data) {
                    if (empty($data['date'])) {
                        Notification::make()
                            ->title('Seleziona un giorno')
                            ->warning()
                            ->send();

                        return;
                    }

                    $url = route('pdf.company-adjustments.day', [
                        'date' => Carbon::parse($data['date'])->toDateString(),
                    ]);

                    $this->js('window.open(' . json_encode($url) . ', "_blank");');
                }),

            Actions\Action::make('print_week')
                ->label('Stampa aggiusti aziende per settimana')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->modalHeading('Stampa aggiusti aziende per settimana')
                ->form([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Inizio settimana')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->closeOnDateSelection(),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('Fine settimana')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->closeOnDateSelection(),
                ])
                ->modalSubmitActionLabel('Stampa PDF')
                ->action(function (array $data) {
                    if (empty($data['start_date']) || empty($data['end_date'])) {
                        Notification::make()
                            ->title('Seleziona inizio e fine settimana')
                            ->warning()
                            ->send();

                        return;
                    }

                    $url = route('pdf.company-adjustments.week', [
                        'startDate' => Carbon::parse($data['start_date'])->toDateString(),
                        'endDate' => Carbon::parse($data['end_date'])->toDateString(),
                    ]);

                    $this->js('window.open(' . json_encode($url) . ', "_blank");');
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AdjustmentCalendarWidget::class,
        ];
    }
}
