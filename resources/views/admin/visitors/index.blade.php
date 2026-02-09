@extends('layouts.app')

@section('title', __('admin.visitor_analytics'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            @lang('admin.visitor_analytics')
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            @lang('admin.visitor_analytics_description')
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <!-- Total Visitors -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            @lang('admin.total_visitors')
                        </dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ number_format($stats['total_visitors']) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Last 7 Days -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            @lang('admin.last_7_days')
                        </dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ number_format($stats['last_7_days']) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Last 30 Days -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            @lang('admin.last_30_days')
                        </dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ number_format($stats['last_30_days']) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Last 12 Months -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            @lang('admin.last_12_months')
                        </dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ number_format($stats['last_12_months']) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Live Visitors -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            @lang('admin.live_visitors')
                        </dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white" id="liveCount">
                            {{ $stats['live_visitors'] }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Selection & Export -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('admin.select_period')
                </label>
                <select id="period" name="period" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="last_7_days" {{ $period === 'last_7_days' ? 'selected' : '' }}>@lang('admin.last_7_days')</option>
                    <option value="last_30_days" {{ $period === 'last_30_days' ? 'selected' : '' }}>@lang('admin.last_30_days')</option>
                    <option value="last_12_months" {{ $period === 'last_12_months' ? 'selected' : '' }}>@lang('admin.last_12_months')</option>
                </select>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.visitors.export', ['period' => $period]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    @lang('admin.export_csv')
                </a>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Visitors by Date Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                @lang('admin.visitors_by_date')
            </h2>
            @if($analytics['visitors_by_date']->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">
                        @lang('admin.no_visitor_data')
                    </p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($analytics['visitors_by_date'] as $data)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $data->date }}</span>
                            <div class="flex items-center gap-2 flex-1 ml-4">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($data->count / collect($analytics['visitors_by_date'])->max('count')->count) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white min-w-max">{{ $data->count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Top Pages -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                @lang('admin.top_pages')
            </h2>
            @if($analytics['top_pages']->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">
                        @lang('admin.no_page_data')
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($analytics['top_pages'] as $page)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $page->path }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $page->visits }} @lang('admin.visits') · {{ $page->unique_visitors }} @lang('admin.unique')
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                    {{ $page->visits }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                @lang('admin.total_visits')
            </dt>
            <dd class="mt-2 text-3xl font-medium text-gray-900 dark:text-white">
                {{ number_format($analytics['total_visits']) }}
            </dd>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                @lang('admin.unique_visitors_period')
            </dt>
            <dd class="mt-2 text-3xl font-medium text-gray-900 dark:text-white">
                {{ number_format($analytics['unique_visitors']) }}
            </dd>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                @lang('admin.avg_visits_per_visitor')
            </dt>
            <dd class="mt-2 text-3xl font-medium text-gray-900 dark:text-white">
                {{ $analytics['unique_visitors'] > 0 ? number_format($analytics['total_visits'] / $analytics['unique_visitors'], 1) : 0 }}
            </dd>
        </div>
    </div>
</div>

<script>
    // Auto-update live visitor count every 30 seconds
    function updateLiveCount() {
        fetch('{{ route('admin.visitors.live-count') }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('liveCount').textContent = data.count;
                }
            })
            .catch(error => console.error('Error updating live count:', error));
    }

    // Update on page load and then every 30 seconds
    setInterval(updateLiveCount, 30000);

    // Period change handler
    document.getElementById('period').addEventListener('change', function() {
        const period = this.value;
        window.location.href = `{{ route('admin.visitors') }}?period=${period}`;
    });
</script>
@endsection
