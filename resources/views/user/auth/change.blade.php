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
</head>

<body id="body" class="auth-page vh-100 " style="background: #13c4a1;">
    <!-- Log In page -->
    <div class="w-100 h-min-100 h-auto">
        <div class="d-flex h-min-100 h-auto flex-row">
            
            <div class="card m-0 h-min-100 bg-wgite col-lg-5 col-12">
                
                <div style="min-height: 100vh"
                    class="card-body py-5 px-md-4  h-min-100 d-flex flex-column align-items-center">
                    <img src="{{ asset('fronts/assets/images/logo-cl.png') }}" class="col-5" />
                    <input type="checkbox" class="d-none continue" id="continue" />
                    <form class="my-4 w-75 register-form h-min-100" method="post" action="{{ route('password.changePassword', ['token' => $token]) }}">
                        @csrf()
                        
                        
                        
                        <div class="form-group mb-2">
                            <label>Password <small class="text-danger">*</small></label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Password" required>
                            <small class="text-danger invalid-text fw-bold">
                                <i class="fa fa-exclamation-circle mr-2"></i>
                                Please enter Password
                            </small>
                        </div>
                        <!--end form-group-->

                        <div class="form-group mb-2">
                            <label>Confirm Password <small class="text-danger">*</small></label>
                            <input type="password" class="form-control" id="confirmPassword"
                                placeholder="Confirm Password" required>
                        </div>
                        <small class="text-danger fw-bold" id="passwordWarning" style="display:none;">
                            <i class="fa fa-exclamation-circle mr-2"></i>
                            Passwords don't match
                        </small>
                        <!--end form-group-->

                        
                        <!--end form-group-->
                        

                        <!--end form-group-->

                        
                        <div class="form-group mb-0 row">
                            <div class="col-12">
                                <div class="d-grid mt-3">
                                    <button class="btn btn-success" type="submit">Continue</button>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end form-group-->
                    </form>
                    <!--end form-->
                    
                </div>
                <!--end card-body-->
            </div>
            
        </div>
        <!--end row-->
    </div>
    <!--end container-->
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
    <!-- App js -->
    <script src="{{ asset('fronts') }}/assets/js/app.js"></script>
    <script>
        const phoneInputField = document.querySelector("#phone");
        const phoneInput = window.intlTelInput(phoneInputField, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });
    </script>
</body>

</html>
