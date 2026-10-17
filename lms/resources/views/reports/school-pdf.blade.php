<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #111; margin: 0; padding: 18px; }
        h1 { margin: 0; font-size: 16px; text-align: center; text-transform: uppercase; }
        .school { text-align: center; font-size: 12px; margin-bottom: 2px; }
        .meta { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .meta td { border: none; padding: 2px 8px 2px 0; vertical-align: top; }
        .label { font-weight: bold; width: 110px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #222; padding: 4px 5px; text-align: left; }
        table.data th { background: #333; color: #fff; }
        table.data tr.alt td { background: #f4f4f4; }
        .note { margin: 8px 0 12px; }
        .footer { margin-top: 16px; border-top: 1px solid #222; padding-top: 6px; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <p class="school">Panorama Montessori School, Inc.</p>
    <h1>{{ $title }}</h1>
    <table class="meta">
        <tr>
            <td class="label">Academic year</td>
            <td>{{ $year->name ?? 'No current academic year' }}</td>
            <td class="label">Section</td>
            <td>{{ $sectionLabel }}</td>
        </tr>
        <tr>
            <td class="label">Search</td>
            <td>{{ $search !== '' ? $search : '—' }}</td>
            <td class="label">Rows</td>
            <td>{{ number_format($rows->count()) }}</td>
        </tr>
    </table>
    @if($note)
        <p class="note">{{ $note }}</p>
    @endif
    @if($summary)
        <p class="note">
            @foreach($summary as $label => $value)
                <strong>{{ $label }}:</strong> {{ is_numeric($value) ? number_format($value) : $value }}@if(! $loop->last) &nbsp;&nbsp; @endif
            @endforeach
        </p>
    @endif
    <table class="data">
        <thead>
            <tr>
                @forelse($columns as $column)
                    <th>{{ $column }}</th>
                @empty
                    <th>Report</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr class="{{ $loop->even ? 'alt' : '' }}">
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(1, count($columns)) }}">No records for this report.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <p class="footer">Generated {{ $generatedAt }}</p>
</body>
</html>
