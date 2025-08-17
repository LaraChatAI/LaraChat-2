#!/bin/bash

# User data script for EC2 instances
set -e

# Update system
apt-get update
apt-get upgrade -y

# Install required packages
apt-get install -y \
    nginx \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-gd \
    php8.3-intl \
    php8.3-redis \
    composer \
    git \
    supervisor \
    awscli

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Clone application from S3 or Git
aws s3 cp s3://${s3_bucket}/releases/latest/app.tar.gz /tmp/
tar -xzf /tmp/app.tar.gz -C /var/www/app

# Set up environment variables
cat > /var/www/app/.env << EOF
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:$(openssl rand -base64 32)
APP_DEBUG=false
APP_URL=${app_url}

DB_CONNECTION=mysql
DB_HOST=${db_host}
DB_PORT=3306
DB_DATABASE=${db_name}
DB_USERNAME=${db_user}
DB_PASSWORD=${db_password}

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=${redis_host}
REDIS_PASSWORD=null
REDIS_PORT=6379

AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=${s3_bucket}
EOF

# Set permissions
chown -R www-data:www-data /var/www/app
chmod -R 755 /var/www/app
chmod -R 775 /var/www/app/storage
chmod -R 775 /var/www/app/bootstrap/cache

# Install dependencies
cd /var/www/app
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan migrate --force

# Configure Nginx
cat > /etc/nginx/sites-available/laravel << 'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    
    root /var/www/app/public;
    index index.php;
    
    server_name _;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    location /health {
        access_log off;
        default_type text/plain;
        return 200 "healthy\n";
    }
}
NGINX

ln -sf /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Configure Supervisor
cat > /etc/supervisor/conf.d/laravel-worker.conf << 'SUPERVISOR'
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/worker.log
SUPERVISOR

# Start services
systemctl restart php8.3-fpm
systemctl restart nginx
supervisorctl reread
supervisorctl update

# Configure CloudWatch agent (optional)
wget https://s3.amazonaws.com/amazoncloudwatch-agent/ubuntu/amd64/latest/amazon-cloudwatch-agent.deb
dpkg -i amazon-cloudwatch-agent.deb