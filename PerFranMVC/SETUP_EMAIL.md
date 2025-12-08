# Email-Based Quiz Approval System - Setup Instructions

## 📧 Gmail Configuration Required

To use this system, you need to configure Gmail with an **App Password**. Follow these steps:

### Step 1: Enable 2-Step Verification

1. Go to your Google Account: https://myaccount.google.com/
2. Click on **Security** (left sidebar)
3. Under "How you sign in to Google", click **2-Step Verification**
4. Follow the prompts to enable it (if not already enabled)

### Step 2: Generate an App Password

1. Go to: https://myaccount.google.com/apppasswords
2. In the "Select app" dropdown, choose **Mail**
3. In the "Select device" dropdown, choose **Other (Custom name)**
4. Enter a name like "PerFran Quiz System"
5. Click **Generate**
6. **Copy the 16-character password** Google provides (it will look like: `abcd efgh ijkl mnop`)

### Step 3: Update Configuration File

1. Open the file: `c:\xampp\htdocs\perfran\PerFran-master\PerFranMVC\config\email_config.php`
2. Find the line: `'smtp_password' => 'your-app-password-here',`
3. Replace `'your-app-password-here'` with your app password (remove spaces)
4. Example: `'smtp_password' => 'abcdefghijklmnop',`
5. Save the file

## 🧪 Testing the System

### Test Quiz Submission

1. Open: http://localhost/perfran/PerFran-master/PerFranMVC/View/FrontOffice/suggest.php
2. Fill in a quiz with at least 3 blanks, example:
   ```
   Le [chat] de mon [voisin] mange des [souris].
   ```
3. Choose a difficulty
4. (Optional) Check "Ajouter des mots intrus" and add: `chien, oiseau`
5. Click "Envoyer suggestion"

### Check Email

1. Check inbox for: **mahdimk.kar2005@gmail.com**
2. You should see an email: **"🎯 Nouvelle suggestion de quiz reçue"**
3. The email will show:
   - Quiz preview with blanks as underlines
   - Correct answers list
   - Intruder words (if any)
   - Two buttons: **Approve** and **Reject**

### Test Approval

1. Click the **✅ Approuver** button in the email
2. You should see a success page
3. The quiz should now appear in the BackOffice quiz list with `approved = 1`

### Test Rejection

1. Submit another quiz suggestion
2. Click the **❌ Rejeter** button in the email
3. You should see a confirmation page
4. The quiz should NOT appear in the database

## 🔧 Troubleshooting

### Email not sending?

1. **Check XAMPP error log**: `c:\xampp\apache\logs\error.log`
2. **Verify Gmail credentials** in `config/email_config.php`
3. **Check firewall**: Allow PHP/Apache to make outbound connections
4. **Test connection**:
   ```php
   <?php
   $socket = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
   echo $socket ? "Connection OK!" : "Cannot connect: $errstr ($errno)";
   ?>
   ```

### "Invalid credentials" error?

- Make sure you're using an **App Password**, not your regular Gmail password
- Verify 2-Step Verification is enabled
- Generate a new App Password and try again

### Links in email not working?

1. Check the `base_url` in `config/email_config.php`
2. Make sure it matches your XAMPP setup (default: `http://localhost/perfran/PerFran-master/PerFranMVC`)

## 📁 Files Created/Modified

### New Files:
- `config/email_config.php` - Email configuration
- `Controller/QuizSuggestionEmailer.php` - Email sending logic
- `Controller/handle_approval.php` - Approval/rejection handler
- `data/pending_suggestions/` - Temporary storage for pending suggestions

### Modified Files:
- `View/FrontOffice/suggest.php` - Now sends email instead of saving to DB

## 🔒 Security Notes

- Pending suggestions are stored temporarily in `data/pending_suggestions/`
- Each suggestion has a unique cryptographic token
- Files older than 7 days are automatically deleted
- Tokens are validated before processing approvals
- Never commit `email_config.php` with real passwords to Git!

## 📝 How It Works

1. **Player submits quiz** → `suggest.php` validates and sends email
2. **Email sent to admin** → Contains quiz preview + approve/reject links
3. **Admin clicks link** → `handle_approval.php` processes the action
4. **If approved** → Quiz + blanks + intruders saved to database
5. **If rejected** → Suggestion discarded, no database changes

---

**Ready to test!** 🚀
