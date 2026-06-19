<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        h2 { text-align: center; color: #006198; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #006198; color: #fff; padding: 5px 4px; text-align: left; font-size: 10px; }
        td { padding: 4px; border-bottom: 1px solid #ddd; font-size: 9px; }
        .r { text-align: right; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <table>
        @if($hasHeaders && count($rows) > 0)
        <thead>
            <tr>@foreach($rows[0] as $key => $val)<th>{{ is_string($key) ? $key : '' }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
        @else
        <thead>
            <tr>@foreach($rows[0] ?? [] as $cell)<th>{{ $cell }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @foreach(array_slice($rows, 1) as $row)
            <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
        @endif
    </table>
</body>
</html>
