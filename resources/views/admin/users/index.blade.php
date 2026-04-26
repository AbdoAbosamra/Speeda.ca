@extends('layouts.app')

@section('title', __('admin.users_management'))

@section('content')
<div class="admin-content-wrapper">
    <div class="container py-4">
        <!-- Livewire User Management Component -->
        <livewire:admin.user-management />
    </div>
</div>
@endsection
