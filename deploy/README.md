# AWS Deployment Guide

This directory contains everything needed to deploy the Laravel application to AWS.

## Deployment Options

### 1. EC2 Deployment (Simple)

For direct deployment to EC2 instances:

```bash
# Set environment variables
export EC2_HOST=your-ec2-ip
export EC2_USER=ubuntu
export EC2_KEY_PATH=~/.ssh/your-key.pem

# Run deployment
./deploy/deploy.sh production
```

### 2. Docker Deployment

Build and run with Docker:

```bash
# Build image
docker build -f deploy/docker/Dockerfile -t laravel-app .

# Run with docker-compose
cd deploy
docker-compose up -d
```

### 3. Terraform Infrastructure (Production-Ready)

Deploy complete AWS infrastructure:

```bash
cd deploy/terraform

# Initialize Terraform
terraform init

# Plan deployment
terraform plan -var="domain_name=yourdomain.com"

# Apply infrastructure
terraform apply -var="domain_name=yourdomain.com"
```

This creates:
- VPC with public/private subnets
- Application Load Balancer with SSL
- Auto Scaling Group (1-4 instances)
- RDS MySQL database
- ElastiCache Redis
- S3 bucket for storage
- CloudWatch monitoring

### 4. GitHub Actions CI/CD

Automated deployment on push to master:

1. Set GitHub secrets:
   - `AWS_ACCESS_KEY_ID`
   - `AWS_SECRET_ACCESS_KEY`
   - `EC2_HOST`
   - `EC2_USER`
   - `EC2_SSH_KEY`
   - `S3_DEPLOYMENT_BUCKET`

2. Push to master branch to trigger deployment

## Initial EC2 Setup

For new EC2 instances, run the setup script:

```bash
# SSH into EC2
ssh -i your-key.pem ubuntu@ec2-ip

# Download and run setup
curl -O https://raw.githubusercontent.com/your-repo/master/deploy/aws-setup.sh
chmod +x aws-setup.sh
sudo ./aws-setup.sh
```

## Environment Configuration

Create `.env` file with:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint
DB_DATABASE=laravel
DB_USERNAME=admin
DB_PASSWORD=your-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=your-redis-endpoint

AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-s3-bucket
```

## SSL Certificate

After deployment, set up SSL:

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## Monitoring

- CloudWatch dashboards automatically created
- Health check endpoint: `/health`
- Application logs in CloudWatch Logs
- Auto-scaling based on CPU utilization

## Rollback

To rollback deployment:

```bash
# EC2: Restore from backup
ssh ubuntu@ec2-ip
sudo tar -xzf /var/www/backups/backup_TIMESTAMP.tar.gz -C /var/www/app

# Terraform: Use previous state
terraform apply -target=aws_autoscaling_group.app -var="desired_capacity=2"
```

## Cost Optimization

- Use Spot instances for non-production
- Enable S3 lifecycle policies
- Use Reserved Instances for production
- Configure auto-scaling schedules

## Security Checklist

- [ ] Restrict SSH access in security groups
- [ ] Enable AWS WAF on ALB
- [ ] Rotate database passwords
- [ ] Enable encryption at rest
- [ ] Configure backup retention
- [ ] Set up CloudTrail logging
- [ ] Enable GuardDuty
- [ ] Configure Secrets Manager for credentials