@extends('admin.layouts.app')
@section('title','Plans')

@section('content')
  @foreach($css_files ?? [] as $f) <link rel="stylesheet" href="{{ $f }}"> @endforeach

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Plans</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Plans</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="card"><div class="card-body pt-3">
        {!! $output !!}
      </div></div>
    </section>
  </main>

  @foreach($js_files ?? [] as $f) <script src="{{ $f }}"></script> @endforeach
@endsection
