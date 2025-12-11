# Security Implementation Guide

## ✅ What Has Been Fixed

### 1. Environment Configuration
- Created `config.php` for centralized configuration
- Created `.env.example` template for environment variables
- Removed hardcoded credentials from code

### 2. Database Security
- Updated `database.php` to use environment variables
- Added charset UTF-8 for security
- Improved error handling (no exposed details in production)
- Added PDO security options

### 3. Email Security
- Updated `EmailNotification.php` to use environment variables
- Removed hardcoded SMTP credentials

### 4. Session Security
- Created `security.php` with helper functions
- Implemented secure session initialization
- Added session timeout (2 hours default)
- Added session ID regeneration
- HttpOnly and Secure cookie flags
- SameSite cookie protection

### 5. Login Security
- Added CSRF token protection
- Implemented rate limiting (5 attempts per 15 minutes)
- Added security event logging
- Input validation and sanitization
- Session regeneration on login

### 6. Security Logging
- All login attempts logged
- Failed login tracking
- CSRF violations logged
- Rate limit violations logged

## 🔧 Installation Steps

### Step 1: Create Environment File
```bash
# Copy the example file
cp .env.example .env

# Edit with your actual credentials
notepad .env
```

### Step 2: Configure Your .env File
```env
# Database - CREATE A NEW USER, DON'T USE ROOT!
DB_HOST=localhost
DB_NAME=warranty_tracker
DB_USER=warranty_user
DB_PASS=YourStrongPassword123!

# Email - Use App-Specific Password for Gmail
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_16_char_app_password
SMTP_FROM_EMAIL=your_email@gmail.com
SMTP_FROM_NAME=Warranty Tracker

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### Step 3: Create MySQL User (DO NOT USE ROOT!)
```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create dedicated user
CREATE USER 'warranty_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';

-- Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON warranty_tracker.* TO 'warranty_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify
SHOW GRANTS FOR 'warranty_user'@'localhost';
```

### Step 4: Create Logs Directory
```bash
mkdir logs
chmod 755 logs
```

### Step 5: Secure File Permissions
```bash
# Make .env readable only by web server
chmod 600 .env

# Regular PHP files
chmod 644 *.php

# Directories
chmod 755 .
```

### Step 6: Gmail App Password Setup
1. Go to https://myaccount.google.com/security
2. Enable 2-Step Verification
3. Go to App Passwords
4. Select "Mail" and "Other (Custom name)"
5. Enter "Warranty Tracker"
6. Copy the 16-character password
7. Paste it into .env as SMTP_PASS

## 🔒 Security Features Implemented

### CSRF Protection
- All forms now require CSRF tokens
- Tokens validated on submission
- Failed validations are logged

### Rate Limiting
- Login attempts: 5 per 15 minutes
- Automatically resets on successful login
- Lockout time displayed to users

### Session Security
- HttpOnly cookies (no JavaScript access)
- Secure flag (HTTPS only in production)
- SameSite strict (CSRF protection)
- 2-hour timeout
- Periodic session ID regeneration

### Input Validation
- Email format validation
- Phone number format validation
- XSS protection via htmlspecialchars
- SQL injection protection via PDO

### Error Handling
- Production mode: Generic error messages
- Debug mode: Detailed errors for development
- All errors logged to file
- No database details exposed

### Security Logging
- Login successes and failures
- Rate limit violations
- CSRF token failures
- Includes IP address and user agent

## 📋 Pre-Production Checklist

### Required
- [ ] Copy .env.example to .env
- [ ] Fill in all .env values
- [ ] Create dedicated MySQL user (not root)
- [ ] Set up Gmail app password
- [ ] Create logs/ directory
- [ ] Set proper file permissions
- [ ] Test login functionality
- [ ] Test rate limiting
- [ ] Verify email sending works

### Recommended for Production
- [ ] Install SSL certificate (Let's Encrypt)
- [ ] Force HTTPS (uncomment in config.php)
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Regular database backups
- [ ] Monitor security.log file
- [ ] Set up firewall rules
- [ ] Regular security updates

## 🚨 Important Security Notes

### DO NOT:
- ❌ Use root MySQL user
- ❌ Commit .env file to Git
- ❌ Leave APP_DEBUG=true in production
- ❌ Use weak passwords
- ❌ Ignore security.log warnings

### DO:
- ✅ Use strong unique passwords
- ✅ Enable HTTPS in production
- ✅ Monitor logs regularly
- ✅ Keep backups
- ✅ Update dependencies

## 📊 Monitoring

### Check Security Logs
```bash
tail -f logs/security.log
```

### Check Error Logs
```bash
tail -f logs/error.log
```

### Monitor Failed Logins
```bash
grep "LOGIN_FAILED" logs/security.log
```

### Monitor Rate Limit Hits
```bash
grep "RATE_LIMIT_EXCEEDED" logs/security.log
```

## 🔄 Migration from Development

If you have existing admin accounts:
```sql
-- All existing accounts are already verified (set earlier)
-- Just update to use new database user

-- Verify existing accounts
SELECT email, is_verified FROM admin;
```

## 🆘 Troubleshooting

### "Database connection failed"
- Check .env credentials
- Verify MySQL user exists
- Check user has correct permissions

### "Email not sending"
- Verify Gmail app password
- Check SMTP settings in .env
- Enable "Less secure app access" if needed

### "Too many login attempts"
- Wait 15 minutes OR
- Clear session data OR
- Restart browser

### "CSRF validation failed"
- Clear browser cookies
- Ensure forms have csrf_token field
- Check session is working

## 📞 Support

For security issues:
1. Check logs/security.log
2. Check logs/error.log
3. Verify .env configuration
4. Test with APP_DEBUG=true (development only)
