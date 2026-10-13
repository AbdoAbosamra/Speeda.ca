{{-- Shared Local Offers styles: card, carousel, pages. Pushed once per request. --}}
@once
@push('styles')
<style>
    :root {
        --so-blue: #2563eb;
        --so-blue-dk: #1d4ed8;
        --so-blue-soft: #eff6ff;
        --so-blue-line: #dbeafe;
        --so-red: #e11d48;
        --so-green: #059669;
        --so-gold-ink: #92400e;
        --so-gold-soft: #fef3c7;
        --so-ink: #0f172a;
        --so-body: #334155;
        --so-muted: #64748b;
        --so-line: #e6ecf3;
        --so-line-2: #eef2f6;
        --so-bg: #f4f7fb;
        --so-ease: cubic-bezier(.16, 1, .3, 1);
    }

    /* ---------- Card ---------- */
    .so-card {
        background: #fff;
        border: 1px solid var(--so-line);
        border-radius: 18px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-width: 0;
        transition: transform .3s var(--so-ease), box-shadow .3s var(--so-ease);
    }
    .so-card:hover { transform: translateY(-4px); box-shadow: 0 24px 44px -24px rgba(37, 99, 235, .35); }
    .so-card-media { position: relative; aspect-ratio: 16 / 10; overflow: hidden; display: block; }
    .so-card-media img, .so-card-media .so-cover-fallback {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: transform .6s var(--so-ease);
    }
    .so-card:hover .so-card-media img, .so-card:hover .so-card-media .so-cover-fallback { transform: scale(1.06); }
    .so-cover-fallback { display: grid !important; place-items: center; color: rgba(255, 255, 255, .9); font-size: 3.4rem; position: relative; }
    .so-cover-fallback::before, .so-cover-fallback::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .1); }
    .so-cover-fallback::before { width: 180px; height: 180px; top: -60px; inset-inline-end: -40px; }
    .so-cover-fallback::after { width: 120px; height: 120px; bottom: -50px; inset-inline-start: -20px; }

    .so-badge {
        position: absolute; top: 12px; inset-inline-start: 12px;
        font-weight: 800; font-size: .95rem; line-height: 1; letter-spacing: .01em;
        color: #fff; padding: 9px 12px; border-radius: 10px;
        box-shadow: 0 8px 18px -8px rgba(0, 0, 0, .45);
        background: linear-gradient(135deg, #f43f5e, #e11d48);
    }
    .so-badge--free { background: linear-gradient(135deg, #10b981, #059669); }
    .so-badge--custom { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .so-urgent {
        position: absolute; top: 12px; inset-inline-end: 12px;
        font-size: .72rem; font-weight: 600; line-height: 1;
        background: rgba(15, 23, 42, .8); color: #fff; padding: 7px 9px; border-radius: 8px;
        display: inline-flex; align-items: center; gap: 6px; backdrop-filter: blur(4px);
    }
    .so-urgent::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: #fb923c; }

    .so-card-body { padding: 0 16px 16px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
    .so-card-provider { display: flex; align-items: flex-start; gap: 10px; margin-top: -22px; position: relative; z-index: 1; min-width: 0; }
    .so-logo {
        width: 46px; height: 46px; border-radius: 13px; flex: none; overflow: hidden;
        background: #fff; border: 3px solid #fff; box-shadow: 0 6px 14px -6px rgba(15, 31, 61, .35);
        display: grid; place-items: center;
    }
    .so-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 9px; }
    .so-logo span { width: 100%; height: 100%; border-radius: 9px; display: grid; place-items: center; color: #fff; font-weight: 800; font-size: .9rem; }
    .so-card-provider-text { min-width: 0; padding-top: 27px; }
    .so-card-provider-text strong { display: block; font-size: .92rem; color: var(--so-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .so-card-provider-text small { display: block; font-size: .68rem; color: var(--so-muted); text-transform: uppercase; letter-spacing: .06em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    [dir="rtl"] .so-card-provider-text small { letter-spacing: 0; }
    .so-tag {
        align-self: flex-start; justify-self: start; font-size: .66rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase;
        color: var(--so-gold-ink); background: var(--so-gold-soft); padding: 4px 9px; border-radius: 6px;
    }
    [dir="rtl"] .so-tag { letter-spacing: 0; font-size: .74rem; }
    .so-card-title { font-size: 1.06rem; font-weight: 700; color: var(--so-ink); line-height: 1.3; margin: 0; }
    .so-card-title a { color: inherit !important; text-decoration: none; }
    .so-card-title a::after { content: ""; position: absolute; inset: 0; }
    .so-card { position: relative; }
    .so-card-text {
        margin: 0; font-size: .88rem; line-height: 1.5; color: var(--so-muted); min-height: 2.65rem;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .so-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: .78rem; font-weight: 500; color: var(--so-muted); }
    .so-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .so-meta i { color: var(--so-blue); }
    .so-meta .so-sep { width: 1px; height: 14px; background: var(--so-line); }
    .so-meta .so-meta-likes i { color: var(--so-red); }
    .so-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        border: 0; border-radius: 12px; padding: 11px 16px; font-weight: 600; font-size: .95rem;
        text-decoration: none; cursor: pointer; transition: background .2s, transform .2s, box-shadow .2s;
    }
    .so-btn:focus-visible { outline: 3px solid var(--so-blue-line); outline-offset: 2px; }
    .so-btn-primary { background: var(--so-blue); color: #fff !important; }
    .so-btn-primary:hover { background: var(--so-blue-dk); color: #fff !important; }
    .so-btn-ghost { background: var(--so-blue-soft); color: var(--so-blue) !important; }
    .so-btn-ghost:hover { background: var(--so-blue-line); color: var(--so-blue-dk) !important; }
    .so-btn-wa { background: #16a34a; color: #fff !important; box-shadow: 0 10px 22px -12px rgba(22, 163, 74, .8); }
    .so-btn-wa:hover { background: #15803d; color: #fff !important; }
    .so-btn[disabled] { opacity: .6; cursor: progress; }
    .so-card .so-btn { margin-top: auto; width: 100%; position: relative; z-index: 2; }
    .so-card .so-btn i { transition: transform .2s; }
    .so-card:hover .so-btn i { transform: translateX(3px); }
    [dir="rtl"] .so-card .so-btn i { transform: scaleX(-1); }
    [dir="rtl"] .so-card:hover .so-btn i { transform: scaleX(-1) translateX(3px); }

    /* ---------- Home section ---------- */
    .so-home-head { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .so-home-icon {
        width: 58px; height: 58px; border-radius: 16px; flex: none; display: grid; place-items: center;
        background: linear-gradient(140deg, var(--so-blue-soft), var(--so-blue-line)); color: var(--so-blue); font-size: 1.5rem;
    }
    .so-home-title { flex: 1; min-width: 200px; }
    .so-home-title h2 { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; text-align: start; justify-content: flex-start; }
    .so-home-title .section-subtitle { text-align: start; margin-inline: 0; max-width: none; }
    .so-live {
        font-size: .75rem; font-weight: 600; color: var(--so-green); background: #ecfdf5;
        border-radius: 999px; padding: 4px 10px 4px 8px; display: inline-flex; align-items: center; gap: 6px; letter-spacing: 0;
    }
    .so-live::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: var(--so-green); animation: so-pulse 2s infinite; }
    @keyframes so-pulse { 0% { box-shadow: 0 0 0 0 rgba(5, 150, 105, .5); } 70% { box-shadow: 0 0 0 7px rgba(5, 150, 105, 0); } 100% { box-shadow: 0 0 0 0 rgba(5, 150, 105, 0); } }

    .so-carousel { position: relative; }
    .so-track {
        display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - 3 * 18px) / 4); gap: 18px;
        overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; scrollbar-width: none;
        padding: 6px 2px 14px; overscroll-behavior-x: contain;
    }
    .so-track::-webkit-scrollbar { display: none; }
    .so-track > * { scroll-snap-align: start; }
    .so-arrow {
        position: absolute; top: 36%; width: 44px; height: 44px; border-radius: 50%; z-index: 3;
        border: 1px solid var(--so-line); background: #fff; color: var(--so-blue);
        display: grid; place-items: center; cursor: pointer; box-shadow: 0 4px 18px -8px rgba(15, 31, 61, .25);
        transition: transform .2s, opacity .2s;
    }
    .so-arrow:hover { transform: scale(1.07); }
    .so-arrow[disabled] { opacity: .3; cursor: default; transform: none; }
    .so-arrow-prev { inset-inline-start: -20px; }
    .so-arrow-next { inset-inline-end: -20px; }
    [dir="rtl"] .so-arrow i { transform: scaleX(-1); }
    .so-pager { display: flex; justify-content: center; gap: 8px; margin-top: 6px; }
    .so-pager button { width: 9px; height: 9px; border-radius: 999px; border: 0; padding: 0; background: var(--so-line); cursor: pointer; transition: width .3s, background .3s; }
    .so-pager button.is-active { width: 26px; background: var(--so-blue); }
    @media (max-width: 1199.98px) { .so-track { grid-auto-columns: calc((100% - 2 * 18px) / 3); } }
    @media (max-width: 991.98px) { .so-track { grid-auto-columns: calc((100% - 18px) / 2); } }
    @media (max-width: 575.98px) { .so-track { grid-auto-columns: 84%; } .so-arrow { display: none; } }

    /* ---------- Offers page ---------- */
    .so-hero {
        background: linear-gradient(135deg, #1e3a8a, #2563eb 55%, #3b82f6); color: #fff;
        border-radius: 24px; padding: 2.2rem clamp(1.25rem, 3vw, 2.5rem); position: relative; overflow: hidden;
    }
    .so-hero h1 { color: #fff; font-weight: 800; font-size: clamp(1.7rem, 3.4vw, 2.4rem); letter-spacing: -.02em; margin: 0; }
    .so-hero p { margin: .5rem 0 0; opacity: .88; max-width: 58ch; }
    .so-hero .so-crumb { font-size: .85rem; opacity: .8; margin-bottom: .6rem; }
    .so-hero .so-crumb a { color: #fff !important; text-decoration: none; }
    .so-hero-deco { position: absolute; inset-inline-end: -10px; top: -30px; font-size: 15rem; opacity: .1; transform: rotate(-12deg); pointer-events: none; }
    .so-hero-contact {
        position: relative; margin-top: 1.25rem; min-height: 48px; max-width: 100%;
        background: #facc15; color: var(--so-ink) !important; font-weight: 700;
        box-shadow: 0 8px 20px -10px rgba(15, 23, 42, .45);
    }
    .so-hero-contact:hover { background: #fde047; color: var(--so-ink) !important; box-shadow: 0 10px 24px -10px rgba(15, 23, 42, .5); }
    .so-hero-contact:focus-visible { outline: 3px solid #fff; outline-offset: 4px; }
    .so-hero-actions { position: relative; display: flex; align-items: center; gap: 6px 16px; flex-wrap: wrap; margin-top: 1.25rem; }
    .so-hero-actions .so-hero-contact { margin-top: 0; }
    .so-hero-number { font-weight: 600; font-size: .95rem; opacity: .92; }
    @media (max-width: 575.98px) { .so-hero-contact { width: 100%; padding-inline: 12px; } }
    .so-strip-title { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; font-size: 1.15rem; font-weight: 700; color: var(--so-ink); margin: 0 0 .85rem; }
    .so-strip-title small { font-size: .85rem; font-weight: 500; color: var(--so-muted); }
    .so-soon { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; }
    .so-soon-item {
        display: flex; gap: 12px; align-items: center; background: #fff; border: 1px solid var(--so-line);
        border-radius: 14px; padding: 10px; text-decoration: none; color: inherit !important; min-width: 0;
        transition: border-color .2s, box-shadow .2s;
    }
    .so-soon-item:hover { border-color: var(--so-blue-line); box-shadow: 0 10px 24px -18px rgba(37, 99, 235, .5); color: inherit; }
    .so-soon-thumb { width: 64px; height: 64px; border-radius: 10px; overflow: hidden; flex: none; }
    .so-soon-thumb img, .so-soon-thumb .so-cover-fallback { width: 100%; height: 100%; object-fit: cover; font-size: 1.5rem; }
    .so-soon-item > div { min-width: 0; }
    .so-soon-item b { display: block; font-size: .9rem; color: var(--so-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .so-soon-item span { font-size: .8rem; color: var(--so-muted); }
    .so-clock { margin-inline-start: auto; text-align: center; font-weight: 800; font-size: 1.15rem; line-height: 1; color: var(--so-red); font-variant-numeric: tabular-nums; flex: none; }
    .so-clock small { display: block; font-size: .62rem; font-weight: 600; color: var(--so-muted); margin-top: 3px; }
    .so-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(255px, 1fr)); gap: 18px; }
    .so-empty { text-align: center; padding: 3rem 1rem; border: 1px dashed var(--so-line); border-radius: 18px; color: var(--so-muted); background: #fff; }
    .so-empty i { font-size: 2rem; color: var(--so-blue-line); margin-bottom: .75rem; display: block; }

    /* ---------- Offer page ---------- */
    .so-detail { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 22px; align-items: start; }
    @media (max-width: 991.98px) { .so-detail { grid-template-columns: 1fr; } }
    .so-panel { background: #fff; border: 1px solid var(--so-line); border-radius: 18px; padding: 20px; }
    .so-detail-media { border-radius: 20px; overflow: hidden; position: relative; aspect-ratio: 16 / 9; }
    .so-detail-media img, .so-detail-media .so-cover-fallback { width: 100%; height: 100%; object-fit: cover; font-size: 5rem; }
    .so-detail-media .so-badge { font-size: 1.25rem; padding: 12px 16px; top: 16px; inset-inline-start: 16px; }
    .so-detail h1 { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: var(--so-ink); letter-spacing: -.02em; margin: 0; }
    .so-detail .so-lead { font-size: 1.02rem; line-height: 1.7; color: var(--so-body); margin: 0; white-space: pre-line; }
    .so-terms { margin: 0; padding-inline-start: 1.2rem; color: var(--so-muted); line-height: 1.85; }
    .so-side { display: grid; gap: 14px; position: sticky; top: 90px; }
    @media (max-width: 991.98px) { .so-side { position: static; } }
    .so-big-badge { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
    .so-big-badge b { font-size: 2.6rem; font-weight: 900; line-height: 1; color: var(--so-red); letter-spacing: -.03em; }
    .so-big-badge b.is-free { color: var(--so-green); }
    .so-big-badge span { font-weight: 600; color: var(--so-muted); }
    .so-countdown { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .so-countdown div { background: var(--so-bg); border-radius: 12px; padding: 10px 4px; text-align: center; font-size: 1.35rem; font-weight: 800; line-height: 1; color: var(--so-ink); font-variant-numeric: tabular-nums; }
    .so-countdown small { display: block; font-size: .62rem; font-weight: 600; color: var(--so-muted); text-transform: uppercase; letter-spacing: .05em; margin-top: 5px; }
    @keyframes so-pop { from { transform: scale(.96); opacity: 0; } to { transform: none; opacity: 1; } }
    .so-fine { font-size: .82rem; color: var(--so-muted); margin: 0; line-height: 1.55; }
    .so-prov-row { display: flex; gap: 12px; align-items: center; }
    .so-prov-row .so-logo { border: 0; box-shadow: none; width: 52px; height: 52px; }
    .so-stars { color: #f59e0b; font-size: .82rem; display: flex; gap: 3px; align-items: center; }
    .so-stars em { font-style: normal; color: var(--so-muted); margin-inline-start: 4px; }
    .so-notice { border-radius: 14px; padding: 12px 14px; font-size: .9rem; display: flex; gap: 10px; align-items: flex-start; }
    .so-notice--warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .so-notice--muted { background: var(--so-line-2); color: var(--so-body); }

    /* ---------- Engagement: likes, favorites, comments ---------- */
    .so-engage { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding-top: 12px; margin-top: 4px; border-top: 1px solid var(--so-line-2); }
    .so-pill {
        display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--so-line); background: #fff;
        border-radius: 999px; padding: 7px 14px; font-weight: 600; font-size: .88rem; color: var(--so-body) !important;
        text-decoration: none; cursor: pointer; transition: background .2s, border-color .2s, color .2s, transform .15s;
    }
    .so-pill:hover { border-color: #fecdd3; background: #fff1f2; }
    .so-pill:active { transform: scale(.96); }
    .so-pill[disabled] { opacity: .6; cursor: progress; }
    .so-pill i { font-size: 1rem; }
    .so-pill.is-on { background: #fff1f2; border-color: #fda4af; color: var(--so-red) !important; }
    .so-pill.is-on i { animation: so-pop .35s var(--so-ease); }
    .so-pill--save:hover { border-color: var(--so-blue-line); background: var(--so-blue-soft); }
    .so-pill--save.is-on { background: var(--so-blue-soft); border-color: #93c5fd; color: var(--so-blue) !important; }
    .so-pill--link:hover { border-color: var(--so-blue-line); background: var(--so-blue-soft); }
    .so-comments { display: grid; gap: 14px; scroll-margin-top: 90px; }
    .so-comments-title { font-size: 1.05rem; font-weight: 800; color: var(--so-ink); margin: 0; display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
    .so-comments-title i { color: var(--so-blue); }
    .so-comments-title small { font-size: .82rem; font-weight: 500; color: var(--so-muted); }
    .so-comment { display: flex; gap: 12px; align-items: flex-start; }
    .so-comment-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex: none; display: grid; place-items: center;
        background: linear-gradient(135deg, #dbeafe, #c7d2fe); color: #3730a3; font-weight: 800;
    }
    .so-comment-body { flex: 1; min-width: 0; background: var(--so-bg); border-radius: 4px 16px 16px 16px; padding: 10px 14px; }
    [dir="rtl"] .so-comment-body { border-radius: 16px 4px 16px 16px; }
    .so-comment-head { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .so-comment-head b { color: var(--so-ink); font-size: .92rem; }
    .so-comment-head time { color: var(--so-muted); font-size: .76rem; }
    .so-comment-body p { margin: 4px 0 0; color: var(--so-body); font-size: .92rem; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
    .so-comment-form { display: grid; gap: 10px; border-top: 1px solid var(--so-line-2); padding-top: 14px; }
    .so-comment-form-title { font-size: .95rem; font-weight: 700; color: var(--so-ink); margin: 0; }
    /* Clipped in place rather than pushed off-screen: left:-9999px gave
       Arabic (RTL) pages a 10,000px sideways scroll. */
    .so-hp { position: absolute !important; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; border: 0; }
    .so-notice--ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .so-publish {
        display: grid; grid-template-columns: auto 1fr; gap: 10px 12px; align-items: center;
        background: linear-gradient(135deg, #ecfdf5, #fff); border: 1px solid #bbf7d0; border-radius: 18px; padding: 16px;
    }
    .so-publish-icon { width: 44px; height: 44px; border-radius: 12px; background: #16a34a; color: #fff; display: grid; place-items: center; font-size: 1.4rem; }
    .so-publish-text b { color: var(--so-ink); }
    .so-publish-text p { margin: 2px 0 0; font-size: .85rem; color: var(--so-muted); line-height: 1.5; }
    .so-publish .so-btn { grid-column: 1 / -1; }

    /* ---------- Provider profile block ---------- */
    .so-profile-offers {
        border-radius: 18px; padding: 18px; display: grid; gap: 12px; margin-bottom: 1.5rem;
        background: linear-gradient(120deg, #fff1f3, #fff 60%); border: 1px solid #fecdd3;
    }
    .so-profile-offers-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-weight: 700; color: var(--so-ink); font-size: 1.05rem; }
    .so-profile-offers-head i { color: var(--so-red); }
    .so-profile-offers-head small { font-weight: 500; color: var(--so-muted); font-size: .85rem; }
    .so-profile-offer {
        display: flex; gap: 14px; align-items: center; flex-wrap: wrap; background: #fff;
        border: 1px solid var(--so-line); border-radius: 14px; padding: 12px; position: relative;
    }
    .so-profile-offer-pct { min-width: 88px; text-align: center; font-weight: 900; font-size: 1.3rem; line-height: 1.1; color: var(--so-red); letter-spacing: -.02em; }
    .so-profile-offer-pct.is-free { color: var(--so-green); }
    .so-profile-offer-text { flex: 1; min-width: 180px; }
    .so-profile-offer-text b { color: var(--so-ink); }
    .so-profile-offer-text p { margin: 2px 0 0; font-size: .85rem; color: var(--so-muted); }
    .so-avatar-flag {
        position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%); z-index: 3;
        background: var(--so-red); color: #fff !important; font-weight: 800; font-size: .68rem; padding: 3px 9px; border-radius: 999px;
        white-space: nowrap; border: 2px solid #fff; text-decoration: none;
    }
    .so-list-flag {
        display: inline-flex; align-items: center; gap: 5px; background: var(--so-red); color: #fff !important;
        font-weight: 800; font-size: .7rem; padding: 4px 9px; border-radius: 999px; box-shadow: 0 6px 14px -6px rgba(225, 29, 72, .6);
    }

    .so-list-flag--corner { position: absolute; top: 12px; inset-inline-end: 12px; z-index: 3; }

    @media (prefers-reduced-motion: reduce) {
        .so-scope *, .so-card, .so-track { transition: none !important; animation: none !important; scroll-behavior: auto !important; }
    }
</style>
@endpush
@endonce
