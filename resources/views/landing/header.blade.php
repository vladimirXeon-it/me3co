<header>
    <div class="container">
      <div class="row mt-2">
          <div class="col-xl-2 col-5 col-sm-5 col-md-5 col-lg-5">
              <div class="logo w-100 d-inline-block">
               <a href="/">
                  <img src="{{ asset('home') }}/images/logo-image.svg">
                </a>
              </div>
          </div>
          <div class="col-xl-10 col-7 col-sm-7 col-md-7 col-lg-7">
            <div class="menu-side float-end d-xl-none d-block mt-xl-3 mt-1">
                <a href="javascript:void(0)" id="menu-button">
                   <img src="{{ asset('home') }}/images/menu-btn.svg">
                </a>
            </div>
            <div id="sidebar">
                <span class="close-text d-xl-none d-block" 
                    id="close-button">&times;</span>
                  <ul class="float-start d-inline-block mt-2 ms-4 main-navbar" 
                  id="myDiv">
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-black text-13 fw-bold activeB text-md-color" href="#how-it-work">
                              HOW DOES IT WORK
                          </a>
                      </li>
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-black text-13 fw-bold text-md-color" href="#trade-section">
                              TRADES
                          </a>
                      </li>
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-black text-13 fw-bold text-md-color" href="#offer-section">
                              WHAT WE OFFER
                          </a>
                      </li>
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-black text-13 fw-bold text-md-color" href="#price-section">
                              PRICE
                          </a>
                      </li>
                      <li class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-black text-13 fw-bold text-md-color" href="#contact-section">
                              CONTACT
                          </a>
                      </li>
                  </ul>
                  <div class="float-end d-inline-block mt-xl-2 ms-xl-0 
                  ms-4">
                       <a class="text-13 login-btn me-xl-3 mb-xl-0 mb-3 d-inline-block" 
                       href="{{ route('login') }}">
                       <span>
                            <img src="{{ asset('home') }}/images/login.svg">
                       </span>
                          LOGIN    
                        </a>
                        <a class="text-13 text-white signup-btn me-2 d-inline-block" 
                            href="{{ route("register") }}">
                       <span><img src="{{ asset('home') }}/images/profile.svg"></span>
                          SIGN UP FOR FREE   
                        </a>
                        <a class="text-13 text-white flag-image" 
                       href="javascript:void(0)">
                       <img class="d-inline-block" src="{{ asset('home') }}/images/flag.svg">
                        </a>
                  </div>
              </div>
          </div>
        </div>   
    </div>
</header>
    <header class="w-100 bg-sticky d-inline-block">
	<div class="container">
      <div class="row mt-2">
          <div class="col-xl-2 col-5 col-sm-5 col-md-5 col-lg-5">
              <div class="logo w-100 d-inline-block">
               <a href="/">
                  <img src="{{ asset('home') }}/images/logo-image-sticky.svg">
                </a>
              </div>
          </div>
          <div class="col-xl-10 col-7 col-sm-7 col-md-7 col-lg-7">
            <div class="menu-side float-end d-xl-none d-block mt-xl-3 mt-1">
                <a href="javascript:void(0)" id="menu-sticky-button">
                   <img src="{{ asset('home') }}/images/menu-btn.svg">
                </a>
            </div>
            <div id="sidebar">
                <span class="close-text d-xl-none d-block" 
                    id="close-button">&times;</span>
                  <ul class="float-start d-inline-block mt-2 ms-4 sticky-navbar" 
                  id="myDiv">
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-white text-13 fw-bold activeB text-md-color" href="#how-it-work">
                              HOW DOES IT WORK
                          </a>
                      </li>
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-white text-13 fw-bold text-md-color" href="#trade-section">
                              TRADES
                          </a>
                      </li>
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-white text-13 fw-bold text-md-color" href="#offer-section">
                              WHAT WE OFFER
                          </a>
                      </li>
                      <li class="float-start me-xl-4 w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-white text-13 fw-bold text-md-color" href="#price-section">
                              PRICE
                          </a>
                      </li>
                      <li class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3">
                          <a class="text-white text-13 fw-bold text-md-color" href="#contact-section">
                              CONTACT
                          </a>
                      </li>
                  </ul>
                  <div class="float-end d-inline-block mt-xl-2 ms-xl-0 
                  ms-4">
                       <a class="text-13 login-btn me-xl-3 mb-xl-0 mb-3 d-inline-block" 
                       href="{{ route('login') }}">
                       <span>
                            <img src="{{ asset('home') }}/images/login.svg">
                        </span>
                          LOGIN    
                        </a>
                        <a class="text-13 text-white signup-btn me-2 d-inline-block sign-bg" 
                        href="javascript:void(0)">
                       <span><img src="{{ asset('home') }}/images/profile.svg"></span>
                          SIGN UP FOR FREE   
                        </a>
                        <a class="text-13 text-white flag-image" 
                       href="javascript:void(0)">
                       <img class="d-inline-block" src="{{ asset('home') }}/images/flag.svg">
                        </a>
                  </div>
              </div>
          </div>
        </div>   
    </div>
</header>