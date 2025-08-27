@extends('admin.layouts.app')

@section('title', 'Equipments')

@section('content')
@foreach ($css_files as $f) <link rel="stylesheet" href="{{ $f }}"> @endforeach
    <main id="main" class="main">

      <div class="pagetitle">
          <h1>Equipments</h1>
          <nav>
              <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                  <li class="breadcrumb-item">Equipments</li>
              </ol>
          </nav>
      </div><!-- End Page Title -->

      <section class="section">
        <div class="row">
            <div class="col-12">

              <div class="card">
                <div class="card-body">
                  {!! $output !!}
                  @foreach ($js_files as $f) <script src="{{ $f }}"></script> @endforeach
                </div>
              </div>

            </div>
        </div>
      </section>

    </main>


@endsection()