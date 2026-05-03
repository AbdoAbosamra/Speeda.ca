$indexFile = "y:\Speeda - Versions\Speeda\resources\views\service-providers\index.blade.php"
$lines = Get-Content $indexFile -Encoding UTF8

# Top replacement: Replace lines 0 to 1680
$topContent = "@extends('layouts.app')`n`n@push('styles')`n    @vite(['resources/css/providers.css'])`n@endpush`n`n@section('content')`n"

# The middle content (keep everything from line 1681 to 2343)
$middleContent = $lines[1681..2343] -join "`n"

# Bottom replacement
$bottomContent = "`n@endsection`n"

$newContent = $topContent + $middleContent + $bottomContent

Set-Content -Path $indexFile -Value $newContent -Encoding UTF8 -NoNewline
Write-Host "Updated index.blade.php layout successfully"
