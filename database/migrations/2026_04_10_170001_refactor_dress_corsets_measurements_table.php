<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'dress_corsets';

        $this->renameColumns($table, [
            'pinza_vita_davanti' => 'ripresa_vita_davanti',
            'pinza_vita_lato' => 'ripresa_vita_lato',
            'pinza_vita_dietro' => 'ripresa_vita_dietro',
            'pinza_fianchi_davanti' => 'ripresa_fianchi_davanti',
            'pinza_fianchi_lato' => 'ripresa_fianchi_lato',
            'pinza_fianchi_dietro' => 'ripresa_fianchi_dietro',
            'linea_sottoseno_davanti' => 'legacy_linea_sottoseno_davanti',
            'linea_sottoseno_lato' => 'legacy_linea_sottoseno_lato',
            'linea_sottoseno_dietro' => 'legacy_linea_sottoseno_dietro',
        ]);

        $newColumns = [
            'circonferenza_seno',
            'circonferenza_sotto_seno',
            'circonferenza_vita',
            'circonferenza_fianchi_15_cm',
            'altezza_laterale',
            'arco_orizzontale',
            'altezza_seno',
            'linea_sotto_seno',
            'raggio_inferiore',
        ];

        Schema::table($table, function (Blueprint $table) use ($newColumns) {
            foreach ($newColumns as $column) {
                if (! Schema::hasColumn('dress_corsets', $column)) {
                    $table->decimal($column, 5, 1)->nullable();
                }
            }
        });

        if (
            Schema::hasColumn($table, 'linea_sotto_seno')
            && Schema::hasColumn($table, 'legacy_linea_sottoseno_davanti')
            && Schema::hasColumn($table, 'legacy_linea_sottoseno_lato')
            && Schema::hasColumn($table, 'legacy_linea_sottoseno_dietro')
        ) {
            DB::table($table)->update([
                'linea_sotto_seno' => DB::raw(
                    'COALESCE(linea_sotto_seno, legacy_linea_sottoseno_davanti, legacy_linea_sottoseno_lato, legacy_linea_sottoseno_dietro)'
                ),
            ]);
        }
    }

    public function down(): void
    {
        $table = 'dress_corsets';

        if (
            Schema::hasColumn($table, 'linea_sotto_seno')
            && Schema::hasColumn($table, 'legacy_linea_sottoseno_davanti')
        ) {
            DB::table($table)
                ->whereNull('legacy_linea_sottoseno_davanti')
                ->update([
                    'legacy_linea_sottoseno_davanti' => DB::raw('linea_sotto_seno'),
                ]);
        }

        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn($table, 'circonferenza_seno') ? 'circonferenza_seno' : null,
            Schema::hasColumn($table, 'circonferenza_sotto_seno') ? 'circonferenza_sotto_seno' : null,
            Schema::hasColumn($table, 'circonferenza_vita') ? 'circonferenza_vita' : null,
            Schema::hasColumn($table, 'circonferenza_fianchi_15_cm') ? 'circonferenza_fianchi_15_cm' : null,
            Schema::hasColumn($table, 'altezza_laterale') ? 'altezza_laterale' : null,
            Schema::hasColumn($table, 'arco_orizzontale') ? 'arco_orizzontale' : null,
            Schema::hasColumn($table, 'altezza_seno') ? 'altezza_seno' : null,
            Schema::hasColumn($table, 'linea_sotto_seno') ? 'linea_sotto_seno' : null,
            Schema::hasColumn($table, 'raggio_inferiore') ? 'raggio_inferiore' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table($table, function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        $this->renameColumns($table, [
            'ripresa_vita_davanti' => 'pinza_vita_davanti',
            'ripresa_vita_lato' => 'pinza_vita_lato',
            'ripresa_vita_dietro' => 'pinza_vita_dietro',
            'ripresa_fianchi_davanti' => 'pinza_fianchi_davanti',
            'ripresa_fianchi_lato' => 'pinza_fianchi_lato',
            'ripresa_fianchi_dietro' => 'pinza_fianchi_dietro',
            'legacy_linea_sottoseno_davanti' => 'linea_sottoseno_davanti',
            'legacy_linea_sottoseno_lato' => 'linea_sottoseno_lato',
            'legacy_linea_sottoseno_dietro' => 'linea_sottoseno_dietro',
        ]);
    }

    private function renameColumns(string $table, array $renameMap): void
    {
        foreach ($renameMap as $from => $to) {
            if (! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) use ($from, $to) {
                $table->renameColumn($from, $to);
            });
        }
    }
};
