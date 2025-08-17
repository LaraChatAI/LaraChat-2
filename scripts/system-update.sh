#!/bin/bash

# System Update Script
# Combines refresh-master.sh and deploy.sh functionality

echo "Starting system update..."

# First run refresh-master.sh
echo "Refreshing from master branch..."
./scripts/refresh-master.sh

# Check if refresh-master.sh succeeded
if [ $? -eq 0 ]; then
    echo "Master branch refresh completed successfully"
    
    # Now run deploy.sh
    echo "Running deployment..."
    ./scripts/deploy.sh
    
    if [ $? -eq 0 ]; then
        echo "System update completed successfully!"
        exit 0
    else
        echo "Deployment failed!"
        exit 1
    fi
else
    echo "Master branch refresh failed! Skipping deployment."
    exit 1
fi