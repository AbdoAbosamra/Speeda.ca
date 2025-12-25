{{--
    Inline Form Error Component
    Usage: <x-form-error field="email" />

    Props:
    - field: The field name to check for errors (required)
    - class: Additional CSS classes (optional)
--}}

@props(['field', 'class' => ''])

@error($field)
    <div class="invalid-feedback d-block {{ $class }}" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i>
        {{ $message }}
    </div>
@enderror
