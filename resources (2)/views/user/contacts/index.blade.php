@extends('user.layouts.app')

@section('title', 'Contacts')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            {{-- <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Contact</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Contacts</li>
                                </ol>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end page-title-box-->
                </div>
                <!--end col-->
            </div> --}}
             <div class="row mt-3">
                <div class="col-10">
                    <div class="w-100 d-inline-block">
                        <h2 class="text-black fs-4 fw-bold">My Contact</h2>
                    </div>
                </div>
                <div class="col-2">
                    <div class="float-end d-inline-block">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                            <li class="breadcrumb-item active">Contact</li>
                        </ol>
                    </div>
                    <div class="float-end d-inline-block creat-project-btn">
                        <a href="{{ route('contact.create') }}" 
                                class="text-14 fw-bold d-inline-block btn-create-project"
                        >
                        <img src="{{ asset('projects') }}/images/plus-icon.svg">
                            Create Contact
                        </a>
                    </div>
                </div>
            </div>
             <div class="row">
            <div class="col-12">
                <div class="w-100 d-inline-block project-table mt-4">
                    <table class="table table-striped" id="projectTableId">
                         <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Contact Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Created Date</th>
                                <th>Company Name</th>
                                <th>Action</th>
                            </tr>
                            <!--end tr-->
                        </thead>
                        <tbody class="project-sec">
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($contacts as $contact)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->email }}</td>
                                    <td>{{ $contact->phone }}</td>
                                    <td>{{ $contact->created_at->format("d F, Y") }}</td>
                                    <td>{{ $contact->company }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('contact.edit', ['id' => $contact->id]) }}">
                                            <img class="edit-icon me-2" src="{{ asset('projects') }}/images/edit-icon.svg">
                                        </a>
                                        <a
                                            href="{{ route('contact.delete', ['id' => $contact->id]) }}">
                                            <img class="edit-icon" src="{{ asset('projects') }}/images/delete-icon.svg">
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            <!--end row-->
        </div><!-- container -->
    </div>
@endsection()

@section('script')
    <script>
        $(document).ready(function() {
            $('#projectTableId').DataTable({
                "paging": true,  // Enable pagination
                "searching": true,  // Enable search
                // You can customize further options here
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            $('#create-new').click(function() {
                $('.new-project').show();
                $('.my-project').hide();
            });
            $('#back').click(function() {
                $('.new-project').hide();
                $('.my-project').show();
            });
        })
    </script>
@endsection()
