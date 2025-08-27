@extends('landing.layout')

@section('content')

<div class="main-banner book-home" style="height: 500px">
    <div class="d-table">
        <div class="d-table-cell">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-md-12">
                        <div class="hero-content text-center">
                            <h1 class="mb-5">{{ $content->title }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="creative-shape"><img src="{{ asset('landing') }}/assets/img/bg3.png" alt="bg"></div>
    <div class="bg-gray shape-1"></div>
    <div class="shape1"><img src="{{ asset('landing') }}/assets/img/shape1.png" alt="img"></div>
    <div class="shape2"><img src="{{ asset('landing') }}/assets/img/shape2.png" alt="img"></div>
    <div class="shape3"><img src="{{ asset('landing') }}/assets/img/shape3.png" alt="img"></div>
    <div class="shape6"><img src="{{ asset('landing') }}/assets/img/shape6.png" alt="img"></div>
    <div class="shape8 rotateme"><img src="{{ asset('landing') }}/assets/img/shape8.svg" alt="shape"></div>
    <div class="shape9"><img src="{{ asset('landing') }}/assets/img/shape9.svg" alt="shape"></div>
</div>
<section class="ptb-100 pt-0">
    <div class="features-inner-area">
        <div class="container">
            {!! $content->content !!}
        </div>
    </div>
</section>

@endsection()