#!/bin/bash

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Check if PROJECT_ID is provided as first argument
if [ -z "$1" ]; then
    echo "Error: PROJECT_ID is required as first argument"
    echo "Usage: $(basename "$0") <PROJECT_ID> [claude arguments...]"
    echo "Example: $(basename "$0") my-project --help"
    exit 1
fi

PROJECT_ID="$1"
shift # Remove PROJECT_ID from arguments to pass remaining args to claude

# For LaraChat, we work in the LaraChat directory itself
# or use a specific project directory if provided
if [ "$PROJECT_ID" = "default" ] || [ "$PROJECT_ID" = "larachat" ]; then
    # Use the LaraChat directory itself
    PROJECT_DIR="$SCRIPT_DIR"
else
    # Try project-specific locations
    # Try new location first (direct subdomain path)
    PROJECT_DIR="/Users/customer/www/subdomains/$PROJECT_ID"
    
    # If not found, try old location in storage
    if [ ! -d "$PROJECT_DIR" ]; then
        PROJECT_DIR="$SCRIPT_DIR/storage/app/private/repositories/projects/$PROJECT_ID"
    fi
    
    # If still not found, try another old location
    if [ ! -d "$PROJECT_DIR" ]; then
        PROJECT_DIR="$SCRIPT_DIR/storage/Users/customer/www/subdomains/projects/$PROJECT_ID"
    fi
fi

if [ ! -d "$PROJECT_DIR" ]; then
    echo "Error: Project directory does not exist: $PROJECT_DIR"
    echo "Available locations checked:"
    echo "  - /Users/customer/www/subdomains/$PROJECT_ID"
    echo "  - $SCRIPT_DIR/storage/app/private/repositories/projects/$PROJECT_ID"
    echo "  - $SCRIPT_DIR/storage/Users/customer/www/subdomains/projects/$PROJECT_ID"
    exit 1
fi

cd "$PROJECT_DIR" || exit 1

# Set up environment for Claude CLI
# Use the actual user's environment
export PATH="/opt/homebrew/bin:$PATH"
export HOME="${HOME:-/Users/arturhanusek}"
export USER="${USER:-arturhanusek}"

# Execute claude with all arguments from the project directory
exec claude "$@"