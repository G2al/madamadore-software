<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends EditProfile
{
    public static function getLabel(): string
    {
        return 'Profilo';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Cambia password';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('password')
                    ->label('Nuova password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->autocomplete('new-password')
                    ->required()
                    ->maxLength(255)
                    ->same('passwordConfirmation')
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                TextInput::make('passwordConfirmation')
                    ->label('Ripeti nuova password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->autocomplete('new-password')
                    ->required()
                    ->same('password')
                    ->dehydrated(false),
            ]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Password aggiornata');
    }
}
