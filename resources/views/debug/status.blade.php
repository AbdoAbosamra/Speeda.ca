@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-3">Debug Status</h2>

    <div class="card mb-3">
        <div class="card-body">
            <h5>User</h5>
            @if($user)
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Role:</strong> {{ $user->role ?? 'null' }}</p>
                <p><strong>isAdmin():</strong> {{ $user->isAdmin() ? 'true' : 'false' }}</p>
            @else
                <p class="text-muted">No authenticated user.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5>Application</h5>
            <p><strong>Admin route present:</strong> {{ $hasAdminRoute ? 'yes' : 'no' }}</p>
            <p><strong>Public storage linked (public/storage):</strong> {{ $publicStorageLinked ? 'yes' : 'no' }}</p>
            <p><strong>Locations total / active:</strong> {{ $locationsCount }} / {{ $activeCount }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5>Migration Status</h5>
            <pre style="max-height:400px; overflow:auto; background:#f8f9fa; padding:1rem;">{{ $migrateOutput }}</pre>
        </div>
    </div>
</div>
@endsection
