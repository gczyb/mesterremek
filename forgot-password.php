<?php
require_once 'config.php';

$error = '';
$success = '';
$step = 'request';

if (isset($_GET['token'])) {
    $step = 'reset';
    $token = $_GET['token'];
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $reset_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
            $stmt->bind_param("sss", $reset_token, $expires, $email);
            $stmt->execute();
            
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/forgot-password.php?token=" . $reset_token;
            
            // --- SEND PASSWORD RESET EMAIL ---
            $subject = "Treasure Quest - Password Reset Request";
            $message = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #0f172a; color: #cbd5e1; padding: 20px;'>
                <div style='background-color: #1e293b; padding: 30px; border-radius: 8px; border: 1px solid #334155; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #fbbf24; margin-top: 0;'>Password Reset Request</h2>
                    <p style='font-size: 16px; line-height: 1.5;'>We received a request to reset your Treasure Quest password.</p>
                    <p style='font-size: 16px; line-height: 1.5;'>Click the link below to set a new password. This link will expire in 1 hour.</p>
                    <div style='margin: 30px 0;'>
                        <a href='" . $reset_link . "' style='background-color: #fbbf24; color: #000000; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password</a>
                    </div>
                    <p style='font-size: 12px; color: #94a3b8; margin-top: 30px; border-top: 1px solid #334155; padding-top: 15px;'>If you did not request this, please safely ignore this email. Your password will remain unchanged.</p>
                </div>
            </body>
            </html>";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
            
            mail($email, $subject, $message, $headers);
            // ---------------------------------
            
            // Generic message maintains security so attackers can't verify if an email exists
            $success = 'If an account exists with that email, a reset link has been sent.';
        } else {
            $success = 'If an account exists with that email, a reset link has been sent.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user['id']);
            $stmt->execute();
            
            $success = 'Password reset successfully! <a href="login.php" style="color: #fbbf24;">Click here to login</a>';
        } else {
            $error = 'Invalid or expired reset token';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Treasure Quest</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h1>TREASURE QUEST</h1>
            <p><?php echo $step === 'request' ? 'Reset Your Password' : 'Create New Password'; ?></p>
        </div>

        <div class="auth-content">
            <a href="login.php" class="back-link">← Back to Login</a>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($step === 'request'): ?>
                <p class="info-text">Enter your email address and we'll send you a link to reset your password.</p>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <button type="submit" name="request_reset" class="btn btn-block">Send Reset Link</button>
                </form>
            <?php else: ?>
                <p class="info-text">Enter your new password below.</p>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" name="reset_password" class="btn btn-block">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>