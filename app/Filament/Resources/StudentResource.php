<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers\PresencesRelationManager;
use App\Filament\Resources\StudentResource\RelationManagers\PaymentsRelationManager;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Presenze Studenti';
    protected static ?string $pluralLabel = 'Presenze Studenti';
    protected static ?string $modelLabel = 'Presenza Studente';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Presenze & Calendari';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('cognome')
                    ->label('Cognome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('telefono')
                    ->label('Telefono')
                    ->tel()
                    ->maxLength(20),

                Forms\Components\TextInput::make('costo_lezione')
                    ->label('Costo Lezione (€)')
                    ->numeric()
                    ->required()
                    ->prefix('€'),

                // ✅ Saldato: SOLO VISUALIZZAZIONE, calcolato dalle presenze
                Forms\Components\Toggle::make('saldato')
                    ->label('Saldato')
                    ->disabled()        // non cliccabile
                    ->dehydrated(false)  // non salva dal form
                    ->formatStateUsing(function ($state, ?Student $record) {
                        if (! $record) return false;
                        // ON solo se nessuna presenza è non pagata
                        return $record->presences()->where('is_paid', false)->doesntExist();
                    })
                    ->helperText('Si attiva quando tutte le lezioni sono pagate.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount([
                    'presences',
                    'presences as paid_presences_count' => fn ($q) => $q->where('is_paid', true),
                ]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Telefono'),

                Tables\Columns\TextColumn::make('costo_lezione')
                    ->label('Costo Lezione')
                    ->money('EUR'),

                // Usiamo il withCount già fatto sopra
                Tables\Columns\TextColumn::make('presences_count')
                    ->label('Presenze')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payments_sum_amount')
                    ->sum('payments', 'amount')
                    ->money('EUR')
                    ->label('Totale Pagato'),

                // 👉 NUOVA colonna: "Pagate" come 3/10 con colore dinamico e ordinabile per %
                Tables\Columns\TextColumn::make('paid_ratio')
                    ->label('Pagate')
                    ->badge()
                    ->getStateUsing(fn (Student $r) => "{$r->paid_presences_count}/{$r->presences_count}")
                    ->color(function (Student $r) {
                        $total = (int) $r->presences_count;
                        $paid  = (int) $r->paid_presences_count;
                        if ($total === 0) return 'gray';
                        $missing = $total - $paid;
                        if ($missing === 0) return 'success';   // verde
                        if ($missing <= 3) return 'warning';     // arancione (1–3 mancanti)
                        return 'danger';                          // rosso (4+ mancanti)
                    })
                    ->sortable(
                        query: function (Builder $query, string $direction): Builder {
                            // Ordina per % pagate; gestisce divisione per 0
                            $sql = '(CASE WHEN presences_count = 0 THEN 0 ELSE (paid_presences_count * 1.0 / presences_count) END)';
                            return $query->orderByRaw($sql . ' ' . ($direction === 'desc' ? 'DESC' : 'ASC'));
                        }
                    )
                    ->tooltip(fn (Student $r) => $r->presences_count
                        ? 'Mancano ' . ($r->presences_count - $r->paid_presences_count) . ' lezioni'
                        : 'Nessuna lezione'),

                // Indicatore "Saldato" (solo visuale) senza N+1, usando i counts caricati
                Tables\Columns\IconColumn::make('saldato')
                    ->label('Saldato')
                    ->state(fn (Student $r) => $r->presences_count > 0 && $r->paid_presences_count === $r->presences_count)
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PresencesRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
