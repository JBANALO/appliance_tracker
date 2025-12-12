# 📋 Warranty Tracker - Deployment Guide

**Last Updated:** December 12, 2025  
**Version:** 1.0  
**Status:** ✅ **READY FOR SCHOOL DEPLOYMENT**

---

## 📖 Table of Contents
1. [Quick Start](#quick-start)
2. [System Requirements](#system-requirements)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [Testing Checklist](#testing-checklist)
6. [Troubleshooting](#troubleshooting)
7. [Features Overview](#features-overview)

---

## 🚀 Quick Start

### For Teachers/Evaluators (5 minutes)

```powershell
# 1. Start XAMPP
Start-Process "C:\xampp\xampp-control.exe"

# Wait for Apache and MySQL to be running (green lights)

# 2. Open in browser
Start-Process "http://localhost/appliance_tracker/login.php"

# 3. Use demo account (create via register.php first)
```

### Demo User Credentials (Create via Registration)
- **Username:** admin
- **Email:** admin@school.edu
- **Password:** SecurePass123

---

## 💻 System Requirements

### Minimum Requirements
- **OS:** Windows 7 or newer
- **RAM:** 2GB
- **Disk Space:** 500MB
- **Browser:** Chrome, Firefox, Edge, Safari (2020+)

### Required Software (All included in XAMPP)
- **PHP:** 8.2.12 or higher ✅
- **MySQL:** 5.7+ or MariaDB 10.4+ ✅
- **Apache:** 2.4+ ✅
- **PDO Extension:** Required ✅

### Verify Installation
```powershell
php -v
# Should show: PHP 8.2.12 ...
```

---

## 📥 Installation Steps

### Step 1: Extract Files
```powershell
# Files should already be in:
# C:\xampp\htdocs\appliance_tracker\
```

### Step 2: Create Database
```powershell
# 1. Open XAMPP Control Panel
# 2. Click "Admin" button next to MySQL
# 3. Or use command line:

# Open phpMyAdmin (auto opens on MySQL admin click)
# Navigate to Import tab

# Import database files in order:
# 1. database/admin.sql
# 2. database/owner.sql
# 3. database/appliance.sql
# 4. database/claim.sql
# 5. database/notification.sql
```

**Or via Command Line:**
```powershell
cd C:\xampp\mysql\bin

# Login to MySQL
.\mysql.exe -u root

# Create database
mysql> CREATE DATABASE warranty_tracker;
mysql> USE warranty_tracker;

# Import SQL files
mysql> SOURCE C:/xampp/htdocs/appliance_tracker/database/admin.sql;
mysql> SOURCE C:/xampp/htdocs/appliance_tracker/database/owner.sql;
mysql> SOURCE C:/xampp/htdocs/appliance_tracker/database/appliance.sql;
mysql> SOURCE C:/xampp/htdocs/appliance_tracker/database/claim.sql;
mysql> SOURCE C:/xampp/htdocs/appliance_tracker/database/notification.sql;

mysql> EXIT;
```

### Step 3: Configure Environment (OPTIONAL)
```powershell
# Copy template to actual file
Copy-Item ".env.example" ".env"

# Edit if needed (defaults work for local development)
# C:\xampp\htdocs\appliance_tracker\.env
```

### Step 4: Start Services
```powershell
# XAMPP Control Panel > Click "Start" for both:
# ✅ Apache
# ✅ MySQL

# Wait for green indicators
```

### Step 5: Access Application
```
http://localhost/appliance_tracker/
```

---

## ⚙️ Configuration

### For School/Local Use (Default Settings)
File: `.env` (already configured)

```ini
# Database (local)
DB_HOST=localhost
DB_NAME=warranty_tracker
DB_USER=root
DB_PASS=

# Email (skip for now - optional feature)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=

# App Mode
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Session
SESSION_LIFETIME=7200
SESSION_SECURE=false
SESSION_HTTPONLY=true
```

### To Enable Email Verification (Optional)
1. Create Gmail app password (not regular password)
2. Edit `.env` and add Gmail credentials
3. Restart Apache

**This is optional for school deployment.**

---

## ✅ Testing Checklist

Use this checklist to verify everything works:

### 1. **Login/Registration** (5 minutes)
- [ ] Navigate to `http://localhost/appliance_tracker/`
- [ ] Click "Register" 
- [ ] Create test account with valid email
- [ ] Click "Login"
- [ ] Login with created credentials
- [ ] Should see **Admin Dashboard**

### 2. **Dashboard** (5 minutes)
- [ ] View dashboard statistics
- [ ] See total appliances, warranties status
- [ ] See claims count
- [ ] Click notification bell (if available)

### 3. **Appliances Module** (10 minutes)
- [ ] Click "Appliances" → "View All"
- [ ] Click "Add New Appliance"
- [ ] Fill form with test data:
  - Appliance Name: TV
  - Model: ABC123
  - Serial: SN001
  - Category: Electronics
  - Purchase Date: 2025-01-01
  - Warranty Period: 24 months
  - Owner: Select from dropdown
- [ ] Click "Save" → Should see success message
- [ ] Search appliance by name or serial
- [ ] Edit appliance (change warranty date)
- [ ] Delete appliance → Confirm dialog appears
- [ ] Appliance removed from list

### 4. **Owners Module** (10 minutes)
- [ ] Click "Owners" → "View All"
- [ ] Click "Add New Owner"
- [ ] Fill form with test data:
  - Name: John Doe
  - Email: john@test.com
  - Phone: 555-1234
  - Address: 123 Main St
  - City: Springfield
  - State: IL
  - ZIP: 62701
- [ ] Click "Save" → Success message
- [ ] Search owner by name or email
- [ ] Edit owner details
- [ ] Delete owner
- [ ] Verify owner removed

### 5. **Claims Module** (10 minutes)
- [ ] Click "Claims" → "View All"
- [ ] Click "Add New Claim"
- [ ] Select appliance from dropdown
- [ ] Fill claim details:
  - Claim Date: Today
  - Description: Test claim
  - Status: Pending
- [ ] Click "Submit"
- [ ] Search claims by date range
- [ ] Edit claim status (change to Approved/Rejected)
- [ ] Delete claim

### 6. **Reports** (5 minutes)
- [ ] Click "Reports" 
- [ ] View warranty expiration report
- [ ] Filter by date range
- [ ] Export or print report (should show formatted table)

### 7. **Session & Security** (5 minutes)
- [ ] Login, then leave idle for 30 seconds
- [ ] Click a page → Session should still be active
- [ ] Click "Logout" → Redirected to login
- [ ] Try accessing admin page directly → Should redirect to login

### 8. **Form Validation** (5 minutes)
- [ ] Try adding owner with empty name → Should show error
- [ ] Try adding appliance with invalid email owner → Should show error
- [ ] Try submitting form with incomplete data → Should highlight required fields

---

## 🔧 Troubleshooting

### Issue: "Cannot connect to database"
**Solution:**
```powershell
# 1. Check MySQL is running in XAMPP
# 2. Verify database exists:

cd C:\xampp\mysql\bin
.\mysql.exe -u root

mysql> SHOW DATABASES;
# Should show "warranty_tracker"

# 3. If missing, import SQL files (see Installation Step 2)
```

### Issue: "PDO Exception" or "Invalid parameter"
**Solution:**
```powershell
# 1. Check PHP PDO extension is enabled
# 2. Edit C:\xampp\php\php.ini
# 3. Find and uncomment: 
#    extension=pdo_mysql
# 4. Restart Apache
```

### Issue: "Session errors" or "Not logged in"
**Solution:**
```powershell
# 1. Clear browser cookies
# 2. Clear XAMPP temporary files:
#    C:\xampp\tmp\*
# 3. Restart Apache
# 4. Login again
```

### Issue: "Cannot find appliance.php" or similar
**Solution:**
```powershell
# 1. Verify file exists:
#    C:\xampp\htdocs\appliance_tracker\[filename].php
# 2. Check file paths in code (should be relative paths)
# 3. Restart Apache
```

### Issue: "Email verification not working"
**Solution:**
```
This is optional for school deployment.
Email feature requires Gmail app password.
Skip this for basic functionality.
```

### Issue: "Upload errors" or "Permission denied"
**Solution:**
```powershell
# 1. Check folder permissions:
# Right-click C:\xampp\htdocs\appliance_tracker\
# Properties > Security > Edit > Your User > Full Control

# 2. Restart Apache after permission changes
```

---

## 🎯 Features Overview

### User Management
- ✅ Secure registration with email verification
- ✅ Login with rate limiting (5 attempts per 15 min)
- ✅ Password reset via email
- ✅ Session timeout (2 hours)
- ✅ CSRF protection on forms
- ✅ Secure password hashing (bcrypt)

### Appliance Tracking
- ✅ Add/Edit/Delete appliances
- ✅ Track warranty end dates
- ✅ Search by name, model, serial
- ✅ Filter by warranty status
- ✅ Automatic expiry notifications
- ✅ Owner assignment

### Owner Management
- ✅ Add/Edit/Delete customers
- ✅ Contact information storage
- ✅ Email and phone validation
- ✅ Address tracking
- ✅ Search and filter
- ✅ Duplicate prevention

### Warranty Claims
- ✅ File warranty claims
- ✅ Track claim status
- ✅ Attach to appliances
- ✅ Status workflow (Pending → Approved/Rejected)
- ✅ Email notifications
- ✅ Search and filter

### Dashboard
- ✅ Summary statistics
- ✅ Active warranties count
- ✅ Expiring soon alerts
- ✅ Recent claims
- ✅ Notification center
- ✅ Quick actions

### Reporting
- ✅ Warranty expiration report
- ✅ Date range filtering
- ✅ Printable output
- ✅ Export capabilities

### Security
- ✅ SQL Injection prevention (PDO)
- ✅ XSS protection (htmlspecialchars)
- ✅ CSRF tokens on forms
- ✅ Rate limiting on login
- ✅ Session security
- ✅ Password hashing
- ✅ Input validation
- ✅ Secure headers

---

## 📱 User Roles

### Admin Role
- Full access to all features
- Can manage all data
- Can view reports
- Can manage users (if enabled)

---

## 📊 Database Structure

### Tables
1. **admin** - User accounts
2. **owner** - Customer information
3. **appliance** - Product information
4. **claim** - Warranty claims
5. **notification** - System notifications

All tables use proper:
- ✅ Primary keys
- ✅ Foreign keys
- ✅ Data validation
- ✅ Timestamps

---

## 🔐 Security Features

### Implemented ✅
- PDO prepared statements (SQL injection safe)
- Password hashing with PASSWORD_DEFAULT
- Session regeneration after login
- CSRF token validation
- Input sanitization (htmlspecialchars)
- Rate limiting on login (5 attempts/15 min)
- Session timeouts (2 hours)
- Secure session flags
- Email verification for admin accounts

### Best Practices Used
- No plain text passwords
- No direct database access in templates
- Separate classes for business logic
- Proper error handling
- Secure header configuration

---

## 📞 Support

For issues or questions:
1. Check **Troubleshooting** section above
2. Review error messages in browser console
3. Check XAMPP Apache error logs:
   - `C:\xampp\apache\logs\error.log`
4. Check PHP error logs:
   - `C:\xampp\php\logs\php_error.log`

---

## ✨ Tips for School Presentation

### Demo Scenario (15 minutes)
1. **Show Registration** (2 min)
   - Register new admin account
   - Show email verification

2. **Show Dashboard** (2 min)
   - Explain statistics widgets
   - Show notification system

3. **Show Appliance Module** (4 min)
   - Add sample appliance
   - Search functionality
   - Edit warranty date
   - Show automatic expiry detection

4. **Show Claims** (4 min)
   - File new claim
   - Show status updates
   - Email notification (if configured)

5. **Show Reports** (2 min)
   - Generate warranty expiration report
   - Show print functionality

6. **Highlight Security** (1 min)
   - Logout and show login protection
   - Explain session timeout

### Key Highlights for Teacher
✅ Uses modern PHP 8.2  
✅ Secure database queries (PDO)  
✅ Proper authentication  
✅ Email integration  
✅ Professional UI  
✅ Error handling  
✅ Data validation  

---

## 📝 Checklist Before Submission

- [ ] Database imported successfully
- [ ] All modules tested and working
- [ ] Login/logout works
- [ ] Admin account created
- [ ] Sample data added (5+ appliances, 3+ owners, 2+ claims)
- [ ] Forms validate properly
- [ ] Search functionality tested
- [ ] No console errors (F12)
- [ ] Session logout works
- [ ] Application handles errors gracefully
- [ ] Documentation complete
- [ ] Code commented where needed
- [ ] No debug output visible
- [ ] Folder structure intact

---

## 🎓 Learning Outcomes

This project demonstrates:
- ✅ PHP object-oriented programming
- ✅ MVC architecture principles
- ✅ Database design and management
- ✅ User authentication systems
- ✅ Security best practices
- ✅ Form validation and handling
- ✅ Session management
- ✅ Error handling
- ✅ Web development fundamentals
- ✅ Professional code organization

---

**Good Luck with Your Presentation! 🚀**

For questions, refer back to specific sections or check the code comments in each PHP file.
