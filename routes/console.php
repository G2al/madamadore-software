<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Qui puoi registrare i tuoi comandi Artisan personalizzati e la logica
| di scheduling (pianificazione) per Laravel 12+. Non esiste più il Kernel,
| quindi le pianificazioni vanno definite direttamente qui.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Pianificazione dei comandi
|--------------------------------------------------------------------------
|
| Qui definiamo il job che aggiorna automaticamente lo stato degli
| appuntamenti scaduti ogni 5 minuti.
|
*/

Schedule::command('appuntamenti:update-scaduti')->everyMinute();

