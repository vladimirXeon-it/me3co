@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Profile</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Users</li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </nav>
        </div>
        <section class="section profile">
            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center"> <img
                                src="{{ asset('fronts/assets/images/users/default.png') }}" alt="Profile"
                                class="rounded-circle">
                            <h2>{{ $user->name }}</h2>
                            <h3>{{ $user->company }}</h3>
                            <div class="form-row">
                                <button type="button" class="btn btn-primary btn-sm">Contacts<span
                                        class="badge bg-white text-primary ms-1">{{ $user->contacts->count() }}</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm">Projects<span
                                        class="badge bg-white text-primary ms-1">{{ $user->projects->count() }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body pt-3">
                            <ul class="nav nav-tabs nav-tabs-bordered">
                                <li class="nav-item"> <button class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#profile-overview">Overview</button></li>
                                <li class="nav-item"> <button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#profile-edit">Edit Profile</button></li>
                                <li class="nav-item"> <button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#profile-settings">Settings</button></li>
                            </ul>
                            <div class="tab-content pt-2">
                                <div class="tab-pane fade show active profile-overview" id="profile-overview">
                                    <h5 class="card-title">Profile Details</h5>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label ">Full Name</div>
                                        <div class="col-lg-9 col-md-8">{{ $user->name }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Company</div>
                                        <div class="col-lg-9 col-md-8">{{ $user->company }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Email</div>
                                        <div class="col-lg-9 col-md-8">{{ $user->email }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Username</div>
                                        <div class="col-lg-9 col-md-8">{{ $user->username }}</div>
                                    </div>
                                </div>
                                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                                    <form method="POST" action="{{ route('admin.users.update', ['id' => $user->id]) }}">
                                        @csrf()
                                        <div class="row mb-3"> <label for="fullName"
                                                class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                                            <div class="col-md-8 col-lg-9"> <input name="name" type="text"
                                                    class="form-control" id="fullName" value="{{ $user->name }}"
                                                    required></div>
                                        </div>
                                        <div class="row mb-3"> <label for="company"
                                                class="col-md-4 col-lg-3 col-form-label">Company</label>
                                            <div class="col-md-8 col-lg-9"> <input name="company" type="text"
                                                    class="form-control" id="company" value="{{ $user->company }}"
                                                    required></div>
                                        </div>
                                        <div class="row mb-3"> <label for="email"
                                                class="col-md-4 col-lg-3 col-form-label">Email</label>
                                            <div class="col-md-8 col-lg-9"> <input name="email" type="email"
                                                    class="form-control" id="email" value="{{ $user->email }}"
                                                    required></div>
                                        </div>
                                        <div class="row mb-3"> <label for="username"
                                                class="col-md-4 col-lg-3 col-form-label">Username</label>
                                            <div class="col-md-8 col-lg-9"> <input name="username" type="text"
                                                    class="form-control" id="username" value="{{ $user->username }}"
                                                    required></div>
                                        </div>

                                        <div class="text-center"> <button type="submit" class="btn btn-primary">Save
                                                Changes</button></div>
                                    </form>
                                </div>
                                <div class="tab-pane fade pt-3" id="profile-settings">
                                    <form method="POST" action="{{ route('admin.users.setting', ['id'=>$user->id]) }}">
                                        @csrf()
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                @php
                                                    $settings = get_user_settings($user->id)
                                                @endphp
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        id="changesMade" name="auto_update" @if($settings->auto_update) checked @endif()> <label class="form-check-label"
                                                        for="changesMade">Auto Update</label></div>
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        id="newProducts" name="location" @if($settings->location) checked @endif()> <label class="form-check-label"
                                                        for="newProducts">Location Permission
                                                    </label></div>
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        id="proOffers" name="status" @if($settings->status) checked @endif()> <label class="form-check-label" for="proOffers">
                                                        Online Status</label></div>
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        id="securityNotify" name="notification" @if($settings->notification) checked @endif()> <label
                                                        class="form-check-label" for="securityNotify">PopUp Notification
                                                    </label></div>
                                            </div>
                                        </div>
                                        <div class="text-center"> <button type="submit" class="btn btn-primary">Save
                                                Changes</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection()
