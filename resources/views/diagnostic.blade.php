@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>تشخيص مشكلة الصور</h1>

    <h2>🔍 فحص قاعدة البيانات:</h2>
    <ul>
        <li><strong>عدد مزودي الخدمة الكلي:</strong> {{ $totalSP }}</li>
        <li><strong>مزودي خدمة بـ profile_image:</strong> {{ $spWithImages }}</li>
        <li><strong>الملفات الموجودة في storage:</strong> {{ $storageCount }}</li>
    </ul>

    @if($totalSP === 0)
        <div class="alert alert-danger">
            ❌ <strong>لا توجد مزودي خدمة في الـ database!</strong>
            <p>الحل: اذهب لصفحة التسجيل وأنشئ حساب مزود خدمة أولاً.</p>
        </div>
    @elseif($spWithImages === 0)
        <div class="alert alert-warning">
            ⚠️ <strong>لا توجد صور مرفوعة!</strong>
            <p>الحل: سجل دخول كـ مزود خدمة ورفع صورة ملفك الشخصي.</p>
        </div>
    @endif

    <h2>📋 أول 3 مزودي خدمة:</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>النسبة</th>
                <th>profile_image​ (DB)</th>
                <th>profile_image_url (Accessor)</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($serviceProviders as $sp)
                <tr>
                    <td>{{ $sp->id }}</td>
                    <td>{{ $sp->company_name }}</td>
                    <td>
                        @if($sp->profile_image)
                            <code style="word-break: break-all;">{{ $sp->profile_image }}</code>
                        @else
                            <span class="text-muted">NULL</span>
                        @endif
                    </td>
                    <td>
                        <code style="word-break: break-all;">{{ $sp->profile_image_url }}</code>
                    </td>
                    <td>
                        @if($sp->profile_image && file_exists(storage_path('app/public/' . $sp->profile_image)))
                            ✅ موجود
                        @elseif($sp->profile_image)
                            ❌ في DB لكن ليس في الـ storage!
                        @else
                            ⚪ placeholder
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">لا توجد مزودي خدمة</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>📁 الملفات الموجودة في storage:</h2>
    <ul>
        @foreach($storageFiles as $file)
            <li><code>{{ $file }}</code></li>
        @endforeach
    </ul>

    <p class="mt-4 text-muted">
        <small>
            ملاحظة: هذه الصفحة للتشخيص فقط وسيتم حذفها.
        </small>
    </p>
</div>
@endsection
