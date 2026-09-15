<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        {!! $styles !!}
        .combined-page { page-break-after: always; }
        .combined-page:last-child { page-break-after: auto; }
    </style>
</head>
<body>
    @forelse($pages as $page)
        <div class="combined-page">{!! $page !!}</div>
    @empty
        <p>No enrolled students were found for this section.</p>
    @endforelse
</body>
</html>
