<?php

namespace App\Filament\Widgets;

use App\Models\Person;
use App\Models\Presence;
use Filament\Forms;
use Filament\Forms\Form as FilamentForm;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class PresenceCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Presenze';
    protected static ?int $sort = 4;

    /**
     * Diciamo al widget quale Model usare per le Actions (Create/Edit/Delete)
     * così può aprire il modal automaticamente alla selezione del giorno.
     */
    public Model|string|null $model = Presence::class;

    /**
     * Configurazione FullCalendar
     */
    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'editable'    => true,   // drag & drop eventi
            'selectable'  => true,   // selezione giorno/intervallo -> apre CreateAction
            'locale'      => 'it',
            // opzionale: header più ricco
            'headerToolbar' => [
                'left'   => 'dayGridMonth,timeGridWeek,timeGridDay',
                'center' => 'title',
                'right'  => 'prev,next today',
            ],
        ];
    }

    /**
     * Eventi dal DB -> FullCalendar
     */
    public function fetchEvents(array $fetchInfo): array
    {
        return Presence::with('person')
            // volendo puoi filtrare con $fetchInfo['start'] / ['end']
            ->get()
            ->map(fn (Presence $presence) => EventData::make()
                ->id((string) $presence->id)
                ->title($presence->person?->full_name ?? 'Senza nome')
                ->start($presence->date->toDateString())
                ->backgroundColor('#10B981')
                ->textColor('#ffffff')
            )
            ->toArray();
    }

    /**
     * Drag & drop: aggiorna la data della presenza spostata
     */
    public function onEventDrop(
        array $event,
        array $oldEvent,
        array $relatedEvents,
        array $delta,
        ?array $oldResource,
        ?array $newResource
    ): bool {
        if ($presence = Presence::find($event['id'])) {
            $presence->update(['date' => $event['start']]);
        }
        return true;
    }

    /**
     * Azioni del modal (Edit/Delete quando clicchi un evento)
     * e Create quando selezioni un giorno: la CreateAction viene
     * montata automaticamente dal pacchetto alla selezione.
     * (Vedi "Creating events on day selection" nei docs)
     */
   protected function headerActions(): array
{
    return [
        Actions\CreateAction::make()
            ->label('Aggiungi Presenza')
            ->mountUsing(function (FilamentForm $form, array $arguments) {
                $form->fill([
                    'date' => $arguments['start'] ?? now()->toDateString(),
                ]);
            })
            ->after(function () {
                $this->dispatch('$refresh'); // 👈 rinfresca il widget
                Notification::make()->title('Presenza creata')->success()->send();
            }),
    ];
}

protected function modalActions(): array
{
    return [
        Actions\EditAction::make()
            ->after(function () {
                $this->dispatch('$refresh');
                Notification::make()->title('Presenza aggiornata')->success()->send();
            }),
        Actions\DeleteAction::make()
            ->after(function () {
                $this->dispatch('$refresh'); // 👈 refresh anche qui
                Notification::make()->title('Presenza eliminata')->success()->send();
            }),
    ];
}


    /**
     * Schema del form usato da Create/Edit/View actions
     */
    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('person_id')
                ->label('Persona')
                ->options(
                    Person::query()
                        ->orderBy('nome')
                        ->orderBy('cognome')
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => $p->full_name])
                )
                ->searchable()
                ->required(),

            Forms\Components\DatePicker::make('date')
                ->label('Data')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y'),
        ];
    }
}
