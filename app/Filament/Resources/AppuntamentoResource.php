<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppuntamentoResource\Pages;
use App\Models\Appuntamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;

class AppuntamentoResource extends Resource
{
    protected static ?string $model = Appuntamento::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Gestione Appuntamenti';
    protected static ?string $navigationLabel = 'Appuntamenti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('nome')
                        ->label('Nome')
                        ->required(),

                    Forms\Components\TextInput::make('cognome')
                        ->label('Cognome')
                        ->required(),

                    Forms\Components\TextInput::make('telefono')
                        ->label('Telefono')
                        ->tel()
                        ->maxLength(20)
                        ->required(),

                    Forms\Components\DatePicker::make('data_appuntamento')
                        ->label('Data Appuntamento')
                        ->required(),

                    Forms\Components\TimePicker::make('ora_appuntamento')
                        ->label('Ora Appuntamento')
                        ->required(),
                ]),

                Forms\Components\Textarea::make('descrizione')
                    ->label('Descrizione')
                    ->rows(3),

                Forms\Components\Select::make('stato')
                    ->label('Stato')
                    ->options([
                        'da_fare' => 'Da fare',
                        'fatto' => 'Fatto',
                        'scaduto' => 'Scaduto',
                    ])
                    ->default('da_fare')
                    ->disabled(fn ($record) => $record && $record->stato === 'scaduto'),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Completato il')
                    ->disabled()
                    ->visible(fn ($record) => $record && $record->completed_at),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cognome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('telefono'),
                Tables\Columns\TextColumn::make('data_appuntamento')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('ora_appuntamento')->time('H:i'),
                Tables\Columns\TextColumn::make('descrizione')->limit(40)->wrap(),
                
                BadgeColumn::make('stato')
                    ->colors([
                        'warning' => 'da_fare',
                        'success' => 'fatto',
                        'danger' => 'scaduto',
                    ])
                    ->label('Stato'),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completato il')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('segna_fatto')
                    ->label('Segna come fatto')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->stato !== 'fatto')
                    ->requiresConfirmation()
                    ->action(function (Appuntamento $record): void {
                        $record->markAsCompleted();

                        Notification::make()
                            ->title('Appuntamento segnato come completato')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppuntamentos::route('/'),
            'create' => Pages\CreateAppuntamento::route('/create'),
            'edit' => Pages\EditAppuntamento::route('/{record}/edit'),
        ];
    }
}
