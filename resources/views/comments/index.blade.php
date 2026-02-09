@extends('layouts.app')

@section('title', __('comments.view_comments'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">{{ __('comments.view_comments') }}</h1>

    @if ($comments->count() > 0)
        <div class="space-y-4">
            @foreach ($comments as $comment)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $comment->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                        @if (Auth::check() && (Auth::user()->id === $comment->user_id || Auth::user()->isAdmin()))
                            <div class="flex gap-2">
                                @if (Auth::user()->id === $comment->user_id)
                                    <a href="{{ route('comments.edit', $comment) }}" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline" onsubmit="return confirm('{{ __('comments.delete_comment') }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <p class="text-gray-700">{{ $comment->content }}</p>
                </div>
            @endforeach
        </div>

        {{ $comments->links() }}
    @else
        <p class="text-center text-gray-500">{{ __('comments.no_comments') }}</p>
    @endif

    @auth
        <div class="mt-8">
            <a href="{{ route('comments.create', ['commentable_type' => $commentable_type, 'commentable_id' => $commentable_id]) }}" class="btn btn-primary">
                {{ __('comments.add_comment') }}
            </a>
        </div>
    @else
        <div class="mt-8 text-center">
            <p class="mb-4">{{ __('comments.must_login_to_comment') }}</p>
            <a href="{{ route('login') }}" class="btn btn-primary">{{ __('auth.login') }}</a>
        </div>
    @endauth
</div>
@endsection
