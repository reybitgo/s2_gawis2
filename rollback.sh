#!/bin/bash

# Rollback Script: Dual-Path Rank Advancement System
# Usage: ./rollback.sh [TIMESTAMP]

set -e  # Exit on error

# Configuration
TIMESTAMP="${1:-$(date +%Y%m%d_%H%M%S)}"
DB_NAME="${DB_DATABASE:-your_db_name}"
DB_USER="${DB_USERNAME:-your_db_user}"
BACKUP_DIR="/var/backups/your-app"

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

# Check for backup file
BACKUP_FILE="${BACKUP_DIR}/backup_before_deployment_${TIMESTAMP}.sql"

if [ ! -f "$BACKUP_FILE" ]; then
    log_error "Backup file not found: ${BACKUP_FILE}"
    log_info "Available backups:"
    ls -lh ${BACKUP_DIR}/backup_before_deployment_*.sql 2>/dev/null || log_error "No backups found"
    exit 1
fi

log_info "Starting rollback using backup: ${BACKUP_FILE}"
log_info "Timestamp: ${TIMESTAMP}"

# Step 1: Enable maintenance mode
log_info "Step 1: Enabling maintenance mode..."
php artisan down --message="System undergoing emergency maintenance."

# Step 2: Rollback code
log_info "Step 2: Rolling back code..."
cd /var/www/your-app

# Find most recent pre-deployment tag
TAG=$(git tag -l "pre-dual-path-*" | tail -1)

if [ -z "$TAG" ]; then
    log_warn "No pre-deployment git tag found, skipping code rollback"
else
    log_info "  - Checking out git tag: ${TAG}"
    git checkout ${TAG}

    if [ $? -eq 0 ]; then
        log_info "Code rollback successful!"
    else
        log_error "Code rollback failed!"
        exit 1
    fi
fi

# Step 3: Restore database
log_info "Step 3: Restoring database..."

if [ -f "$BACKUP_FILE" ]; then
    mysql -u ${DB_USER} -p${DB_PASSWORD} ${DB_NAME} < ${BACKUP_FILE}

    if [ $? -eq 0 ]; then
        log_info "Database restore successful!"
    else
        log_error "Database restore failed!"
        exit 1
    fi
else
    log_error "Backup file not found: ${BACKUP_FILE}"
    exit 1
fi

# Step 4: Clear caches
log_info "Step 4: Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Step 5: Restart queue workers
log_info "Step 5: Restarting queue workers..."
php artisan queue:restart

# Step 6: Disable maintenance mode
log_info "Step 6: Disabling maintenance mode..."
php artisan up

# Step 7: Verify rollback
log_info "Step 7: Verifying rollback..."

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

if [ "$TABLE_EXISTS" -eq 0 ]; then
    log_info "Database schema verified! (points_tracker table removed)"
else
    log_warn "Database schema may not be fully rolled back. Manual verification required."
fi

# Rollback complete
log_info "=========================================="
log_info "Rollback completed successfully!"
log_info "=========================================="
log_info ""
log_info "Next steps:"
log_info "1. Monitor logs: tail -f storage/logs/laravel.log"
log_info "2. Test user login and dashboard"
log_info "3. Test admin configuration"
log_info "4. Monitor database performance"
log_info "5. Communicate with users about rollback"
log_info ""
log_info "Backup used: ${BACKUP_FILE}"
log_info "Code tag used: ${TAG}"

exit 0
