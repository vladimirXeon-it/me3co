<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" href="{{ asset('qto-app') }}/favicon.ico" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="info" project="{{ $id }}" token="{{ auth()->user()->remember_token }}"/>
    <link rel="stylesheet" href="{{ asset('qto-app') }}/fontawsome/css/all.css">
    <link rel="stylesheet" href="{{ asset('qto-app') }}/css/styles.css">
    <link rel="apple-touch-icon" href="{{ asset('qto-app') }}/logo192.png" />
    <link rel="manifest" href="{{ asset('qto-app') }}/manifest.json" />
    <title>{{ env('APP_NAME') }}</title>
    <script defer="defer" src="{{ asset('qto-app') }}/static/js/main.2487c73c.js"></script>
    <link href="{{ asset('qto-app') }}/static/css/main.2da8fe12.css" rel="stylesheet">
</head>

<body>
    <div id="root"></div>
</body>

</html>