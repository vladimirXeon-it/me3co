@extends('admin.layouts.app')

@section('title', 'Add Pages')
@section('style')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
@endsection()

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Add Pages</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Pages</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-md-12 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 new-project">
                                <form method="post" action="{{ route('admin.static.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="form-group">
                                            <label for="title" class="form-label">
                                                Title
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input name="title" id="title" class="form-control" required/>
                                        </div>
                                        <div class="form-group">
                                            <label for="content" class="form-label">
                                                Content
                                                <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="content" id="content" cols="30" rows="10" class="form-control" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            
                                            <button type="submit" class="btn btn-primary">Create</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                </div>
            </div>
        </section>

    </main>

@endsection()

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#content').summernote()
        })
    </script>
@endsection()
