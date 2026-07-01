@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Legal CMS"
                title="Edit Legal Page"
                :subtitle="$page->title_en"
            >
                <x-slot:actions>
                    @if($page->isPublished())
                        <x-ui.button
                            :href="$page->public_url"
                            target="_blank"
                            rel="noopener"
                            variant="secondary"
                            icon="fas fa-arrow-up-right-from-square"
                            class="admin-btn admin-btn-secondary"
                        >
                            View Public Page
                        </x-ui.button>
                    @endif
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
                'action' => route('admin.legal-pages.update', $page),
                'method' => 'PUT',
                'submitLabel' => 'Save Changes',
            ])
        </div>
    </div>
@endsection
