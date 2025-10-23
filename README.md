# Treasure Quest - Account System Setup

## Installation Instructions

### 1. Database Setup
1. Create a MySQL database named `treasure_quest`
2. Import the SQL from `database.sql` or run these commands:

```sql
CREATE DATABASE IF NOT EXISTS treasure_quest;
USE treasure_quest;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Configure Database Connection
Edit `config.php` and update these constants:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'treasure_quest');
```

### 3. File Structure
Place these files in your web server directory:
```
/your-website/
├── config.php
├── index.php
├── login.php
├── forgot-password.php
├── logout.php
└── .htaccess (optional)
```

### 4. Required PHP Extensions
- mysqli
- session support
- password_hash support (PHP 5.5+)

### 5. Testing
1. Navigate to your website
2. Click "Login" button
3. Register a new account
4. Login with your credentials
5. You should see your avatar icon instead of the Login button

## Features Included

✅ User Registration with validation
✅ Secure Login system (password hashing)
✅ Password Reset functionality
✅ User avatar display when logged in
✅ User dropdown menu with profile options
✅ Session management
✅ Mobile responsive design
✅ SQL injection protection (prepared statements)
✅ Password strength requirements (min 6 characters)

## Security Notes

### Production Recommendations:
1. **Enable HTTPS** - Uncomment the HTTPS redirect in .htaccess
2. **Email Integration** - The forgot password currently shows the reset link. In production, integrate with an email service (PHPMailer, SendGrid, etc.)
3. **CSRF Protection** - Add CSRF tokens to forms
4. **Rate Limiting** - Implement rate limiting for login attempts
5. **Environment Variables** - Store database credentials in environment variables
6. **Input Sanitization** - Already implemented with prepared statements and htmlspecialchars()

## Password Reset Flow

1. User clicks "Forgot Password?" on login page
2. Enters their email address
3. System generates a secure token
4. Token is valid for 1 hour
5. User clicks reset link (in production, sent via email)
6. User enters new password
7. Password is updated and user can login

## Customization

### Change Password Requirements
Edit the validation in `login.php`:
```php
elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters';
}
```

### Modify User Avatar
The avatar shows the first letter of the username. To change this, edit `index.php`:
```php
<?php echo strtoupper(substr($user['username'], 0, 1)); ?>
```

### Add Profile Picture Support
1. Add `profile_picture` column to users table
2. Create upload functionality
3. Display image instead of letter avatar

## Troubleshooting

**Can't connect to database:**
- Check database credentials in config.php
- Verify MySQL service is running
- Check user has proper permissions

**Session not persisting:**
- Ensure session_start() is called before any output
- Check PHP session configuration
- Verify write permissions on session directory

**Password reset link doesn't work:**
- Check that the token hasn't expired (1 hour limit)
- Verify the URL is complete and not truncated
- Check server time is correct

## Support
For issues or questions, please refer to the PHP and MySQL documentation.();
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();
        
        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email or username already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>