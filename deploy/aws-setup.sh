#!/bin/bash

# AWS EC2 Setup Script for Laravel Application
# Run this on a fresh Ubuntu 22.04 or 24.04 EC2 instance

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_message() {
    echo -e "${2}${1}${NC}"
}

# Update system
print_message "Updating system packages..." "$YELLOW"
sudo apt-get update
sudo apt-get upgrade -y

# Install required packages
print_message "Installing required packages..." "$YELLOW"
sudo apt-get install -y \
    nginx \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-sqlite3 \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-gd \
    php8.3-intl \
    php8.3-redis \
    php8.3-opcache \
    composer \
    nodejs \
    npm \
    git \
    supervisor \
    redis-server \
    certbot \
    python3-certbot-nginx \
    unzip

# Install Node.js 20.x
print_message "Installing Node.js 20.x..." "$YELLOW"
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Configure PHP
print_message "Configuring PHP..." "$YELLOW"
sudo tee /etc/php/8.3/fpm/conf.d/99-laravel.ini > /dev/null << 'EOF'
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
EOF

# Create application directory
print_message "Creating application directory..." "$YELLOW"
sudo mkdir -p /var/www/app
sudo chown -R www-data:www-data /var/www/app

# Configure Nginx
print_message "Configuring Nginx..." "$YELLOW"
sudo tee /etc/nginx/sites-available/laravel > /dev/null << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    root /var/www/app/public;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml application/atom+xml image/svg+xml text/javascript application/vnd.ms-fontobject application/x-font-ttf font/opentype;

    # Health check endpoint
    location /health {
        access_log off;
        default_type text/plain;
        return 200 "healthy\n";
    }
}
EOF

# Enable site
sudo ln -sf /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Configure supervisor for queue workers
print_message "Configuring Supervisor for queue workers..." "$YELLOW"
sudo tee /etc/supervisor/conf.d/laravel-worker.conf > /dev/null << 'EOF'
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/worker.log
stopwaitsecs=3600
EOF

# Configure log rotation
print_message "Configuring log rotation..." "$YELLOW"
sudo tee /etc/logrotate.d/laravel > /dev/null << 'EOF'
/var/www/app/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.3-fpm
    endscript
}
EOF

# Create swap file (for small instances)
print_message "Creating swap file..." "$YELLOW"
if [ ! -f /swapfile ]; then
    sudo fallocate -l 2G /swapfile
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
fi

# Configure firewall
print_message "Configuring firewall..." "$YELLOW"
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# Start services
print_message "Starting services..." "$YELLOW"
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
sudo systemctl restart redis-server
sudo supervisorctl reread
sudo supervisorctl update

print_message "AWS EC2 setup completed successfully!" "$GREEN"
print_message "Next steps:" "$YELLOW"
print_message "1. Copy your .env file to /var/www/app/.env" "$NC"
print_message "2. Run the deployment script" "$NC"
print_message "3. Configure SSL with: sudo certbot --nginx -d yourdomain.com" "$NC"