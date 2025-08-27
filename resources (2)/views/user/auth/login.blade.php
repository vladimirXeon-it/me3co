<!DOCTYPE html>
<html lang="en">
<head>
	<title>Me3Co.com - Construction Me3Co.com and estimating software</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="{{ asset('sign-register') }}/css/login-register.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('sign-register') }}/css/login-register-responsive.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
</head>
<body>
   <div class="container-fluid">
   	    <div class="row">
   	    	<div class="col-xl-6 col-12 col-md-6 col-lg-6 d-xl-block d-none p-0 d-sm-block d-md-block d-lg-block">
   	    		<div class="bg-signIn w-100 d-inline-block">
   	    			<div class="w-100 d-inline-block logo mt-5">
   	    				<img class="w-35 d-inline-block mt-xl-5" 
   	    				src="{{ asset('sign-register') }}/images/logo-image.svg">
   	    				<p class="text-white fs-4 mt-3 w-55 fw-bold w-md-100 w-ipad-100">
   	    					Accelerate Bidding. Secure More Wins. Construct with Intelligence
   	    				</p>
   	    				<span class="text-white text-14">
   	    					From project assessment, takeoff, and estimation to construction and project completion, contractors utilize MSNSOFT's cloud-based software to streamline their operations and optimize profitability.
   	    				</span>
   	    			</div>
   	    		</div>
   	    	</div>
   	    	<div class="col-xl-6 col-12 col-md-6 col-lg-6">
   	    		<div class="w-100 d-inline-block mt-3">
   	    			<a class="text-15 back-text w-50 d-inline-block w-xs-25 w-md-35" href="{{ url('/') }}">
   	    				<i class="fa fa-angle-left" aria-hidden="true">          
                        </i>
   	    				Back
   	    			</a>
   	    			<span class="text-14">
   	    				Don’t have an account?
   	    				<a class="text-15 back-text" 
   	    				href="{{ route('register') }}">
   	    				  Sign up!
                        </a>  
   	    			</span>
   	    		</div>
   	    		<div class="w-70 m-auto d-block mt-xl-5 mt-3 w-xs-100 w-md-100 w-ipad-100">
   	    			<div class="w-100 d-inline-block">
   	    				<p class="fw-bold fs-3 text-black mb-0">
   	    				Welcome Back
   	    			    </p>
	   	    			<span class="text-14 text-black">
	   	    				Login into your account
	   	    			</span>
   	    			</div>
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
   	    			<form method="post" action="{{ route('do_login') }}">
                        @csrf()
   	    				<div class="w-100 d-inline-block form-group mt-3">
   	    					<input class="w-100 d-inline-block pt-2 pb-2 pe-3 ps-3" type="text" placeholder="User Name" name="username" id="username">
   	    				</div>
   	    				<div class="w-100 d-inline-block form-group mt-3 position-relative">
   	    					<input id="userpassword" class="w-100 d-inline-block pt-2 pb-2 pe-3 ps-3" type="password" 
   	    					placeholder="Password" name="password" id="userpassword">
   	    					<a class="position-absolute eye-icon" 
   	    					href="javascript:void(0)" onclick="togglePasswordVisibility()">
   	    						<i class="fa fa-eye-slash" aria-hidden="true" id="eyeSlash"></i>
   	    						<i class="fa fa-eye" aria-hidden="true" id="eye" style="display: none;"></i>
   	    					</a>
   	    				</div>
   	    				<div class="w-100 d-inline-block form-check form-switch mt-3">
   	    					<input class="form-check-input" name="remember_me" type="checkbox" role="switch" id="SwitchCheckDefault">
   	    					<span class="text-black text-14">Remember me
   	    					</span>
   	    					<a class="back-text text-14 float-end" 
   	    					href="{{ route('password.reset') }}">
   	    						Recover Password
   	    					</a>
   	    				</div>
   	    				<div class="w-100 d-inline-block login-btn mt-3 text-center">
   	    					<button class="text-white text-14 p-2 w-100 d-inline-block" type="submit">
   	    						LOGIN
   	    					</button>
   	    					{{-- <p class="text-black text-14 position-relative mt-3">Or continue with</p> --}}
   	    				</div>
   	    				{{-- <div class="w-100 d-inline-block google-btn 
                        mb-4">
   	    					<a class="d-inline-block me-2 w-xs-100 text-center" 
   	    					href="javascript:void(0)">
   	    						<img src="{{ asset('sign-register') }}/images/google-icon-image.svg">
   	    						<span class="text-black text-14">Login with Google</span>
   	    					</a>
   	    					<a class="d-inline-block w-xs-100 
                            text-center mt-xl-0 mt-3" 
                            href="javascript:void(0)">
   	    						<img src="{{ asset('sign-register') }}/images/microsoft-icon-image.svg">
   	    						<span class="text-black text-14">Login with Microsoft</span>
   	    					</a>
   	    				</div> --}}
   	    			</form>
   	    		</div>
   	    	</div>
   	    </div>
   </div>

<script type="text/javascript" src="{{ asset('sign-register') }}/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
   
function togglePasswordVisibility() {
        var passwordInput = document.getElementById("userpassword");
        var eye = document.getElementById("eye");
        var eyeSlash = document.getElementById("eyeSlash");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eye.style.display = "inline-block";
            eyeSlash.style.display = "none";
        } else {
            passwordInput.type = "password";
            eye.style.display = "none";
            eyeSlash.style.display = "inline-block";
        }
    }

</script>
</body>
</html>
