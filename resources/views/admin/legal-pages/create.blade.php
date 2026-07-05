@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Legal CMS"
                title="Create Legal Page"
                subtitle="Create a public policy, privacy, terms, or custom legal page."
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.legal-pages.index')"
                        variant="secondary"
                        icon="fas fa-arrow-left"
                        class="admin-btn admin-btn-secondary"
                    >
                        Back to Legal Pages
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            @include('admin.legal-pages.partials.form', [
                'action' => route('admin.legal-pages.store'),
                'method' => 'POST',
                'submitLabel' => 'Create Page',
            ])
        </div>
    </div>
@endsection
