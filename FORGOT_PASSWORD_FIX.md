# Forgot Password Fix - Configuration Guide

## Issues Found and Fixed

### 1. **Database Configuration Mismatch** ✓ FIXED
- **Problem:** The `.env` file had `DB_NAME=warranty_tracker` but your actual database is `warranty_trackerr`
- **Fixed:** Updated `.env` to use `DB_NAME=warranty_trackerr`

### 2. **Missing SMTP Configuration** ⚠️ NEEDS YOUR ACTION
- **Problem:** The forgot password feature cannot send verification code emails without proper SMTP configuration
- **Status:** Placeholder credentials in `.env` - Need to be updated with real Gmail account

### 3. **Missing Config Include** ✓ FIXED
- **Problem:** `forgot_password.php` was not including `config.php`, so environment variables weren't loaded
- **Fixed:** Added `require_once __DIR__ . '/config.php';` at the top

---

## How to Fix Forgot Password (Email Configuration)

The forgot password feature sends a 6-digit verification code via email. To make it work:

### Step 1: Set Up Gmail App Password
1. Go to your Gmail account: https://myaccount.google.com
2. Click **Security** in the left menu
3. Enable **2-Step Verification** (if not already enabled)
4. Search for and go to **App passwords**
5. Select **Mail** and **Windows Computer** (or your device)
6. Google will generate a 16-character password - **Copy this**

### Step 2: Update .env File
Open `c:\xampp\htdocs\appliance_tracker\.env` and update these lines:

```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_gmail_email@gmail.com
SMTP_PASS=xxxx xxxx xxxx xxxx    (the 16-char password from Step 1)
SMTP_FROM_EMAIL=your_gmail_email@gmail.com
SMTP_FROM_NAME=Warranty Tracker
```

**Example:**
```
SMTP_USER=josiebanalo977@gmail.com
SMTP_PASS=abcd efgh ijkl mnop
SMTP_FROM_EMAIL=josiebanalo977@gmail.com
```

### Step 3: Test the Configuration
1. Open your browser and go to: `http://localhost/appliance_tracker/test_forgot_password.php`
2. Check if all green checkmarks appear
3. If SMTP is configured, you can send a test email from that page

### Step 4: Test Forgot Password Flow
1. Go to `http://localhost/appliance_tracker/login.php`
2. Click "Forgot Password"
3. Enter an admin email address (e.g., `josiebanalo977@gmail.com`)
4. You should receive an email with the 6-digit code
5. Enter the code on the verification page
6. Set your new password

---

## Files Modified

1. **`.env`** - Updated database name from `warranty_tracker` to `warranty_trackerr`
2. **`forgot_password.php`** - Added config.php import and better error handling
3. **`test_forgot_password.php`** - Created new diagnostic tool

---

## Troubleshooting

### Email Not Sending?
- Check `.env` file - SMTP credentials must be configured
- Make sure Gmail App Password is correct (16 characters, no spaces)
- Go to `test_forgot_password.php` to diagnose the issue

### "Account Not Found" Error?
- Check that the email address exists in the admin table
- Current admin accounts:
  - jossie (josiebanalo977@gmail.com)
  - admin (admin@warranty.com)
  - heidilynn (heidilynnrubia09@gmail.com)
  - heidi1 (heidilynnrubia09@gmail.com)
  - heidi2 (heidilynnrubia23@gmail.com)

### Code Expired?
- Verification codes expire in 15 minutes
- Request a new code if expired

---

## Database Tables Required

All tables are present in `warranty_trackerr`:
- ✓ admin
- ✓ appliance
- ✓ claim
- ✓ notification
- ✓ owner

---

## Need Help?

If emails still aren't sending after configuration:
1. Check the error log in `logs/` folder
2. Verify Gmail account security settings
3. Make sure 2-Factor Authentication is enabled on Gmail
4. Try generating a new App Password
