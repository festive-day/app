#!/bin/bash
# Test Runner Script for Cookie Consent Manager
# T092: Run all integration tests from tests/integration/

echo "Running Cookie Consent Manager Integration Tests..."
echo "=================================================="

# Check if WP_TESTS_DIR is set
if [ -z "$WP_TESTS_DIR" ]; then
    echo "Error: WP_TESTS_DIR environment variable not set."
    echo "Please set it to your WordPress test suite directory."
    echo "Example: export WP_TESTS_DIR=/path/to/wordpress-tests-lib"
    exit 1
fi

# Check if PHPUnit is available
if ! command -v phpunit &> /dev/null; then
    echo "Error: PHPUnit not found. Please install PHPUnit."
    echo "Example: composer require --dev phpunit/phpunit"
    exit 1
fi

# Run tests
echo "Running integration tests..."
phpunit --bootstrap tests/bootstrap.php tests/integration/

# Check exit code
if [ $? -eq 0 ]; then
    echo ""
    echo "=================================================="
    echo "✅ All tests passed!"
    echo "=================================================="
    exit 0
else
    echo ""
    echo "=================================================="
    echo "❌ Some tests failed. Please review the output above."
    echo "=================================================="
    exit 1
fi

