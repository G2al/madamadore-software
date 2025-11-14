<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sketch - Canvas Draw Tool</title>

    <!-- Reset -->
    <link rel="stylesheet" href="https://public.codepenassets.com/css/reset-2.0.min.css">

    <!-- Your CSS -->
    <link rel="stylesheet" href="{{ asset('canvas/style.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body ondragover="dragOver(event)" ondrop="drop(event)">

    {{-- Hidden Dress ID (null se siamo in CREATE) --}}
    <input type="hidden" id="dress_id" value="{{ $dress?->id }}">

    {{-- Modalità: dress (edit) / temp (create) – opzionale ma chiaro --}}
    <input type="hidden" id="draw_mode" value="{{ $dress ? 'dress' : 'temp' }}">

    <!-- CANVAS -->
    <canvas id="draw" width="800" height="450"></canvas>
    <canvas id="canvasBg" width="800" height="450"></canvas>

    <!-- PREVIEW WINDOW -->
    <div class="imgNav drag" draggable="true" ondragstart="dragStart(event)" id="imgNav">
        <div class="imgNavTitle"><span class="cross" id="imgNavCross">&#x2715;</span></div>
        <img id="canvasImg" draggable="false" />
        <img id="canvasBgImg" draggable="false" />
    </div>

    <!-- BRUSH PANEL -->
    <div class="brushPanel hide" id="brushPanel">
        <div class="toolTitle panelTitle"><span class="cross" id="panelCross">&#x2715;</span></div>

        <label for="brushSize" class="sizeLabel">Size</label>
        <div class="brushSizePreviewCont">
            <label for="brushSize" class="brushSizePreview"></label>
        </div>
        <input type="range" class="brushSize" id="brushSize" value="10" min="1" max="80">

        <label for="brushOpacity">Opacity</label>
        <input type="range" class="brushOpacity" value="1" min="0.1" max="1" step=".1" id="brushOpacity">
    </div>

    <!-- SPRAY PANEL -->
    <div class="sprayPanel hide" id="sprayPanel">
        <div class="toolTitle sprayPaneTitle"><span class="cross" id="sprayPanelCross">&#x2715;</span></div>

        <label for="sprayDensity" class="sizeLabel">Density</label>
        <input type="range" class="sprayDensity" id="sprayDensity" value="50" min="5" max="300">

        <label for="sprayRadius">Radius</label>
        <input type="range" class="sprayRadius" id="sprayRadius" value="20" min="20" max="80" step="1" id="sprayRadius">
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar drag" id="drag-tool" draggable="true" ondragstart="dragStart(event)">
        <div class="toolTitle">&#x2715;</div>

        <div class="tool brush" data-tool-tip="Brush Size & Opacity"></div>
        <div class="tool rainbow" data-tool-tip="Rainbow Brush Tool"></div>
        <div class="tool spray" data-tool-tip="Spray Can Tool"></div>
        <div class="tool bg" data-tool-tip="Change Background Colour"></div>
        <div class="tool eraser" id="eraserTool" data-tool-tip="Eraser Tool"></div>
        <div class="tool nav active" data-tool-tip="Navigator Hide/Show"></div>
        <div class="tool save" data-tool-tip="Save Canvas"></div>
        <div class="tool clear" data-tool-tip="Clear Canvas"></div>
        <div class="tool dl" data-tool-tip="Download As PNG">
            <a id="download"></a>
        </div>

        <input type="color" class="tool colorSelector" value="#e53935" data-tool-tip="Select Colour">
    </div>

    <!-- CUSTOM SAVE BUTTON -->
    <button id="saveDrawing"
        style="position:fixed; bottom:25px; right:25px; padding:12px 20px; font-size:16px; background:#0ea5e9; color:white; border:none; border-radius:6px; cursor:pointer; z-index:9999;">
        💾 SALVA DISEGNO
    </button>

    <!-- ORIGINAL SCRIPT (tutto il tool canvas) -->
    <script src="{{ asset('canvas/script.js') }}"></script>

    <!-- EXTRA SCRIPT PER SALVARE SU BACKEND -->
    <script>
        document.getElementById('saveDrawing').addEventListener('click', async () => {
            const canvas   = document.getElementById('draw');
            const dressId  = document.getElementById('dress_id').value;
            const mode     = document.getElementById('draw_mode').value;
            const imageData = canvas.toDataURL('image/png');

            // 🔁 Scegli endpoint in base alla modalità
            const endpoint = (mode === 'dress' && dressId)
                ? `/admin/draw/dress/${dressId}`   // EDIT: salva direttamente sull'abito
                : `/admin/draw/temp/save`;         // CREATE: salva temporaneo + sessione

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ image: imageData }),
            });

            const res = await response.json();

            if (res.success) {
                alert(res.message || 'Disegno salvato con successo!');

                // In modalità TEMP (nuovo abito), notifica la finestra Filament che ha aperto il canvas
                if (mode === 'temp' && window.opener) {
                    window.opener.dispatchEvent(new CustomEvent('drawingSaved', {
                        detail: { path: res.path }
                    }));
                }
            } else {
                alert('Errore nel salvataggio');
            }
        });
    </script>

</body>
</html>
