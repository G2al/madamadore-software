<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\StudentPresence;
use Filament\Forms;
use Filament\Forms\Form as FilamentForm;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class StudentCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Lezioni';
    protected static ?int $sort = 4;

    /**
     * Indichiamo il modello da usare per le azioni automatiche.
     */
    public Model|string|null $model = StudentPresence::class;

    /**
     * Configurazione di FullCalendar.
     */
    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'editable'    => true,
            'selectable'  => true,
            'locale'      => 'it',
            'headerToolbar' => [
                'left'   => 'dayGridMonth,timeGridWeek,timeGridDay',
                'center' => 'title',
                'right'  => 'prev,next today',
            ],
        ];
    }

    /**
     * Carica le presenze dal DB.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        return StudentPresence::with('student')
            ->get()
            ->map(fn (StudentPresence $presence) => EventData::make()
                ->id((string) $presence->id)
                ->title($presence->student?->full_name ?? 'Studente senza nome')
                ->start($presence->date->toDateString())
                ->backgroundColor('#3B82F6') // blu
                ->textColor('#ffffff')
            )
            ->toArray();
    }

    /**
     * Drag & drop per aggiornare la data.
     */
    public function onEventDrop(
        array $event,
        array $oldEvent,
        array $relatedEvents,
        array $delta,
        ?array $oldResource,
        ?array $newResource
    ): bool {
        if ($presence = StudentPresence::find($event['id'])) {
            $presence->update(['date' => $event['start']]);
        }
        return true;
    }

    /**
     * Azioni quando clicchi "Nuovo evento".
     */
    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Aggiungi Lezione')
                ->mountUsing(function (FilamentForm $form, array $arguments) {
                    $form->fill([
                        'date' => $arguments['start'] ?? now()->toDateString(),
                    ]);
                })
                ->after(function () {
                    $this->dispatch('$refresh');
                    Notification::make()->title('Lezione creata')->success()->send();
                }),
        ];
    }

    /**
     * Azioni su un evento esistente (edit/delete).
     */
    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->after(function () {
                    $this->dispatch('$refresh');
                    Notification::make()->title('Lezione aggiornata')->success()->send();
                }),
            Actions\DeleteAction::make()
                ->after(function () {
                    $this->dispatch('$refresh');
                    Notification::make()->title('Lezione eliminata')->success()->send();
                }),
        ];
    }

    /**
     * Schema del form per creare/modificare le lezioni.
     */
    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('student_id')
                ->label('Studente')
                ->options(
                    Student::query()
                        ->orderBy('nome')
                        ->orderBy('cognome')
                        ->get()
                        ->mapWithKeys(fn ($s) => [$s->id => $s->full_name])
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

    public static function canView(): bool
{
    $user = auth()->user();

    return ($user?->role === 'admin')
        && request()->routeIs('filament.admin.resources.students.*'); // solo nella resource Studenti
}

}
