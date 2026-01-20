#!/bin/bash

# Deployment Script: Dual-Path Rank Advancement System
# Usage: ./deploy.sh [--staging|--production|--dry-run]

set -e  # Exit on error

# Configuration
DEPLOY_TYPE="${1:-production}"
DB_NAME="${DB_DATABASE:-your_db_name}"
DB_USER="${DB_USERNAME:-your_db_user}"
BACKUP_DIR="/var/backups/your-app"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Pre-flight checks
log_info "Starting deployment for ${DEPLOY_TYPE}..."
log_info "Timestamp: ${TIMESTAMP}"

if [ "$DEPLOY_TYPE" == "--dry-run" ]; then
    log_warn "Dry-run mode - no changes will be made"
    exit 0
fi

# Step 1: Create backups
log_info "Step 1: Creating backups..."

mkdir -p ${BACKUP_DIR}

# Database backup
log_info "Backing up database..."
mysqldump -u ${DB_USER} -p${DB_PASSWORD} ${DB_NAME} \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    --events \
    > ${BACKUP_DIR}/backup_before_deployment_${TIMESTAMP}.sql

if [ $? -eq 0 ]; then
    log_info "Database backup successful: ${BACKUP_DIR}/backup_before_deployment_${TIMESTAMP}.sql"
else
    log_error "Database backup failed!"
    exit 1
fi

# Code backup
log_info "Backing up application code..."
tar -czf ${BACKUP_DIR}/your-app_backup_${TIMESTAMP}.tar.gz /var/www/your-app

if [ $? -eq 0 ]; then
    log_info "Code backup successful: ${BACKUP_DIR}/your-app_backup_${TIMESTAMP}.tar.gz"
else
    log_error "Code backup failed!"
    exit 1
fi

# Step 2: Enable maintenance mode
log_info "Step 2: Enabling maintenance mode..."
php artisan down --message="System maintenance in progress. We'll be back soon."

# Step 3: Deploy code
log_info "Step 3: Deploying code..."
cd /var/www/your-app

if [ "$DEPLOY_TYPE" == "--staging" ]; then
    git checkout staging
    git pull origin staging
else
    git checkout main
    git pull origin main
fi

# Install dependencies
log_info "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Build assets
log_info "Building frontend assets..."
npm run build

# Step 4: Run migrations
log_info "Step 4: Running migrations..."

# Run enum update first (CRITICAL)
log_info "  - Running enum update migration..."
php artisan migrate --path=database/migrations/2026_01_20_084117_update_rank_advancements_enum_for_dual_path.php

if [ $? -ne 0 ]; then
    log_error "Enum update migration failed! Rolling back..."
    ./rollback.sh ${TIMESTAMP}
    exit 1
fi

# Run remaining migrations
log_info "  - Running remaining migrations..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    log_error "Migrations failed! Rolling back..."
    ./rollback.sh ${TIMESTAMP}
    exit 1
fi

log_info "Migrations completed successfully!"

# Step 5: Clear caches
log_info "Step 5: Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Recreate optimized caches
log_info "  - Recreating optimized caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 6: Restart queue workers
log_info "Step 6: Restarting queue workers..."
php artisan queue:restart

# Step 7: Disable maintenance mode
log_info "Step 7: Disabling maintenance mode..."
php artisan up

# Step 8: Verify deployment
log_info "Step 8: Verifying deployment..."

# Check if application is accessible
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://your-domain.com)

if [ "$HTTP_CODE" == "200" ]; then
    log_info "Application is accessible!"
else
    log_error "Application returned HTTP ${HTTP_CODE}! Check logs."
    exit 1
fi

# Check database schema
log_info "  - Verifying database schema..."
TABLE_EXISTS=$(mysql -u ${DB_USER} -p${DB_PASSWORD} -e "SHOW TABLES LIKE 'points_tracker'" ${DB_NAME} | grep -c points_tracker || 0)

if [ "$TABLE_EXISTS" -eq 1 ]; then
    log_info "Database schema verified!"
else
    log_error "Database schema verification failed!"
    exit 1
fi

# Deployment complete
log_info "=========================================="
log_info "Deployment completed successfully!"
log_info "=========================================="
log_info ""
log_info "Next steps:"
log_info "1. Monitor logs: tail -f storage/logs/laravel.log"
log_info "2. Monitor queue: php artisan queue:work"
log_info "3. Test user dashboard"
log_info "4. Test admin configuration"
log_info "5. Send user announcement"
log_info ""
log_info "Backup location: ${BACKUP_DIR}/"
log_info "Rollback command: ./rollback.sh ${TIMESTAMP}"

exit 0
