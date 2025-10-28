<?php

namespace App\Filament\Resources\AppuntamentoResource\Widgets;

use App\Models\Appuntamento;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form as FilamentForm;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AppuntamentiCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Appuntamenti';
    protected static ?int $sort = 1;

    /**
     * Indica al widget quale model usare per Create/Edit/Delete
     */
    public Model|string|null $model = Appuntamento::class;

    /**
     * Configurazione FullCalendar
     */
    public function config(): array
    {
        return [
            'initialView'   => 'dayGridMonth',
            'editable'      => true,      // drag & drop eventi
            'selectable'    => true,      // selezione slot per creare evento
            'locale'        => 'it',
            'timeZone'      => 'local',   // usa timezone locale del browser
            'eventTimeFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'slotMinTime'   => '07:00:00',
            'slotMaxTime'   => '22:00:00',
            'headerToolbar' => [
                'left'   => 'dayGridMonth,timeGridWeek,timeGridDay',
                'center' => 'title',
                'right'  => 'prev,next today',
            ],
        ];
    }

    /**
     * Converte Appuntamenti -> Eventi FullCalendar
     */
    public function fetchEvents(array $fetchInfo): array
    {
        // Filtro per range visibile (ottimizza queries)
        $start = Carbon::parse($fetchInfo['start']);
        $end   = Carbon::parse($fetchInfo['end']);

        return Appuntamento::query()
            ->whereBetween('data_appuntamento', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->map(function (Appuntamento $a) {
                $start = Carbon::parse(
    ($a->data_appuntamento instanceof \Carbon\Carbon
        ? $a->data_appuntamento->toDateString()
        : \Carbon\Carbon::parse($a->data_appuntamento)->toDateString()
    ) . ' ' . $a->ora_appuntamento
);
                // Colori per stato
                $colorMap = [
                    'da_fare' => '#f59e0b', // amber-500
                    'fatto'   => '#10b981', // emerald-500
                    'scaduto' => '#ef4444', // red-500
                ];
                $bg = $colorMap[$a->stato] ?? '#64748b'; // slate-500 fallback

                $title = trim($a->nome.' '.$a->cognome);
                $title .= $a->telefono ? ' — '.$a->telefono : '';
                if ($a->descrizione) {
                    $title .= ' • '.Str::limit($a->descrizione, 40);
                }

                return EventData::make()
                    ->id((string)$a->id)
                    ->title($title)
                    ->start($start->format('Y-m-d H:i:s'))
                    ->allDay(false)
                    ->backgroundColor($bg)
                    ->textColor('#ffffff');
            })
            ->toArray();
    }

    /**
     * Drag & drop: aggiorna data/ora quando sposti un evento
     */
    public function onEventDrop(
        array $event,
        array $oldEvent,
        array $relatedEvents,
        array $delta,
        ?array $oldResource,
        ?array $newResource
    ): bool {
        if ($record = Appuntamento::find($event['id'])) {
            $start = Carbon::parse($event['start']); // ISO string
            $record->update([
                'data_appuntamento' => $start->toDateString(),
                'ora_appuntamento'  => $start->format('H:i:s'),
            ]);
        }

        // refresh del widget
        $this->dispatch('$refresh');

        Notification::make()
            ->title('Appuntamento riprogrammato')
            ->success()
            ->send();

        return true;
    }

    /**
     * Azione di creazione (da selezione calendario)
     */
    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuovo Appuntamento')
                ->mountUsing(function (FilamentForm $form, array $arguments) {
                    $start = Carbon::parse($arguments['start'] ?? now());
                    $form->fill([
                        'data_appuntamento' => $start->toDateString(),
                        'ora_appuntamento'  => $start->format('H:i:s'),
                        'stato'             => 'da_fare',
                    ]);
                })
                ->mutateFormDataUsing(function (array $data) {
                    $data['stato'] = 'da_fare';
                    return $data;
                })
                ->after(function () {
                    $this->dispatch('$refresh');
                    Notification::make()->title('Appuntamento creato')->success()->send();
                }),
        ];
    }

    /**
     * Azioni modal sugli eventi (Edit/Delete)
     */
    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->after(function () {
                    $this->dispatch('$refresh');
                    Notification::make()->title('Appuntamento aggiornato')->success()->send();
                }),
            Actions\DeleteAction::make()
                ->after(function () {
                    $this->dispatch('$refresh');
                    Notification::make()->title('Appuntamento eliminato')->success()->send();
                }),
        ];
    }

    /**
     * Form usato per Create/Edit nel calendario
     * (versione compatta ma completa)
     */
    public function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome')->required(),
                Forms\Components\TextInput::make('cognome')
                    ->label('Cognome')->required(),
                Forms\Components\TextInput::make('telefono')
                    ->label('Telefono')->tel()->maxLength(20)->required(),
                Forms\Components\DatePicker::make('data_appuntamento')
                    ->label('Data')->required(),
                Forms\Components\TimePicker::make('ora_appuntamento')
                    ->label('Ora')->seconds(false)->required(),
            ]),
            Forms\Components\Textarea::make('descrizione')
                ->label('Descrizione')->rows(3),
            Forms\Components\Select::make('stato')
                ->label('Stato')
                ->options([
                    'da_fare' => 'Da fare',
                    'fatto'   => 'Fatto',
                    'scaduto' => 'Scaduto',
                ])
                ->default('da_fare'),
        ];
    }
}
