#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored messages
print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "ℹ $1"
}

# Step 1: Check current branch and status
print_info "Checking current branch and status..."
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"

# Check if we're on master/main
if [[ "$CURRENT_BRANCH" == "master" ]] || [[ "$CURRENT_BRANCH" == "main" ]]; then
    print_error "You are on $CURRENT_BRANCH branch!"
    print_warning "Please create a feature branch first using: git checkout -b feature/descriptive-name"
    exit 1
fi

# Show git status
git status

# Step 2: Check for uncommitted changes
if [[ -n $(git status --porcelain) ]]; then
    print_warning "You have uncommitted changes. Committing them now..."
    
    # Add all files
    git add .
    
    # Get commit message from user
    read -p "Enter commit message: " COMMIT_MSG
    if [[ -z "$COMMIT_MSG" ]]; then
        COMMIT_MSG="Update changes for PR"
    fi
    
    git commit -m "$COMMIT_MSG"
    print_success "Changes committed"
else
    print_success "No uncommitted changes"
fi

# Step 3: Get PR details from user
print_info "Preparing to create PR..."

# Extract feature name from current branch if it's already a feature branch
if [[ "$CURRENT_BRANCH" == feature/* ]]; then
    FEATURE_NAME="${CURRENT_BRANCH#feature/}"
    print_info "Using current feature branch: feature/$FEATURE_NAME"
else
    read -p "Enter feature branch name (e.g., fix-sidebar-navigation): " FEATURE_NAME
    if [[ -z "$FEATURE_NAME" ]]; then
        print_error "Feature branch name cannot be empty!"
        exit 1
    fi
    
    # Create and checkout feature branch
    print_info "Creating feature branch: feature/$FEATURE_NAME"
    git checkout -b "feature/$FEATURE_NAME"
fi

read -p "Enter PR title: " PR_TITLE
if [[ -z "$PR_TITLE" ]]; then
    print_error "PR title cannot be empty!"
    exit 1
fi

read -p "Enter PR description (use \\n for new lines): " PR_BODY
if [[ -z "$PR_BODY" ]]; then
    PR_BODY="PR created via script"
fi

# Convert \n to actual newlines in PR body
PR_BODY=$(echo -e "$PR_BODY")

# Step 4: Push to fork and create PR
print_info "Pushing to fork and creating PR..."

# Execute the exact command pattern from AGENTS.md
if git push -u fork "feature/$FEATURE_NAME"; then
    print_success "Pushed to fork successfully"
    
    # Create PR using gh CLI
    if gh pr create \
        --base master \
        --head "AdamAidenCommet:feature/$FEATURE_NAME" \
        --repo CodingCab/LaraChat \
        --title "$PR_TITLE" \
        --body "$PR_BODY"; then
        
        print_success "PR created successfully!"
        print_info "You can view your PR at: https://github.com/CodingCab/LaraChat/pulls"
    else
        print_error "Failed to create PR. Please check your gh CLI authentication and try again."
        exit 1
    fi
else
    print_error "Failed to push to fork. Please check your remote configuration."
    print_info "Make sure 'fork' remote is configured: git remote -v"
    exit 1
fi