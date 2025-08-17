#!/bin/bash

# AWS Deployment Script for Laravel + Vue Application
# This script handles deployment to EC2 instances

set -e

# Configuration
DEPLOY_ENV=${1:-production}
AWS_REGION=${AWS_REGION:-us-east-1}
EC2_HOST=${EC2_HOST}
EC2_USER=${EC2_USER:-ubuntu}
EC2_KEY_PATH=${EC2_KEY_PATH:-~/.ssh/aws-key.pem}
APP_PATH=${APP_PATH:-/var/www/app}
BRANCH=${2:-master}

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_message() {
    echo -e "${2}${1}${NC}"
}

# Check required environment variables
check_requirements() {
    if [ -z "$EC2_HOST" ]; then
        print_message "Error: EC2_HOST environment variable is not set" "$RED"
        exit 1
    fi

    if [ ! -f "$EC2_KEY_PATH" ]; then
        print_message "Error: SSH key not found at $EC2_KEY_PATH" "$RED"
        exit 1
    fi
}

# Build assets locally
build_assets() {
    print_message "Building production assets..." "$YELLOW"
    
    # Install dependencies
    npm ci --prefer-offline --no-audit
    composer install --no-dev --optimize-autoloader
    
    # Build frontend assets
    npm run build
    
    # Create deployment archive
    tar -czf deploy.tar.gz \
        --exclude=node_modules \
        --exclude=.git \
        --exclude=.env \
        --exclude=storage/app/* \
        --exclude=storage/logs/* \
        --exclude=storage/framework/cache/* \
        --exclude=storage/framework/sessions/* \
        --exclude=storage/framework/views/* \
        --exclude=bootstrap/cache/* \
        --exclude=tests \
        --exclude=deploy \
        .
    
    print_message "Assets built successfully" "$GREEN"
}

# Deploy to EC2
deploy_to_ec2() {
    print_message "Deploying to EC2 instance..." "$YELLOW"
    
    # Upload deployment archive
    scp -i "$EC2_KEY_PATH" deploy.tar.gz "$EC2_USER@$EC2_HOST:/tmp/"
    
    # Execute deployment commands on remote server
    ssh -i "$EC2_KEY_PATH" "$EC2_USER@$EC2_HOST" << 'ENDSSH'
        set -e
        
        # Variables
        APP_PATH="/var/www/app"
        BACKUP_PATH="/var/www/backups"
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        
        # Create backup of current deployment
        if [ -d "$APP_PATH" ]; then
            echo "Creating backup..."
            sudo mkdir -p "$BACKUP_PATH"
            sudo tar -czf "$BACKUP_PATH/backup_$TIMESTAMP.tar.gz" -C "$APP_PATH" .
        fi
        
        # Extract new deployment
        echo "Extracting new deployment..."
        sudo mkdir -p "$APP_PATH"
        sudo tar -xzf /tmp/deploy.tar.gz -C "$APP_PATH"
        
        # Set permissions
        sudo chown -R www-data:www-data "$APP_PATH"
        sudo chmod -R 755 "$APP_PATH"
        sudo chmod -R 775 "$APP_PATH/storage"
        sudo chmod -R 775 "$APP_PATH/bootstrap/cache"
        
        # Navigate to app directory
        cd "$APP_PATH"
        
        # Install PHP dependencies
        echo "Installing PHP dependencies..."
        sudo -u www-data composer install --no-dev --optimize-autoloader
        
        # Run Laravel deployment commands
        echo "Running Laravel deployment commands..."
        sudo -u www-data php artisan config:cache
        sudo -u www-data php artisan route:cache
        sudo -u www-data php artisan view:cache
        sudo -u www-data php artisan migrate --force
        sudo -u www-data php artisan queue:restart
        
        # Optimize Opcache
        if command -v cachetool &> /dev/null; then
            sudo cachetool opcache:reset
        fi
        
        # Reload PHP-FPM
        sudo systemctl reload php8.2-fpm || sudo systemctl reload php8.3-fpm
        
        # Reload Nginx
        sudo systemctl reload nginx
        
        # Clean up
        rm /tmp/deploy.tar.gz
        
        echo "Deployment completed successfully!"
ENDSSH
    
    # Clean up local archive
    rm deploy.tar.gz
    
    print_message "Deployment completed successfully!" "$GREEN"
}

# Health check
health_check() {
    print_message "Running health check..." "$YELLOW"
    
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "http://$EC2_HOST/health" || echo "000")
    
    if [ "$RESPONSE" = "200" ]; then
        print_message "Health check passed!" "$GREEN"
    else
        print_message "Health check failed with status: $RESPONSE" "$RED"
        exit 1
    fi
}

# Main execution
main() {
    print_message "Starting deployment to $DEPLOY_ENV environment" "$GREEN"
    
    check_requirements
    build_assets
    deploy_to_ec2
    health_check
    
    print_message "Deployment completed successfully!" "$GREEN"
}

# Run main function
main