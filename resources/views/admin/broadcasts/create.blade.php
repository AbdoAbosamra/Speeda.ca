@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Provider Emails</p>
                    <h1>Compose Email</h1>
                    <p>Write a message and send it to every active service provider.</p>
                </div>
                <a href="{{ route('admin.broadcasts.index') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Emails</span>
                </a>
            </section>

            @include('admin.broadcasts.partials.form', [
                'action' => route('admin.broadcasts.store'),
                'method' => 'POST',
                'broadcast' => $broadcast,
                'audienceCount' => $audienceCount,
            ])
        </div>
    </div>
@endsection
