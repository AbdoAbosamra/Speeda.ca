@extends('layouts.app')

@section('content')
    <div class="admin-cms-page pam-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Provider Operations"
                title="Provider Activity Monitor"
                subtitle="Prioritize provider profile gaps, lead activity, and stale marketplace listings."
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.whatsapp_analytics.index')"
                        variant="secondary"
                        icon="fab fa-whatsapp"
                        class="admin-btn admin-btn-secondary"
                    >
                        WhatsApp Analytics
                    </x-ui.button>
                    <x-ui.button
                        :href="route('admin.dashboard')"
                        variant="secondary"
                        icon="fas fa-arrow-left"
                        class="admin-btn admin-btn-secondary"
                    >
                        Dashboard
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            <section class="pam-summary-grid" aria-label="Provider operations summary">
                <article class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-blue"><i class="fas fa-briefcase"></i></span>
                    <div>
                        <strong>{{ number_format($summary['total']) }}</strong>
                        <span>Total Providers</span>
                    </div>
                </article>
                <article class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-red"><i class="fas fa-triangle-exclamation"></i></span>
                    <div>
                        <strong>{{ number_format($summary['needs_work']) }}</strong>
                        <span>Need Profile Work</span>
                    </div>
                </article>
                <a href="{{ route('admin.provider_activity_monitor.index', ['listing_status' => 'hidden']) }}" class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-red"><i class="fas fa-eye-slash"></i></span>
                    <div>
                        <strong>{{ number_format($summary['hidden'] ?? 0) }}</strong>
                        <span>Hidden Listings</span>
                    </div>
                </a>
                <a href="{{ route('admin.provider_activity_monitor.index', ['listing_status' => 'verified']) }}" class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-blue"><i class="fas fa-circle-check"></i></span>
                    <div>
                        <strong>{{ number_format($summary['verified'] ?? 0) }}</strong>
                        <span>Verified</span>
                    </div>
                </a>
                <a href="{{ route('admin.provider_activity_monitor.index', ['listing_status' => 'featured']) }}" class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-amber"><i class="fas fa-star"></i></span>
                    <div>
                        <strong>{{ number_format($summary['featured'] ?? 0) }}</strong>
                        <span>Featured</span>
                    </div>
                </a>
                <article class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-slate"><i class="fas fa-moon"></i></span>
                    <div>
                        <strong>{{ number_format($summary['no_activity']) }}</strong>
                        <span>No Activity Yet</span>
                    </div>
                </article>
                <article class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-amber"><i class="fas fa-images"></i></span>
                    <div>
                        <strong>{{ number_format($summary['missing_gallery']) }}</strong>
                        <span>Gallery Below 4</span>
                    </div>
                </article>
                <article class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-teal"><i class="fab fa-whatsapp"></i></span>
                    <div>
                        <strong>{{ number_format($summary['total_clicks']) }}</strong>
                        <span>WhatsApp Clicks</span>
                    </div>
                </article>
                <article class="pam-summary-card">
                    <span class="pam-summary-icon pam-tone-green"><i class="fas fa-percent"></i></span>
                    <div>
                        <strong>{{ $summary['conversion_rate'] }}%</strong>
                        <span>View To Lead</span>
                    </div>
                </article>
            </section>

            <section class="pam-attention-band" aria-label="Quick issue filters">
                <div>
                    <p class="pam-band-eyebrow">Action Queue</p>
                    <h2>Start with the problems that block conversion.</h2>
                </div>
                <div class="pam-issue-links">
                    <a href="{{ route('admin.provider_activity_monitor.index', ['issue' => 'missing_photo']) }}" class="pam-issue-chip">
                        <i class="fas fa-image"></i>
                        <span>No Photo</span>
                        <strong>{{ number_format($summary['missing_photo']) }}</strong>
                    </a>
                    <a href="{{ route('admin.provider_activity_monitor.index', ['issue' => 'missing_gallery']) }}" class="pam-issue-chip">
                        <i class="fas fa-images"></i>
                        <span>Gallery Gap</span>
                        <strong>{{ number_format($summary['missing_gallery']) }}</strong>
                    </a>
                    <a href="{{ route('admin.provider_activity_monitor.index', ['activity' => 'never']) }}" class="pam-issue-chip">
                        <i class="fas fa-clock"></i>
                        <span>No Activity</span>
                        <strong>{{ number_format($summary['no_activity']) }}</strong>
                    </a>
                </div>
            </section>

            <section class="admin-section-block">
                <form method="GET" action="{{ route('admin.provider_activity_monitor.index') }}" class="pam-filter-bar">
                    <label class="pam-field pam-field-search">
                        <span>Search</span>
                        <div class="pam-search-input">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Provider, owner email, or ID">
                        </div>
                    </label>

                    <label class="pam-field">
                        <span>Completion</span>
                        <select name="completion_status">
                            <option value="">Any completion</option>
                            <option value="complete" @selected(request('completion_status') === 'complete')>Complete</option>
                            <option value="partial" @selected(request('completion_status') === 'partial')>Partial</option>
                            <option value="incomplete" @selected(request('completion_status') === 'incomplete')>0% only</option>
                        </select>
                    </label>

                    <label class="pam-field">
                        <span>Issue</span>
                        <select name="issue">
                            <option value="">Any issue</option>
                            <option value="missing_photo" @selected(request('issue') === 'missing_photo')>No profile photo</option>
                            <option value="missing_gallery" @selected(request('issue') === 'missing_gallery')>Gallery below 4</option>
                            <option value="missing_services" @selected(request('issue') === 'missing_services')>No services</option>
                            <option value="missing_address" @selected(request('issue') === 'missing_address')>No address</option>
                        </select>
                    </label>

                    <label class="pam-field">
                        <span>Activity</span>
                        <select name="activity">
                            <option value="">Any activity</option>
                            <option value="today" @selected(request('activity') === 'today')>Today</option>
                            <option value="week" @selected(request('activity') === 'week')>This week</option>
                            <option value="month" @selected(request('activity') === 'month')>This month</option>
                            <option value="never" @selected(request('activity') === 'never')>Never</option>
                        </select>
                    </label>

                    <label class="pam-field">
                        <span>Listing</span>
                        <select name="listing_status">
                            <option value="">Any listing</option>
                            <option value="active" @selected(request('listing_status') === 'active')>Live</option>
                            <option value="hidden" @selected(request('listing_status') === 'hidden')>Hidden</option>
                            <option value="verified" @selected(request('listing_status') === 'verified')>Verified</option>
                            <option value="unverified" @selected(request('listing_status') === 'unverified')>Not verified</option>
                            <option value="featured" @selected(request('listing_status') === 'featured')>Featured</option>
                        </select>
                    </label>

                    <label class="pam-field">
                        <span>Sort</span>
                        <select name="sort">
                            <option value="attention" @selected(request('sort', 'attention') === 'attention')>Needs attention</option>
                            <option value="clicks" @selected(request('sort') === 'clicks')>WhatsApp clicks</option>
                            <option value="views" @selected(request('sort') === 'views')>Profile views</option>
                            <option value="completion_low" @selected(request('sort') === 'completion_low')>Lowest completion</option>
                            <option value="newest" @selected(request('sort') === 'newest')>Newest providers</option>
                        </select>
                    </label>

                    <label class="pam-field pam-field-small">
                        <span>Rows</span>
                        <select name="per_page">
                            @foreach([10, 15, 25, 50] as $count)
                                <option value="{{ $count }}" @selected((int) request('per_page', 15) === $count)>{{ $count }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="pam-filter-actions">
                        <x-ui.button type="submit" icon="fas fa-filter" class="admin-btn admin-btn-primary text-white">
                            Apply
                        </x-ui.button>
                        @if(request()->hasAny(['search', 'completion_status', 'issue', 'activity', 'sort', 'per_page']))
                            <x-ui.button :href="route('admin.provider_activity_monitor.index')" variant="secondary" class="admin-btn admin-btn-secondary">
                                Reset
                            </x-ui.button>
                        @endif
                    </div>
                </form>
            </section>

            <x-admin.bulk-form
                :action="route('admin.providers.bulk')"
                label="providers"
                :actions="[
                    'show'      => ['label' => __('admin.bulk_verb_shown'), 'icon' => 'fa-eye', 'variant' => 'success'],
                    'hide'      => ['label' => __('admin.bulk_verb_hidden'), 'icon' => 'fa-eye-slash', 'variant' => 'warning'],
                    'verify'    => ['label' => __('admin.verified'), 'icon' => 'fa-circle-check'],
                    'unverify'  => ['label' => __('admin.bulk_verb_unverified'), 'icon' => 'fa-circle-xmark'],
                    'feature'   => ['label' => __('admin.feature'), 'icon' => 'fa-star'],
                    'unfeature' => ['label' => __('admin.unfeature'), 'icon' => 'fa-star-half-stroke'],
                ]"
            >
            <x-admin.table-card class="pam-table-card">
                <table class="admin-data-table pam-table">
                    <thead>
                        <tr>
                            <th style="width:1%;"><x-admin.bulk-checkbox master /></th>
                            <th>Provider</th>
                            <th>Profile Health</th>
                            <th>Lead Activity</th>
                            <th>Missing / Risk</th>
                            <th>Account</th>
                            <th>Last Activity</th>
                            <th>Listing</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $p)
                            @php
                                $completion = max(0, min(100, (int) ($p->profile_completion_percent ?? 0)));
                                $galleryCount = (int) ($p->gallery_count ?? 0);
                                $views = (int) ($p->profile_views ?? 0);
                                $clicks = (int) ($p->whatsapp_clicks ?? 0);
                                $conversion = $views > 0 ? round(($clicks / $views) * 100, 1) : 0;
                                $hasPhoto = (bool) $p->has_profile_photo;
                                $hasServices = (bool) $p->has_services;
                                $hasAddress = (bool) $p->has_address;
                                $hasExperience = (bool) $p->has_experience;
                                $ownerActive = (bool) $p->owner_is_active;
                                $name = $p->company_name ?: ('Provider #' . $p->id);
                                $initial = strtoupper(mb_substr($name, 0, 1));
                                $category = $p->category_name_en ?: $p->category_name ?: 'No category';
                                $location = $p->location_city ?: 'No city';
                                $lastActivityType = match($p->last_action_type ?? null) {
                                    'view' => 'Profile view',
                                    'click_whatsapp' => 'WhatsApp click',
                                    null => 'No activity',
                                    default => \Illuminate\Support\Str::headline($p->last_action_type),
                                };
                                $completionTone = $completion >= 100 ? 'good' : ($completion >= 60 ? 'warn' : 'bad');
                                $missing = [];
                                if (!$hasPhoto) $missing[] = ['label' => 'Photo', 'tone' => 'bad'];
                                if ($galleryCount < 4) $missing[] = ['label' => "Gallery {$galleryCount}/4", 'tone' => 'warn'];
                                if (!$hasServices) $missing[] = ['label' => 'Services', 'tone' => 'bad'];
                                if (!$hasAddress) $missing[] = ['label' => 'Address', 'tone' => 'warn'];
                                if (!$hasExperience) $missing[] = ['label' => 'Experience', 'tone' => 'warn'];
                            @endphp
                            <tr>
                                <td><x-admin.bulk-checkbox :value="$p->id" /></td>
                                <td>
                                    <div class="pam-provider-cell">
                                        <span class="pam-avatar">
                                            @if($p->profile_image)
                                                <img src="{{ asset('storage/' . $p->profile_image) }}" alt="{{ $name }}" loading="lazy">
                                            @else
                                                {{ $initial }}
                                            @endif
                                        </span>
                                        <div class="pam-provider-copy">
                                            <a href="{{ route('service-providers.show', $p->id) }}" target="_blank" rel="noopener">{{ $name }}</a>
                                            <span>ID {{ $p->id }} · {{ $category }} · {{ $location }}</span>
                                            <small>{{ $p->owner_email ?: 'No owner email' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="pam-health-cell">
                                        <div class="pam-health-head">
                                            <strong class="pam-health-{{ $completionTone }}">{{ $completion }}%</strong>
                                            <span>{{ $completion >= 100 ? 'Complete' : 'Needs work' }}</span>
                                        </div>
                                        <div class="pam-health-track">
                                            <span class="pam-health-fill pam-health-fill-{{ $completionTone }}" style="width: {{ $completion }}%;"></span>
                                        </div>
                                        <small>{{ $galleryCount }}/4 gallery images</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="pam-leads">
                                        <span><strong>{{ number_format($views) }}</strong> views</span>
                                        <span><strong>{{ number_format($clicks) }}</strong> WhatsApp</span>
                                        <span class="pam-conversion">{{ $conversion }}% conversion</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="pam-risk-list">
                                        @forelse($missing as $item)
                                            <span class="pam-risk pam-risk-{{ $item['tone'] }}">{{ $item['label'] }}</span>
                                        @empty
                                            <span class="pam-risk pam-risk-good">No blockers</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="pam-account-flags">
                                        <span class="pam-flag {{ $ownerActive ? 'pam-flag-good' : 'pam-flag-bad' }}">
                                            {{ $ownerActive ? 'Active user' : 'Inactive user' }}
                                        </span>
                                        @if($p->is_verified)
                                            <span class="pam-flag pam-flag-good">Verified</span>
                                        @endif
                                        @if($p->is_certified)
                                            <span class="pam-flag pam-flag-info">Certified</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="pam-activity-cell">
                                        <strong>{{ $lastActivityType }}</strong>
                                        <span>
                                            {{ $p->last_activity_at ? \Carbon\Carbon::parse($p->last_activity_at)->diffForHumans() : 'Never tracked' }}
                                        </span>
                                        <small>Created {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('M d, Y') : '-' }}</small>
                                    </div>
                                </td>
                                <td>
                                    {{-- Listing status: is_active is the admin-controlled listing flag,
                                         owner_is_active is the account flag. Both must be on to be public. --}}
                                    <div class="pam-status-cell">
                                        @if(!$p->owner_is_active)
                                            <span class="pam-badge pam-badge-off" title="Owner account is deactivated">
                                                <i class="fas fa-user-slash"></i> Account off
                                            </span>
                                        @elseif(!$p->is_active)
                                            <span class="pam-badge pam-badge-off" title="Listing hidden by an admin">
                                                <i class="fas fa-eye-slash"></i> Hidden
                                            </span>
                                        @else
                                            <span class="pam-badge pam-badge-on"><i class="fas fa-eye"></i> Live</span>
                                        @endif

                                        @if($p->is_verified)
                                            <span class="pam-badge pam-badge-verified"><i class="fas fa-circle-check"></i> Verified</span>
                                        @endif
                                        @if($p->is_featured)
                                            <span class="pam-badge pam-badge-featured"><i class="fas fa-star"></i> Featured</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="pam-actions">
                                        <a href="{{ route('admin.providers.edit', $p->id) }}" class="pam-icon-action pam-icon-primary" title="Edit provider">
                                            <i class="fas fa-pen"></i>
                                            <span>Edit</span>
                                        </a>

                                        <form action="{{ route('admin.providers.toggle_active', $p->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="pam-icon-action"
                                                    title="{{ $p->is_active ? 'Hide listing from the public site' : 'Show listing on the public site' }}">
                                                <i class="fas {{ $p->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                <span>{{ $p->is_active ? 'Hide' : 'Show' }}</span>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.providers.toggle_verified', $p->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="pam-icon-action"
                                                    title="{{ $p->is_verified ? 'Remove verification' : 'Mark as verified' }}">
                                                <i class="fas {{ $p->is_verified ? 'fa-circle-xmark' : 'fa-circle-check' }}"></i>
                                                <span>{{ $p->is_verified ? 'Unverify' : 'Verify' }}</span>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.providers.toggle_featured', $p->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="pam-icon-action"
                                                    title="{{ $p->is_featured ? 'Remove from featured' : 'Feature this provider' }}">
                                                <i class="{{ $p->is_featured ? 'far' : 'fas' }} fa-star"></i>
                                                <span>{{ $p->is_featured ? 'Unfeature' : 'Feature' }}</span>
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.provider_activity_monitor.show', $p->id) }}" class="pam-icon-action" title="Open analytics timeline">
                                            <i class="fas fa-chart-line"></i>
                                            <span>Timeline</span>
                                        </a>
                                        <a href="{{ route('service-providers.show', $p->id) }}" class="pam-icon-action" target="_blank" rel="noopener" title="View public profile">
                                            <i class="fas fa-arrow-up-right-from-square"></i>
                                            <span>Public</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <x-admin.empty-state
                                        icon="fas fa-chart-line"
                                        title="No providers match these filters"
                                        description="Try clearing filters or searching for another provider."
                                    >
                                        <x-slot:actions>
                                            <x-ui.button :href="route('admin.provider_activity_monitor.index')" class="admin-btn admin-btn-primary text-white">
                                                Reset Filters
                                            </x-ui.button>
                                        </x-slot:actions>
                                    </x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-card>
            </x-admin.bulk-form>

            @if($providers->hasPages())
                <div class="admin-pagination-wrap">
                    {{ $providers->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .pam-page {
            --pam-text: var(--sp-color-text, #0f172a);
            --pam-muted: var(--sp-color-text-muted, #64748b);
            --pam-subtle: var(--sp-color-text-subtle, #94a3b8);
            --pam-border: var(--sp-color-border-strong, #dbe3ef);
            --pam-surface: var(--sp-color-surface, #ffffff);
            --pam-soft: var(--sp-color-surface-muted, #f8fafc);
        }

        .pam-summary-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1.25rem;
        }

        .pam-summary-card {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
            padding: 1rem;
            background: var(--pam-surface);
            border: 1px solid var(--pam-border);
            border-radius: var(--sp-radius-lg, 0.75rem);
            box-shadow: var(--sp-shadow-xs, 0 1px 2px rgba(15, 23, 42, 0.04));
        }

        .pam-summary-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.75rem;
            flex: 0 0 auto;
        }

        .pam-summary-card strong {
            display: block;
            color: var(--pam-text);
            font-size: 1.35rem;
            line-height: 1.1;
            font-weight: 900;
        }

        .pam-summary-card span:not(.pam-summary-icon) {
            display: block;
            color: var(--pam-muted);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .pam-tone-blue { background: #eff6ff; color: #1d4ed8; }
        .pam-tone-red { background: #fef2f2; color: #b91c1c; }
        .pam-tone-slate { background: #f1f5f9; color: #475569; }
        .pam-tone-amber { background: #fffbeb; color: #b45309; }
        .pam-tone-teal { background: #ecfdf5; color: #047857; }
        .pam-tone-green { background: #f0fdf4; color: #15803d; }

        .pam-attention-band {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding: 1.1rem 1.25rem;
            background: #12313f;
            color: #fff;
            border-radius: var(--sp-radius-xl, 1rem);
        }

        .pam-band-eyebrow {
            margin: 0 0 0.2rem;
            color: #9fe3c2;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .pam-attention-band h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 900;
            letter-spacing: 0;
        }

        .pam-issue-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            justify-content: flex-end;
        }

        .pam-issue-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 2.35rem;
            padding: 0.45rem 0.65rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.8rem;
        }

        .pam-issue-chip:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
        }

        .pam-issue-chip strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.6rem;
            height: 1.6rem;
            border-radius: 999px;
            background: #fff;
            color: #12313f;
            padding: 0 0.45rem;
        }

        .pam-filter-bar {
            display: grid;
            grid-template-columns: minmax(220px, 1.4fr) repeat(5, minmax(132px, 0.6fr)) auto;
            gap: 0.75rem;
            align-items: end;
        }

        .pam-field {
            display: grid;
            gap: 0.35rem;
            min-width: 0;
        }

        .pam-field span {
            color: var(--pam-muted);
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .pam-field input,
        .pam-field select {
            width: 100%;
            height: 2.75rem;
            border: 1px solid var(--pam-border);
            border-radius: var(--sp-radius-lg, 0.75rem);
            background: var(--pam-surface);
            color: var(--pam-text);
            padding: 0 0.85rem;
            font: inherit;
        }

        .pam-search-input {
            position: relative;
        }

        .pam-search-input i {
            position: absolute;
            inset-inline-start: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pam-subtle);
        }

        .pam-search-input input {
            padding-inline-start: 2.35rem;
        }

        .pam-filter-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            justify-content: flex-end;
        }

        .pam-table-card .table-responsive {
            overflow-x: auto;
        }

        .pam-table th,
        .pam-table td {
            vertical-align: middle;
        }

        .pam-provider-cell {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 260px;
        }

        .pam-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 0.85rem;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 900;
            flex: 0 0 auto;
            overflow: hidden;
        }

        .pam-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pam-provider-copy {
            display: grid;
            gap: 0.18rem;
            min-width: 0;
        }

        .pam-provider-copy a {
            color: var(--pam-text);
            font-weight: 900;
            text-decoration: none;
        }

        .pam-provider-copy a:hover {
            color: #0f766e;
        }

        .pam-provider-copy span,
        .pam-provider-copy small {
            color: var(--pam-muted);
            font-size: 0.8rem;
        }

        .pam-health-cell {
            display: grid;
            gap: 0.35rem;
            min-width: 150px;
        }

        .pam-health-head {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.82rem;
        }

        .pam-health-head span,
        .pam-health-cell small {
            color: var(--pam-muted);
            font-weight: 700;
        }

        .pam-health-good { color: #047857; }
        .pam-health-warn { color: #b45309; }
        .pam-health-bad { color: #b91c1c; }

        .pam-health-track {
            height: 0.5rem;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .pam-health-fill {
            display: block;
            height: 100%;
            border-radius: inherit;
        }

        .pam-health-fill-good { background: #10b981; }
        .pam-health-fill-warn { background: #f59e0b; }
        .pam-health-fill-bad { background: #ef4444; }

        .pam-leads,
        .pam-activity-cell {
            display: grid;
            gap: 0.25rem;
            min-width: 120px;
        }

        .pam-leads span,
        .pam-activity-cell span,
        .pam-activity-cell small {
            color: var(--pam-muted);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .pam-leads strong,
        .pam-activity-cell strong {
            color: var(--pam-text);
            font-weight: 900;
        }

        .pam-conversion {
            color: #047857 !important;
        }

        .pam-risk-list,
        .pam-account-flags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            min-width: 150px;
        }

        .pam-risk,
        .pam-flag {
            display: inline-flex;
            align-items: center;
            min-height: 1.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 900;
        }

        .pam-risk-bad,
        .pam-flag-bad {
            background: #fef2f2;
            color: #b91c1c;
        }

        .pam-risk-warn {
            background: #fffbeb;
            color: #b45309;
        }

        .pam-risk-good,
        .pam-flag-good {
            background: #ecfdf5;
            color: #047857;
        }

        .pam-flag-info {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .pam-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.45rem;
            min-width: 150px;
        }

        .pam-actions form {
            display: inline;
        }

        /* Listing status badges */
        .pam-status-cell {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.3rem;
            min-width: 110px;
        }

        .pam-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
            font-size: 0.74rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .pam-badge-on { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .pam-badge-off { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
        .pam-badge-verified { background: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
        .pam-badge-featured { background: rgba(245, 158, 11, 0.15); color: #b45309; }

        .pam-icon-action {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            height: 2.25rem;
            padding: 0 0.7rem;
            border: 1px solid var(--pam-border);
            border-radius: var(--sp-radius-md, 0.5rem);
            background: var(--pam-surface);
            color: var(--pam-muted);
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 900;
        }

        .pam-icon-action:hover {
            background: var(--pam-soft);
            color: var(--pam-text);
        }

        .pam-icon-primary {
            border-color: #0f766e;
            background: #0f766e;
            color: #fff;
        }

        .pam-icon-primary:hover {
            background: #115e59;
            color: #fff;
        }

        @media (max-width: 1280px) {
            .pam-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .pam-filter-bar {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .pam-field-search,
            .pam-filter-actions {
                grid-column: 1 / -1;
            }

            .pam-filter-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 768px) {
            .pam-summary-grid,
            .pam-filter-bar {
                grid-template-columns: 1fr;
            }

            .pam-attention-band {
                align-items: flex-start;
                flex-direction: column;
            }

            .pam-issue-links {
                justify-content: flex-start;
            }
        }
    </style>
@endpush
