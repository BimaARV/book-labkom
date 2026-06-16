@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Profile') }}</h1>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card shadow mb-4 border-danger">
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
