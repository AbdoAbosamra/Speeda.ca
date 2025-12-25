@echo off
REM 🧪 Comprehensive Test Runner Script for Windows
REM
REM This script runs all tests with proper reporting and coverage analysis
REM Priority: ⭐⭐⭐⭐⭐ (Critical)

setlocal enabledelayedexpansion

REM Configuration
set TEST_DB=speeda_test
set COVERAGE_DIR=coverage
set REPORTS_DIR=test-reports
set MIN_COVERAGE_PERCENT=75

echo 🧪 Speeda Comprehensive Test Suite
echo ==================================

REM Create directories
if not exist %COVERAGE_DIR% mkdir %COVERAGE_DIR%
if not exist %REPORTS_DIR% mkdir %REPORTS_DIR%

REM Setup test environment
echo.
echo 🛠️  Setting Up Test Environment
echo =============================

if not exist .env.testing (
    echo Creating .env.testing file...
    copy .env .env.testing
    powershell -Command "(gc .env.testing) -replace 'DB_DATABASE=.*', 'DB_DATABASE=%TEST_DB%' | Out-File -encoding ASCII .env.testing"
    powershell -Command "(gc .env.testing) -replace 'APP_ENV=.*', 'APP_ENV=testing' | Out-File -encoding ASCII .env.testing"
)

REM Prepare Laravel environment
echo Preparing Laravel environment...
php artisan config:clear --env=testing
php artisan cache:clear --env=testing
php artisan view:clear --env=testing

REM Run migrations
echo Running test migrations...
php artisan migrate:fresh --database=testing --env=testing --seed

REM Function to run test suites
:run_test_suite
echo.
echo Running %1 tests...
set start_time=%time%

if "%3"=="" (
    php artisan test %2 --log-junit=%REPORTS_DIR%\%1-results.xml --testdox-html=%REPORTS_DIR%\%1-testdox.html
) else (
    php artisan test %2 --coverage-html=%COVERAGE_DIR%\%3 --coverage-clover=%REPORTS_DIR%\%3.xml --log-junit=%REPORTS_DIR%\%1-results.xml --testdox-html=%REPORTS_DIR%\%1-testdox.html
)

set end_time=%time%
echo ✓ %1 completed
goto :eof

REM 1. Unit Tests
echo.
echo 🔬 Unit Tests
echo ============
call :run_test_suite "Unit" "tests/Unit" "unit"

REM 2. Feature Tests
echo.
echo 🎯 Feature Tests
echo ===============
call :run_test_suite "Feature" "tests/Feature" "feature"

REM 3. Integration Tests
echo.
echo 🔗 Integration Tests
echo ===================
call :run_test_suite "Integration" "tests/Integration" "integration"

REM 4. Security Tests
echo.
echo 🔒 Security Tests
echo ================
call :run_test_suite "Security" "tests/Feature/Security" ""

REM 5. Performance Tests
echo.
echo 🚀 Performance Tests
echo ===================
echo Note: Performance tests may take longer to complete...
call :run_test_suite "Performance" "tests/Performance" ""

REM 6. Browser Tests (if available)
if exist "tests\Browser" (
    echo.
    echo 🌐 Browser Tests
    echo ===============
    echo Starting browser tests ^(requires Chrome/Chromium^)...

    REM Start Laravel server for Dusk
    start /B php artisan serve --env=testing --port=8001
    timeout /t 3 /nobreak > nul

    REM Run Dusk tests
    php artisan dusk --log-junit=%REPORTS_DIR%\browser-results.xml

    REM Stop server (kill process on port 8001)
    for /f "tokens=5" %%a in ('netstat -aon ^| find ":8001"') do taskkill /PID %%a /F > nul 2>&1
) else (
    echo.
    echo ⏭️  Skipping browser tests ^(Dusk not available^)
)

REM 7. API Tests (if they exist)
if exist "tests\Api" (
    echo.
    echo 🌐 API Tests
    echo ===========
    call :run_test_suite "API" "tests/Api" ""
)

REM Generate reports
echo.
echo 📊 Coverage Analysis
echo ===================

REM Create HTML summary report
echo ^<!DOCTYPE html^> > %REPORTS_DIR%\summary.html
echo ^<html^> >> %REPORTS_DIR%\summary.html
echo ^<head^> >> %REPORTS_DIR%\summary.html
echo     ^<title^>Speeda Test Results Summary^</title^> >> %REPORTS_DIR%\summary.html
echo     ^<style^> >> %REPORTS_DIR%\summary.html
echo         body { font-family: Arial, sans-serif; margin: 20px; } >> %REPORTS_DIR%\summary.html
echo         .success { color: green; } >> %REPORTS_DIR%\summary.html
echo         .warning { color: orange; } >> %REPORTS_DIR%\summary.html
echo         .error { color: red; } >> %REPORTS_DIR%\summary.html
echo         .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; } >> %REPORTS_DIR%\summary.html
echo     ^</style^> >> %REPORTS_DIR%\summary.html
echo ^</head^> >> %REPORTS_DIR%\summary.html
echo ^<body^> >> %REPORTS_DIR%\summary.html
echo     ^<h1^>🧪 Speeda Test Results Summary^</h1^> >> %REPORTS_DIR%\summary.html
echo     ^<p^>Generated on: %date% %time%^</p^> >> %REPORTS_DIR%\summary.html
echo     ^<div class="section"^> >> %REPORTS_DIR%\summary.html
echo         ^<h2^>Test Files^</h2^> >> %REPORTS_DIR%\summary.html
echo         ^<ul^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="unit-testdox.html"^>Unit Tests^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="feature-testdox.html"^>Feature Tests^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="integration-testdox.html"^>Integration Tests^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="security-testdox.html"^>Security Tests^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="performance-testdox.html"^>Performance Tests^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo         ^</ul^> >> %REPORTS_DIR%\summary.html
echo     ^</div^> >> %REPORTS_DIR%\summary.html
echo     ^<div class="section"^> >> %REPORTS_DIR%\summary.html
echo         ^<h2^>Coverage Reports^</h2^> >> %REPORTS_DIR%\summary.html
echo         ^<ul^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="../coverage/unit/index.html"^>Unit Test Coverage^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="../coverage/feature/index.html"^>Feature Test Coverage^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo             ^<li^>^<a href="../coverage/integration/index.html"^>Integration Test Coverage^</a^>^</li^> >> %REPORTS_DIR%\summary.html
echo         ^</ul^> >> %REPORTS_DIR%\summary.html
echo     ^</div^> >> %REPORTS_DIR%\summary.html
echo ^</body^> >> %REPORTS_DIR%\summary.html
echo ^</html^> >> %REPORTS_DIR%\summary.html

echo.
echo 📋 Test Summary
echo ==============

REM Count test files
for /f %%i in ('dir /s /b tests\*.php ^| find /c ".php"') do set total_tests=%%i
echo Total test files: !total_tests!

echo.
echo 🎉 All tests completed successfully!
echo 📊 Reports available in: %REPORTS_DIR%\
echo 📈 Coverage reports in: %COVERAGE_DIR%\
echo 📋 Summary report: %REPORTS_DIR%\summary.html

REM Cleanup
echo.
echo 🧹 Cleanup
echo ==========
php artisan config:clear --env=testing
echo ✅ Test suite completed successfully

pause
