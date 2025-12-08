# XAMPP Email Setup - Quick Steps

## 🚀 3 Easy Steps to Configure Email

### Step 1: Edit sendmail.ini

1. Open: `c:\xampp\sendmail\sendmail.ini`
2. Replace entire content with:

```ini
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=tls
auth_username=mahdimk.kar2005@gmail.com
auth_password=PUT_YOUR_APP_PASSWORD_HERE
force_sender=mahdimk.kar2005@gmail.com
error_logfile=error.log
debug_logfile=debug.log
hostname=localhost
```

3. Save file

---

### Step 2: Edit php.ini

1. Open: `c:\xampp\php\php.ini`
2. Search for: `sendmail_path`
3. Find this line (around line 1000):
   ```ini
   ;sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
   ```
4. Remove semicolon:
   ```ini
   sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
   ```
5. Save file

---

### Step 3: Get Gmail App Password

#### Option A: If you can't use 2-Step Verification
1. Go to: https://myaccount.google.com/lesssecureapps
2. Turn ON "Allow less secure apps"
3. Use your regular Gmail password in `sendmail.ini`
4. ⚠️ NOT RECOMMENDED for production!

#### Option B: Use 2-Step Verification (Recommended)
1. Enable 2-Step with SMS:
   - Go to: https://myaccount.google.com/security
   - Click "2-Step Verification"
   - Choose "Text message (SMS)"
   - Enter your phone number
   
2. Get App Password:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Windows Computer"
   - Copy the 16-character code
   - Paste in `sendmail.ini` (remove spaces)

---

## ✅ Test It!

1. **Restart Apache** in XAMPP Control Panel
2. Open browser: `http://localhost/perfran/PerFran-master/PerFranMVC/test_email.php`
3. If successful, check your email inbox
4. Then test: `http://localhost/perfran/PerFran-master/PerFranMVC/View/FrontOffice/suggest.php`

---

## 🔧 If It Doesn't Work

Check error log:
```
c:\xampp\sendmail\error.log
```

Common fixes:
- Make sure Apache is restarted
- Verify app password has no spaces
- Check auth_username is your full email
- Make sure 2-Step is enabled (if using app password)

---

**That's it! Your email system is ready!** 📧
