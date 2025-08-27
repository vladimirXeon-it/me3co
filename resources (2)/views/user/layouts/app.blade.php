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
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    {{-- <link href="{{ asset('fronts') }}/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> --}}
    {{-- <link href="{{ asset('fronts') }}/assets/css/icons.min.css" rel="stylesheet" type="text/css" /> --}}
    <link href="{{ asset('fronts') }}/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('fronts') }}/assets/css/fSelect.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="{{ asset('projects') }}/css/table.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('projects') }}/css/table-responsive.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
</head>

<body data-layout="horizontal" class="dark-topbar">
    @include('components.user.topbar')

    <div class="page-wrapper">
        <!-- Page Content-->
        @yield('content')
        <!-- end page content -->
        @include("components.user.frame")
    </div>

    @include('components.user.offcanvas')
    @include('components.user.footer')
    <!-- end Footer -->
    <!--end footer-->
    @include('components.user.toast')
    
    <!-- Javascript  -->
    <script src="{{ asset('fronts') }}/assets/js/frames.js" ></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    {{-- <script src="{{ asset('fronts') }}/assets/plugins/chartjs/chart.js"></script> --}}
    {{-- <script src="{{ asset('fronts') }}/assets/plugins/lightpicker/litepicker.js"></script> --}}
    {{-- <script src="{{ asset('fronts') }}/assets/plugins/apexcharts/apexcharts.min.js"></script> --}}
    {{-- <script src="{{ asset('fronts') }}/assets/js/fSelect.js"></script> --}}
    {{-- <script src="{{ asset('fronts') }}/assets/js/app.js"></script> --}}
    <script src="{{ asset('fronts') }}/assets/js/datalist.js"></script>
    <script type="text/javascript" src="{{ asset('projects') }}/js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    @yield('script')
</body>

</html>
