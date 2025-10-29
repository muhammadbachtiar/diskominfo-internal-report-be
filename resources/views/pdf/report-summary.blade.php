<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; }
    </style>
    <title>Ringkasan Laporan</title>
    <meta name="pdf-hash" content="{{ hash('sha256', $report->number.$report->id) }}" />
    <meta name="coords" content="{{ $report->lat }},{{ $report->lng }}, acc: {{ $report->accuracy }}m" />
    <meta name="report-number" content="{{ $report->number }}" />
    <meta name="qr" content="{{ $qr }}" />
    </head>
<body>
    <h1>Laporan: {{ $report->number }}</h1>
    <p>Judul: {{ $report->title }}</p>
    <p>Kategori: {{ $report->category }}</p>
    <p>Waktu Kejadian: {{ $report->event_at }}</p>
    <p>Lokasi: {{ $report->location }} ({{ $report->lat }}, {{ $report->lng }}) Akurasi: {{ $report->accuracy }} m</p>
    <img src="{{ $qr }}" alt="QR" width="120" />
    <h2>Bukti (Foto)</h2>
    <table>
        <thead><tr><th>Nama</th><th>Checksum</th><th>Koordinat</th></tr></thead>
        <tbody>
            @foreach($evidences as $ev)
                <tr>
                    <td>{{ $ev->original_name }}</td>
                    <td>{{ $ev->checksum }}</td>
                    <td>{{ $ev->lat }}, {{ $ev->lng }} (±{{ $ev->accuracy }}m)</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <footer>
        <p>Hash Dokumen (runtime): {{ hash('sha256', $report->number.$report->id) }}</p>
    </footer>
</body>
</html>

