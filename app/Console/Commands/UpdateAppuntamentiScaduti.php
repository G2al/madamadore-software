<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appuntamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateAppuntamentiScaduti extends Command
{
    /**
     * Nome del comando Artisan
     */
    protected $signature = 'appuntamenti:update-scaduti';
    protected $description = 'Aggiorna lo stato degli appuntamenti scaduti e imposta completed_at';
    
public function handle(): void
{
    // Ora locale dell'app (Europe/Rome)
    $today = now()->toDateString();      // es. "2025-10-28"
    $currentTime = now()->format('H:i:s'); // es. "17:12:00"

    // Segna scaduti:
    // - tutti gli appuntamenti con data < oggi
    // - oppure data = oggi e ora <= orario corrente
    $count = \App\Models\Appuntamento::where('stato', 'da_fare')
        ->where(function ($q) use ($today, $currentTime) {
            $q->where('data_appuntamento', '<', $today)
              ->orWhere(function ($q2) use ($today, $currentTime) {
                  $q2->where('data_appuntamento', $today)
                     ->where('ora_appuntamento', '<=', $currentTime);
              });
        })
        ->update([
            'stato' => 'scaduto',
            'completed_at' => now(),
        ]);

    if ($count === 0) {
        \Log::info('[Scheduler] Nessun appuntamento da aggiornare alle ' . now());
        $this->info('Nessun appuntamento da aggiornare.');
        return;
    }

    \Log::info("[Scheduler] {$count} appuntamenti aggiornati a scaduti alle " . now());
    $this->info("{$count} appuntamenti aggiornati a scaduti.");
}



}
