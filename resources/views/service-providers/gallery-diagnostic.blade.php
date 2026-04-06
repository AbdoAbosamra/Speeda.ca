@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>🖼️ تشخيص معرض مزود الخدمة #{{ $serviceProvider->id }}</h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>📊 حالة البيانات</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Provider ID:</strong> {{ $serviceProvider->id }}</li>
                        <li><strong>Company:</strong> {{ $serviceProvider->company_name }}</li>
                        <li><strong>Profile Image:</strong> {{ $serviceProvider->profile_image ? '✅ موجود' : '❌ فاضي' }}<br>
                            URL: <code>{{ $serviceProvider->profile_image_url }}</code></li>
                        <li><strong>Gallery Count:</strong> <span class="badge bg-{{ $serviceProvider->getMedia('provider_gallery')->count() > 0 ? 'success' : 'danger' }}">{{ $serviceProvider->getMedia('provider_gallery')->count() }}</span></li>
                        <li><strong>Media Table Total:</strong> {{ $totalMedia }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>🖼️ المعرض الحالي</h5>
                </div>
                <div class="card-body">
                    @if($gallery->count() > 0)
                        <div class="row g-2">
                            @foreach($gallery as $media)
                                <div class="col-6">
                                    <div class="border p-2 rounded">
                                        <img src="{{ $serviceProvider->getMediaPublicUrl($media, 'gallery_thumb') ?? $serviceProvider->default_image_url }}" class="img-fluid" style="height:100px;object-fit:cover;">
                                        <small><code>{{ $media->file_name }}</code></small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <p class="text-muted">❌ لا توجد صور في provider_gallery</p>
                            <a href="{{ route('service-providers.edit', $serviceProvider) }}" class="btn btn-primary">رفع صور</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5>🔍 فحص Media Records</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>File</th>
                                <th>Collection</th>
                                <th>Conversions</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mediaRecords as $record)
                                <tr>
                                    <td>{{ $record->id }}</td>
                                    <td>{{ $record->file_name }}</td>
                                    <td><span class="badge bg-secondary">{{ $record->collection_name }}</span></td>
                                    <td>{{ $record->conversions_disk ? '✅' : '⚠️' }}</td>
                                    <td>
                                        @if($record->manipulations)
                                            <span class="badge bg-success">Has Thumbs</span>
                                        @else
                                            <span class="badge bg-warning">No Conversions</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">لا توجد media records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <strong>التشخيص:</strong><br>
        @if($gallery->count() > 0)
            ✅ المعرض يعمل! الصور موجودة ({{ $gallery->count() }}/4)
        @else
            ❌ المعرض فارغ. <strong>الحل: ارفع صور من صفحة التعديل</strong>
        @endif
    </div>
</div>
@endsection
