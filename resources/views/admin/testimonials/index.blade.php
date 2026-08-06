@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Testimonials"
                title="Speeda Testimonials"
                subtitle="Provider testimonials shown as cards on the home page (before the coverage map)."
            >
                <x-slot:actions>
                    <x-ui.button
                        type="button"
                        icon="fas fa-plus"
                        class="admin-btn admin-btn-primary text-white"
                        x-data
                        onclick="openTestimonialModal()"
                    >
                        Add Testimonial
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            {{-- Status banner: home section only shows with exactly 3 active --}}
            @php($isReady = $activeCount === \App\Models\SiteTestimonial::DISPLAY_COUNT)
            <div class="admin-testimonial-status {{ $isReady ? 'is-ready' : 'is-warning' }}">
                <i class="fas {{ $isReady ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                @if($isReady)
                    <span>{{ $activeCount }} active testimonials — the home section is live.</span>
                @else
                    <span>{{ $activeCount }} active testimonial(s). The home section shows only when <strong>exactly 3</strong> are active; otherwise it is hidden.</span>
                @endif
            </div>

            <x-admin.bulk-form
                :action="route('admin.testimonials.bulk')"
                label="testimonials"
                :actions="[
                    'activate'   => ['label' => __('admin.activate_bulk'), 'icon' => 'fa-eye', 'variant' => 'success'],
                    'deactivate' => ['label' => __('admin.deactivate_bulk'), 'icon' => 'fa-eye-slash', 'variant' => 'warning'],
                    'delete'     => ['label' => __('admin.delete'), 'icon' => 'fa-trash', 'variant' => 'danger', 'confirm' => __('admin.bulk_confirm_delete')],
                ]"
            >
            <x-admin.table-card>
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th style="width:1%;"><x-admin.bulk-checkbox master /></th>
                            <th>Provider</th>
                            <th>Rating</th>
                            <th>Testimonial</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($testimonials as $testimonial)
                            <tr>
                                <td><x-admin.bulk-checkbox :value="$testimonial->id" /></td>
                                <td>
                                    {{-- Photo + city come from the linked provider. --}}
                                    <div class="tst-provider">
                                        @if($testimonial->display_photo)
                                            <img class="tst-avatar" src="{{ $testimonial->display_photo }}"
                                                 alt="{{ $testimonial->display_name }}" loading="lazy">
                                        @else
                                            <span class="tst-avatar tst-avatar-initial">{{ $testimonial->display_initial }}</span>
                                        @endif
                                        <div class="tst-provider-meta">
                                            <div class="admin-table-title">{{ $testimonial->display_name }}</div>
                                            <div class="admin-table-subtitle">
                                                @if($testimonial->display_city)
                                                    <i class="fas fa-location-dot"></i> {{ $testimonial->display_city }}
                                                @else
                                                    <span class="tst-muted">{{ __('admin.no_city') }}</span>
                                                @endif
                                                @if($testimonial->display_title)
                                                    · {{ $testimonial->display_title }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="testimonial-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa{{ $i <= $testimonial->rating ? 's' : 'r' }} fa-star"></i>
                                        @endfor
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-table-subtitle">{{ Str::limit($testimonial->testimonial_text, 90) }}</div>
                                </td>
                                <td>{{ $testimonial->sort_order }}</td>
                                <td>
                                    <x-ui.badge
                                        :variant="$testimonial->is_active ? 'success' : 'warning'"
                                        class="admin-badge {{ $testimonial->is_active ? 'admin-badge-published' : 'admin-badge-draft' }}"
                                    >
                                        {{ $testimonial->is_active ? 'Active' : 'Hidden' }}
                                    </x-ui.badge>
                                </td>
                                <td>
                                    <div class="admin-row-actions">
                                        <button type="button" class="admin-icon-action"
                                            onclick='openTestimonialModal(@json($testimonial))'>
                                            <i class="fas fa-pen"></i>
                                            <span>Edit</span>
                                        </button>
                                        <form action="{{ route('admin.testimonials.toggle', $testimonial) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="admin-icon-action">
                                                <i class="fas {{ $testimonial->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                <span>{{ $testimonial->is_active ? 'Hide' : 'Show' }}</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST"
                                            onsubmit="return confirm('Delete this testimonial?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-icon-action admin-icon-danger">
                                                <i class="fas fa-trash"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state
                                        icon="fas fa-quote-right"
                                        title="No testimonials yet"
                                        description="Add testimonials collected from your service providers to feature them on the home page."
                                        class="admin-empty-state"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-card>
            </x-admin.bulk-form>
        </div>
    </div>

    {{-- Create / Edit modal --}}
    <div class="testimonial-modal-backdrop" id="testimonialModal" role="dialog" aria-modal="true">
        <div class="testimonial-modal">
            <div class="testimonial-modal-header">
                <h5 id="testimonialModalTitle">Add Testimonial</h5>
                <button type="button" class="testimonial-modal-close" onclick="closeTestimonialModal()" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="testimonialForm" action="{{ route('admin.testimonials.store') }}">
                @csrf
                <input type="hidden" name="_method" id="testimonialMethod" value="POST">
                {{-- Carried through validation failures so the modal can reopen in edit mode. --}}
                <input type="hidden" name="testimonial_id" id="testimonialId" value="">

                @if($errors->any())
                    <div class="testimonial-errors">
                        <strong><i class="fas fa-triangle-exclamation"></i> Please fix the following:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- The provider is picked from the live list; name, photo and
                     city are derived from it rather than retyped. --}}
                <div class="testimonial-field">
                    <label for="service_provider_id">{{ __('admin.testimonial_provider') }} <span class="req">*</span></label>
                    <select name="service_provider_id" id="service_provider_id" required>
                        <option value="">{{ __('admin.testimonial_choose_provider') }}</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider['id'] }}">
                                {{ $provider['name'] }}@if($provider['city']) — {{ $provider['city'] }}@endif
                            </option>
                        @endforeach
                    </select>
                    <small class="testimonial-hint">{{ __('admin.testimonial_provider_hint') }}</small>
                </div>

                <div class="testimonial-field">
                    <label for="provider_title">{{ __('admin.testimonial_title_override') }} <span class="opt">({{ __('admin.optional') }})</span></label>
                    <input type="text" name="provider_title" id="provider_title" maxlength="255">
                    <small class="testimonial-hint">{{ __('admin.testimonial_title_hint') }}</small>
                </div>

                <div class="testimonial-field">
                    <label for="rating">Rating <span class="req">*</span></label>
                    <select name="rating" id="rating" required>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>

                <div class="testimonial-field">
                    <label for="testimonial_text">Testimonial <span class="req">*</span></label>
                    <textarea name="testimonial_text" id="testimonial_text" rows="4" maxlength="1000" required></textarea>
                </div>

                <div class="testimonial-field-row">
                    <div class="testimonial-field">
                        <label for="sort_order">Sort Order</label>
                        {{-- 1-based: position 0 reads wrong to an admin. --}}
                        <input type="number" name="sort_order" id="sort_order" min="1" max="65535"
                               value="{{ $nextSortOrder }}" data-default="{{ $nextSortOrder }}">
                    </div>
                    <div class="testimonial-field testimonial-check">
                        <label class="testimonial-switch">
                            <input type="checkbox" name="is_active" id="is_active" value="1">
                            <span>Active (visible on home)</span>
                        </label>
                    </div>
                </div>

                <div class="testimonial-modal-actions">
                    <button type="button" class="admin-btn admin-btn-secondary" onclick="closeTestimonialModal()">Cancel</button>
                    <button type="submit" class="admin-btn admin-btn-primary text-white">Save Testimonial</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .admin-testimonial-status {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.85rem 1.15rem;
        margin-bottom: 1.5rem;
        border-radius: var(--sp-radius-lg);
        font-size: 0.9rem;
        border: 1px solid var(--sp-color-border);
    }
    .admin-testimonial-status.is-ready {
        background: rgba(16, 185, 129, 0.08);
        border-color: rgba(16, 185, 129, 0.35);
        color: var(--sp-color-success);
    }
    .admin-testimonial-status.is-warning {
        background: rgba(245, 158, 11, 0.08);
        border-color: rgba(245, 158, 11, 0.35);
        color: var(--sp-color-warning);
    }
    .admin-testimonial-status strong { font-weight: 700; }

    .testimonial-stars { color: #F59E0B; letter-spacing: 1px; white-space: nowrap; }

    .testimonial-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 31, 61, 0.55);
        z-index: 1080;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .testimonial-modal-backdrop.open { display: flex; }

    .testimonial-modal {
        background: var(--sp-color-surface);
        border-radius: var(--sp-radius-xl);
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 1.5rem;
        box-shadow: var(--sp-shadow-lg, 0 20px 60px rgba(15,31,61,0.3));
    }
    .testimonial-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .testimonial-modal-header h5 { margin: 0; font-weight: 700; color: var(--sp-color-text); }
    .testimonial-modal-close {
        background: none; border: none; cursor: pointer;
        color: var(--sp-color-text-subtle); font-size: 1.1rem;
    }

    .testimonial-errors {
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: var(--sp-radius-lg);
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: var(--sp-color-danger, #b91c1c);
        font-size: 0.875rem;
    }
    .testimonial-errors ul { margin: 0.4rem 0 0; padding-inline-start: 1.1rem; }

    /* Provider cell: avatar + name + city */
    .tst-provider { display: flex; align-items: center; gap: 0.75rem; }
    .tst-avatar {
        width: 44px; height: 44px; border-radius: 12px; object-fit: cover;
        flex-shrink: 0; background: var(--sp-color-surface-muted, #f1f5f9);
    }
    .tst-avatar-initial {
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; color: var(--sp-color-primary, #4f46e5);
        background: rgba(99, 102, 241, 0.12);
    }
    .tst-provider-meta { min-width: 0; }
    .tst-provider-meta .admin-table-subtitle i { margin-inline-end: 0.2rem; opacity: 0.7; }
    .tst-muted { color: var(--sp-color-text-subtle, #94a3b8); }

    .testimonial-hint {
        display: block; margin-top: 0.3rem;
        font-size: 0.78rem; color: var(--sp-color-text-subtle, #94a3b8);
    }

    .testimonial-field { margin-bottom: 1rem; }
    .testimonial-field label { display: block; margin-bottom: 0.35rem; font-size: 0.85rem; font-weight: 600; color: var(--sp-color-text-muted); }
    .testimonial-field .req { color: var(--sp-color-danger, #ef4444); }
    .testimonial-field .opt { color: var(--sp-color-text-subtle); font-weight: 400; }
    .testimonial-field input[type="text"],
    .testimonial-field input[type="number"],
    .testimonial-field select option,
    .testimonial-field select,
    .testimonial-field textarea {
        width: 100%;
        padding: 0.6rem 0.85rem;
        border: 1px solid var(--sp-color-border-strong);
        border-radius: var(--sp-radius-lg);
        background: var(--sp-color-surface);
        color: var(--sp-color-text);
        font-size: 0.9375rem;
    }
    .testimonial-field textarea { resize: vertical; }

    .testimonial-field-row { display: flex; gap: 1rem; flex-wrap: wrap; }
    .testimonial-field-row .testimonial-field { flex: 1; min-width: 160px; }
    .testimonial-check { display: flex; align-items: flex-end; }
    .testimonial-switch { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
    .testimonial-switch input { width: auto; }

    .testimonial-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }
    </style>

    <script>
        function openTestimonialModal(data = null) {
            const modal = document.getElementById('testimonialModal');
            const form = document.getElementById('testimonialForm');
            const title = document.getElementById('testimonialModalTitle');
            const method = document.getElementById('testimonialMethod');

            form.reset();

            if (data) {
                title.textContent = 'Edit Testimonial';
                method.value = 'PATCH';
                form.action = '{{ url('admin/testimonials') }}/' + data.id;
                document.getElementById('testimonialId').value = data.id;
                document.getElementById('service_provider_id').value = data.service_provider_id ?? '';
                document.getElementById('provider_title').value = data.provider_title ?? '';
                document.getElementById('rating').value = data.rating ?? 5;
                document.getElementById('testimonial_text').value = data.testimonial_text ?? '';
                document.getElementById('sort_order').value = data.sort_order ?? 1;
                document.getElementById('is_active').checked = !!data.is_active;
            } else {
                title.textContent = 'Add Testimonial';
                method.value = 'POST';
                form.action = '{{ route('admin.testimonials.store') }}';
                document.getElementById('testimonialId').value = '';
                document.getElementById('is_active').checked = true;
                // New entries land at the end of the list.
                const orderInput = document.getElementById('sort_order');
                orderInput.value = orderInput.dataset.default || 1;
            }

            modal.classList.add('open');
        }

        function closeTestimonialModal() {
            document.getElementById('testimonialModal').classList.remove('open');
        }

        document.getElementById('testimonialModal').addEventListener('click', function (e) {
            if (e.target === this) closeTestimonialModal();
        });

        {{-- Validation failures redirect back with the modal closed, which used to
             hide the errors completely. Reopen it and restore what was typed. --}}
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function () {
                @if(old('_method') === 'PATCH' && old('testimonial_id'))
                    openTestimonialModal({ id: {{ (int) old('testimonial_id') }} });
                    document.getElementById('testimonialMethod').value = 'PATCH';
                    document.getElementById('testimonialForm').action =
                        '{{ url('admin/testimonials') }}/' + {{ (int) old('testimonial_id') }};
                @else
                    openTestimonialModal();
                @endif

                document.getElementById('service_provider_id').value = @json(old('service_provider_id', ''));
                document.getElementById('provider_title').value   = @json(old('provider_title', ''));
                document.getElementById('rating').value           = @json(old('rating', 5));
                document.getElementById('testimonial_text').value = @json(old('testimonial_text', ''));
                document.getElementById('sort_order').value       = @json(old('sort_order', $nextSortOrder));
                document.getElementById('is_active').checked      = {{ old('is_active') ? 'true' : 'false' }};
            });
        @endif
    </script>
@endsection
