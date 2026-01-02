<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Stampa {{ $filename }}</title>
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; background: #f5f5f5; color: #222; }
        .wrap { max-width: 620px; margin: 24px auto; padding: 16px 18px; background: #fff; border-radius: 8px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        h1 { margin: 0 0 8px; font-size: 18px; }
        p { margin: 0 0 14px; line-height: 1.4; }
        button { padding: 10px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .primary { background: #2563eb; color: #fff; }
        .secondary { background: #e5e7eb; color: #111; }
        iframe { width: 0; height: 0; border: 0; position: absolute; inset: 0; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Preparazione stampa</h1>
        <p>Se il popup stampa non compare, usa il pulsante qui sotto.</p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="primary" id="printBtn">Stampa ora</button>
            <button class="secondary" id="closeBtn">Chiudi</button>
        </div>
    </div>

    <iframe id="pdfFrame" src="{{ $pdfData }}" title="Anteprima PDF"></iframe>

    <script>
        const frame = document.getElementById('pdfFrame');
        const printBtn = document.getElementById('printBtn');
        const closeBtn = document.getElementById('closeBtn');

        function triggerPrint() {
            try {
                frame.contentWindow?.focus();
                frame.contentWindow?.print();
            } catch (e) {
                console.error('Impossibile avviare la stampa automatica:', e);
            }
        }

        frame.onload = triggerPrint;
        printBtn.addEventListener('click', triggerPrint);
        closeBtn.addEventListener('click', () => window.close());
    </script>
</body>
</html>
