# PerFran - French Language Learning Platform

![PerFran Logo](PerFranMVC/View/Perfran.png)

PerFran is an interactive web platform for learning French through engaging quiz-based games.

##  Features

- **Fill-in-the-Blank Quizzes**: Interactive French grammar exercises
- **Three Difficulty Levels**: Easy, Medium, and Hard
- **AI Quiz Generation**: Automatically create custom quizzes using Google Gemini AI
- **Voice Recognition**: Hands-free quiz interaction using speech
- **Single & Multiplayer Modes**: Practice solo or compete with others
- **Admin Dashboard**: Manage quizzes, users, and content
- **Email Approval System**: Review user-submitted quizzes via email

##  Technologies Used

### Backend
- **PHP 8.2.12** - Server-side logic
- **MariaDB 10.4.32** - Database
- **MVC Architecture** - Code organization

### Frontend
- **HTML4-5** - Modern web structure and advanced APIs (Web Speech, Drag & Drop)
- **CSS3** - Styling and design
- **JavaScript (Vanilla)** - Interactivity
- **Bootstrap 4** - Responsive UI

### APIs & Services
- **Google Gemini 2.5 Flash** - AI quiz generation
- **Web Speech API** - Voice recognition
- **PHPMailer** - Email functionality

##  Installation

### Prerequisites
- XAMPP (Apache, MySQL/MariaDB, PHP 8.2+)
- Gmail account (for email features)
- Google Gemini API key (for AI quiz generation)

### Setup Steps

1. **Extract to XAMPP**
   ```
   c:\xampp\htdocs\perfran\
   ```

2. **Import Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `perfran`
   - Import: `database/perfran.sql`

3. **Configure (Optional)**
   - Email: Edit `PerFranMVC/config/email_config.php`
   - AI: Create `database/config.php` and add your Gemini API key

4. **Start XAMPP**
   - Launch XAMPP Control Panel
   - Start Apache and MySQL

5. **Access Application**
   - FrontOffice: `http://localhost/perfran/PerFran-master/PerFranMVC/View/FrontOffice/`
   - BackOffice: `http://localhost/perfran/PerFran-master/PerFranMVC/View/BackOffice/`

##  How to Use

### For Players
1. Register or login
2. Choose difficulty level (Easy, Medium, Hard)
3. Play quizzes by filling in blanks
4. Optional: Use voice mode to speak your answers
5. Track your progress and scores

### For Administrators
1. Login with admin credentials
2. Create, edit, or delete quizzes
3. Manage users and statistics
4. Approve/reject user-submitted quizzes

## 📊 Database Structure

The project uses **2 tables**:

- `quiz` - Quiz paragraphs
- `quiz_blanks` - Answer options for each quiz

##  Security Features

- Password hashing (SHA1)
- SQL injection protection (PDO prepared statements)
- Role-based access control (player/admin)
- Input validation and sanitization

##  Troubleshooting

**Database connection error?**
- Ensure MySQL is running in XAMPP
- Verify database name is `perfran`

**Email not sending?**
- Check Gmail App Password in `config/email_config.php`
- Enable 2-Step Verification on Gmail

**AI generation not working?**
- Verify API key in `database/config.php`


## 📝 License

This project is developed for educational purposes.

---

**PerFran** - Learn French, Play Smart! 🇫🇷
