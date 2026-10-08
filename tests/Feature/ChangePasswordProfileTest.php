<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\ChangePassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ChangePasswordProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_profile_page(): void
    {
        config()->set('app.env', 'local');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('filament.admin.auth.profile'))
            ->assertOk()
            ->assertSee('Cambia password');
    }

    public function test_authenticated_user_can_change_password_without_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('vecchia-password'),
        ]);

        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->assertFormFieldExists('password')
            ->assertFormFieldExists('passwordConfirmation')
            ->assertFormFieldDoesNotExist('name')
            ->assertFormFieldDoesNotExist('email')
            ->fillForm([
                'password' => 'nuova-password',
                'passwordConfirmation' => 'nuova-password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('nuova-password', $user->fresh()->password));
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('vecchia-password'),
        ]);

        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->fillForm([
                'password' => 'nuova-password',
                'passwordConfirmation' => 'password-diversa',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'password' => 'same',
                'passwordConfirmation' => 'same',
            ]);

        $this->assertTrue(Hash::check('vecchia-password', $user->fresh()->password));
    }
}
