@extends('layouts.app')

@section('title', __('comments.edit_comment'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">{{ __('comments.edit_comment') }}</h1>

    @if ($comment->is_active)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-yellow-800">{{ __('comments.cannot_edit_approved_comments') }}</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('general.back') }}</a>
    @else
        <form method="POST" action="{{ route('comments.update', $comment) }}" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <div class="form-group mb-4">
                <label for="content" class="block font-semibold mb-2">{{ __('comments.your_comment') }}</label>
                <textarea
                    id="content"
                    name="content"
                    class="form-control w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror"
                    rows="6"
                    placeholder="{{ __('comments.comment_placeholder') }}"
                    required
                    minlength="5"
                    maxlength="500"
                >{{ old('content', $comment->content) }}</textarea>
                @error('content')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary px-6 py-2">{{ __('general.save') }}</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary px-6 py-2">{{ __('general.cancel') }}</a>
            </div>
        </form>
    @endif
</div>
@endsection
