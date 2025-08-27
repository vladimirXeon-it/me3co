<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Me3Co.com - Construction Me3Co.com and estimating software :: @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Me3Co.com - Construction Me3Co.com and estimating software" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('fronts') }}/assets/images/favicon.ico">
    <!-- App css -->
    <link href="{{ asset('fronts') }}/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('fronts') }}/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('fronts') }}/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('fronts') }}/assets/css/fSelect.css">
    <style>
        body{
            height: 100vh;
        }
    </style>
</head>

<body class="d-flex flex-column justify-content-center align-items-center">
    <h1 class="fa text-success fa-check-circle"></h1>
    <h1>We sent a Password Reset Link</h1>
    <p class="mt-4">Open your email account and use our link to reset password</p>
    <a href="{{ route('login')}} " class="btn btn-success">Go to Login</a>
</body>

</html>
