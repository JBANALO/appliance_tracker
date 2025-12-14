# 🚀 INFINITYFREE DEPLOYMENT GUIDE

**Status:** ✅ Ready to Deploy  
**Updated:** December 14, 2025

---

## ⚠️ SECURITY FIX - IMPORTANT!

Your credentials were hardcoded in `config.php`. This has been fixed. Now follow this guide:

---

## 📋 Step-by-Step InfinityFree Deployment

### **STEP 1: Create MySQL Database**
1. Login to InfinityFree cPanel
2. Click **"MySQL Databases"**
3. Click **"Create New Database"**
4. Name: `warranty_tracker` (or similar)
5. **COPY THESE VALUES** - You'll need them:
   - DB_HOST (usually `sql307.infinityfree.com` or similar)
   - DB_NAME (something like `if0_12345_warranty_tracker`)
   - DB_USER (something like `if0_12345`)
   - DB_PASS (create a strong password)

### **STEP 2: Import Database Tables**
1. In cPanel, click **"PhpMyAdmin"**
2. Select your new database
3. Click **"Import"** tab
4. Choose file: `database/warranty_tracker_backup_full.sql`
5. Click **"Go"**
6. Wait for completion ✅

### **STEP 3: Create .env File**
1. Create a new text file with this content:

```env
# Database Configuration - FROM YOUR CPANEL
DB_HOST=sql307.infinityfree.com
DB_NAME=if0_12345_warranty_tracker
DB_USER=if0_12345
DB_PASS=your_strong_password

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your.email@gmail.com
SMTP_PASS=your-16-char-app-password
SMTP_FROM_EMAIL=your.email@gmail.com
SMTP_FROM_NAME=Appliance Tracker

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.infinityfreeapp.com

# Session Configuration
SESSION_LIFETIME=7200
SESSION_SECURE=false
SESSION_HTTPONLY=true
```

2. **REPLACE THESE VALUES:**
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` - from Step 1
   - `SMTP_USER` - your Gmail address
   - `SMTP_PASS` - your Gmail App Password (see below)
   - `APP_URL` - your InfinityFree domain

3. Save file as `.env` (no .example)

### **STEP 4: Set Up Gmail App Password**
1. Go to https://myaccount.google.com/apppasswords
2. Login with your Gmail
3. Select **"Mail"** and **"Windows Computer"** (or your device)
4. Click **"Generate"**
5. Copy the 16-character password
6. Paste into `.env` as `SMTP_PASS`

### **STEP 5: Upload Files via FTP**
1. Download **FileZilla** (free): https://filezilla-project.org/
2. Get FTP credentials from InfinityFree dashboard
3. In FileZilla:
   - Host: (from InfinityFree)
   - Username: (from InfinityFree)
   - Password: (from InfinityFree)
   - Port: 21
4. Click **"Quickconnect"**
5. Navigate to: **`public_html`** folder (left side = your computer, right side = server)
6. Upload these files/folders:
   - All `.php` files
   - `.env` file (the one you created)
   - `styles.css`
   - `PHPMailer/` folder
   - `database/` folder

### **STEP 6: Test Your App**
1. Go to: `https://yoursite.infinityfreeapp.com`
2. Try to login
3. Check these work:
   - ✅ Login page loads
   - ✅ Database connection works
   - ✅ Can register admin account
   - ✅ Verification email sends

### **STEP 7: Enable Error Logging**
1. Create `logs/` folder on server (via FTP)
2. In FTP, right-click on logs folder → Properties
3. Set permissions to `755`

---

## 🔧 Troubleshooting

### **Error: "Database connection failed"**
- ✅ Check DB credentials in `.env` match cPanel
- ✅ Check database was imported
- ✅ Check DB_HOST is correct (ask InfinityFree support if unsure)

### **Error: "SMTP connection failed"**
- ✅ Check Gmail 2FA is enabled
- ✅ Check you used App Password (not regular password)
- ✅ Check SMTP_USER matches your Gmail
- ✅ Try disabling 2FA if still failing (less secure)

### **Error: ".env file not found"**
- ✅ Make sure file is named `.env` (not `.env.txt`)
- ✅ Upload it to root directory (same level as index.php)
- ✅ Check file permissions are 644

### **Can't create files/folders on server**
- ✅ Use FileZilla to create folders
- ✅ Right-click → Create Directory
- ✅ For permissions: Right-click → File Attributes → 755

---

## ✅ Security Checklist

- [ ] `.env` file is uploaded (contains passwords)
- [ ] `.env` is in root folder (same level as index.php)
- [ ] `.env` is NOT committed to Git (check .gitignore)
- [ ] `config.php` now reads from environment variables
- [ ] No passwords visible in PHP files
- [ ] APP_DEBUG=false in production
- [ ] logs/ folder exists and has 755 permissions

---

## 📞 Getting Help

If you still have errors:
1. Check `logs/error.log` file on your server
2. Check email in cPanel → "Email Accounts" → "Check Mail"
3. Try connecting to MySQL via PhpMyAdmin
4. Ask InfinityFree support for help with credentials

**Common InfinityFree Support:**
- Email: support@infinityfree.net
- Live Chat: Available in dashboard
