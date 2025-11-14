# Database Reset Functionality

This document describes the database reset functionality that allows admins to reset user data while preserving system configuration.

## Overview

The database reset feature provides a secure way to reset user data while preserving system settings, including:

- All user accounts (except default admin and member)
- All wallet balances and transactions
- **System settings are PRESERVED (not reset)**
- Roles and permissions recreated
- Default users recreated with fresh wallets

## Security Features

- **Admin Only**: Only users with admin role can access
- **Authentication Required**: Must be logged in and verified
- **Confirmation Required**: Two-step confirmation process
- **Activity Logging**: All reset attempts are logged
- **IP Tracking**: IP addresses are recorded for security

## Usage

### Web Interface
1. Log in as admin user
2. Navigate to: `http://your-domain.com/reset`
3. Review the confirmation page
4. Check the confirmation checkbox
5. Click "Reset Database"

### API Usage
```bash
# Get reset status (requires admin authentication)
GET /reset-status

# Request reset confirmation
GET /reset

# Perform reset (requires confirmation)
GET /reset?confirm=yes
```

## Default Accounts After Reset

### Admin Account
- **Email**: admin@ewallet.com
- **Password**: Admin123!@#
- **Role**: admin
- **Wallet Balance**: $1,000.00
- **Permissions**: All permissions

### Member Account
- **Email**: member@ewallet.com
- **Password**: Member123!@#
- **Role**: member
- **Wallet Balance**: $100.00
- **Permissions**: Limited to wallet operations

## System Settings Restored

The following default settings are created:

### Email Settings
- `email_verification_enabled`: true

### Transfer Fee Settings
- `transfer_fee_enabled`: true
- `transfer_fee_type`: "percentage"
- `transfer_fee_value`: 2.5%
- `transfer_fee_min`: $0.50
- `transfer_fee_max`: $25.00

### Withdrawal Fee Settings
- `withdrawal_fee_enabled`: true
- `withdrawal_fee_type`: "fixed"
- `withdrawal_fee_value`: $5.00
- `withdrawal_fee_min`: $1.00
- `withdrawal_fee_max`: $50.00

### Transaction Limits
- `min_deposit_amount`: $1.00
- `max_deposit_amount`: $10,000.00
- `min_transfer_amount`: $1.00
- `max_transfer_amount`: $10,000.00
- `min_withdrawal_amount`: $1.00
- `max_withdrawal_amount`: $10,000.00

### App Settings
- `app_name`: "Gawis iHerbal E-Wallet"
- `app_version`: "1.0.0"
- `maintenance_mode`: false

## Files Created

### Database Seeder
- `database/seeders/DatabaseResetSeeder.php`

### Controller
- `app/Http/Controllers/DatabaseResetController.php`

### View
- `resources/views/admin/database-reset-confirm.blade.php`

### Routes
- `GET /reset` - Main reset endpoint
- `GET /reset-status` - Get reset status

## Technical Details

### What Happens During Reset

1. **Clear Cache**: All Laravel caches are cleared
2. **Clear Data**: All tables are truncated in order:
   - transactions
   - wallets
   - model_has_roles
   - model_has_permissions
   - role_has_permissions
   - users
   - roles
   - permissions
   - system_settings

3. **Reset Auto-Increment**: All table counters are reset to 1

4. **Create Fresh Data**:
   - Roles and permissions
   - Default users with wallets
   - System settings
   - Update reset tracking

5. **Clear Permission Cache**: Spatie permission cache is cleared

### Logging

All reset activities are logged to `storage/logs/laravel.log`:
- Reset attempts (successful and failed)
- User information and IP addresses
- Detailed error messages if reset fails

### Error Handling

- Comprehensive try-catch blocks
- Detailed error logging
- User-friendly error messages
- Rollback safety (seeder is atomic)

## Security Considerations

⚠️ **WARNING**: This functionality should be used with extreme caution:

- **Irreversible**: All data is permanently deleted
- **Production Risk**: Should be disabled in production environments
- **Admin Only**: Only trusted admin users should have access
- **Audit Trail**: All actions are logged for security review

## Troubleshooting

### Common Issues

1. **Permission Denied**
   - Ensure user has admin role
   - Check authentication middleware

2. **Foreign Key Constraints**
   - Tables are cleared in correct order
   - All relationships are handled

3. **Cache Issues**
   - All caches are cleared before and after reset
   - Permission cache is refreshed

### Manual Recovery

If reset fails, you can manually run:

```bash
php artisan db:seed --class=DatabaseResetSeeder --force
php artisan permission:cache-reset
```

## Customization

To modify default data, edit:
- `DatabaseResetSeeder.php` - Change default users, settings, balances
- `database-reset-confirm.blade.php` - Modify confirmation page
- `DatabaseResetController.php` - Adjust security or logging

## Development vs Production

**Development**: Full functionality available
**Production**: Consider disabling or adding additional security layers

To disable in production, add to your environment:
```env
DB_RESET_ENABLED=false
```