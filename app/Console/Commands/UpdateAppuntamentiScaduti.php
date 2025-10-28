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
        // Aggiorna in un'unica query usando il timezone del DB
        $count = \App\Models\Appuntamento::where('stato', 'da_fare')
            ->whereRaw("TIMESTAMP(data_appuntamento, ora_appuntamento) <= NOW()")
            ->update([
                'stato' => 'scaduto',
                'completed_at' => \Illuminate\Support\Facades\DB::raw('NOW()'),
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
