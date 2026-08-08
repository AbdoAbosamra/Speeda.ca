@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Provider Emails</p>
                    <h1>Edit Draft</h1>
                    <p>This email has not been sent yet. Nothing leaves until you confirm below.</p>
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('admin.broadcasts.destroy', $broadcast) }}" method="POST"
                          onsubmit="return confirm('Delete this draft?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-trash"></i>
                            <span>Delete Draft</span>
                        </button>
                    </form>
                    <a href="{{ route('admin.broadcasts.index') }}" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Emails</span>
                    </a>
                </div>
            </section>

            @include('admin.broadcasts.partials.form', [
                'action' => route('admin.broadcasts.update', $broadcast),
                'method' => 'PUT',
                'broadcast' => $broadcast,
                'audienceCount' => $audienceCount,
            ])
        </div>
    </div>
@endsection
