<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ceremony;

class CeremonySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ceremonies = [
            ['name' => 'Matrimonio', 'icon' => '💒', 'sort_order' => 1],
            ['name' => 'Battesimo', 'icon' => '👶', 'sort_order' => 2],
            ['name' => 'Comunione', 'icon' => '🕯️', 'sort_order' => 3],
            ['name' => 'Cresima', 'icon' => '📿', 'sort_order' => 4],
            ['name' => 'Festa 18 Anni', 'icon' => '🎉', 'sort_order' => 5],
            ['name' => 'Laurea', 'icon' => '🎓', 'sort_order' => 6],
            ['name' => 'Altro', 'icon' => '✨', 'sort_order' => 99],
        ];

        foreach ($ceremonies as $ceremony) {
            Ceremony::updateOrCreate(
                ['name' => $ceremony['name']],
                ['icon' => $ceremony['icon'], 'sort_order' => $ceremony['sort_order']]
            );
        }
    }
}
