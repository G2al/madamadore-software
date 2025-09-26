<?php

namespace App\Filament\Resources;

use App\Models\Dress;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\DressInAttesaAccontoResource\Pages;

class DressInAttesaAccontoResource extends Resource
{
    protected static ?string $model = Dress::class;
    
    // Configurazione navigazione
    protected static ?string $navigationGroup = 'Abiti';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'In Attesa Acconto';
    protected static ?string $modelLabel = 'Abito in Attesa Acconto';
    protected static ?string $pluralModelLabel = 'Abiti in Attesa Acconto';
    protected static ?int $navigationSort = 2;

    // Usa lo stesso form della DressResource principale
    public static function form(Form $form): Form
    {
        return DressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            // FILTRO AUTOMATICO INVISIBILE
            ->modifyQueryUsing(fn ($query) => $query->where('status', 'in_attesa_acconto'))
            
            // COLONNE SPECIFICHE PER QUESTO STATO
            ->columns([
    TextColumn::make('customer_name')
        ->label('Cliente')
        ->description(fn ($record) => $record->phone_number)
        ->searchable()
        ->sortable()
        ->icon('heroicon-o-user')
        ->weight('bold'),

    TextColumn::make('ceremony_type')
        ->label('Cerimonia')
        ->formatStateUsing(fn ($state) => ucfirst($state))
        ->badge()
        ->color('info')
        ->sortable(),

    TextColumn::make('ceremony_date')
        ->label('Data Cerimonia')
        ->date('d/m/Y')
        ->sortable(),

    TextColumn::make('total_client_price')
        ->label('Prezzo Totale')
        ->money('EUR')
        ->color('success')
        ->sortable()
        ->visible(fn() => auth()->user()->role === 'admin'),

    TextColumn::make('created_at')
        ->label('Creato il')
        ->date('d/m/Y')
        ->sortable(),
])
            ->actions([
    // Solo il bottone principale per questo stato
    Action::make('acconto_ricevuto')
        ->label('Acconto Ricevuto')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->requiresConfirmation()
        ->action(fn (Dress $record) => $record->update(['status' => 'confermato']))
        ->successNotificationTitle('Abito confermato! Acconto ricevuto.'),
])
        ->bulkActions([
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDressInAttesaAcconto::route('/'),
            'create' => Pages\CreateDressInAttesaAcconto::route('/create'),
            'edit' => Pages\EditDressInAttesaAcconto::route('/{record}/edit'),
        ];
    }

    // Badge con conteggio
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'in_attesa_acconto')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'in_attesa_acconto')->count();
        
        return match (true) {
            $count > 5 => 'danger',
            $count > 2 => 'warning', 
            $count > 0 => 'primary',
            default => null
        };
    }
}