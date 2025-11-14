<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dress;
use Illuminate\Support\Facades\Storage;

class DrawingController extends Controller
{
    // ➤ Canvas per abito ESISTENTE
    public function edit(Dress $dress)
    {
        return view('draw_canvas', [
            'dress' => $dress,
        ]);
    }

    // ➤ Salvataggio per abito ESISTENTE
    public function store(Request $request, Dress $dress)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        // PNG in Base64 → estrai solo i dati
        $image = $request->image;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = base64_decode($image);

        // Salva su filesystem
        $filename = 'dress-drawings/' . uniqid('drawing_') . '.png';
        Storage::disk('public')->put($filename, $image);

        // Aggiorna DB
        $dress->drawing_image = $filename;
        $dress->save();

        return response()->json([
            'success' => true,
            'message' => 'Disegno salvato con successo!',
            'path' => $filename
        ]);
    }


    // ======================================================
    // ➤ MODALITÀ TEMPORANEA (NUOVO ABITO)
    // ======================================================

    // Apri canvas SENZA dress_id
    public function editTemp()
    {
        // dress null → modalità nuova creazione
        return view('draw_canvas', [
            'dress' => null,
        ]);
    }

    // Salva file PNG temporaneo
    public function storeTemp(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        // PNG → Base64 → dati raw
        $image = $request->image;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = base64_decode($image);

// Salva su storage
$filename = 'dress-drawings/' . uniqid('temp_drawing_') . '.png';
Storage::disk('public')->put($filename, $image);

// 🔴 aggiungi QUESTO:
session()->put('last_dress_temp_drawing', $filename);

// Rispondi con JSON…
return response()->json([
    'success' => true,
    'message' => 'Disegno temporaneo salvato!',
    'path' => $filename,
]);

    }
}
