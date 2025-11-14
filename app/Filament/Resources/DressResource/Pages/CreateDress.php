<?php

namespace App\Filament\Resources\DressResource\Pages;

use App\Filament\Resources\DressResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateDress extends CreateRecord
{
    protected static string $resource = DressResource::class;

    /**
     * Prima di creare il Dress nel DB, agganciamo
     * l'eventuale disegno salvato in modalità TEMP
     * (canvas aperto da "Nuovo Abito").
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Se NON c'è già un'immagine caricata a mano
        // e abbiamo un disegno temporaneo salvato dal canvas,
        // usalo come drawing_image.
        if (empty($data['drawing_image']) && Session::has('last_dress_temp_drawing')) {
            $data['drawing_image'] = Session::pull('last_dress_temp_drawing'); // legge e svuota la sessione
        }

        return $data;
    }
}
