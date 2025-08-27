@extends('landing.layout')

@section('content')
    <!-- Start Book Main Banner -->
    <div class="w-100 d-inline-block banner-image">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="w-100 d-inline-block mt-xl-5 mt-3">
                        <h1 class="text-white fw-bold w-50 w-xs-100 w-sm-100 w-md-100 text-xs-20 w-lg-100">
                            Takeoff & <span class="text-color">Estimating
                            </span> Software For The Construction Industry
                        </h1>
                    </div>
                </div>
                <div class="account-section w-100 d-inline-block">
                    <p class="text-white text-14 fw-bold">Quote Jobs In Minutes</p>
                    <ul class="d-inline-block w-35 w-xs-100 w-sm-100 w-md-100 w-lg-100">
                        <li class="w-100 d-inline-block mb-2">
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                Easy To Use
                            </span>
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                Cloud Base
                            </span>
                        </li>
                        <li class="w-100 d-inline-block mb-2">
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                No training needed.
                            </span>
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                Work On All Devices
                            </span>
                        </li>
                        <li class="w-100 d-inline-block mb-2">
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                Free Lifetime Account
                            </span>
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                Save Time And Money
                            </span>
                        </li>
                        <li class="w-100 d-inline-block mb-2">
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                Digital Measure
                            </span>
                            <span class="text-white text-13 float-start 
                        w-45 text-i-11">
                                <img class="d-inline-block" src="{{ asset('home') }}/images/tick-icon.svg">
                                Plan Viewer
                            </span>
                        </li>
                        <li class="w-100 d-inline-block mt-3">
                            <a class="text-white text-14 fw-bold account-btn box-radius" href="{{ route('register') }}">
                                Sign Up For A Free Account
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="w-100 d-inline-block bg-about-us">
        <div class="container">
            <div id="all-about-content" class="w-100 d-inline-block">
                <div class="row">
                    <div class="col-xl-6 col-12 col-sm-6 col-md-6 col-lg-6 mb-2">
                        <div class="w-100 d-inline-block mt-xl-4 mt-2">
                            <h2 class="text-black text-20 fw-bold">About Us</h2>
                            <p class="text-15 mt-3 about-text w-xs-100 w-sm-100 w-md-100 w-lg-100 mt-xl-2">
                                Welcome to me3co.com, where innovation meets affordability in the construction technology
                                landscape. We are a dedicated team of construction professionals and technology enthusiasts
                                who
                                recognized a critical need for more accessible and cost-effective technology solutions for
                                the
                                everyday contractor.
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12 col-sm-6 col-md-6 col-lg-6 mb-2">
                        <div class="w-100 d-inline-block mt-xl-4 mt-2">
                            <h2 class="text-black text-20 fw-bold">Our Vision: Bridging the Gap in Construction Technology
                            </h2>
                            <p class="text-15 mt-3 about-text w-xs-100 w-sm-100 w-md-100 w-lg-100 mt-xl-2">
                                In the vast realm of construction software, we observed a gap – a void between tools that
                                were
                                somewhat ineffective and those that came with an exorbitant price tag. This realization
                                fueled
                                our passion to make a difference, leading to the inception of me3co.com.
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12 col-sm-6 col-md-6 col-lg-6 mb-2">
                        <div class="w-100 d-inline-block mt-2">
                            <h2 class="text-black text-20 fw-bold">Affordable Technology for Every Contractor</h2>
                            <p class="text-15 mt-3 about-text w-xs-100 w-sm-100 w-md-100 w-lg-100 mt-xl-2">
                                At me3co.com, we understand the challenges faced by contractors in adopting technology.
                                That's
                                why we are committed to providing a solution that bridges the affordability gap. Our
                                platform is
                                tailored to meet the needs of the average contractor, offering functionality without
                                compromise
                                and without breaking the bank.
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12 col-sm-6 col-md-6 col-lg-6 mb-2">
                        <div class="w-100 d-inline-block mt-2">
                            <h2 class="text-black text-20 fw-bold">The me3co.com Difference: Effective and Accessible</h2>
                            <p class="text-15 mt-3 about-text w-xs-100 w-sm-100 w-md-100 w-lg-100 mt-xl-2">
                                What sets us apart is our commitment to delivering a solution that is both effective and
                                accessible. We believe that advanced technology should be within reach for every contractor,
                                empowering them to streamline their processes, enhance efficiency, and elevate their
                                projects.
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12 col-sm-6 col-md-6 col-lg-6 mb-2">
                        <div class="w-100 d-inline-block mt-2">
                            <h2 class="text-black text-20 fw-bold">Free Plan for All</h2>
                            <p class="text-15 mt-3 about-text w-xs-100 w-sm-100 w-md-100 w-lg-100 mt-xl-2">
                                To ensure that our commitment to accessibility is unwavering, we proudly offer a free plan
                                for
                                everyone. We believe that every contractor, regardless of size or budget, should have access
                                to
                                powerful tools that can make a real difference in their day-to-day operations.
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12 col-sm-6 col-md-6 col-lg-6 mb-2">
                        <div class="w-100 d-inline-block mt-2">
                            <h2 class="text-black text-20 fw-bold">Join Us on the Journey</h2>
                            <p class="text-15 mt-3 about-text w-xs-100 w-sm-100 w-md-100 w-lg-100 mt-xl-2">
                                Whether you're a seasoned professional or a newcomer to the industry, me3co.com is here to
                                support you on your technological journey. We invite you to explore our platform, experience
                                the
                                difference, and witness firsthand how we are reshaping the landscape of construction
                                technology.
                                Your success is our mission, and we're here to empower you every step of the way.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="w-100 d-inline-block text-center read-more-text">
                        <a id="show-more" class="text-14 text-color fw-bold" href="javascript:void(0)">
                            Read More
                            <i aria-hidden="true" class="fa fa-angle-double-right text-17 fw-bold"></i>
                        </a>
                        <a id="show-less" class="text-14 text-color fw-bold" href="javascript:void(0)">
                            Read Less
                            <i aria-hidden="true" class="fa fa-angle-double-left text-17 fw-bold"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="how-it-work" class="w-100 d-inline-block mt-4 mt-xl-0">
        <div class="w-50 d-inline-block w-xs-100 w-sm-100 w-md-100 w-lg-100">
            <div class="w-100 d-inline-block float-start project-image position-relative">
                <p class="work-text fs-1 fw-bold position-absolute position-inherit text-center">HOW DOES IT WORK?</p>
                <img class="w-100 d-xl-block d-none" src="{{ asset('home') }}/images/project-image.svg">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div
                                class="d-inline-block position-absolute section-top position-inherit w-xs-100 w-sm-100 w-md-100 w-lg-100">
                                <div
                                    class="w-100 d-inline-block create-project border-bottom pb-1 position-relative cursor">
                                    <span class="text-color text-14 w-100 d-inline-block">01</span>
                                    <span
                                        class="text-18 fw-bold w-85 d-inline-block create-project-btn text-xs-15 activeA">Create
                                        a project
                                    </span>
                                    <span>
                                        <img class="creat-project-icon"
                                            src="{{ asset('home') }}/images/creat-project-icon.svg">
                                    </span>
                                    <p class="mb-0 text-black text-13 hidden">
                                        This step involves the initial setup of a project. Name, bid date and time.
                                    </p>
                                </div>
                                <div
                                    class="w-100 d-inline-block digital-blueprints border-bottom pb-1 position-relative mt-2 cursor">
                                    <span class="text-color text-14 w-100 d-inline-block">02</span>
                                    <span class="text-18 fw-bold w-85 d-inline-block create-project-btn text-xs-15">Select
                                        your digital blueprints and upload them</span>
                                    <span>
                                        <img class="creat-project-icon"
                                            src="{{ asset('home') }}/images/digital-blueprints-icon.svg">
                                    </span>
                                    <p class="mb-0 text-black text-13 hidden">
                                        Choose your digital blueprints and upload them.
                                    </p>
                                </div>
                                <div
                                    class="w-100 d-inline-block scale-project border-bottom pb-1 position-relative mt-2 
                        cursor">
                                    <span class="text-color text-14 w-100 d-inline-block">03</span>
                                    <span class="text-18 fw-bold w-85 d-inline-block create-project-btn text-xs-15">Set
                                        your
                                        scale or Calibrate the scale</span>
                                    <span>
                                        <img class="creat-project-icon" src="{{ asset('home') }}/images/scale-icon.svg">
                                    </span>
                                    <p class="mb-0 text-black text-13 hidden">
                                        To ensure accurate measurements and dimensions on the digital blueprints, it's
                                        essential to set the scale or calibrate the scale. Always check the scale against
                                        any known dimension
                                    </p>
                                </div>
                                <div
                                    class="w-100 d-inline-block create-measuring-project border-bottom pb-1 position-relative mt-2 cursor">
                                    <span class="text-color text-14 w-100 d-inline-block">04</span>
                                    <span class="text-18 fw-bold w-85 d-inline-block create-project-btn text-xs-15">Start
                                        Measuring</span>
                                    <span>
                                        <img class="creat-project-icon"
                                            src="{{ asset('home') }}/images/measuring-icon.svg">
                                    </span>
                                    <p class="mb-0 text-black text-13 hidden">
                                        Once the scale is set, you can begin your takeoff
                                    </p>
                                </div>
                                <div
                                    class="w-100 d-inline-block create-report-project border-bottom pb-1 position-relative mt-2 cursor">
                                    <span class="text-color text-14 w-100 d-inline-block">05</span>
                                    <span class="text-18 fw-bold w-85 d-inline-block create-project-btn text-xs-15">View
                                        the measuring report</span>
                                    <span>
                                        <img class="creat-project-icon"
                                            src="{{ asset('home') }}/images/measuring-report-icon.svg">
                                    </span>
                                    <p class="mb-0 text-black text-13 hidden">
                                        After measuring, you can review a measuring report that summarizes the collected
                                        data. If discrepancies or errors are identified, adjustments can be made to ensure
                                        the accuracy of the project's measurements.
                                    </p>
                                </div>
                                {{-- <div class="w-100 d-inline-block create-formulas-project border-bottom pb-1 position-relative mt-2 cursor">
                            <span class="text-color text-14 w-100 d-inline-block">06</span>
                            <span class="text-18 fw-bold w-85 d-inline-block create-project-btn text-xs-15">Entering Or Changing Formulas</span>
                            <span>
                                <img class="creat-project-icon" src="{{ asset('home') }}/images/formulas-icon.svg">
                            </span>
                            <p class="mb-0 text-black text-13 hidden">
                                This step involves the initial setup of a project. Name, bid date, and time
                            </p>
                        </div> --}}
                                <div
                                    class="w-100 d-inline-block create-estimated-project border-bottom pb-1 position-relative mt-2 cursor">
                                    <span class="text-color text-14 w-100 d-inline-block">06</span>
                                    <span
                                        class="text-18 fw-bold w-85
                             d-inline-block create-project-btn text-xs-15">View
                                        the total Estimated price for the project</span>
                                    <span>
                                        <img class="creat-project-icon"
                                            src="{{ asset('home') }}/images/estimated-icon.svg">
                                    </span>
                                    <p class="mb-0 text-black text-13 hidden">
                                        Finally, you can view the total sale price for the project. This involves all the
                                        costs associated with materials, labor, and any other expenses Including overhead
                                        and profit based on the measurements and formulas applied. The total sale price
                                        represents the estimated or actual cost of the project.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="w-50 d-inline-block float-end project-image-2 w-xs-100 w-sm-100 w-md-100 w-lg-100">
            <div class="w-100 d-inline-block">
                <img src="{{ asset('home') }}/images/project-image-2.svg" class="project-img w-100">
                <img src="{{ asset('home') }}/images/digital-blueprints-image.svg" class="blueprint-img hidden w-100">
                <img src="{{ asset('home') }}/images/scale-image.svg" class="scale-img hidden w-100">
                <img src="{{ asset('home') }}/images/measuring-image.svg" class="measuring-img 
        hidden w-100">
                <img src="{{ asset('home') }}/images/measuring-report-image.svg"
                    class="measuring-report-img hidden w-100">
                <img src="{{ asset('home') }}/images/changing-formulas.svg" class="changing-formulas-img hidden w-100">
                <img src="{{ asset('home') }}/images/estimated-price.svg" class="estimated-price-img hidden w-100">
            </div>
        </div>
    </div>
    <div id="trade-section" class="w-100 d-inline-block trades-bg-image 
pb-4">
        <div class="container">
            <div class="row mt-xl-5 mt-3">
                <div class="col-12">
                    <div class="w-35 d-inline-block float-start w-xs-100 w-sm-100 w-md-100 w-lg-100">
                        <span class="text-white fw-bold text-14">OUR TRADES
                        </span>
                        <p class="text-white text-14 mt-3">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce sed fringilla ante. Lorem ipsum
                            dolor sit amet, consectetur adipiscing elit.
                        </p>
                    </div>
                    <div
                        class="w-20 d-inline-block contracter-box text-center float-start mb-3 w-xs-100 w-sm-50 w-md-33 w-lg-25">
                        <a class="text-black text-14 bg-white d-inline-block fw-bold" href="javascript:void(0)">
                            <span class="d-block mt-3">
                                <img src="{{ asset('home') }}/images/contracter-icon.svg">
                            </span>
                            <span class="mt-3 d-inline-block">General Contractor</span>
                        </a>
                    </div>
                    <div
                        class="w-20 d-inline-block contracter-box text-center float-start mb-3 w-xs-100 w-sm-50 w-md-33 w-lg-25">
                        <a class="text-black text-14 bg-white d-inline-block fw-bold" href="javascript:void(0)">
                            <span class="d-block mt-3">
                                <img src="{{ asset('home') }}/images/masonry-icon.svg">
                            </span>
                            <span class="mt-3 d-inline-block">Masonry</span>
                        </a>
                    </div>
                    <div
                        class="w-20 d-inline-block contracter-box text-center float-start mb-3 w-xs-100 w-sm-50 w-md-33 w-lg-25">
                        <a class="text-black text-14 bg-white d-inline-block fw-bold" href="javascript:void(0)">
                            <span class="d-block mt-3">
                                <img src="{{ asset('home') }}/images/plumbing-icon.svg">
                            </span>
                            <span class="mt-3 d-inline-block">Plumbing</span>
                        </a>
                    </div>
                    <div
                        class="w-20 d-inline-block contracter-box text-center float-start mb-3 carpentry-box w-xs-100 w-sm-50 w-md-33 w-lg-25">
                        <a class="text-black text-14 bg-white d-inline-block fw-bold" href="javascript:void(0)">
                            <span class="d-block mt-3">
                                <img src="{{ asset('home') }}/images/carpentry-icon.svg">
                            </span>
                            <span class="mt-3 d-inline-block">Carpentry
                            </span>
                        </a>
                    </div>
                    <div
                        class="w-20 d-inline-block contracter-box text-center float-start mb-3 w-xs-100 w-sm-50 w-md-33 w-lg-25">
                        <a class="text-black text-14 bg-white d-inline-block fw-bold" href="javascript:void(0)">
                            <span class="d-block mt-3">
                                <img src="{{ asset('home') }}/images/curb-icon.svg">
                            </span>
                            <span class="mt-3 d-inline-block">Curb & gutter
                            </span>
                        </a>
                    </div>
                    <div
                        class="w-20 d-inline-block contracter-box text-center float-start mb-3 w-xs-100 w-sm-50 w-md-33 w-lg-25">
                        <a class="text-black text-14 bg-white d-inline-block fw-bold" href="javascript:void(0)">
                            <span class="d-block mt-3">
                                <img src="{{ asset('home') }}/images/roofing-icon.svg">
                            </span>
                            <span class="mt-3 d-inline-block">Roofing</span>
                        </a>
                    </div>
                    <div
                        class="w-20 d-inline-block contracter-box text-center float-start mb-3 w-xs-100 w-sm-50 w-md-33 w-lg-25">
                        <a class="text-black text-14 bg-white d-inline-block fw-bold" href="javascript:void(0)">
                            <span class="d-block mt-3">
                                <img src="{{ asset('home') }}/images/electrical-icon.svg">
                            </span>
                            <span class="mt-3 d-inline-block">Electrical
                            </span>
                        </a>
                    </div>
                    <div class="w-100 d-inline-block text-center mt-3">
                        <a class="text-white text-13 btn-box p-2 d-inline-block see-more-btn" href="javascript:void(0)">
                            Click to see more
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="offer-section" class="w-100 d-inline-block bg-bussiness">
        <div class="container">
            <div class="row mt-xl-5 mt-4">
                <div class="col-xl-4 col-12 col-sm-6 col-md-5 col-lg-5 pe-xl-0">
                    <div class="w-100 d-inline-block bussiness-under-image">
                        <img class="w-100" src="{{ asset('home') }}/images/under-bussiness-image.svg">
                    </div>
                </div>
                <div class="col-xl-7 col-12 col-sm-6 col-md-7 col-lg-7 ps-xl-0">
                    <div
                        class="bussiness-content d-inline-block p-3 
                mt-xl-5 w-xs-100 w-sm-100 w-md-100 w-lg-100">
                        <p class="text-white fw-bold fs-1 mb-1 text-sm-20">
                            What do we offer?</p>
                        <p class="text-white text-14">
                            At Me3co, our cutting-edge platform provides tailored solutions to meet the unique needs of each
                            contractor. We pride ourselves on offering cost-conscious entrepreneurs the opportunity to
                            access top-notch technology without breaking the bank. Our platform encompasses the following.
                        </p>
                        <ul class="w-100 d-inline-block">
                            <li class="w-100 d-inline-block mb-2">
                                <span
                                    class="text-white d-inline-block w-45 text-15 w-xs-100 mb-xl-0 mb-2 w-sm-100 w-md-100">
                                    <img src="{{ asset('home') }}/images/tick-vector.svg" style="height:10px;">
                                    Online Estimating & Takeoff
                                </span>
                                <span class="text-white text-15 d-inline-block w-45 w-xs-100 w-sm-100 w-md-100">
                                    <img src="{{ asset('home') }}/images/tick-vector.svg" style="height:10px;">
                                    Comprehensive Reporting
                                </span>
                            </li>
                            <li class="w-100 d-inline-block mb-2">
                                <span
                                    class="text-white d-inline-block w-45 text-15 w-xs-100 mb-xl-0 mb-2 w-sm-100 w-md-100">
                                    <img src="{{ asset('home') }}/images/tick-vector.svg" style="height:10px;">
                                    Complimentary Lifetime Account
                                </span>
                                <span class="text-white text-15 d-inline-block w-45 w-xs-100 w-sm-100 w-md-100">
                                    <img src="{{ asset('home') }}/images/tick-vector.svg" style="height:10px;">
                                    Personalized Customer Support
                                </span>
                            </li>
                            <li class="w-100 d-inline-block">
                                <span
                                    class="text-white d-inline-block w-45 text-15 w-xs-100 mb-xl-0 mb-2 w-sm-100 w-md-100">
                                    <img src="{{ asset('home') }}/images/tick-vector.svg" style="height:10px;">
                                    Training Resources
                                </span>
                                <span
                                    class="text-white d-inline-block w-45 text-15 w-xs-100 mb-xl-0 mb-2 w-sm-100 w-md-100">
                                    <img src="{{ asset('home') }}/images/tick-vector.svg" style="height:10px;">
                                    Real-time Collaboration Features
                                </span>
                            </li>
                            <li class="w-100 d-inline-block mt-3">
                                <span class="text-white d-inline-block text-15 mb-xl-0 mb-2 w-100">
                                    We're not just a service,
                                    we're your partner in navigating the world of project estimation efficiently and
                                    affordably
                                </span>
                            </li>
                            <li class="w-100 d-inline-block mt-3">
                                <a class="text-13 text-white signup-btn me-2" href="{{ route('register') }}">
                                    <span><img src="{{ asset('home') }}/images/profile.svg">
                                    </span>
                                    SIGN UP FOR FREE
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="price-section" class="w-100 d-inline-block price-bg">
        <div class="container">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="w-100 d-inline-block text-center">
                        <h2 class="fw-bold text-black fs-3">
                            Plans And Price
                        </h2>
                    </div>
                </div>
            </div>
            <div class="w-75 m-auto d-block w-xs-100 w-sm-100 w-md-100 w-lg-100">
                <div class="row mb-4">
                    @php
                        $plans = get_subscriptions();
                    @endphp
                    @foreach ($plans as $plan)
                        <div class="col-xl-4 col-12 col-sm-6 col-md-4 col-lg-4 mb-xl-0 mb-2">
                            <div class="w-100 d-inline-block main-price-box p-1 border position-relative bg-white">
                                <div class="w-100 d-inline-block price-box border
                        p-2 bg-white">
                                    <span class="text-black text-16 fw-bold">
                                        {{ $plan->name }}
                                    </span>
                                    @if ($plan->name === 'BUSINESS')
                                        <a class="text-white text-13 popular-btn float-end" href="javascript:void(0)">
                                            Popular
                                        </a>
                                    @endif
                                    <p class="mt-3">
                                        <span class="dolar-icon text-black float-start mt-2 fw-bold">$</span>
                                        <span class="text-color fs-1">{{ $plan->price }}</span>
                                        <span class="m-icon icon-text">
                                            @if ($plan->type == 0)
                                                /Month
                                            @elseif($plan->type == 1)
                                                /Year
                                            @endif
                                        </span>
                                    </p>
                                    <p class="icon-text text-13 w-100 d-inline-block mb-0">
                                        <span class="dolar-icon text-color float-start text-15 fw-bold">Project
                                            Allowed</span>
                                        <span
                                            class="text-black text-20 float-end me-xl-4">{{ $plan->project_allowed }}</span>
                                    </p>
                                    <div class="plane-description w-100 d-inline-block">
                                        @foreach (get_plan_feature($plan->description) as $feature)
                                            <p class="text-14 text-black border-bottom pb-1 mt-2 mb-3">
                                                {{ $feature }}
                                            </p>
                                        @endforeach
                                    </div>
                                    <div class="w-100 d-inline-block text-center my-project-btn mb-4">
                                        <a class="text-white text-13 btn-box p-2" href="{{ route('register') }}">
                                            START MY PROJECT
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <!-- End Features Area -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const allAboutContent = document.getElementById("all-about-content");
            const showMoreBtn = document.getElementById("show-more");
            const showLessBtn = document.getElementById("show-less");
            showMoreBtn.addEventListener("click", function() {
                allAboutContent.style.height = "auto";
                showMoreBtn.style.display = "none";
                showLessBtn.style.display = "inline-block";
            });
            showLessBtn.addEventListener("click", function() {
                allAboutContent.style.height = "165px";
                window.scrollTo({
                    top: allAboutContent.offsetTop - 20,
                    behavior: "smooth"
                });
                showMoreBtn.style.display = "inline-block";
                showLessBtn.style.display = "none";
            });
        });
    </script>
@endsection()
