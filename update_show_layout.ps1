$showFile = "y:\Speeda - Versions\Speeda\resources\views\service-providers\show.blade.php"
$outputFile = "y:\Speeda - Versions\Speeda\resources\css\provider-profile.css"

$lines = Get-Content $showFile -Encoding UTF8

# CSS Block 1: Lines 30 to 1024 (0-indexed 29 to 1023)
$cssLines1 = $lines[29..1023]
$cleaned1 = $cssLines1 | ForEach-Object { $_ -replace '^        ', '' }

# CSS Block 2: Lines 2930 to 2969 (0-indexed 2929 to 2968)
$cssLines2 = $lines[2929..2968]
$cleaned2 = $cssLines2 | ForEach-Object { $_ -replace '^        ', '' }

# Combine and write to CSS file
$header = "/* ============================================`n   PROVIDER PROFILE PAGE`n   Extracted from show.blade.php inline styles`n   ============================================ */`n"
$outputCss = $header + ($cleaned1 -join "`n") + "`n" + ($cleaned2 -join "`n")
Set-Content -Path $outputFile -Value $outputCss -Encoding UTF8 -NoNewline

# Now update the Blade file
# Top replacement: Replace lines 0 to 1043
$topContent = "@extends('layouts.app')`n`n@push('styles')`n    @vite(['resources/css/provider-profile.css'])`n@endpush`n`n@section('content')`n"

# Middle content: Lines 1044 to 2928 (0-indexed 1043 to 2927, wait, if I replace 0..1043 then I start at 1044)
$middleContent = $lines[1044..2928] -join "`n"

# Bottom replacement: Replace lines 2929 to end with @endsection
$bottomContent = "`n@endsection`n"

$newContent = $topContent + $middleContent + $bottomContent
Set-Content -Path $showFile -Value $newContent -Encoding UTF8 -NoNewline

Write-Host "Extracted $( $cssLines1.Count + $cssLines2.Count ) CSS lines and updated layout successfully"
