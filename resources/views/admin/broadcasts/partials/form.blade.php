@php
    $isNew = !$broadcast->exists;
@endphp

<form action="{{ $action }}" method="POST" class="admin-blog-form" id="broadcast-form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-form-main">
        <section class="admin-form-card">
            <div class="admin-form-section-head">
                <h2>Message</h2>
                <p>Write the email exactly as providers will read it. Nothing is sent until you confirm on this page.</p>
            </div>

            <div class="admin-form-grid">
                <label class="admin-field admin-field-wide">
                    <span>Subject <strong>*</strong></span>
                    <input type="text" name="subject" value="{{ old('subject', $broadcast->subject) }}" required
                           maxlength="255" placeholder="e.g. New feature: showcase your work gallery">
                    @error('subject')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Preview line</span>
                    <input type="text" name="preheader" value="{{ old('preheader', $broadcast->preheader) }}"
                           maxlength="255" placeholder="The grey line shown next to the subject in the inbox">
                    @error('preheader')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Email body <strong>*</strong></span>
                    <textarea name="body" rows="16" class="rich-editor" data-editor-dir="ltr"
                              placeholder="Write your message to the providers">{{ old('body', $broadcast->body) }}</textarea>
                    @error('body')<small>{{ $message }}</small>@enderror
                </label>
            </div>

            <div class="broadcast-placeholders">
                <strong>Placeholders</strong>
                <p>Type these anywhere in the subject or body — each provider sees their own value.</p>
                <ul>
                    @foreach(\App\Models\ProviderBroadcast::PLACEHOLDERS as $token => $description)
                        <li><code>&#123;&#123; {{ $token }} &#125;&#125;</code> <span>{{ $description }}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="admin-form-card">
            <div class="admin-form-section-head">
                <h2>Button (optional)</h2>
                <p>Adds a single call-to-action button under the message. Leave both empty for a plain email.</p>
            </div>

            <div class="admin-form-grid">
                <label class="admin-field admin-field-wide">
                    <span>Button label</span>
                    <input type="text" name="cta_label" value="{{ old('cta_label', $broadcast->cta_label) }}"
                           maxlength="80" placeholder="e.g. Open My Dashboard">
                    @error('cta_label')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Button link</span>
                    <input type="url" name="cta_url" value="{{ old('cta_url', $broadcast->cta_url) }}"
                           maxlength="2048" placeholder="https://speeda.ca/...">
                    @error('cta_url')<small>{{ $message }}</small>@enderror
                </label>
            </div>
        </section>
    </div>

    <aside class="admin-form-side">
        <section class="admin-form-card">
            <div class="admin-form-section-head">
                <h2>Audience</h2>
            </div>

            <div class="broadcast-audience">
                <span class="broadcast-audience-count">{{ number_format($audienceCount) }}</span>
                <span class="broadcast-audience-label">active provider(s) will receive this</span>
            </div>

            <p class="broadcast-note">
                Providers with a disabled account, no email address, or who have unsubscribed are excluded automatically.
            </p>
        </section>

        <div class="admin-sticky-actions">
            <button type="submit" class="admin-btn admin-btn-primary text-white">
                <i class="fas fa-floppy-disk"></i>
                <span>{{ $isNew ? 'Save Draft' : 'Update Draft' }}</span>
            </button>
            <button type="button" id="broadcast-preview-btn" class="admin-btn admin-btn-secondary">
                <i class="fas fa-eye"></i>
                <span>Preview Email</span>
            </button>
            <a href="{{ route('admin.broadcasts.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
        </div>
    </aside>
</form>

@if($broadcast->exists && $broadcast->isEditable())
    {{-- Test send and the real send live outside the draft form so their own
         POSTs are never confused with saving the draft. --}}
    <div class="admin-form-card broadcast-danger-zone">
        <div class="admin-form-section-head">
            <h2>Send</h2>
            <p>Always send yourself a test first — an email cannot be recalled once it leaves.</p>
        </div>

        <form action="{{ route('admin.broadcasts.send_test', $broadcast) }}" method="POST" class="broadcast-test-row">
            @csrf
            <label class="admin-field">
                <span>Send a test to</span>
                <input type="email" name="test_email" required maxlength="255"
                       value="{{ old('test_email', auth()->user()?->email) }}" placeholder="you@example.com">
                @error('test_email')<small>{{ $message }}</small>@enderror
            </label>
            <button type="submit" class="admin-btn admin-btn-secondary">
                <i class="fas fa-paper-plane"></i>
                <span>Send Test</span>
            </button>
        </form>

        <hr class="broadcast-divider">

        <form action="{{ route('admin.broadcasts.send', $broadcast) }}" method="POST" class="broadcast-send-row"
              onsubmit="return confirm('Send this email to {{ number_format($audienceCount) }} providers? This cannot be undone.');">
            @csrf
            <label class="admin-field">
                <span>Type <strong>SEND</strong> to confirm</span>
                <input type="text" name="confirm" required placeholder="SEND" autocomplete="off">
                @error('confirm')<small>{{ $message }}</small>@enderror
            </label>
            <button type="submit" class="admin-btn admin-btn-danger">
                <i class="fas fa-bullhorn"></i>
                <span>Send to {{ number_format($audienceCount) }} Providers</span>
            </button>
        </form>
    </div>
@endif

{{-- ===================== Email Preview Modal ===================== --}}
<div id="broadcast-preview-modal" class="blog-preview-modal" aria-hidden="true">
    <div class="blog-preview-backdrop" data-preview-close></div>
    <div class="blog-preview-dialog" role="dialog" aria-modal="true" aria-label="Email preview">
        <header class="blog-preview-head">
            <span class="blog-preview-label"><i class="fas fa-envelope-open-text"></i> Exactly what providers will receive</span>
            <button type="button" class="blog-preview-x" data-preview-close aria-label="Close preview">&times;</button>
        </header>
        <div class="blog-preview-body broadcast-preview-body">
            {{-- name must be set in the markup, not by script: it is what the
                 hidden preview form targets, so the browsing context has to
                 exist before that form is ever submitted. --}}
            <iframe id="broadcast-preview-frame" name="broadcast-preview-frame-target" title="Email preview"></iframe>
        </div>
    </div>
</div>

<form id="broadcast-preview-form" action="{{ route('admin.broadcasts.preview') }}" method="POST"
      target="broadcast-preview-frame-target" style="display:none;">
    @csrf
    <input type="hidden" name="subject">
    <input type="hidden" name="preheader">
    <input type="hidden" name="body">
    <input type="hidden" name="cta_label">
    <input type="hidden" name="cta_url">
</form>

@push('styles')
    <style>
        .blog-preview-modal { position: fixed; inset: 0; z-index: 1080; display: none; }
        .blog-preview-modal.is-open { display: block; }
        .blog-preview-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.62); }
        .blog-preview-dialog { position: relative; max-width: 720px; margin: 3vh auto; height: 94vh; background: #fff; border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,.35); display: flex; flex-direction: column; overflow: hidden; }
        .blog-preview-head { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; }
        .blog-preview-label { color: #64748b; font-size: .85rem; font-weight: 600; }
        .blog-preview-x { margin-inline-start: auto; border: 0; background: transparent; font-size: 1.7rem; line-height: 1; color: #64748b; cursor: pointer; padding: 0 6px; }
        .blog-preview-x:hover { color: #111; }
        .broadcast-preview-body { flex: 1; padding: 0; background: #F0F4FA; overflow: hidden; }
        #broadcast-preview-frame { width: 100%; height: 100%; border: 0; display: block; }

        .broadcast-placeholders { margin-top: 18px; padding: 16px 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .broadcast-placeholders strong { display: block; font-size: .8rem; text-transform: uppercase; letter-spacing: .08em; color: #475569; }
        .broadcast-placeholders p { margin: 6px 0 10px; font-size: .85rem; color: #64748b; }
        .broadcast-placeholders ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
        .broadcast-placeholders code { background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 6px; font-size: .82rem; }
        .broadcast-placeholders span { color: #64748b; font-size: .82rem; margin-inline-start: 8px; }

        .broadcast-audience { text-align: center; padding: 12px 0 6px; }
        .broadcast-audience-count { display: block; font-size: 2.4rem; font-weight: 800; color: #0F1F3D; line-height: 1; }
        .broadcast-audience-label { display: block; margin-top: 6px; font-size: .85rem; color: #64748b; }
        .broadcast-note { font-size: .82rem; color: #64748b; line-height: 1.6; margin: 12px 0 0; }

        .broadcast-danger-zone { margin-top: 24px; border: 2px solid #fecaca; }
        .broadcast-test-row, .broadcast-send-row { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; }
        .broadcast-test-row .admin-field, .broadcast-send-row .admin-field { flex: 1 1 260px; margin: 0; }
        .broadcast-divider { border: 0; border-top: 1px solid #e5e7eb; margin: 22px 0; }
        .admin-btn-danger { background: #dc2626; border-color: #dc2626; color: #fff; }
        .admin-btn-danger:hover { background: #b91c1c; border-color: #b91c1c; color: #fff; }
    </style>
@endpush

@push('scripts')
    {{-- Same editor as the blog CMS, so composing an email feels identical --}}
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
    <script>
        (() => {
            if (typeof tinymce === 'undefined') return;

            document.querySelectorAll('textarea.rich-editor').forEach((el) => {
                tinymce.init({
                    target: el,
                    height: 460,
                    menubar: false,
                    branding: false,
                    promotion: false,
                    // No 'hr' or 'table' here: TinyMCE 6 removed the hr plugin
                    // (the toolbar button is core now), so requesting it 404s
                    // on every page load, and no table button is exposed below.
                    plugins: 'lists link image code autolink',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | blockquote | link image hr | alignleft aligncenter alignright | removeformat | code',
                    block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Quote=blockquote',
                    image_uploadtab: false,
                    automatic_uploads: false,
                    file_picker_types: '',
                    link_default_target: '_blank',
                    default_link_target: '_blank',
                    convert_urls: false,
                    invalid_elements: 'script',
                    content_style: 'body{font-family:Inter,-apple-system,Segoe UI,sans-serif;font-size:16px;line-height:1.7;color:#374151}img{max-width:100%;height:auto}',
                    setup: (editor) => {
                        editor.on('change keyup', () => editor.save());
                    },
                });
            });

            document.getElementById('broadcast-form')?.addEventListener('submit', () => {
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();
            });
        })();
    </script>

    {{-- Server-rendered preview: posts the current (unsaved) copy through the
         real Mailable and shows the result, so the preview cannot drift from
         what actually gets delivered. --}}
    <script>
        (() => {
            const openBtn = document.getElementById('broadcast-preview-btn');
            const modal = document.getElementById('broadcast-preview-modal');
            const frame = document.getElementById('broadcast-preview-frame');
            const previewForm = document.getElementById('broadcast-preview-form');
            if (!openBtn || !modal || !frame || !previewForm) return;

            const closeEls = modal.querySelectorAll('[data-preview-close]');
            const field = (name) => document.querySelector(`#broadcast-form [name="${name}"]`);

            function close() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            openBtn.addEventListener('click', () => {
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();

                ['subject', 'preheader', 'body', 'cta_label', 'cta_url'].forEach((name) => {
                    previewForm.querySelector(`[name="${name}"]`).value = field(name)?.value || '';
                });

                // The iframe is the POST target, so the preview renders inside
                // the modal without navigating the page away from the draft.
                previewForm.submit();

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });

            closeEls.forEach((el) => el.addEventListener('click', close));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
            });
        })();
    </script>
@endpush
