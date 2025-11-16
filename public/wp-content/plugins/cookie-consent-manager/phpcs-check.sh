#!/bin/bash
# PHPCS Check Script for Cookie Consent Manager
# Runs WordPress Coding Standards validation

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=========================================="
echo "PHPCS WordPress Coding Standards Check"
echo "=========================================="
echo ""

# Check if PHPCS is available
if ! command -v phpcs &> /dev/null; then
    echo -e "${YELLOW}PHPCS not found. Attempting to install via Composer...${NC}"
    
    # Check if composer.json exists
    if [ ! -f "composer.json" ]; then
        echo "Creating composer.json..."
        cat > composer.json << 'EOF'
{
    "require-dev": {
        "wp-coding-standards/wpcs": "^3.0",
        "squizlabs/php_codesniffer": "^3.7"
    },
    "scripts": {
        "phpcs": "phpcs --standard=.phpcs.xml .",
        "phpcbf": "phpcbf --standard=.phpcs.xml ."
    }
}
EOF
    fi
    
    # Install dependencies
    if command -v composer &> /dev/null; then
        echo "Installing PHPCS and WordPress Coding Standards..."
        composer install --no-interaction
        # Use vendor/bin/phpcs
        PHPCS_BIN="./vendor/bin/phpcs"
    else
        echo -e "${RED}Error: Neither phpcs nor composer found.${NC}"
        echo "Please install PHPCS globally or install Composer."
        echo ""
        echo "To install PHPCS globally:"
        echo "  composer global require squizlabs/php_codesniffer wp-coding-standards/wpcs"
        echo "  export PATH=\$PATH:~/.composer/vendor/bin"
        exit 1
    fi
else
    PHPCS_BIN="phpcs"
fi

# Check if WordPress Coding Standards are installed
if [ -f ".phpcs.xml" ]; then
    echo "Using .phpcs.xml configuration..."
    $PHPCS_BIN --standard=.phpcs.xml .
    EXIT_CODE=$?
else
    echo -e "${YELLOW}Warning: .phpcs.xml not found. Using WordPress standard directly...${NC}"
    $PHPCS_BIN --standard=WordPress .
    EXIT_CODE=$?
fi

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✓ PHPCS check passed!${NC}"
else
    echo -e "${RED}✗ PHPCS check failed. Please fix the issues above.${NC}"
    echo ""
    echo "To auto-fix some issues, run:"
    echo "  phpcbf --standard=.phpcs.xml ."
fi

exit $EXIT_CODE

