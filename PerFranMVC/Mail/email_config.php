<?php
/**
 * Email Configuration
 * 
 * IMPORTANT: Before using this system, you need to:
 * 1. Enable 2-Step Verification in your Gmail account
 * 2. Generate an App Password: https://myaccount.google.com/apppasswords
 * 3. Replace 'your-app-password-here' below with your actual app password
 */

return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_auth' => true,
    
    // Gmail credentials
    'smtp_username' => 'mahdimk.kar2005@gmail.com',
    'smtp_password' => 'your-app-password-here', // REPLACE WITH YOUR GMAIL APP PASSWORD
    
    // Sender info
    'from_email' => 'mahdimk.kar2005@gmail.com',
    'from_name' => 'PerFran Quiz System',
    
    // Admin email (who receives suggestions)
    'admin_email' => 'mahdimk.kar2005@gmail.com',
    'admin_name' => 'Quiz Administrator',
    
    // Server URL (for approval links)
    'base_url' => 'http://localhost/perfran/PerFran-master/PerFranMVC',
];
