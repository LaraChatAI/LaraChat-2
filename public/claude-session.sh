#!/bin/bash

# Script to start a new Claude session with hot repository

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
HOT_REPO_DIR="/Users/arturhanusek/PhpstormProjects/LaraChat/public/hot"
PROJECTS_DIR="/Users/arturhanusek/PhpstormProjects"

echo -e "${GREEN}Starting new Claude session setup...${NC}"

# Check if hot repository exists
if [ ! -d "$HOT_REPO_DIR" ]; then
    echo -e "${RED}Error: Hot repository directory not found at $HOT_REPO_DIR${NC}"
    exit 1
fi

# List contents of hot directory
echo -e "${YELLOW}Contents of hot repository:${NC}"
ls -la "$HOT_REPO_DIR"

# Get the repository name (first directory in hot folder)
REPO_NAME=$(ls -1 "$HOT_REPO_DIR" | head -n 1)

if [ -z "$REPO_NAME" ]; then
    echo -e "${RED}Error: No repository found in hot directory${NC}"
    exit 1
fi

SOURCE_PATH="$HOT_REPO_DIR/$REPO_NAME"
DEST_PATH="$PROJECTS_DIR/$REPO_NAME"

echo -e "${GREEN}Found repository: $REPO_NAME${NC}"

# Check if destination already exists
if [ -d "$DEST_PATH" ]; then
    echo -e "${YELLOW}Warning: Destination $DEST_PATH already exists${NC}"
    read -p "Do you want to overwrite it? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}Operation cancelled${NC}"
        exit 1
    fi
    echo -e "${YELLOW}Removing existing directory...${NC}"
    rm -rf "$DEST_PATH"
fi

# Move the repository to projects folder
echo -e "${GREEN}Moving repository from hot to projects...${NC}"
mv "$SOURCE_PATH" "$DEST_PATH"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}Successfully moved $REPO_NAME to $PROJECTS_DIR${NC}"
else
    echo -e "${RED}Error: Failed to move repository${NC}"
    exit 1
fi

# Change to the project directory and start Claude
echo -e "${GREEN}Changing to project directory: $DEST_PATH${NC}"
cd "$DEST_PATH"

# Check if claude command exists
if command -v claude &> /dev/null; then
    echo -e "${GREEN}Starting Claude in $DEST_PATH...${NC}"
    echo -e "${YELLOW}Working directory: $(pwd)${NC}"
    claude
else
    echo -e "${RED}Error: Claude command not found${NC}"
    echo -e "${YELLOW}Please ensure Claude CLI is installed and in your PATH${NC}"
    echo -e "${YELLOW}Current directory: $(pwd)${NC}"
    echo -e "${YELLOW}You can manually start Claude from this directory${NC}"
fi