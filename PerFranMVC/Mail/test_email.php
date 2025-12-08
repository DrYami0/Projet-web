<?php
/**
 * Email Test Script
 * Use this to verify XAMPP sendmail is working correctly
 */

// Test recipient
$to = 'mahdimk.kar2005@gmail.com';
$subject = 'XAMPP Email Test - PerFran Quiz System';

// HTML email body
$message = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f7fa; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #27ae60; font-size: 48px; text-align: center; }
        h1 { color: #2c3e50; margin-top: 0; }
        .info { background: #e8f4f8; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #7f8c8d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="success">✅</div>
            <h1 style="text-align: center;">Email Test Successful!</h1>
            
            <p>If you\'re reading this email, it means XAMPP sendmail is configured correctly and working!</p>
            
            <div class="info">
                <strong>Test Details:</strong><br>
                Sent from: XAMPP Server<br>
                Date/Time: ' . date('Y-m-d H:i:s') . '<br>
                Server: ' . $_SERVER['SERVER_NAME'] . '<br>
            </div>
            
            <p><strong>What this means:</strong></p>
            <ul>
                <li>✅ php.ini is configured correctly</li>
                <li>✅ sendmail.ini has valid Gmail credentials</li>
                <li>✅ SMTP connection to Gmail is working</li>
                <li>✅ Your quiz suggestion emails will work!</li>
            </ul>
            
            <p style="text-align: center; margin-top: 30px;">
                <strong>You can now test the quiz suggestion form!</strong><br>
                <a href="http://localhost/perfran/PerFran-master/PerFranMVC/View/FrontOffice/suggest.php" 
                   style="display: inline-block; background: #27ae60; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 15px;">
                    Go to Suggest Form
                </a>
            </p>
        </div>
        
        <div class="footer">
            PerFran Quiz System - Email Test<br>
            Powered by XAMPP Sendmail
        </div>
    </div>
</body>
</html>
';

// Headers for HTML email
$headers = array(
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: PerFran Quiz System <mahdimk.kar2005@gmail.com>',
    'Reply-To: mahdimk.kar2005@gmail.com',
    'X-Mailer: PHP/' . phpversion()
);

// Send email
$result = mail($to, $subject, $message, implode("\r\n", $headers));

// Display result
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test Result</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            max-width: 700px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideIn 0.4s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: <?= $result ? '#27ae60' : '#e74c3c' ?>;
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .message {
            font-size: 18px;
            color: #2c3e50;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-box h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        
        .info-box li {
            padding: 8px 0;
            color: #555;
        }
        
        .info-box li:before {
            content: "▪ ";
            color: #3498db;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .error-box {
            background: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .button {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 10px 5px;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .button.success {
            background: #27ae60;
        }
        
        .button.danger {
            background: #e74c3c;
        }
        
        .code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon"><?= $result ? '✅' : '❌' ?></div>
            <h1><?= $result ? 'Email Sent Successfully!' : 'Email Failed to Send' ?></h1>
        </div>
        
        <div class="content">
            <?php if ($result): ?>
                <div class="message">
                    <strong>Great news!</strong> The test email was sent successfully to <strong><?= $to ?></strong>.
                </div>
                
                <div class="info-box">
                    <h3>✅ What This Means:</h3>
                    <ul>
                        <li>XAMPP sendmail is configured correctly</li>
                        <li>php.ini has the correct sendmail_path</li>
                        <li>sendmail.ini has valid Gmail credentials</li>
                        <li>SMTP connection to Gmail is working</li>
                        <li><strong>Quiz suggestion emails will work!</strong></li>
                    </ul>
                </div>
                
                <div class="message">
                    Check your inbox at <strong><?= $to ?></strong> to confirm the email was received.
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="View/FrontOffice/suggest.php" class="button success">Test Quiz Suggestion Form</a>
                    <a href="test_email.php" class="button">Send Another Test Email</a>
                </div>
                
            <?php else: ?>
                <div class="message">
                    The email could not be sent. Please check your configuration.
                </div>
                
                <div class="error-box">
                    <h3>⚠️ Troubleshooting Steps:</h3>
                    <ul>
                        <li>Check <code>c:\xampp\sendmail\error.log</code> for error messages</li>
                        <li>Verify Gmail App Password in <code>c:\xampp\sendmail\sendmail.ini</code></li>
                        <li>Make sure 2-Step Verification is enabled on your Gmail account</li>
                        <li>Check that <code>sendmail_path</code> is uncommented in <code>c:\xampp\php\php.ini</code></li>
                        <li>Restart Apache after making configuration changes</li>
                    </ul>
                </div>
                
                <div class="info-box">
                    <h3>📋 Configuration Checklist:</h3>
                    <ul>
                        <li>sendmail.ini: smtp_server = smtp.gmail.com</li>
                        <li>sendmail.ini: smtp_port = 587</li>
                        <li>sendmail.ini: smtp_ssl = tls</li>
                        <li>sendmail.ini: auth_username = your-email@gmail.com</li>
                        <li>sendmail.ini: auth_password = your-app-password</li>
                        <li>php.ini: sendmail_path uncommented</li>
                    </ul>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="XAMPP_EMAIL_SETUP.md" class="button">View Setup Guide</a>
                    <a href="test_email.php" class="button danger">Try Again</a>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #ecf0f1; text-align: center; color: #7f8c8d; font-size: 14px;">
                <p>Test performed at: <strong><?= date('Y-m-d H:i:s') ?></strong></p>
                <p>Server: <strong><?= $_SERVER['SERVER_NAME'] ?></strong></p>
                <p>PHP Version: <strong><?= phpversion() ?></strong></p>
            </div>
        </div>
    </div>
</body>
</html>
