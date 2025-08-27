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
    <link href="{{ asset('fronts') }}/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('fronts') }}/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('fronts') }}/assets/css/app.min.css" rel="stylesheet" type="text/css" />
</head>

<body id="body" class="auth-page vh-100 " style="background: #13c4a1;">
    <!-- Log In page -->
    <div class="w-100">
        <div class="d-flex flex-row">
            {{-- <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 auth-header-box">
                                    <div class="text-center p-3">
                                        <a href="/" class="logo logo-admin">
                                            <img src="{{ asset('fronts') }}/assets/images/logo-sm.png" height="50" alt="logo" class="auth-logo">
                                        </a>
                                        <h4 class="mt-3 mb-1 fw-semibold text-white font-18">Let's Get Started Me3Co.com</h4>   
                                        <p class="text-muted  mb-0">Sign up to continue to Me3Co.com.</p>  
                                    </div>
                                </div>
                                <div class="card-body pt-0">                                    
                                    <form class="my-4" method="post" action="{{ route('do_register') }}"> 
                                        @csrf()           
                                        <div class="form-group mb-2">
                                            <label>Name <small class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="name" placeholder="Name" required>                               
                                        </div><!--end form-group--> 
                                        <div class="form-group mb-2">
                                            <label >Email <small class="text-danger">*</small></label>
                                            <input type="email" class="form-control" name="email" placeholder="Email" required>                               
                                        </div>
                                        <div class="form-group mb-2">
                                            <label>Username <small class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="username" placeholder="Username" required>                               
                                        </div><!--end form-group--> 
            
                                        <div class="form-group mb-2">
                                            <label>Password <small class="text-danger">*</small></label>                                            
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>                            
                                        </div><!--end form-group--> 

                                        <div class="form-group mb-2">
                                            <label>Confirm Password <small class="text-danger">*</small></label>                                            
                                            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" required>                            
                                        </div><!--end form-group--> 

                                        <div class="form-group mb-2">
                                            <label>Company Name <small class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="company" placeholder="Company Name" required>                               
                                        </div><!--end form-group--> 
                                        <div class="form-group mb-2">
                                            <label>Business Type <small class="text-danger">*</small></label>
                                            <select name="business_type" class="form-control" required>
                                                <option value="" selected hidden>Select</option>
                                                <option value="General Contractor">General Contractor</option>
                                                <option value="Subcontractor">Subcontractor</option>
                                                <option value="Supplier">Supplier</option>
                                                <option value="Service Provider">Service Provider</option>   
                                            </select>                            
                                        </div>
                                        <div class="form-group row mt-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch form-switch-success">
                                                    <input class="form-check-input" type="checkbox" id="customSwitchSuccess">
                                                    <label class="form-check-label" for="customSwitchSuccess">By registering you agree to the QTO <a href="#" class="text-primary">Terms of Use</a></label>
                                                </div>
                                            </div><!--end col--> 
                                        </div><!--end form-group--> 
            
                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <div class="d-grid mt-3">
                                                    <button class="btn btn-primary" type="submit">Register <i class="fas fa-sign-in-alt ms-1"></i></button>
                                                </div>
                                            </div><!--end col--> 
                                        </div> <!--end form-group-->                           
                                    </form><!--end form-->
                                    <div class="m-3 text-center text-muted">
                                        <p class="mb-0">Already have an account ? <a href="{{ route('login') }}" class="text-primary ms-2">Log in</a></p>
                                    </div>
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end card-body-->
            </div><!--end col--> --}}
            <div class="card m-0 h-100 bg-wgite col-lg-5 col-12">
                {{-- <div class="card-body p-0 auth-header-box">
                    <div class="text-center p-3">
                        <a href="/" class="logo logo-admin">
                            <img src="{{ asset('fronts') }}/assets/images/logo-sm.png" height="50" alt="logo" class="auth-logo">
                        </a>
                        <h4 class="mt-3 mb-1 fw-semibold text-white font-18">Let's Get Started Me3Co.com</h4>   
                        <p class="text-muted  mb-0">Sign up to continue to Me3Co.com.</p>  
                    </div>
                </div> --}}
                <div class="card-body py-5 px-md-4 vh-100 d-flex flex-column align-items-center">
                    <img src="{{ asset('fronts/assets/images/logo-cl.png') }}" class="col-5" />
                    <form class="my-4 col-md-9" id="resetForm" method="post">
                        @csrf()
                        <div class="form-group mb-2">
                            <label class="form-label" for="email">Email Address <small
                                    class="text-danger">*</small></label>
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="Enter email" required>
                            <small class="text-danger email-message fw-bold" style="display: none">
                                <i class="fa fa-exclamation-circle mr-2"></i>
                                Email address doesn't exits!
                            </small>
                        </div>
                        <!--end form-group-->



                        <div class="form-group mb-0 row">
                            <div class="col-12">
                                <div class="d-grid mt-3">
                                    <button class="btn btn-success" type="submit">Continue <i
                                            class="fas fa-sign-in-alt ms-1"></i></button>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end form-group-->
                    </form>
                    <!--end form-->
                    <div class="m-3 text-center text-muted">
                        <p class="mb-0">Don't have an account ? <a href="{{ route('register') }}"
                                class="text-primary ms-2">Register</a></p>
                    </div>
                </div>
                <!--end card-body-->
            </div>
            <div class="col-lg-7 banner-lg p-md-4 d-lg-flex d-none">
                <img src="{{ asset('fronts') }}/assets/images/logo_mono.png" height="50" alt="logo"
                    class="auth-logo">
                Reset Password!
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

</body>

</html>
