#!/bin/bash

# Script to manage repository workflow for new conversations
# Usage: ./start-conversation.sh <repository-name> <new-branch-name>

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check arguments
if [ $# -ne 2 ]; then
    echo "Usage: $0 <repository-name> <new-branch-name>"
    exit 1
fi

REPO_NAME="$1"
BRANCH_NAME="$2"
REPO_BASE_DIR="$(dirname "$(dirname "$(realpath "$0")")")/repositories"

HOT_REPO="$REPO_BASE_DIR/hot/$REPO_NAME"
PROJECT_REPO="$REPO_BASE_DIR/project/$REPO_NAME/$BRANCH_NAME"
BASE_REPO="$REPO_BASE_DIR/base/$REPO_NAME"

# Check if hot repository exists
if [ ! -d "$HOT_REPO" ]; then
    echo -e "${YELLOW}Warning: Hot repository '$HOT_REPO' does not exist${NC}"
    echo "Please ensure the repository exists in repositories/hot/ first"
    exit 1
fi

# Move repository from hot to project with new branch
echo -e "${GREEN}Moving repository from hot to project...${NC}"
mkdir -p "$(dirname "$PROJECT_REPO")"
mv "$HOT_REPO" "$PROJECT_REPO"
echo "✓ Moved to: $PROJECT_REPO"

# Change to the new directory
cd "$PROJECT_REPO"
echo "✓ Changed directory to: $PROJECT_REPO"

# Copy base repository back to hot in background
if [ -d "$BASE_REPO" ]; then
    echo -e "${GREEN}Copying base repository back to hot (in background)...${NC}"
    (
        cp -r "$BASE_REPO" "$HOT_REPO" 2>/dev/null && \
        echo "✓ Background copy completed: base → hot" || \
        echo "✗ Background copy failed"
    ) &
    BACKGROUND_PID=$!
    echo "✓ Background copy started (PID: $BACKGROUND_PID)"
else
    echo -e "${YELLOW}Warning: Base repository '$BASE_REPO' does not exist${NC}"
    echo "Skipping background copy"
fi

echo -e "${GREEN}Setup complete!${NC}"
echo "Current directory: $(pwd)"

# Start Claude from the current directory (which is already the project directory)
echo -e "${GREEN}Starting Claude from: $(pwd)${NC}"
# Use exec to replace the shell with claude, keeping the current working directory
exec claude
