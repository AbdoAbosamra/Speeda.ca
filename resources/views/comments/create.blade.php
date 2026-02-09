@extends('layouts.app')

@section('title', __('comments.add_comment'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">{{ __('comments.add_comment') }}</h1>

    <form method="POST" action="{{ route('comments.store') }}" class="bg-white rounded-lg shadow p-6">
        @csrf

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
            >{{ old('content') }}</textarea>
            @error('content')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <input type="hidden" name="commentable_type" value="{{ $commentable_type }}">
        <input type="hidden" name="commentable_id" value="{{ $commentable_id }}">

        <div class="flex gap-4">
            <button type="submit" class="btn btn-primary px-6 py-2">{{ __('comments.add_comment') }}</button>
            <a href="{{ route('comments.index', ['commentable_type' => $commentable_type, 'commentable_id' => $commentable_id]) }}" class="btn btn-secondary px-6 py-2">
                {{ __('general.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
