#!/usr/bin/env bash

set -e # Stop execution on error

# Color settings for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Find files via git
MD_FILES=$(git ls-files | grep -E '\.(md|markdown)$' || true)

if [[ -z "$MD_FILES" ]]; then
    echo -e "${YELLOW}No Markdown files to lint.${NC}"
    exit 0
fi

# 2. Function to run markdownlint
run_markdownlint() {
    local fix_mode=$1
    local config_args=""

    if [[ -f .markdownlint.json ]]; then
        config_args="-c .markdownlint.json"
    fi

    if [[ "$fix_mode" == "--fix" ]]; then
        markdownlint --fix $config_args $MD_FILES
    else
        markdownlint $config_args $MD_FILES
    fi
}

# 3. The basic logic behind choosing a tool
if command -v markdownlint >/dev/null 2>&1; then
    echo "Running markdownlint..."

    # Attempt to launch without fixes
    if run_markdownlint; then
        echo -e "${GREEN}Done markdownlint!${NC}"
    else
        echo -e "${RED}markdownlint found issues.${NC}"

        # Checking the interactivity of the terminal
        if [[ -t 0 ]]; then
            read -r -p "Fix issues automatically with --fix? [y/N]: " answer
            if [[ "$answer" =~ ^[Yy] ]]; then
                echo "Applying fixes..."
                run_markdownlint "--fix"
                echo "Verifying fixes..."
                run_markdownlint
                exit $?
            fi
        fi
        exit 1
    fi

elif command -v mdl >/dev/null 2>&1; then
    echo "Running mdl..."
    mdl $MD_FILES
else
    echo -e "${RED}Error: No linter found.${NC}"
    echo "Install: npm i -g markdownlint-cli OR gem install mdl"
    exit 127
fi
