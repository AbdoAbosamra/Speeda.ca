@extends('layouts.app')

@section('title', 'Offers')

@section('content')
@php
    $statusLabels = ['live' => 'Live', 'scheduled' => 'Scheduled', 'expired' => 'Expired', 'draft' => 'Draft'];
    $canReorder = ! $status && $search === '';
@endphp
<div class="admin-cms-page ao-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
        <x-admin.header
            eyebrow="Content"
            title="Local Offers"
            subtitle="Everything in the home page offers section, the /offers page and the offer boxes on provider profiles is managed here."
        >
            <x-slot:actions>
                <a href="{{ route('admin.offers.analytics') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-chart-line"></i><span>Statistics</span>
                </a>
                <a href="{{ route('offers.index') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-up-right-from-square"></i><span>View page</span>
                </a>
                <a href="{{ route('admin.offers.create') }}" class="admin-btn admin-btn-primary text-white">
                    <i class="fas fa-plus"></i><span>New offer</span>
                </a>
            </x-slot:actions>
        </x-admin.header>

        {{-- Is the home section actually showing? The most common surprise. --}}
        @php
            $homeState = ! $section->is_active ? 'off' : ($homeCount > 0 ? 'live' : 'empty');
        @endphp
        <div class="ao-status ao-status--{{ $homeState }}">
            <i class="fas {{ ['live' => 'fa-circle-check', 'off' => 'fa-circle-minus', 'empty' => 'fa-triangle-exclamation'][$homeState] }}"></i>
            <span>
                @if($homeState === 'live')
                    The home section is live and showing <strong>{{ $homeCount }}</strong> offer{{ $homeCount === 1 ? '' : 's' }} (up to {{ $section->max_items }}).
                @elseif($homeState === 'off')
                    The home section is switched off. Offers still appear on /offers and on provider profiles.
                @else
                    The home section is on but hidden: no offer is both live and starred for the home page.
                @endif
            </span>
        </div>

        @php $pendingComments = \App\Models\OfferComment::pending()->count(); @endphp
        @if($pendingComments > 0)
            <a href="{{ route('admin.offers.comments') }}" class="ao-status ao-status--empty text-decoration-none">
                <i class="fas fa-comments"></i>
                <span><strong>{{ $pendingComments }}</strong> offer comment{{ $pendingComments === 1 ? '' : 's' }} waiting for your review →</span>
            </a>
        @endif

        <div class="ao-metrics">
            @foreach(['live' => ['fa-bolt', 'green'], 'scheduled' => ['fa-clock', 'blue'], 'expired' => ['fa-hourglass-end', 'slate'], 'draft' => ['fa-pen-ruler', 'amber']] as $key => [$icon, $tone])
                <a href="{{ route('admin.offers.index', ['status' => $key]) }}" class="ao-metric ao-tone-{{ $tone }} {{ $status === $key ? 'is-active' : '' }}">
                    <span><i class="fas {{ $icon }}"></i> {{ $statusLabels[$key] }}</span>
                    <strong>{{ $counts[$key] }}</strong>
                </a>
            @endforeach
            <a href="{{ route('admin.offers.analytics') }}" class="ao-metric ao-tone-indigo">
                <span><i class="fas fa-eye"></i> Views · 30 days</span>
                <strong>{{ number_format($metrics['views_30']) }}</strong>
            </a>
            <a href="{{ route('admin.offers.analytics') }}" class="ao-metric ao-tone-green">
                <span><i class="fab fa-whatsapp"></i> WhatsApp clicks · 30 days</span>
                <strong>{{ number_format($metrics['clicks_30']) }}</strong>
            </a>
            <a href="{{ route('admin.offers.analytics') }}" class="ao-metric ao-tone-rose">
                <span><i class="fas fa-heart"></i> Likes · 30 days</span>
                <strong>{{ number_format($metrics['likes_30']) }}</strong>
            </a>
        </div>

        {{-- ============ HOME SECTION SETTINGS ============ --}}
        <details class="ao-card ao-settings" @if($errors->hasAny(['title_en', 'title_ar', 'title_fr', 'subtitle_en', 'subtitle_ar', 'subtitle_fr', 'max_items'])) open @endif>
            <summary>
                <span><i class="fas fa-sliders"></i> Home page section settings</span>
                <small>{{ $section->is_active ? 'Shown' : 'Hidden' }} · up to {{ $section->max_items }} cards · title “{{ $section->title_en ?: __('offers.section_title', [], 'en') }}”</small>
            </summary>
            <form method="POST" action="{{ route('admin.offers.section') }}" class="ao-settings-body">
                @csrf
                @method('PUT')
                <label class="ao-switch-row">
                    <input type="hidden" name="is_active" value="0">
                    <span class="ao-switch"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))><span></span></span>
                    <span><strong>Show the offers section on the home page</strong><small>It sits directly above “Explore Services”.</small></span>
                </label>
                <div class="ao-field" style="max-width: 220px;">
                    <label for="max_items">Cards on the home page</label>
                    <select name="max_items" id="max_items" class="form-select">
                        @foreach(\App\Models\OfferSection::MAX_ITEMS_OPTIONS as $n)
                            <option value="{{ $n }}" @selected((int) old('max_items', $section->max_items) === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ao-locale-grid">
                    @foreach(['en' => 'English', 'ar' => 'العربية', 'fr' => 'Français'] as $code => $label)
                        <div class="ao-locale">
                            <span class="ao-locale-tag">{{ $label }}</span>
                            <div class="ao-field">
                                <label for="title_{{ $code }}">Title</label>
                                <input type="text" class="form-control" name="title_{{ $code }}" id="title_{{ $code }}" maxlength="120" dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                                       value="{{ old('title_' . $code, $section->{'title_' . $code}) }}" placeholder="{{ __('offers.section_title', [], $code) }}">
                                @error('title_' . $code)<span class="ao-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="ao-field">
                                <label for="subtitle_{{ $code }}">Subtitle</label>
                                <input type="text" class="form-control" name="subtitle_{{ $code }}" id="subtitle_{{ $code }}" maxlength="255" dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                                       value="{{ old('subtitle_' . $code, $section->{'subtitle_' . $code}) }}" placeholder="{{ __('offers.section_subtitle', [], $code) }}">
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="ao-hint mb-0">Leave a field empty to use the default text shown as the placeholder.</p>
                <div><button type="submit" class="admin-btn admin-btn-primary text-white"><i class="fas fa-save"></i><span>Save section</span></button></div>
            </form>
        </details>

        {{-- ============ OFFERS TABLE ============ --}}
        <div class="ao-toolbar">
            <nav class="ao-tabs" aria-label="Filter by status">
                <a href="{{ route('admin.offers.index', array_filter(['q' => $search ?: null])) }}" class="{{ ! $status ? 'is-active' : '' }}">All <em>{{ array_sum($counts) }}</em></a>
                @foreach($statusLabels as $key => $label)
                    <a href="{{ route('admin.offers.index', array_filter(['status' => $key, 'q' => $search ?: null])) }}" class="{{ $status === $key ? 'is-active' : '' }}">{{ $label }} <em>{{ $counts[$key] }}</em></a>
                @endforeach
            </nav>
            <form method="GET" class="ao-search" role="search">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search title or provider" aria-label="Search offers">
            </form>
        </div>

        <x-admin.bulk-form
            :action="route('admin.offers.bulk')"
            label="offers"
            :actions="[
                'activate'   => ['label' => 'Switch on', 'icon' => 'fa-toggle-on', 'variant' => 'success'],
                'deactivate' => ['label' => 'Switch off', 'icon' => 'fa-toggle-off', 'variant' => 'warning'],
                'feature'    => ['label' => 'Show on home', 'icon' => 'fa-star'],
                'unfeature'  => ['label' => 'Remove from home', 'icon' => 'fa-star-half-stroke'],
                'delete'     => ['label' => __('admin.delete'), 'icon' => 'fa-trash', 'variant' => 'danger', 'confirm' => __('admin.bulk_confirm_delete')],
            ]"
        >
        <x-admin.table-card>
            <table class="admin-data-table ao-table">
                <thead>
                    <tr>
                        <th style="width:1%;"><x-admin.bulk-checkbox master /></th>
                        @if($canReorder)<th style="width:1%;" title="Drag to reorder"></th>@endif
                        <th>Offer</th>
                        <th>Deal</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th title="Starred offers appear on the home page">Home</th>
                        <th>Views · WhatsApp clicks</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="ao-rows">
                    @forelse($offers as $offer)
                        @php
                            $st = $offer->status();
                            $days = $offer->daysLeft();
                            $rate = $offer->views_count > 0 ? round($offer->claims_count / $offer->views_count * 100, 1) : 0;
                            $providerHidden = ! ($offer->serviceProvider?->is_active && $offer->serviceProvider?->user?->is_active);
                        @endphp
                        <tr data-id="{{ $offer->id }}" @if($canReorder) draggable="true" @endif>
                            <td><x-admin.bulk-checkbox :value="$offer->id" /></td>
                            @if($canReorder)<td class="ao-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></td>@endif
                            <td>
                                <div class="ao-offer">
                                    <div class="ao-thumb so-scope">@include('partials.offers.cover', ['offer' => $offer])</div>
                                    <div style="min-width:0">
                                        <a href="{{ route('admin.offers.edit', $offer) }}" class="admin-table-title text-decoration-none">{{ $offer->title_en }}</a>
                                        <div class="admin-table-subtitle">
                                            {{ $offer->providerName() }}@if($offer->providerCity()) · {{ $offer->providerCity() }}@endif
                                            @if($providerHidden)<span class="ao-warn" title="The provider profile is not public, so this offer is hidden.">provider hidden</span>@endif
                                        </div>
                                        <div class="ao-langs" title="Translations">
                                            @foreach(['en', 'ar', 'fr'] as $l)
                                                <span class="{{ filled($offer->{'title_' . $l}) ? 'is-done' : '' }}">{{ strtoupper($l) }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="ao-deal ao-deal--{{ $offer->deal_type }}">{{ $offer->badgeLabel('en') }}</span></td>
                            <td class="ao-dates">
                                {{ $offer->starts_at?->format('M j') ?? 'Now' }} → {{ $offer->ends_at?->format('M j, Y') ?? 'No end' }}
                                @if($st === 'live' && $days !== null && $days <= 7)
                                    <small class="text-danger fw-bold d-block">{{ $days === 0 ? 'Last day' : $days . ' days left' }}</small>
                                @endif
                            </td>
                            <td><span class="ao-pill ao-pill--{{ $st }}">{{ $statusLabels[$st] }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.offers.feature', $offer) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="ao-star {{ $offer->is_featured ? 'is-on' : '' }}" aria-pressed="{{ $offer->is_featured ? 'true' : 'false' }}"
                                        title="{{ $offer->is_featured ? 'Shown on the home page — click to remove' : 'Not on the home page — click to add' }}">
                                        <i class="{{ $offer->is_featured ? 'fas' : 'far' }} fa-star"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="ao-nums">
                                <b>{{ number_format($offer->views_count) }}</b> · <b>{{ number_format($offer->claims_count) }}</b>
                                <small class="d-block">{{ $rate }}% clicked WhatsApp</small>
                                <small class="d-block" title="Likes · approved comments · saved by clients"><i class="fas fa-heart text-danger"></i> {{ $offer->likes_count }} · <i class="far fa-comment"></i> {{ $offer->comments_count }} · <i class="fas fa-bookmark text-primary"></i> {{ $offer->favorites_count }}</small>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.offers.toggle', $offer) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="ao-switch ao-switch-btn {{ $offer->is_active ? 'is-on' : '' }}" aria-pressed="{{ $offer->is_active ? 'true' : 'false' }}"
                                        title="{{ $offer->is_active ? 'Switch off' : 'Switch on' }}"><span></span></button>
                                </form>
                            </td>
                            <td>
                                <div class="admin-row-actions justify-content-end">
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="admin-icon-action" title="Edit"><i class="fas fa-pen"></i><span>Edit</span></a>
                                    <a href="{{ route('admin.offers.analytics', ['offer_id' => $offer->id, 'range' => '90']) }}" class="admin-icon-action" title="Statistics"><i class="fas fa-chart-simple"></i></a>
                                    <a href="{{ route('offers.show', $offer->slug) }}" target="_blank" rel="noopener" class="admin-icon-action" title="Open public page"><i class="fas fa-arrow-up-right-from-square"></i></a>
                                    <form method="POST" action="{{ route('admin.offers.duplicate', $offer) }}">
                                        @csrf
                                        <button type="submit" class="admin-icon-action" title="Duplicate as draft"><i class="fas fa-clone"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" onsubmit="return confirm('Delete this offer? Its statistics are kept and you can undo from the activity log.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="admin-icon-action admin-icon-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <x-ui.empty-state
                                    icon="fas fa-tags"
                                    :title="$status || $search !== '' ? 'No offers match' : 'No offers yet'"
                                    :description="$status || $search !== '' ? 'Try another status or search.' : 'Create the first offer: pick a provider, write the deal, set the dates.'"
                                    class="admin-empty-state"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.table-card>
        </x-admin.bulk-form>

        @if($canReorder && $offers->count() > 1)
            <p class="ao-hint mt-2"><i class="fas fa-grip-vertical"></i> Drag rows to change the order. The same order is used on the home page and on /offers.</p>
        @endif
    </div>
</div>

@include('admin.offers.partials.styles')
@endsection

@push('scripts')
<script>
(function () {
    const body = document.getElementById('ao-rows');
    if (!body || !body.querySelector('tr[draggable]')) return;
    let dragged = null;

    body.addEventListener('dragstart', function (e) {
        dragged = e.target.closest('tr');
        if (!dragged) return;
        dragged.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
    });
    body.addEventListener('dragover', function (e) {
        e.preventDefault();
        const over = e.target.closest('tr');
        if (!dragged || !over || over === dragged) return;
        const rect = over.getBoundingClientRect();
        body.insertBefore(dragged, (e.clientY - rect.top) > rect.height / 2 ? over.nextSibling : over);
    });
    body.addEventListener('dragend', function () {
        if (!dragged) return;
        dragged.classList.remove('is-dragging');
        dragged = null;
        const ids = Array.from(body.querySelectorAll('tr[data-id]')).map(function (r) { return parseInt(r.dataset.id, 10); });
        fetch(@json(route('admin.offers.reorder')), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ ids: ids })
        }).then(function (r) {
            if (!r.ok) throw new Error();
            if (window.showToast) window.showToast('Order saved', 'success');
        }).catch(function () { alert('The new order could not be saved. Reload the page and try again.'); });
    });
})();
</script>
@endpush
