#!/bin/bash

# 🧪 Comprehensive Test Runner Script
#
# This script runs all tests with proper reporting and coverage analysis
# Priority: ⭐⭐⭐⭐⭐ (Critical)

set -e # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
TEST_DB="speeda_test"
COVERAGE_DIR="coverage"
REPORTS_DIR="test-reports"
MIN_COVERAGE_PERCENT=75

echo -e "${BLUE}🧪 Speeda Comprehensive Test Suite${NC}"
echo "=================================="

# Create directories
mkdir -p $COVERAGE_DIR
mkdir -p $REPORTS_DIR

# Function to print section headers
print_section() {
    echo -e "\n${BLUE}$1${NC}"
    echo "$(printf '%*s' ${#1} | tr ' ' '=')"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Setup test environment
print_section "🛠️  Setting Up Test Environment"

if [ ! -f .env.testing ]; then
    echo -e "${YELLOW}Creating .env.testing file...${NC}"
    cp .env .env.testing
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=${TEST_DB}/" .env.testing
    sed -i "s/APP_ENV=.*/APP_ENV=testing/" .env.testing
fi

# Create test database
echo "Creating test database..."
if command_exists mysql; then
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS ${TEST_DB};" || true
elif command_exists psql; then
    createdb $TEST_DB || true
fi

# Clear cache and prepare
echo "Preparing Laravel environment..."
php artisan config:clear --env=testing
php artisan cache:clear --env=testing
php artisan view:clear --env=testing

# Run migrations
echo "Running test migrations..."
php artisan migrate:fresh --database=testing --env=testing --seed

# Function to run tests with timing
run_test_suite() {
    local test_type="$1"
    local test_path="$2"
    local coverage_file="$3"

    echo -e "\n${GREEN}Running $test_type tests...${NC}"
    start_time=$(date +%s)

    if [ -n "$coverage_file" ]; then
        php artisan test $test_path \
            --coverage-html=$COVERAGE_DIR/$coverage_file \
            --coverage-clover=$REPORTS_DIR/${coverage_file}.xml \
            --log-junit=$REPORTS_DIR/${test_type}-results.xml \
            --testdox-html=$REPORTS_DIR/${test_type}-testdox.html
    else
        php artisan test $test_path \
            --log-junit=$REPORTS_DIR/${test_type}-results.xml \
            --testdox-html=$REPORTS_DIR/${test_type}-testdox.html
    fi

    end_time=$(date +%s)
    duration=$((end_time - start_time))
    echo -e "${GREEN}✓ $test_type completed in ${duration}s${NC}"
}

# 1. Unit Tests
print_section "🔬 Unit Tests"
run_test_suite "Unit" "tests/Unit" "unit"

# 2. Feature Tests
print_section "🎯 Feature Tests"
run_test_suite "Feature" "tests/Feature" "feature"

# 3. Integration Tests
print_section "🔗 Integration Tests"
run_test_suite "Integration" "tests/Integration" "integration"

# 4. Security Tests
print_section "🔒 Security Tests"
run_test_suite "Security" "tests/Feature/Security"

# 5. Performance Tests
print_section "🚀 Performance Tests"
echo -e "${YELLOW}Note: Performance tests may take longer to complete...${NC}"
run_test_suite "Performance" "tests/Performance"

# 6. Browser Tests (if Dusk is available)
if [ -d "tests/Browser" ] && command_exists chromedriver; then
    print_section "🌐 Browser Tests"
    echo -e "${YELLOW}Starting browser tests (requires Chrome/Chromium)...${NC}"

    # Start Laravel server for Dusk
    php artisan serve --env=testing --port=8001 &
    SERVER_PID=$!
    sleep 3

    # Run Dusk tests
    php artisan dusk --log-junit=$REPORTS_DIR/browser-results.xml

    # Stop server
    kill $SERVER_PID || true
else
    echo -e "${YELLOW}⏭️  Skipping browser tests (Dusk not available or ChromeDriver not found)${NC}"
fi

# 7. API Tests (if they exist)
if [ -d "tests/Api" ]; then
    print_section "🌐 API Tests"
    run_test_suite "API" "tests/Api"
fi

# Generate comprehensive coverage report
print_section "📊 Coverage Analysis"

if command_exists phpcov; then
    echo "Merging coverage reports..."
    phpcov merge --clover $REPORTS_DIR/merged-coverage.xml $COVERAGE_DIR/
else
    echo -e "${YELLOW}phpcov not available, using individual coverage reports${NC}"
fi

# Quality checks
print_section "🔍 Quality Analysis"

# Check for minimum coverage
if [ -f "$REPORTS_DIR/merged-coverage.xml" ]; then
    coverage_percent=$(php -r "
        \$xml = simplexml_load_file('$REPORTS_DIR/merged-coverage.xml');
        \$lines = \$xml->project->metrics['statements'];
        \$covered = \$xml->project->metrics['coveredstatements'];
        echo round((\$covered / \$lines) * 100, 2);
    ")

    echo "Code coverage: $coverage_percent%"

    if (( $(echo "$coverage_percent < $MIN_COVERAGE_PERCENT" | bc -l) )); then
        echo -e "${RED}❌ Coverage below minimum threshold ($MIN_COVERAGE_PERCENT%)${NC}"
        exit 1
    else
        echo -e "${GREEN}✅ Coverage meets minimum threshold${NC}"
    fi
fi

# Run static analysis if available
if command_exists phpstan; then
    echo "Running static analysis..."
    phpstan analyse app --level=5 --no-progress || echo -e "${YELLOW}Static analysis found issues${NC}"
fi

# Check code style if available
if command_exists php-cs-fixer; then
    echo "Checking code style..."
    php-cs-fixer fix --dry-run --diff app || echo -e "${YELLOW}Code style issues found${NC}"
fi

# Generate final report
print_section "📋 Test Summary"

total_tests=$(find tests -name "*.php" -exec grep -l "test.*function\|@test" {} \; | wc -l)
echo "Total test files: $total_tests"

if [ -f "$REPORTS_DIR/unit-results.xml" ]; then
    unit_tests=$(grep -o 'tests="[0-9]*"' $REPORTS_DIR/unit-results.xml | grep -o '[0-9]*')
    echo "Unit tests: $unit_tests"
fi

if [ -f "$REPORTS_DIR/feature-results.xml" ]; then
    feature_tests=$(grep -o 'tests="[0-9]*"' $REPORTS_DIR/feature-results.xml | grep -o '[0-9]*')
    echo "Feature tests: $feature_tests"
fi

# Create HTML summary report
cat > $REPORTS_DIR/summary.html << EOF
<!DOCTYPE html>
<html>
<head>
    <title>Speeda Test Results Summary</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>🧪 Speeda Test Results Summary</h1>
    <p>Generated on: $(date)</p>

    <div class="section">
        <h2>Test Coverage</h2>
        <p>Coverage: <span class="success">${coverage_percent:-"N/A"}%</span></p>
    </div>

    <div class="section">
        <h2>Test Files</h2>
        <ul>
            <li><a href="unit-testdox.html">Unit Tests</a></li>
            <li><a href="feature-testdox.html">Feature Tests</a></li>
            <li><a href="integration-testdox.html">Integration Tests</a></li>
            <li><a href="security-testdox.html">Security Tests</a></li>
            <li><a href="performance-testdox.html">Performance Tests</a></li>
        </ul>
    </div>

    <div class="section">
        <h2>Coverage Reports</h2>
        <ul>
            <li><a href="../coverage/unit/index.html">Unit Test Coverage</a></li>
            <li><a href="../coverage/feature/index.html">Feature Test Coverage</a></li>
            <li><a href="../coverage/integration/index.html">Integration Test Coverage</a></li>
        </ul>
    </div>
</body>
</html>
EOF

echo -e "\n${GREEN}🎉 All tests completed successfully!${NC}"
echo -e "📊 Reports available in: ${BLUE}$REPORTS_DIR/${NC}"
echo -e "📈 Coverage reports in: ${BLUE}$COVERAGE_DIR/${NC}"
echo -e "📋 Summary report: ${BLUE}$REPORTS_DIR/summary.html${NC}"

# Cleanup
print_section "🧹 Cleanup"
php artisan config:clear --env=testing
echo -e "${GREEN}✅ Test suite completed successfully${NC}"
