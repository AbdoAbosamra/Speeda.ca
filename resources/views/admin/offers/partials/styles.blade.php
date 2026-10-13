@include('partials.offers.styles')
@once
@push('styles')
<style>
    .ao-page { --ao-line: #e6ecf3; --ao-muted: #64748b; --ao-ink: #0f172a; }
    .ao-hint { font-size: .84rem; color: var(--ao-muted); }
    .ao-error { color: #b91c1c; font-size: .8rem; font-weight: 600; }
    .ao-status { display: flex; gap: .65rem; align-items: center; padding: .85rem 1.1rem; border-radius: 14px; font-size: .92rem; margin-bottom: 1.25rem; border: 1px solid; }
    .ao-status--live { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .ao-status--off { background: #f1f5f9; border-color: #e2e8f0; color: #334155; }
    .ao-status--empty { background: #fffbeb; border-color: #fde68a; color: #92400e; }

    .ao-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(155px, 1fr)); gap: .85rem; margin-bottom: 1.25rem; }
    .ao-metric {
        display: grid; gap: .3rem; padding: .95rem 1.05rem; border-radius: 16px; background: #fff; text-decoration: none;
        border: 1px solid rgba(148, 163, 184, .18); box-shadow: 0 16px 28px -26px rgba(15, 23, 42, .3); transition: transform .2s, border-color .2s;
    }
    .ao-metric:hover { transform: translateY(-2px); }
    .ao-metric.is-active { border-color: currentColor; }
    .ao-metric span { font-size: .74rem; font-weight: 700; color: var(--ao-muted); text-transform: uppercase; letter-spacing: .05em; }
    .ao-metric strong { font-size: 1.75rem; font-weight: 800; color: var(--ao-ink); font-variant-numeric: tabular-nums; line-height: 1.1; }
    .ao-tone-green { color: #059669; } .ao-tone-green i { color: #059669; }
    .ao-tone-blue i { color: #2563eb; } .ao-tone-blue { color: #2563eb; }
    .ao-tone-slate i { color: #64748b; } .ao-tone-slate { color: #64748b; }
    .ao-tone-amber i { color: #d97706; } .ao-tone-amber { color: #d97706; }
    .ao-tone-indigo i { color: #4f46e5; } .ao-tone-indigo { color: #4f46e5; }
    .ao-tone-rose i { color: #e11d48; } .ao-tone-rose { color: #e11d48; }

    .ao-card { background: #fff; border: 1px solid rgba(148, 163, 184, .2); border-radius: 18px; box-shadow: 0 16px 28px -26px rgba(15, 23, 42, .3); }
    .ao-settings { margin-bottom: 1.25rem; }
    .ao-settings > summary { list-style: none; cursor: pointer; padding: 1rem 1.2rem; display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; align-items: center; }
    .ao-settings > summary::-webkit-details-marker { display: none; }
    .ao-settings > summary span { font-weight: 800; color: var(--ao-ink); }
    .ao-settings > summary small { color: var(--ao-muted); }
    .ao-settings[open] > summary { border-bottom: 1px solid var(--ao-line); }
    .ao-settings-body { padding: 1.2rem; display: grid; gap: 1rem; }
    .ao-locale-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
    .ao-locale { border: 1px solid var(--ao-line); border-radius: 14px; padding: .9rem; display: grid; gap: .7rem; position: relative; }
    .ao-locale-tag { font-size: .72rem; font-weight: 800; color: #4338ca; background: #eef2ff; border-radius: 6px; padding: 2px 8px; justify-self: start; }
    .ao-field { display: grid; gap: .35rem; min-width: 0; }
    .ao-field > label, .ao-label { font-weight: 700; font-size: .84rem; color: var(--ao-ink); }

    .ao-switch-row { display: flex; gap: .8rem; align-items: center; cursor: pointer; }
    .ao-switch-row small { display: block; color: var(--ao-muted); font-size: .8rem; }
    .ao-switch { position: relative; width: 44px; height: 25px; flex: none; display: inline-block; }
    .ao-switch input { position: absolute; opacity: 0; inset: 0; margin: 0; cursor: pointer; z-index: 1; }
    .ao-switch span, .ao-switch-btn span { position: absolute; inset: 0; background: #cbd5e1; border-radius: 999px; transition: .2s; }
    .ao-switch span::before, .ao-switch-btn span::before { content: ""; position: absolute; width: 19px; height: 19px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); }
    .ao-switch input:checked + span, .ao-switch-btn.is-on span { background: #059669; }
    .ao-switch input:checked + span::before, .ao-switch-btn.is-on span::before { transform: translateX(19px); }
    .ao-switch input:focus-visible + span { outline: 3px solid #bfdbfe; outline-offset: 2px; }
    .ao-switch-btn { border: 0; padding: 0; background: none; cursor: pointer; }

    .ao-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: .85rem; }
    .ao-tabs { display: flex; gap: .3rem; flex-wrap: wrap; background: #fff; border: 1px solid var(--ao-line); border-radius: 12px; padding: 4px; }
    .ao-tabs a { padding: .4rem .8rem; border-radius: 9px; text-decoration: none; color: #334155 !important; font-weight: 700; font-size: .85rem; }
    .ao-tabs a em { font-style: normal; color: var(--ao-muted); font-weight: 600; margin-inline-start: 2px; }
    .ao-tabs a.is-active { background: #2563eb; color: #fff !important; }
    .ao-tabs a.is-active em { color: rgba(255, 255, 255, .8); }
    .ao-search { display: flex; align-items: center; gap: .5rem; background: #fff; border: 1px solid var(--ao-line); border-radius: 12px; padding: 0 .8rem; min-width: 240px; }
    .ao-search i { color: var(--ao-muted); }
    .ao-search input { border: 0; outline: 0; padding: .55rem 0; flex: 1; background: transparent; }

    .ao-table td { vertical-align: middle; }
    .ao-table tr.is-dragging { opacity: .45; }
    .ao-handle { color: #94a3b8; cursor: grab; }
    .ao-offer { display: flex; gap: .75rem; align-items: center; min-width: 260px; }
    .ao-thumb { width: 66px; height: 44px; border-radius: 9px; overflow: hidden; flex: none; }
    .ao-thumb img, .ao-thumb .so-cover-fallback { width: 100%; height: 100%; object-fit: cover; font-size: 1.1rem; }
    .ao-langs { display: flex; gap: 3px; margin-top: 3px; }
    .ao-langs span { font-size: .6rem; font-weight: 800; padding: 1px 5px; border-radius: 4px; background: #f1f5f9; color: #94a3b8; }
    .ao-langs span.is-done { background: #ecfdf5; color: #059669; }
    .ao-warn { font-size: .7rem; font-weight: 700; color: #b45309; background: #fffbeb; padding: 1px 6px; border-radius: 4px; margin-inline-start: 4px; }
    .ao-deal { display: inline-block; font-weight: 800; font-size: .78rem; color: #fff; padding: .3rem .55rem; border-radius: 7px; white-space: nowrap; background: #e11d48; }
    .ao-deal--free { background: #059669; }
    .ao-deal--custom { background: #d97706; }
    .ao-dates { white-space: nowrap; font-size: .85rem; color: #334155; }
    .ao-pill { display: inline-flex; align-items: center; gap: .35rem; font-size: .76rem; font-weight: 800; padding: .3rem .65rem; border-radius: 999px; white-space: nowrap; }
    .ao-pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ao-pill--live { background: #ecfdf5; color: #059669; }
    .ao-pill--scheduled { background: #eff6ff; color: #2563eb; }
    .ao-pill--expired { background: #f1f5f9; color: #64748b; }
    .ao-pill--draft { background: #fff7ed; color: #c2410c; }
    .ao-pill--deleted { background: #fef2f2; color: #b91c1c; }
    .ao-pill--claimed { background: #eff6ff; color: #2563eb; }
    .ao-pill--redeemed { background: #ecfdf5; color: #059669; }
    .ao-pill--cancelled { background: #fef2f2; color: #b91c1c; }
    .ao-star { border: 0; background: none; font-size: 1.15rem; color: #cbd5e1; cursor: pointer; padding: .2rem; }
    .ao-star.is-on { color: #f59e0b; }
    .ao-nums { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .ao-nums b { color: var(--ao-ink); }
    .ao-nums small { color: var(--ao-muted); }
    .admin-row-actions form { display: inline; }
    .ao-seg { display: inline-flex; background: #f1f5f9; border-radius: 9px; padding: 3px; gap: 2px; }
    .ao-seg button { border: 0; background: none; padding: .25rem .6rem; border-radius: 7px; font-weight: 700; font-size: .75rem; color: #64748b; cursor: pointer; }
    .ao-seg button.is-on { background: #fff; color: #2563eb; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
</style>
@endpush
@endonce
