<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Documento Abito' }}</title>
    <style>
        @page {
            margin: 6mm;
            size: A4 portrait;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #2f2a27;
            margin: 0;
            padding: 0;
            line-height: 1.18;
        }

        .page-break {
            page-break-before: always;
        }

        .document-page {
            border: 1px solid #ddcabc;
            height: 272mm;
            padding: 6mm;
            box-sizing: border-box;
        }

        .page-title {
            text-align: center;
            font-size: 18px;
            letter-spacing: 0.7px;
            margin: 0 0 2mm 0;
            text-transform: uppercase;
        }

        .page-subtitle {
            text-align: center;
            color: #7c6e65;
            font-size: 8.5px;
            margin-bottom: 4mm;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2mm 0;
            padding-bottom: 1.2mm;
            border-bottom: 1px solid #e0d7d1;
        }

        .meta-table,
        .grid-table,
        .measure-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-table td,
        .grid-table td,
        .grid-table th,
        .measure-table td,
        .measure-table th {
            border: 1px solid #c8beb7;
            padding: 4px 5px;
            vertical-align: top;
        }

        .meta-table .label,
        .grid-table th,
        .measure-table th {
            background: #f7f2ee;
            font-weight: bold;
        }

        .meta-table .label {
            width: 34%;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.4px;
        }

        .muted {
            color: #8c7b72;
        }

        .small-text {
            font-size: 8px;
        }

        .tiny-text {
            font-size: 7px;
        }

        .spacer-xs {
            height: 2mm;
        }

        .spacer-sm {
            height: 3mm;
        }

        .spacer-md {
            height: 4mm;
        }

        .image-frame {
            border: 1px solid #ccbfb6;
            background: #fbfaf8;
            text-align: center;
            padding: 3mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .image-frame img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 100%;
            margin: 0 auto;
        }

        .image-frame--portrait img {
            width: auto;
            height: 100%;
            max-width: 100%;
            max-height: none;
        }

        .image-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .image-grid td {
            vertical-align: top;
        }

        .image-grid .image-frame img {
            width: 100%;
        }

        .image-placeholder {
            color: #9e948d;
            font-style: italic;
            padding: 16mm 6mm;
        }

        .sample-image {
            width: 100%;
            height: 18mm;
            border: 1px solid #d7ccc5;
            text-align: center;
            vertical-align: middle;
            background: #fbfaf8;
            overflow: hidden;
        }

        .sample-image img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 100%;
            margin: 0 auto;
        }

        .box {
            border: 1px solid #d8ccc5;
            padding: 2mm;
            background: #fff;
            page-break-inside: avoid;
        }

        .lined-box {
            border: 1px solid #d8ccc5;
            padding: 2mm;
        }

        .writing-lines {
            margin-top: 2mm;
        }

        .writing-lines div {
            border-bottom: 1px solid #ddd4ce;
            height: 6mm;
        }

        .bullet-list {
            margin: 0;
            padding-left: 4mm;
        }

        .bullet-list li {
            margin-bottom: 1mm;
        }

        .detail-card {
            border: 1px solid #d8ccc5;
            padding: 2mm;
            background: #fff;
            page-break-inside: avoid;
        }

        .detail-card .sample-image {
            height: 14mm;
            margin-bottom: 1.5mm;
        }

        .measure-table {
            font-size: 7.5px;
        }

        .measure-table td:first-child,
        .measure-table th:first-child {
            width: 72%;
        }

        .measure-table td:last-child,
        .measure-table th:last-child {
            width: 28%;
        }

        .paragraph-list p {
            margin: 0 0 3mm 0;
        }

        .footer-note {
            text-align: center;
            color: #8d8077;
            font-size: 8px;
            margin-top: 4mm;
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
</body>
</html>
