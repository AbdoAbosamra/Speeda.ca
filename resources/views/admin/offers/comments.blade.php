@extends('layouts.app')

@section('title', 'Offer comments')

@section('content')
@php
    $tabs = ['pending' => ['Waiting for review', 'fa-hourglass-half'], 'approved' => ['Approved (public)', 'fa-check'], 'rejected' => ['Rejected', 'fa-ban']];
@endphp
<div class="admin-cms-page ao-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
        <x-admin.header
            eyebrow="Local Offers"
            title="Offer comments"
            subtitle="Visitors' comments on offers. Nothing appears on an offer until you approve it here."
        >
            <x-slot:actions>
                <a href="{{ route('admin.offers.index') }}" class="admin-btn admin-btn-secondary"><i class="fas fa-tags"></i><span>Offers</span></a>
            </x-slot:actions>
        </x-admin.header>

        @if(($counts['pending'] ?? 0) > 0)
            <div class="ao-status ao-status--empty">
                <i class="fas fa-bell"></i>
                <span><strong>{{ $counts['pending'] }}</strong> comment{{ $counts['pending'] == 1 ? '' : 's' }} waiting for review.</span>
            </div>
        @endif

        <div class="ao-toolbar">
            <nav class="ao-tabs" aria-label="Filter by status">
                @foreach($tabs as $key => [$label, $icon])
                    <a href="{{ route('admin.offers.comments', array_filter(['status' => $key, 'q' => $search ?: null])) }}" class="{{ $status === $key ? 'is-active' : '' }}">
                        <i class="fas {{ $icon }}"></i> {{ $label }} <em>{{ $counts[$key] ?? 0 }}</em>
                    </a>
                @endforeach
            </nav>
            <form method="GET" class="ao-search" role="search">
                <input type="hidden" name="status" value="{{ $status }}">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search name or text" aria-label="Search comments">
            </form>
        </div>

        <x-admin.bulk-form
            :action="route('admin.offers.comments.bulk')"
            label="comments"
            :actions="[
                'approve' => ['label' => 'Approve', 'icon' => 'fa-check', 'variant' => 'success'],
                'reject'  => ['label' => 'Reject', 'icon' => 'fa-ban', 'variant' => 'warning'],
                'delete'  => ['label' => __('admin.delete'), 'icon' => 'fa-trash', 'variant' => 'danger', 'confirm' => __('admin.bulk_confirm_delete')],
            ]"
        >
            <div class="oc-list">
                <div class="oc-list-head">
                    <x-admin.bulk-checkbox master />
                    <span>Select all on this page</span>
                </div>
                @forelse($comments as $comment)
                    <article class="oc-item">
                        <x-admin.bulk-checkbox :value="$comment->id" />
                        <span class="oc-avatar" aria-hidden="true">{{ $comment->initial() }}</span>
                        <div class="oc-main">
                            <div class="oc-meta">
                                <b>{{ $comment->author_name }}</b>
                                @if($comment->user)
                                    <span class="oc-chip">{{ ucfirst(str_replace('_', ' ', $comment->user->role)) }} · {{ $comment->user->email }}</span>
                                @else
                                    <span class="oc-chip oc-chip--guest">Guest</span>
                                @endif
                                <span class="text-muted small">{{ $comment->created_at->format('M j, Y H:i') }} · {{ strtoupper($comment->locale ?? '') }}</span>
                            </div>
                            <p class="oc-body" dir="auto">{{ $comment->body }}</p>
                            <div class="oc-offer small">
                                On
                                @if($comment->offer)
                                    <a href="{{ route('offers.show', $comment->offer->slug) }}#comments" target="_blank" rel="noopener">{{ $comment->offer->title_en }}</a>
                                    <span class="text-muted">· {{ $comment->offer->serviceProvider?->company_name }}</span>
                                @else
                                    <span class="text-muted">deleted offer</span>
                                @endif
                                @if($comment->reviewer)
                                    <span class="text-muted">· {{ $comment->status }} by {{ $comment->reviewer->name }} {{ $comment->reviewed_at?->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="admin-row-actions oc-actions">
                            @if($comment->status !== 'approved')
                                <form method="POST" action="{{ route('admin.offers.comments.approve', $comment) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="admin-icon-action oc-approve"><i class="fas fa-check"></i><span>Approve</span></button>
                                </form>
                            @endif
                            @if($comment->status !== 'rejected')
                                <form method="POST" action="{{ route('admin.offers.comments.reject', $comment) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="admin-icon-action"><i class="fas fa-ban"></i><span>Reject</span></button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.offers.comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment for good?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-icon-action admin-icon-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state
                        icon="far fa-comments"
                        :title="$status === 'pending' ? 'Nothing waiting for review' : 'No comments here'"
                        description="New comments on offers appear in “Waiting for review”."
                        class="admin-empty-state"
                    />
                @endforelse
            </div>
        </x-admin.bulk-form>

        @if($comments->hasPages())
            <div class="mt-3">{{ $comments->links() }}</div>
        @endif
    </div>
</div>

@include('admin.offers.partials.styles')
@push('styles')
<style>
    .oc-list { background: #fff; border: 1px solid rgba(148, 163, 184, .2); border-radius: 18px; overflow: hidden; }
    .oc-list-head { display: flex; gap: .6rem; align-items: center; padding: .7rem 1.1rem; background: #f8fafc; border-bottom: 1px solid #eef2f6; font-size: .82rem; color: #64748b; }
    .oc-item { display: grid; grid-template-columns: auto auto minmax(0, 1fr) auto; gap: .9rem; align-items: start; padding: 1rem 1.1rem; border-bottom: 1px solid #eef2f6; }
    .oc-item:last-child { border-bottom: 0; }
    @media (max-width: 767.98px) { .oc-item { grid-template-columns: auto auto minmax(0, 1fr); } .oc-actions { grid-column: 1 / -1; justify-content: flex-start !important; } }
    .oc-avatar { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; background: linear-gradient(135deg, #dbeafe, #c7d2fe); color: #3730a3; font-weight: 800; }
    .oc-meta { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
    .oc-meta b { color: #0f172a; }
    .oc-chip { font-size: .72rem; font-weight: 700; background: #eff6ff; color: #2563eb; border-radius: 999px; padding: .1rem .55rem; }
    .oc-chip--guest { background: #f1f5f9; color: #64748b; }
    .oc-body { margin: .35rem 0; color: #334155; white-space: pre-line; overflow-wrap: anywhere; background: #f8fafc; border-radius: 4px 14px 14px 14px; padding: .6rem .85rem; }
    .oc-approve { color: #059669 !important; border-color: #a7f3d0 !important; }
</style>
@endpush
@endsection
