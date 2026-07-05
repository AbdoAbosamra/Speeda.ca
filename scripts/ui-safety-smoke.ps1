[CmdletBinding()]
param(
    [switch]$RunTests,
    [switch]$SkipBuild,
    [switch]$SkipComposerValidate
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

function Write-Section {
    param([string]$Message)

    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Stop-SafetyRun {
    param([string]$Message)

    throw "[UI safety] $Message"
}

function Invoke-External {
    param(
        [string]$Label,
        [string]$FilePath,
        [string[]]$Arguments = @()
    )

    Write-Section $Label
    & $FilePath @Arguments

    if ($LASTEXITCODE -ne 0) {
        Stop-SafetyRun "$Label failed with exit code $LASTEXITCODE."
    }
}

function Get-DotEnvValue {
    param([string]$Key)

    if (-not (Test-Path -LiteralPath ".env")) {
        return $null
    }

    $pattern = "^\s*$([regex]::Escape($Key))\s*="
    $line = Get-Content -LiteralPath ".env" |
        Where-Object { $_ -match $pattern } |
        Select-Object -First 1

    if (-not $line) {
        return $null
    }

    return (($line -replace $pattern, "").Trim().Trim('"').Trim("'"))
}

function Assert-EnvIsSafe {
    $envStatus = & git status --porcelain -- .env 2>$null

    if ($LASTEXITCODE -eq 0 -and $envStatus) {
        Stop-SafetyRun ".env has git-visible changes. Do not continue until secrets/env changes are removed from the worktree."
    }

    $appEnv = Get-DotEnvValue "APP_ENV"

    if ($appEnv -and $appEnv.ToLowerInvariant() -eq "production") {
        Stop-SafetyRun "APP_ENV is production. This local UI safety harness refuses to run against production configuration."
    }

    Write-Host "Environment guard passed: .env is not git-visible and APP_ENV is not production."
}

$repoRoot = (& git rev-parse --show-toplevel 2>$null).Trim()
if ($LASTEXITCODE -ne 0 -or -not $repoRoot) {
    Stop-SafetyRun "Run this script from inside the Speeda git repository."
}

Set-Location -LiteralPath $repoRoot

$branch = (& git branch --show-current).Trim()
Write-Section "Safety context"
Write-Host "Repository: $repoRoot"
Write-Host "Branch: $branch"
Write-Host "Tests: $(if ($RunTests) { 'enabled' } else { 'skipped by default' })"

Assert-EnvIsSafe

Invoke-External "Git whitespace/conflict marker check" "git" @("diff", "--check")

if (-not $SkipComposerValidate) {
    Invoke-External "Composer metadata validation" "composer" @("validate", "--no-check-publish", "--no-interaction")
}
else {
    Write-Section "Composer metadata validation"
    Write-Host "Skipped by request."
}

Invoke-External "Laravel route boot check" "php" @("artisan", "route:list", "--except-vendor", "--no-ansi")

try {
    Invoke-External "Blade compile check" "php" @("artisan", "view:cache", "--no-ansi")
}
finally {
    Write-Section "Clear compiled Blade views"
    & php artisan view:clear --no-ansi

    if ($LASTEXITCODE -ne 0) {
        Write-Warning "Could not clear compiled views automatically. Run php artisan view:clear manually."
    }
}

if (-not $SkipBuild) {
    Invoke-External "Vite production build" "npm" @("run", "build")
}
else {
    Write-Section "Vite production build"
    Write-Host "Skipped by request."
}

if ($RunTests) {
    Invoke-External "Laravel tests" "php" @("artisan", "test", "--no-ansi")
}
else {
    Write-Section "Laravel tests"
    Write-Host "Skipped by default. Use -RunTests only with a confirmed non-production test database."
}

Write-Section "UI safety smoke completed"
Write-Host "Safe checks passed. Continue with small UI-only commits and manual visual QA."
