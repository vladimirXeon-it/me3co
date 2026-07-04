@extends('user.layouts.app')

@section('title', 'Contacts')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">

                            <div class="mb-4">
                                <h3 class="me3co-form-title mb-1">Create New Contact</h3>
                                <p class="me3co-form-subtitle mb-0">
                                    Fill in the contact information below.
                                </p>
                            </div>

                            <form method="post" action="{{ route('contact.create') }}">
                                @csrf()

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">Contact Name</label>
                                            <input type="text" class="form-control" name="name" placeholder="Contact Name" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">Company Name</label>
                                            <input type="text" class="form-control" name="company" placeholder="Company Name" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">Phone</label>
                                            <input type="tel" class="form-control" name="phone" placeholder="Phone" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">Email</label>
                                            <input type="email" class="form-control" name="email" placeholder="Email" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">Company Address</label>
                                            <textarea class="form-control" name="address" placeholder="Company Address" required style="height:110px;"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">City</label>
                                            <input type="text" class="form-control" name="city" placeholder="City" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">State</label>
                                            <input type="text" class="form-control" name="state" placeholder="State" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">Country</label>
                                            <input type="text" class="form-control" name="country" placeholder="Country" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group input-project">
                                            <label class="form-label fw-bold text-14">Zip Code</label>
                                            <input type="text" class="form-control" name="zip" placeholder="Zip Code" required>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="javascript:void(0);" onclick="goBack()" class="btn me3co-secondary-btn">
                                        Back
                                    </a>

                                    <button class="btn me3co-primary-btn">
                                        <i class="fa fa-save me-1"></i>
                                        Save Contact
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->

        </div><!-- container -->
    </div>
@endsection()

@section('script')
    <script>
        function goBack() {
            if (window.history.length > 1) {
            window.history.back();
            return;
            }
            window.location.href = "/projects"; // fallback
        }
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
