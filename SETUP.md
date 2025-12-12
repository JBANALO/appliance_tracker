# Appliance Tracker - Quick Setup Guide

Complete guide to set up the Appliance Tracker project on a new laptop.

## Prerequisites

### Required Software
- **XAMPP** (or Apache + PHP 8.2+ + MySQL 10.4+)
- **Git**
- **Composer** (optional, if using PHPMailer manually)
- **Browser** (Chrome, Firefox, Edge, or Safari)

### System Requirements
- Windows 10/11, macOS, or Linux
- 2GB RAM minimum
- 500MB disk space

---

## Step 1: Install XAMPP (If Not Already Installed)

### Windows
```powershell
# Download from https://www.apachefriends.org/download.html
# Run the installer
# Install to default location: C:\xampp
# Start XAMPP Control Panel and start Apache and MySQL
```

### macOS
```bash
# Download from https://www.apachefriends.org/download.html
# Run the installer
# Start XAMPP Control Panel and start Apache and MySQL
```

### Linux
```bash
# Download XAMPP for Linux from https://www.apachefriends.org/download.html
# Extract and run installer
sudo ./xampp-linux-installer.run
sudo /opt/lampp/manager-linux.run
```

---

## Step 2: Clone or Download the Project

### Using Git (Recommended)
```powershell
# Navigate to XAMPP htdocs directory
cd C:\xampp\htdocs

# Clone the repository (replace with your repo URL)
git clone <your-github-repo-url>
cd appliance_tracker

# If already cloned, update it
git pull origin main
```

### Manual Download
```powershell
# Download ZIP file from GitHub
# Extract to C:\xampp\htdocs\appliance_tracker
cd C:\xampp\htdocs\appliance_tracker
```

---

## Step 3: Create Database and Tables

### Option A: Using PhpMyAdmin (GUI - Easiest)
```
1. Open browser: http://localhost/phpmyadmin
2. Click "Import" tab
3. Click "Choose File" and select: database/warranty_tracker_backup_full.sql
4. Click "Go" to import
5. Database created with all tables and sample data
```

### Option B: Using Command Line
```powershell
# Navigate to MySQL bin directory
cd C:\xampp\mysql\bin

# Import the database backup
.\mysql.exe -u root < "C:\xampp\htdocs\appliance_tracker\database\warranty_tracker_backup_full.sql"

# Verify database created
.\mysql.exe -u root -e "SHOW DATABASES; USE warranty_tracker; SHOW TABLES;"
```

### Option C: Create Empty Database (Manual)
```powershell
cd C:\xampp\mysql\bin

.\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS warranty_tracker;"

# Import individual table backups
.\mysql.exe -u root warranty_tracker < "C:\xampp\htdocs\appliance_tracker\database\admin_backup.sql"
.\mysql.exe -u root warranty_tracker < "C:\xampp\htdocs\appliance_tracker\database\owner_backup.sql"
.\mysql.exe -u root warranty_tracker < "C:\xampp\htdocs\appliance_tracker\database\appliance_backup.sql"
.\mysql.exe -u root warranty_tracker < "C:\xampp\htdocs\appliance_tracker\database\claim_backup.sql"
.\mysql.exe -u root warranty_tracker < "C:\xampp\htdocs\appliance_tracker\database\notification_backup.sql"

# Verify
.\mysql.exe -u root warranty_tracker -e "SHOW TABLES;"
```

---

## Step 4: Configure Environment Variables

### Create .env File
```powershell
cd C:\xampp\htdocs\appliance_tracker

# Copy example to .env (use PowerShell)
Copy-Item ".env.example" ".env"

# For local development, .env should contain:
```

### Edit .env File (Local Development)
```bash
# File: .env (for local development)

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/appliance_tracker

# Database Configuration
DB_HOST=localhost
DB_NAME=warranty_tracker
DB_USER=root
DB_PASS=

# Session Configuration
SESSION_SECURE=false
SESSION_HTTPONLY=true
SESSION_SAMESITE=Lax

# Email Configuration (Optional - for testing)
SMTP_HOST=your-smtp-host.com
SMTP_PORT=587
SMTP_USER=your-email@example.com
SMTP_PASS=your-app-password
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME=Appliance Tracker
```

**Note:** For production (Railway), create .env with production credentials from your Railway dashboard.

---

## Step 5: Verify Installation

### Check PHP Version
```powershell
php -v
# Should show: PHP 8.2.12 or higher
```

### Check MySQL Connection
```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root -e "SELECT VERSION();"
# Should show: MySQL version
```

### Access Application
```
1. Start Apache and MySQL in XAMPP Control Panel
2. Open browser and navigate to: http://localhost/appliance_tracker
3. You should see the login page
```

---

## Step 6: Test Login Credentials

### Default Admin Account (After Database Import)
```
Email: admin@warranty.com
Password: admin123

Or any user from the imported database
```

### Create New Admin
```
1. Open http://localhost/phpmyadmin
2. Go to warranty_tracker > admin table
3. Click "Insert"
4. Add new admin record with hashed password

Or register and promote user to admin via database
```

---

## Step 7: Verify All Features

### Run Basic Tests
```
✓ Login with admin account
✓ Dashboard displays
✓ View/Add/Edit/Delete appliances
✓ View/Add/Edit/Delete owners
✓ View/Add/Edit/Delete claims
✓ Generate reports
✓ View notifications
✓ Test logout
```

### Check Console for Errors
```
1. Press F12 in browser (Developer Tools)
2. Check Console tab for JavaScript errors
3. Check Network tab for failed requests
4. All should be clean or showing expected behavior
```

---

## Step 8: For Production Deployment (Railway)

### Create Production .env
```bash
# File: .env (for Railway production)

APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app

# Get these from Railway Dashboard
DB_HOST=<railway-db-host>
DB_NAME=railway
DB_USER=<railway-db-user>
DB_PASS=<railway-db-password>

# Session Configuration (Production Safe)
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict

# Email Configuration (Optional)
SMTP_HOST=your-smtp-host.com
SMTP_PORT=587
SMTP_USER=your-email@example.com
SMTP_PASS=your-app-password
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME=Appliance Tracker
```

### Deploy to Railway
```
1. Push code to GitHub: git push origin main
2. Connect GitHub repo to Railway
3. Set environment variables in Railway Dashboard
4. Import database using PhpMyAdmin on Railway
5. Test live application
```

---

## Troubleshooting

### Problem: Port 80 Already in Use
```powershell
# Find and stop the process using port 80
netstat -ano | findstr :80
taskkill /PID <PID> /F

# Or change Apache port in XAMPP configuration
```

### Problem: MySQL Not Starting
```powershell
# Check MySQL service
Get-Service MySQL*

# Restart MySQL service
Restart-Service MySQL*

# Or reinstall XAMPP
```

### Problem: "Connection Refused" Error
```
1. Verify XAMPP Apache and MySQL are running
2. Check .env file has correct credentials
3. Verify database exists: http://localhost/phpmyadmin
4. Check database/warranty_tracker_backup_full.sql exists
```

### Problem: 404 on Pages
```
1. Verify .htaccess exists in project directory
2. Check Apache mod_rewrite is enabled
3. Clear browser cache (Ctrl+Shift+Delete)
4. Restart Apache
```

### Problem: Session Errors
```
1. Check session save path exists
2. Verify PHP has write permissions to session directory
3. Check .env SESSION_* settings
4. Clear browser cookies and login again
```

### Problem: Database Import Fails
```
# Check backup file integrity
file C:\xampp\htdocs\appliance_tracker\database\warranty_tracker_backup_full.sql

# Try importing with flags
cd C:\xampp\mysql\bin
.\mysql.exe -u root --max_allowed_packet=16M warranty_tracker < "..\..\appliance_tracker\database\warranty_tracker_backup_full.sql"
```

---

## Quick Reference Commands

### Start Development Environment
```powershell
# 1. Start XAMPP (Apache + MySQL)
# 2. Navigate to project
cd C:\xampp\htdocs\appliance_tracker

# 3. Open in browser
start http://localhost/appliance_tracker
```

### Database Backup (Before Major Changes)
```powershell
cd C:\xampp\mysql\bin
.\mysqldump.exe -u root warranty_tracker > "C:\xampp\htdocs\appliance_tracker\database\warranty_tracker_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
```

### Database Restore
```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root warranty_tracker < "C:\xampp\htdocs\appliance_tracker\database\warranty_tracker_backup_full.sql"
```

### Verify Database
```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root warranty_tracker -e "SELECT 'Admin' as Table_Name, COUNT(*) as Records FROM admin UNION SELECT 'Owner', COUNT(*) FROM owner UNION SELECT 'Appliance', COUNT(*) FROM appliance UNION SELECT 'Claim', COUNT(*) FROM claim UNION SELECT 'Notification', COUNT(*) FROM notification;"
```

### Git Operations
```powershell
# Pull latest changes
git pull origin main

# Push your changes
git add .
git commit -m "Your commit message"
git push origin main

# Check status
git status
```

---

## System Information (For Reference)

**Tested Configuration:**
- PHP: 8.2.12
- MySQL: 10.4.32 (MariaDB)
- Apache: 2.4.58
- OS: Windows 10/11

**Database:**
- Name: warranty_tracker
- Tables: 5 (admin, owner, appliance, claim, notification)
- Test Data: Pre-loaded (5 admins, 6 owners, 4 appliances, 10 claims)

**Features:**
- ✓ Admin authentication with email verification
- ✓ Appliance tracking and warranty management
- ✓ Customer warranty claims
- ✓ Report generation
- ✓ Notification system
- ✓ Role-based access control

---

## Support & Documentation

- **README.md** - Project overview and features
- **DEPLOYMENT_GUIDE.md** - Detailed deployment instructions
- **TESTING_REPORT.md** - Testing scenarios and results
- **database/\*.sql** - Database backups

---

## Next Steps

1. ✓ Complete setup.md (You are here)
2. → Verify database is created and contains data
3. → Test login with admin credentials
4. → Test all major features
5. → For production: Push to GitHub and deploy to Railway

---

**Last Updated:** December 12, 2025
**Version:** 1.0
**Status:** Ready for Production
