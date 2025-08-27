<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Me3Co.com - Construction Me3Co.com and estimating software</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Me3Co.com - Construction Me3Co.com and estimating software" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('fronts') }}/assets/images/favicon.ico">
    <!-- App css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <link href="{{ asset('fronts') }}/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('fronts') }}/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('fronts') }}/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <style>
        .iti--allow-dropdown{
            width: 100%
        }
    </style>
</head>

<body id="body" class="auth-page"
    style="background-image: url('{{ asset('fronts') }}/assets/images/p-1.png'); background-size: cover; background-position: center center;">
    <!-- Log In page -->
    <div class="container-md">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 auth-header-box">
                                    <div class="text-center p-3">
                                        <a href="#" class="logo logo-admin">
                                            <img src="{{ asset('fronts') }}/assets/images/logo-sm.png" height="50"
                                                alt="logo" class="auth-logo">
                                        </a>
                                        <h4 class="mt-3 mb-1 fw-semibold text-white font-18">Let's Get Started
                                            Me3Co.com</h4>
                                        <p class="text-muted  mb-0">Complete your business Profile</p>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <form class="my-4" method="post" action="{{ route('do_add_number') }}">
                                        @csrf()
                                        <div class="form-group mb-2">
                                            <label class="form-label" for="username">Add Phone number to complete your
                                                business Profile <small class="text-danger">*</small></label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                placeholder="Your Phone Number" required>
                                        </div>
                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <div class="d-grid mt-3">
                                                    <button class="btn btn-primary" type="submit">Complete <i
                                                            class="fas fa-check ms-1"></i></button>
                                                </div>
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end form-group-->
                                        <!--end form-group-->
                                    </form>
                                    <!--end form-->
                                    <div class="m-3 text-center text-muted">
                                        <p class="mb-0">Skip for Now ? <a href="{{ route('project') }}"
                                                class="text-primary ms-2">Ok</a></p>
                                    </div>
                                </div>
                                <!--end card-body-->
                            </div>
                            <!--end card-->
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <!--end card-body-->
            </div>
            <!--end col-->
        </div>
        <!--end row-->
    </div>
    <!--end container-->

    <!-- App js -->
    @if (Session::has('message'))
        <div class="toast custom show position-fixed align-items-center text-white bg-primary border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {!! Session::get('message') !!}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    @endif
    <script src="{{ asset('fronts') }}/assets/js/app.js"></script>
    <script>
        const phoneInputField = document.querySelector("#phone");
        const phoneInput = window.intlTelInput(phoneInputField, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });
        
    </script>
</body>

</html>
