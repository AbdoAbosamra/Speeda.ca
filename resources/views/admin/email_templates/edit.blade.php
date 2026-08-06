@extends('layouts.app')

@section('title', __('admin.email_templates') . ': ' . $label)

@php
    $fieldLabels = [
        'subject' => 'Subject line',
        'badge' => 'Badge',
        'headline' => 'Headline',
        'lead' => 'Intro paragraph',
        'next_step_label' => 'Action card — label',
        'next_step_title' => 'Action card — title',
        'next_step_desc' => 'Action card — text',
        'why_label' => 'Why-it-matters — label',
        'why_text' => 'Why-it-matters — text',
        'cta_label' => 'Button text',
        'cta_subtext' => 'Text under the button',
        'quote' => 'Closing quote',
    ];
    $required = ['subject', 'headline'];
@endphp

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header eyebrow="Email copy" :title="$label" :subtitle="'Key: ' . $key">
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.email_templates.preview', $key)"
                        variant="secondary"
                        icon="fas fa-eye"
                        class="admin-btn admin-btn-secondary"
                    >
                        {{ __('admin.email_preview') }}
                    </x-ui.button>
                    <x-ui.button
                        :href="route('admin.email_templates.index')"
                        variant="secondary"
                        icon="fas fa-arrow-left"
                        class="admin-btn admin-btn-secondary"
                    >
                        {{ __('admin.back_to_list') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            <div class="etpl-grid">
                <form method="POST" action="{{ route('admin.email_templates.update', $key) }}" class="etpl-card">
                    @csrf
                    @method('PUT')

                    @foreach($fields as $field)
                        @php
                            $isLong = in_array($field, $longFields, true);
                            $isRequired = in_array($field, $required, true);
                            $defaultValue = $default[$field] ?? '';
                        @endphp
                        <div class="etpl-field">
                            <label for="{{ $field }}">
                                {{ $fieldLabels[$field] ?? $field }}
                                @if($isRequired)<span class="req">*</span>@endif
                            </label>

                            @if($isLong)
                                <textarea name="{{ $field }}" id="{{ $field }}" rows="3"
                                          @if($isRequired) required @endif>{{ old($field, $values[$field]) }}</textarea>
                            @else
                                <input type="text" name="{{ $field }}" id="{{ $field }}"
                                       value="{{ old($field, $values[$field]) }}"
                                       @if($isRequired) required @endif>
                            @endif

                            @error($field)
                                <span class="etpl-error">{{ $message }}</span>
                            @enderror

                            @if(!$isRequired && $defaultValue === '')
                                <small class="etpl-hint">{{ __('admin.email_field_hint_optional') }}</small>
                            @elseif($defaultValue !== '' && $defaultValue !== $values[$field])
                                <small class="etpl-hint etpl-hint-diff">
                                    <strong>Default:</strong> {{ Str::limit($defaultValue, 140) }}
                                </small>
                            @endif
                        </div>
                    @endforeach

                    <label class="etpl-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               @checked(old('is_active', $row?->is_active ?? true))>
                        <span>{{ __('admin.email_use_default_toggle') }}</span>
                    </label>

                    <div class="etpl-actions">
                        <button type="submit" class="admin-btn admin-btn-primary text-white">
                            <i class="fas fa-save"></i> {{ __('admin.save') }}
                        </button>
                    </div>
                </form>

                <aside class="etpl-side">
                    <div class="etpl-card etpl-card-tight">
                        <h4>{{ __('admin.email_available_placeholders') }}</h4>
                        <p class="etpl-side-note">
                            Type these anywhere in the text — they are replaced when the email is sent.
                        </p>
                        <ul class="etpl-placeholders">
                            @foreach($placeholders as $token => $description)
                                @php
                                    // Built in PHP: writing the braces inline would
                                    // terminate Blade's echo early.
                                    $display = '{' . '{ ' . $token . ' }' . '}';
                                @endphp
                                <li>
                                    <code>{{ $display }}</code>
                                    <span>{{ $description }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="etpl-card etpl-card-tight">
                        <h4>Status</h4>
                        @if($row)
                            <p class="etpl-side-note">
                                Customised{{ $row->updatedBy ? ' by ' . $row->updatedBy->name : '' }},
                                {{ $row->updated_at?->diffForHumans() }}.
                            </p>
                            <form method="POST" action="{{ route('admin.email_templates.reset', $key) }}"
                                  onsubmit="return confirm('{{ __('admin.email_confirm_reset') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-secondary etpl-reset">
                                    <i class="fas fa-rotate-left"></i> {{ __('admin.email_reset_default') }}
                                </button>
                            </form>
                        @else
                            <p class="etpl-side-note">
                                This email currently uses the built-in default copy. Saving creates an override.
                            </p>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <style>
        .etpl-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 1.25rem; align-items: start; }
        @media (max-width: 992px) { .etpl-grid { grid-template-columns: minmax(0, 1fr); } }

        .etpl-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .etpl-card-tight { padding: 1.15rem 1.25rem; }
        .etpl-card-tight + .etpl-card-tight { margin-top: 1rem; }
        .etpl-card h4 { font-size: 0.95rem; font-weight: 800; color: #1e293b; margin: 0 0 0.5rem; }

        .etpl-field { margin-bottom: 1.1rem; }
        .etpl-field label { display: block; margin-bottom: .35rem; font-size: .82rem; font-weight: 700; color: #475569; }
        .etpl-field .req { color: #dc2626; margin-inline-start: .15rem; }
        .etpl-field input, .etpl-field textarea {
            width: 100%; padding: .6rem .85rem; border: 1px solid #cbd5e1;
            border-radius: 10px; font-size: .9375rem; color: #0f172a; background: #fff;
        }
        .etpl-field textarea { resize: vertical; line-height: 1.6; }
        .etpl-error { display: block; margin-top: .3rem; font-size: .8rem; color: #dc2626; }
        .etpl-hint { display: block; margin-top: .3rem; font-size: .78rem; color: #94a3b8; }
        .etpl-hint-diff { color: #64748b; }

        .etpl-switch { display: flex; align-items: flex-start; gap: .6rem; margin: 1rem 0; cursor: pointer; }
        .etpl-switch input[type="checkbox"] { width: 17px; height: 17px; margin-top: .15rem; }
        .etpl-switch span { font-size: .86rem; color: #475569; }

        .etpl-actions { display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #f1f5f9; }

        .etpl-side-note { font-size: .82rem; color: #64748b; margin: 0 0 .75rem; line-height: 1.6; }
        .etpl-placeholders { list-style: none; margin: 0; padding: 0; display: grid; gap: .55rem; }
        .etpl-placeholders li { display: flex; flex-direction: column; gap: .15rem; }
        .etpl-placeholders code {
            background: #eef2ff; color: #4338ca; padding: .15rem .45rem;
            border-radius: 6px; font-size: .8rem; width: fit-content;
        }
        .etpl-placeholders span { font-size: .76rem; color: #94a3b8; }
        .etpl-reset { width: 100%; justify-content: center; }
    </style>
@endsection
