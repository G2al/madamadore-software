<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Stampa {{ $filename }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            font-family: sans-serif;
        }
        .message {
            position: absolute;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            color: #333;
            font-size: 14px;
        }
        iframe {
            width: 0;
            height: 0;
            border: 0;
            position: absolute;
            inset: 0;
        }
    </style>
</head>
<body>
    <div class="message">Preparazione stampa…</div>
    <iframe id="pdfFrame" src="{{ $pdfData }}" title="Anteprima PDF"></iframe>

    <script>
        const frame = document.getElementById('pdfFrame');
        frame.onload = () => {
            try {
                frame.contentWindow?.focus();
                frame.contentWindow?.print();
            } catch (e) {
                console.error('Impossibile avviare la stampa automatica:', e);
            }
            setTimeout(() => window.close(), 800);
        };
    </script>
</body>
</html>

