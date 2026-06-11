<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Hasil Simulasi KB' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }
        .header {
            margin-bottom: 14px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 4px 0 0 0;
            color: #4b5563;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            text-align: left;
        }
        .label {
            width: 48%;
            font-weight: 600;
            background: #fafafa;
        }
        .value {
            width: 52%;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Hasil Simulasi KB' }}</h1>
        <p>Dibuat pada: {{ optional($generatedAt)->format('d-m-Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Field</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
        @forelse(($rows ?? []) as $row)
            <tr>
                <td class="label">{{ $row['label'] ?? '-' }}</td>
                <td class="value">{{ $row['value'] ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2">Tidak ada data simulasi.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
