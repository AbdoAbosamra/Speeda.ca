#!/usr/bin/env bash
set -euo pipefail

run_tests=0
skip_build=0
skip_composer_validate=0

for arg in "$@"; do
    case "$arg" in
        --run-tests)
            run_tests=1
            ;;
        --skip-build)
            skip_build=1
            ;;
        --skip-composer-validate)
            skip_composer_validate=1
            ;;
        *)
            echo "[UI safety] Unknown argument: $arg" >&2
            exit 2
            ;;
    esac
done

section() {
    printf '\n==> %s\n' "$1"
}

fail() {
    echo "[UI safety] $1" >&2
    exit 1
}

run() {
    section "$1"
    shift
    "$@"
}

repo_root="$(git rev-parse --show-toplevel 2>/dev/null || true)"
if [[ -z "$repo_root" ]]; then
    fail "Run this script from inside the Speeda git repository."
fi

cd "$repo_root"

branch="$(git branch --show-current)"
section "Safety context"
echo "Repository: $repo_root"
echo "Branch: $branch"
if [[ "$run_tests" -eq 1 ]]; then
    echo "Tests: enabled"
else
    echo "Tests: skipped by default"
fi

env_status="$(git status --porcelain -- .env 2>/dev/null || true)"
if [[ -n "$env_status" ]]; then
    fail ".env has git-visible changes. Do not continue until secrets/env changes are removed from the worktree."
fi

app_env=""
if [[ -f ".env" ]]; then
    app_env="$(
        awk -F= '
            $1 ~ /^[[:space:]]*APP_ENV[[:space:]]*$/ {
                value=$2
                gsub(/^[[:space:]"'"'"']+|[[:space:]"'"'"']+$/, "", value)
                print value
                exit
            }
        ' .env
    )"
fi

if [[ "${app_env,,}" == "production" ]]; then
    fail "APP_ENV is production. This local UI safety harness refuses to run against production configuration."
fi

echo "Environment guard passed: .env is not git-visible and APP_ENV is not production."

run "Git whitespace/conflict marker check" git diff --check

if [[ "$skip_composer_validate" -eq 0 ]]; then
    run "Composer metadata validation" composer validate --no-check-publish --no-interaction
else
    section "Composer metadata validation"
    echo "Skipped by request."
fi

run "Laravel route boot check" php artisan route:list --except-vendor --no-ansi

section "Blade compile check"
set +e
php artisan view:cache --no-ansi
view_cache_code=$?
section "Clear compiled Blade views"
php artisan view:clear --no-ansi
view_clear_code=$?
set -e

if [[ "$view_clear_code" -ne 0 ]]; then
    echo "[UI safety] Warning: could not clear compiled views automatically. Run php artisan view:clear manually." >&2
fi

if [[ "$view_cache_code" -ne 0 ]]; then
    fail "Blade compile check failed with exit code $view_cache_code."
fi

if [[ "$skip_build" -eq 0 ]]; then
    run "Vite production build" npm run build
else
    section "Vite production build"
    echo "Skipped by request."
fi

if [[ "$run_tests" -eq 1 ]]; then
    run "Laravel tests" php artisan test --no-ansi
else
    section "Laravel tests"
    echo "Skipped by default. Use --run-tests only with a confirmed non-production test database."
fi

section "UI safety smoke completed"
echo "Safe checks passed. Continue with small UI-only commits and manual visual QA."
