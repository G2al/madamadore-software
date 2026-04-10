<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressCorset extends Model
{
    public const DIMENSIONAL_MEASUREMENTS = [
        'circonferenza_seno' => 'Circonferenza seno',
        'circonferenza_sotto_seno' => 'Circonferenza sotto seno',
        'circonferenza_vita' => 'Circonferenza vita',
        'circonferenza_fianchi_15_cm' => 'Circonferenza fianchi 15 cm',
    ];

    public const STRUCTURAL_MEASUREMENTS = [
        'altezza_laterale' => 'Altezza laterale',
        'arco_orizzontale' => 'Arco orizzontale',
        'altezza_seno' => 'Altezza seno',
        'linea_sotto_seno' => 'Linea sotto il seno',
        'raggio_inferiore' => 'Raggio inferiore',
    ];

    public const RIPRESA_GROUPS = [
        'vita' => [
            'label' => 'Riprese vita',
            'formula' => '(Circ. sotto seno - Circ. vita) : 2',
            'fields' => [
                'ripresa_vita_davanti' => 'Davanti',
                'ripresa_vita_lato' => 'Lato',
                'ripresa_vita_dietro' => 'Dietro',
            ],
        ],
        'fianchi' => [
            'label' => 'Riprese fianchi',
            'formula' => '(Circ. fianchi - Circ. sotto seno) : 2',
            'fields' => [
                'ripresa_fianchi_davanti' => 'Davanti',
                'ripresa_fianchi_lato' => 'Lato',
                'ripresa_fianchi_dietro' => 'Dietro',
            ],
        ],
    ];

    protected $fillable = [
        'dress_id',
        'circonferenza_seno',
        'circonferenza_sotto_seno',
        'circonferenza_vita',
        'circonferenza_fianchi_15_cm',
        'altezza_laterale',
        'arco_orizzontale',
        'altezza_seno',
        'linea_sotto_seno',
        'raggio_inferiore',
        'ripresa_vita_davanti',
        'ripresa_vita_lato',
        'ripresa_vita_dietro',
        'ripresa_fianchi_davanti',
        'ripresa_fianchi_lato',
        'ripresa_fianchi_dietro',
    ];

    protected function casts(): array
    {
        return array_fill_keys(self::dataFieldNames(), 'float');
    }

    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }

    public static function scalarMeasurementFields(): array
    {
        return self::DIMENSIONAL_MEASUREMENTS + self::STRUCTURAL_MEASUREMENTS;
    }

    public static function ripresaFieldNames(): array
    {
        return collect(self::RIPRESA_GROUPS)
            ->flatMap(fn (array $group) => array_keys($group['fields']))
            ->values()
            ->all();
    }

    public static function dataFieldNames(): array
    {
        return array_merge(
            array_keys(self::scalarMeasurementFields()),
            self::ripresaFieldNames(),
        );
    }

    public static function larghezzaSenoFormulaFor(?float $circSeno): ?string
    {
        if ($circSeno === null || $circSeno < 80 || $circSeno > 116) {
            return null;
        }

        return $circSeno <= 100 ? '1/4 CS - 4' : '1/4 CS - 5';
    }

    public static function calculateLarghezzaSeno(?float $circSeno): ?float
    {
        if ($circSeno === null || $circSeno < 80 || $circSeno > 116) {
            return null;
        }

        $offset = $circSeno <= 100 ? 4 : 5;

        return round(($circSeno / 4) - $offset, 1);
    }

    public static function calculateLineaSottoSenoSuggerita(?float $circSeno): ?float
    {
        $larghezzaSeno = self::calculateLarghezzaSeno($circSeno);

        if ($larghezzaSeno === null || $circSeno === null) {
            return null;
        }

        return round(($larghezzaSeno / 2) - ($circSeno / 40), 1);
    }
}
