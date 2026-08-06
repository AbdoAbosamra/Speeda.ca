@extends('layouts.app')

@section('title', __('admin.edit_provider') . ': ' . $provider->company_name)

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Providers"
                :title="__('admin.edit_provider')"
                :subtitle="$provider->company_name ?: ($provider->user->name ?? 'Provider #' . $provider->id)"
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('service-providers.show', $provider->id)"
                        variant="secondary"
                        icon="fas fa-arrow-up-right-from-square"
                        class="admin-btn admin-btn-secondary"
                    >
                        {{ __('admin.view_public_profile') }}
                    </x-ui.button>
                    <x-ui.button
                        :href="route('admin.provider_activity_monitor.show', $provider->id)"
                        variant="secondary"
                        icon="fas fa-chart-line"
                        class="admin-btn admin-btn-secondary"
                    >
                        {{ __('admin.analytics_label') }}
                    </x-ui.button>
                    <x-ui.button
                        :href="route('admin.provider_activity_monitor.index')"
                        variant="secondary"
                        icon="fas fa-arrow-left"
                        class="admin-btn admin-btn-secondary"
                    >
                        {{ __('admin.back_to_list') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            {{-- Owner account context: the listing flag and the account flag are
                 independent, so make the interaction explicit. --}}
            <div class="prov-owner-card">
                <div>
                    <span class="prov-owner-label">{{ __('admin.owner_account') }}</span>
                    <strong>{{ $provider->user->name ?? '—' }}</strong>
                    <span class="prov-owner-email">{{ $provider->user->email ?? '' }}</span>
                </div>
                <div class="prov-owner-status">
                    @if($provider->user?->is_active)
                        <span class="prov-pill prov-pill-ok"><i class="fas fa-circle-check"></i> {{ __('admin.account_active') }}</span>
                    @else
                        <span class="prov-pill prov-pill-bad"><i class="fas fa-circle-xmark"></i> {{ __('admin.account_inactive') }}</span>
                    @endif
                    @if($provider->user)
                        <a href="{{ route('admin.users.edit', $provider->user) }}" class="prov-link">
                            {{ __('admin.manage_account') }} <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            @if(!$provider->user?->is_active)
                <div class="prov-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    {{ __('admin.provider_hidden_by_account') }}
                </div>
            @endif

            <form action="{{ route('admin.providers.update', $provider) }}" method="POST" class="prov-form">
                @csrf
                @method('PATCH')

                <div class="prov-grid">
                    <div class="prov-card">
                        <h3>{{ __('admin.provider_details') }}</h3>

                        <div class="prov-field">
                            <label for="company_name">{{ __('admin.company_name') }} *</label>
                            <input type="text" name="company_name" id="company_name" maxlength="255" required
                                   value="{{ old('company_name', $provider->company_name) }}">
                            @error('company_name') <span class="prov-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="prov-field-row">
                            <div class="prov-field">
                                <label for="category_id">{{ __('admin.category') }}</label>
                                <select name="category_id" id="category_id">
                                    <option value="">—</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            @selected((string) old('category_id', $provider->category_id) === (string) $category->id)>
                                            {{ $category->localized_name ?? $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="prov-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="prov-field">
                                <label for="location_id">{{ __('admin.location') }}</label>
                                <select name="location_id" id="location_id">
                                    <option value="">—</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}"
                                            @selected((string) old('location_id', $provider->location_id) === (string) $location->id)>
                                            {{ $location->city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id') <span class="prov-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="prov-field-row">
                            <div class="prov-field">
                                <label for="phone">{{ __('admin.phone') }}</label>
                                <input type="text" name="phone" id="phone" maxlength="30"
                                       value="{{ old('phone', $provider->phone) }}">
                                @error('phone') <span class="prov-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="prov-field">
                                <label for="whatsapp_number">{{ __('admin.whatsapp_number') }}</label>
                                <input type="text" name="whatsapp_number" id="whatsapp_number" maxlength="30"
                                       value="{{ old('whatsapp_number', $provider->whatsapp_number) }}">
                                @error('whatsapp_number') <span class="prov-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="prov-field">
                                <label for="experience_years">{{ __('admin.experience_years') }}</label>
                                <input type="number" name="experience_years" id="experience_years" min="0" max="80"
                                       value="{{ old('experience_years', $provider->experience_years) }}">
                                @error('experience_years') <span class="prov-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="prov-field">
                            <label for="address">{{ __('admin.address') }}</label>
                            <input type="text" name="address" id="address" maxlength="500"
                                   value="{{ old('address', $provider->address) }}">
                            @error('address') <span class="prov-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="prov-field">
                            <label for="bio">{{ __('admin.bio') }}</label>
                            <textarea name="bio" id="bio" rows="6" maxlength="5000">{{ old('bio', $provider->bio) }}</textarea>
                            @error('bio') <span class="prov-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="prov-card">
                        <h3>{{ __('admin.listing_status') }}</h3>

                        <label class="prov-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   @checked(old('is_active', $provider->is_active))>
                            <span>
                                <strong>{{ __('admin.listing_visible') }}</strong>
                                <small>{{ __('admin.listing_visible_hint') }}</small>
                            </span>
                        </label>

                        <label class="prov-switch">
                            <input type="hidden" name="is_verified" value="0">
                            <input type="checkbox" name="is_verified" value="1"
                                   @checked(old('is_verified', $provider->is_verified))>
                            <span>
                                <strong>{{ __('admin.verified') }}</strong>
                                <small>{{ __('admin.verified_hint') }}</small>
                            </span>
                        </label>

                        <label class="prov-switch">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1"
                                   @checked(old('is_featured', $provider->is_featured))>
                            <span>
                                <strong>{{ __('admin.featured') }}</strong>
                                <small>{{ __('admin.featured_hint') }}</small>
                            </span>
                        </label>

                        <div class="prov-meta">
                            <div><span>{{ __('admin.profile_completion') }}</span><strong>{{ (int) $provider->profile_completion_percent }}%</strong></div>
                            <div><span>{{ __('admin.rating') }}</span><strong>{{ number_format((float) ($provider->calculated_rating ?? 0), 2) }}</strong></div>
                            <div><span>{{ __('admin.views') }}</span><strong>{{ number_format((int) $provider->views) }}</strong></div>
                            <div><span>{{ __('admin.created_at') }}</span><strong>{{ optional($provider->created_at)->format('M d, Y') ?: '—' }}</strong></div>
                        </div>

                        <button type="submit" class="admin-btn admin-btn-primary text-white prov-save">
                            <i class="fas fa-save"></i> {{ __('admin.save') }}
                        </button>
                    </div>
                </div>
            </form>

            {{-- Danger zone --}}
            <div class="prov-danger">
                <div>
                    <h4>{{ __('admin.delete_provider') }}</h4>
                    <p>{{ __('admin.delete_provider_warning') }}</p>
                </div>
                <form action="{{ route('admin.providers.destroy', $provider) }}" method="POST"
                      onsubmit="return confirm('{{ __('admin.confirm_delete_provider') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn-danger">
                        <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .prov-owner-card {
            display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1rem 1.25rem; margin-bottom: 1rem;
        }
        .prov-owner-label { display: block; font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; }
        .prov-owner-card strong { font-size: 1rem; color: #0f172a; margin-inline-end: .5rem; }
        .prov-owner-email { color: #64748b; font-size: .875rem; }
        .prov-owner-status { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
        .prov-pill { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .75rem; border-radius: 99px; font-size: .8rem; font-weight: 700; }
        .prov-pill-ok { background: #ecfdf5; color: #047857; }
        .prov-pill-bad { background: #fef2f2; color: #b91c1c; }
        .prov-link { color: #2563eb; font-weight: 700; font-size: .85rem; text-decoration: none; }

        .prov-warning {
            display: flex; align-items: center; gap: .6rem; padding: .85rem 1.15rem; margin-bottom: 1.25rem;
            border-radius: 14px; background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.35); color: #b45309; font-size: .9rem;
        }

        .prov-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; align-items: start; }
        @media (max-width: 992px) { .prov-grid { grid-template-columns: 1fr; } }

        .prov-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .prov-card h3 { font-size: 1rem; font-weight: 800; color: #1e293b; margin: 0 0 1.25rem; }

        .prov-field { margin-bottom: 1rem; flex: 1; min-width: 160px; }
        .prov-field label { display: block; margin-bottom: .35rem; font-size: .82rem; font-weight: 700; color: #475569; }
        .prov-field input, .prov-field select, .prov-field textarea {
            width: 100%; padding: .6rem .85rem; border: 1px solid #cbd5e1; border-radius: 12px;
            font-size: .9375rem; color: #0f172a; background: #fff;
        }
        .prov-field textarea { resize: vertical; }
        .prov-field-row { display: flex; gap: 1rem; flex-wrap: wrap; }
        .prov-error { display: block; margin-top: .3rem; font-size: .8rem; color: #dc2626; }

        .prov-switch { display: flex; align-items: flex-start; gap: .7rem; padding: .8rem 0; border-bottom: 1px dashed #e2e8f0; cursor: pointer; }
        .prov-switch input[type="checkbox"] { width: 18px; height: 18px; margin-top: .15rem; flex-shrink: 0; }
        .prov-switch strong { display: block; font-size: .9rem; color: #0f172a; }
        .prov-switch small { display: block; font-size: .78rem; color: #64748b; margin-top: .15rem; }

        .prov-meta { margin: 1.25rem 0; display: grid; gap: .5rem; }
        .prov-meta div { display: flex; justify-content: space-between; font-size: .85rem; }
        .prov-meta span { color: #64748b; }
        .prov-meta strong { color: #0f172a; }

        .prov-save { width: 100%; justify-content: center; }

        .prov-danger {
            display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
            margin-top: 1.5rem; padding: 1.25rem 1.5rem; background: #fff;
            border: 1px solid #fecaca; border-inline-start: 5px solid #ef4444; border-radius: 16px;
        }
        .prov-danger h4 { margin: 0 0 .25rem; font-size: 1rem; font-weight: 800; color: #b91c1c; }
        .prov-danger p { margin: 0; font-size: .85rem; color: #64748b; }
        .admin-btn-danger { background: #dc2626; color: #fff; border: none; }
        .admin-btn-danger:hover { background: #b91c1c; color: #fff; }
    </style>
@endsection
