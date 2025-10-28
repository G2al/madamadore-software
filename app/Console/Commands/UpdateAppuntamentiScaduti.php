<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appuntamento;
use Illuminate\Support\Facades\Log;

class UpdateAppuntamentiScaduti extends Command
{
    /**
     * Nome del comando Artisan
     */
    protected $signature = 'appuntamenti:update-scaduti';

    protected $description = 'Aggiorna lo stato degli appuntamenti scaduti e imposta completed_at';

    public function handle(): void
    {
        // Ora locale dell'app (Europe/Rome) — niente dipendenze dal fuso del DB
        $today       = now()->toDateString();      // es: "2025-10-28"
        $currentTime = now()->format('H:i:s');     // es: "17:12:00"
        $nowHuman    = now()->format('Y-m-d H:i:s');

        // Costruisco una query UNA VOLTA sola per evitare duplicazioni
        $query = Appuntamento::where('stato', 'da_fare')
            ->where(function ($q) use ($today, $currentTime) {
                $q->where('data_appuntamento', '<', $today)
                  ->orWhere(function ($q2) use ($today, $currentTime) {
                      $q2->where('data_appuntamento', $today)
                         ->where('ora_appuntamento', '<=', $currentTime);
                  });
            });

        // Prendo gli ID da aggiornare per logging chiaro
        $ids = $query->pluck('id')->all();
        $count = count($ids);

        if ($count === 0) {
            Log::info("[Scheduler] Nessun appuntamento da aggiornare alle {$nowHuman}");
            $this->info('Nessun appuntamento da aggiornare.');
            return;
        }

        // Aggiorno SOLO gli ID trovati (evito di ricostruire la condizione)
        Appuntamento::whereIn('id', $ids)->update([
            'stato'        => 'scaduto',
            'completed_at' => now(),
        ]);

        Log::info("[Scheduler] {$count} appuntamenti aggiornati a scaduti alle {$nowHuman}. IDs: " . implode(',', $ids));
        $this->info("{$count} appuntamenti aggiornati a scaduti.");
    }
}
