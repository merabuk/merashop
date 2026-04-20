#!/usr/bin/env bash

set -e

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# 1. Find files via git
MD_FILES=$(git ls-files | grep -E '\.(md|markdown)$' || true)

if [[ -z "$MD_FILES" ]]; then
    echo -e "${YELLOW}No Markdown files to lint.${NC}"
    exit 0
fi

# 2. Set the path to the linter
# Priority: local node_modules -> global markdownlint -> mdl
if [ -f "./node_modules/.bin/markdownlint" ]; then
    LINTER="./node_modules/.bin/markdownlint"
elif command -v markdownlint >/dev/null 2>&1; then
    LINTER="markdownlint"
elif command -v mdl >/dev/null 2>&1; then
    LINTER="mdl"
else
    echo -e "${RED}Error: No linter found.${NC}"
    echo "Install it locally: npm i --save-dev markdownlint-cli"
    exit 127
fi

# 3. Function to run markdownlint
run_linter() {
    local fix_mode=$1
    local config_args=""

    # If it's markdownlint (not mdl), add the config
    if [[ "$LINTER" == *"markdownlint"* ]]; then
        [[ -f .markdownlint.json ]] && config_args="-c .markdownlint.json"
        $LINTER $fix_mode $config_args $MD_FILES
    else
        $LINTER $MD_FILES
    fi
}

# 4. Runtime base logic
echo "Using linter: $LINTER"

if run_linter; then
    echo -e "${GREEN}Done! Everything looks good.${NC}"
else
    echo -e "${RED}Linter found issues.${NC}"

    # Checking for interactivity (if you're running it manually in the terminal)
    if [[ -t 0 && "$LINTER" == *"markdownlint"* ]]; then
        read -r -p "Fix issues automatically with --fix? [y/N]: " answer
        if [[ "$answer" =~ ^[Yy] ]]; then
            echo "Applying fixes..."
            run_linter "--fix"
            echo "Verifying..."
            run_linter
            exit $?
        fi
    fi
    exit 1
fi
