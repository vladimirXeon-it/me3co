{{-- resources/views/gcrud/test.blade.php --}}
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>GC Test</title>
    @foreach ($css_files as $f) <link rel="stylesheet" href="{{ $f }}"> @endforeach
</head>
<body>
    {!! $output !!}
    @foreach ($js_files as $f) <script src="{{ $f }}"></script> @endforeach
</body>
</html>