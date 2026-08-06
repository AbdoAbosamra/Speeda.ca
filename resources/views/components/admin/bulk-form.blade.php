@props([
    'action',                 // route to POST the bulk request to
    'actions' => [],          // [value => label] or [value => ['label'=>..,'confirm'=>..,'variant'=>..,'icon'=>..]]
    'label' => 'items',       // noun used in "N items selected"
])

{{--
    Bulk-action wrapper for an admin table.

    IMPORTANT: this does NOT wrap the table in a <form>. Admin rows already
    contain their own per-row <form> elements (approve, delete, toggle…), and
    nesting forms is invalid HTML — browsers silently drop the inner ones.

    Instead the table stays form-free, and a separate hidden form is submitted
    with the selected ids injected as hidden inputs at submit time.

    Usage:
        <x-admin.bulk-form :action="..." :actions="[...]">
            <table>
              <th><x-admin.bulk-checkbox master /></th>
              ...
              <td><x-admin.bulk-checkbox :value="$row->id" /></td>
            </table>
        </x-admin.bulk-form>
--}}

<div x-data="adminBulkForm()" x-ref="bulkScope">
    {{ $slot }}

    {{-- The real submission target, kept outside the table. --}}
    <form method="POST" action="{{ $action }}" x-ref="bulkForm" class="d-none">
        @csrf
        <input type="hidden" name="bulk_action" x-ref="bulkAction">
        <div x-ref="bulkIds"></div>
    </form>

    {{-- Sticky action bar, only visible once something is selected --}}
    <div class="admin-bulk-bar" x-show="count > 0" x-cloak x-transition>
        <div class="admin-bulk-bar-inner">
            <span class="admin-bulk-count">
                <strong x-text="count"></strong>
                <span>{{ $label }} {{ __('admin.bulk_selected') }}</span>
            </span>

            <button type="button" class="admin-bulk-clear" @click="clearAll()">
                <i class="fas fa-xmark"></i> {{ __('admin.bulk_clear') }}
            </button>

            <div class="admin-bulk-actions">
                @foreach($actions as $value => $config)
                    @php
                        $cfg = is_array($config) ? $config : ['label' => $config];
                    @endphp
                    <button type="button"
                            class="admin-bulk-btn admin-bulk-btn-{{ $cfg['variant'] ?? 'default' }}"
                            @click="submitAction(@js($value), @js($cfg['confirm'] ?? null))">
                        @isset($cfg['icon'])<i class="fas {{ $cfg['icon'] }}"></i>@endisset
                        <span>{{ $cfg['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function adminBulkForm() {
                return {
                    count: 0,

                    boxes() {
                        return Array.from(this.$refs.bulkScope.querySelectorAll('[data-bulk-id]'));
                    },

                    checked() {
                        return this.boxes().filter(b => b.checked);
                    },

                    recount() {
                        const all = this.boxes();
                        this.count = all.filter(b => b.checked).length;

                        const master = this.$refs.bulkScope.querySelector('[data-bulk-master]');
                        if (master) {
                            master.checked = all.length > 0 && this.count === all.length;
                            master.indeterminate = this.count > 0 && this.count < all.length;
                        }
                    },

                    toggleAll(event) {
                        this.boxes().forEach(b => { b.checked = event.target.checked; });
                        this.recount();
                    },

                    clearAll() {
                        this.boxes().forEach(b => { b.checked = false; });
                        const master = this.$refs.bulkScope.querySelector('[data-bulk-master]');
                        if (master) { master.checked = false; master.indeterminate = false; }
                        this.recount();
                    },

                    submitAction(action, confirmMessage) {
                        const selected = this.checked();
                        if (selected.length === 0) return;

                        if (confirmMessage) {
                            const text = String(confirmMessage).replace(':count', selected.length);
                            if (!window.confirm(text)) return;
                        }

                        // Build the payload fresh on every submit.
                        const holder = this.$refs.bulkIds;
                        holder.innerHTML = '';
                        selected.forEach(box => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = box.getAttribute('data-bulk-id');
                            holder.appendChild(input);
                        });

                        this.$refs.bulkAction.value = action;
                        this.$refs.bulkForm.submit();
                    },
                };
            }
        </script>
    @endpush

    @push('styles')
        <style>
            [x-cloak] { display: none !important; }

            .admin-bulk-bar {
                position: sticky;
                bottom: 0;
                z-index: 20;
                padding: 0.75rem 1rem;
            }

            .admin-bulk-bar-inner {
                display: flex;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
                padding: 0.75rem 1.15rem;
                background: #0f172a;
                color: #fff;
                border-radius: 14px;
                box-shadow: 0 18px 40px -18px rgba(15, 23, 42, 0.7);
            }

            .admin-bulk-count { font-size: 0.9rem; }
            .admin-bulk-count strong { font-size: 1.05rem; margin-inline-end: 0.25rem; }

            .admin-bulk-clear {
                background: transparent;
                border: 1px solid rgba(255, 255, 255, 0.25);
                color: #cbd5e1;
                border-radius: 99px;
                padding: 0.25rem 0.75rem;
                font-size: 0.8rem;
                cursor: pointer;
            }
            .admin-bulk-clear:hover { color: #fff; border-color: rgba(255, 255, 255, 0.5); }

            .admin-bulk-actions {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
                margin-inline-start: auto;
            }

            .admin-bulk-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.45rem 0.95rem;
                border-radius: 10px;
                border: none;
                font-size: 0.85rem;
                font-weight: 700;
                cursor: pointer;
                background: rgba(255, 255, 255, 0.12);
                color: #fff;
            }
            .admin-bulk-btn:hover { background: rgba(255, 255, 255, 0.22); }
            .admin-bulk-btn-success { background: #059669; }
            .admin-bulk-btn-success:hover { background: #047857; }
            .admin-bulk-btn-danger { background: #dc2626; }
            .admin-bulk-btn-danger:hover { background: #b91c1c; }
            .admin-bulk-btn-warning { background: #d97706; }
            .admin-bulk-btn-warning:hover { background: #b45309; }

            .admin-bulk-check { width: 1.05rem; height: 1.05rem; cursor: pointer; }

            @media (max-width: 768px) {
                .admin-bulk-actions { margin-inline-start: 0; width: 100%; }
                .admin-bulk-btn { flex: 1; justify-content: center; }
            }
        </style>
    @endpush
@endonce
