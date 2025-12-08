# XAMPP Sendmail Configuration Guide

## 📧 Configure XAMPP to Send Emails via Gmail

Follow these steps to enable email sending through XAMPP using Gmail.

---

## Step 1: Configure sendmail.ini

1. **Open the file:** `c:\xampp\sendmail\sendmail.ini`
2. **Replace the entire content** with the following:

```ini
[sendmail]

; Gmail SMTP Server
smtp_server=smtp.gmail.com

; Gmail SMTP Port (587 for TLS)
smtp_port=587

; Use TLS for Gmail
smtp_ssl=tls

; Gmail Authentication
; IMPORTANT: Replace with your actual email and app password
auth_username=mahdimk.kar2005@gmail.com
auth_password=YOUR_GMAIL_APP_PASSWORD_HERE

; Set sender email
force_sender=mahdimk.kar2005@gmail.com

; Log errors
error_logfile=error.log

; Uncomment for debugging
debug_logfile=debug.log

; Hostname
hostname=localhost
```

3. **Replace `YOUR_GMAIL_APP_PASSWORD_HERE`** with your Gmail App Password
4. **Save the file**

---

## Step 2: Configure php.ini

1. **Open the file:** `c:\xampp\php\php.ini`
2. **Search for** `[mail function]` (around line 900-1000)
3. **Find these lines:**
   ```ini
   ;sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
   ```
4. **Remove the semicolon** to uncomment it:
   ```ini
   sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
   ```
5. **Save the file**

---

## Step 3: Get Gmail App Password

Since you don't have Windows Hello, use these alternatives:

### Method 1: Enable 2-Step Verification via SMS

1. Go to: https://myaccount.google.com/security
2. Click **2-Step Verification**
3. Click **Get Started**
4. Choose **Text message (SMS)** for verification
5. Enter your phone number
6. Verify with the code sent to your phone
7. Complete setup

### Method 2: Use Google Authenticator App

1. Download **Google Authenticator** on your phone
2. Go to: https://myaccount.google.com/security
3. Click **2-Step Verification**
4. Choose **Authenticator app**
5. Scan QR code with your phone
6. Enter the 6-digit code
7. Complete setup

### After 2-Step Verification is Enabled:

1. Go to: https://myaccount.google.com/apppasswords
2. Select **Mail** and **Windows Computer**
3. Click **Generate**
4. Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)
5. Use this password in `sendmail.ini` (remove spaces: `abcdefghijklmnop`)

---

## Step 4: Restart Apache

After making changes:

1. Open **XAMPP Control Panel**
2. Click **Stop** on Apache
3. Wait 2 seconds
4. Click **Start** on Apache

---

## Step 5: Test Email Sending

I've created a test script for you. Open in browser:

**http://localhost/perfran/PerFran-master/PerFranMVC/test_email.php**

This will send a test email and show you if it works.

---

## 🔍 Troubleshooting

### If email doesn't send:

**Check 1: Error Log**
```
c:\xampp\sendmail\error.log
```
Look for error messages

**Check 2: Debug Log**
Uncomment this line in `sendmail.ini`:
```ini
debug_logfile=debug.log
```
Then check: `c:\xampp\sendmail\debug.log`

**Check 3: Gmail Settings**
- Make sure 2-Step Verification is enabled
- Make sure you're using App Password, not regular password
- Check if Gmail blocked the sign-in attempt

**Check 4: Firewall**
- Make sure Windows Firewall allows Apache
- Check antivirus isn't blocking port 587

### Common Errors:

**"Authentication failed"**
→ Wrong App Password or 2-Step not enabled

**"Connection timed out"**
→ Firewall blocking port 587

**"Could not connect to SMTP host"**
→ Wrong SMTP server or port

---

## ✅ Quick Checklist

- [ ] `sendmail.ini` configured with Gmail credentials
- [ ] `php.ini` uncommented `sendmail_path`
- [ ] Gmail App Password generated
- [ ] App Password added to `sendmail.ini`
- [ ] Apache restarted
- [ ] Test email sent successfully

---

## 🎯 Alternative: Use Regular Password (NOT RECOMMENDED)

If you can't get App Password working, you can try:

1. Go to: https://myaccount.google.com/lesssecureapps
2. Turn ON "Allow less secure apps"
3. Use your regular Gmail password in `sendmail.ini`

⚠️ **This is less secure!** Only use for testing, not production.

---

**Once configured, your quiz suggestion emails will work automatically!**
