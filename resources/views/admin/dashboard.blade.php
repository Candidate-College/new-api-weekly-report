@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">
    <h1>Welcome to the Admin Dashboard</h1>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text">Manage user accounts.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
