#!/bin/bash

# Refresh master branch with latest changes and rebuild
git checkout master && git reset --hard HEAD && git pull origin master

# Fix permissions for scripts folder and all script files
if [ -d "scripts" ]; then
    echo "Fixing permissions for scripts folder..."
    chmod 755 scripts
    chmod +x scripts/*.sh 2>/dev/null
    echo "Scripts folder permissions updated."
else
    echo "Creating scripts folder..."
    mkdir -p scripts
    chmod 755 scripts
    echo "Scripts folder created with proper permissions."
fi

# Fix permissions for this script and other shell scripts in root
chmod +x *.sh 2>/dev/null

echo "Permissions reset complete."
