<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    if (empty($username) || empty($email)) {
        $error = 'Please fill in all fields';
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $username, $email, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already taken';
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $user['id']);
            
            if ($stmt->execute()) {
                $success = 'Profile updated successfully!';
                $_SESSION['username'] = $username;
                $user['username'] = $username;
                $user['email'] = $email;
            } else {
                $error = 'Update failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if (password_verify($current_password, $user_data['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user['id']);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Password change failed. Please try again.';
            }
        } else {
            $error = 'Current password is incorrect';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $confirm_password = $_POST['confirm_delete_password'];
    
    if (empty($confirm_password)) {
        $error = 'Please enter your password to confirm deletion';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if (password_verify($confirm_password, $user_data['password'])) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                session_destroy();
                header('Location: index.php?deleted=1');
                exit;
            } else {
                $error = 'Account deletion failed. Please try again.';
            }
        } else {
            $error = 'Incorrect password';
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
    <title>My Profile - Treasure Quest</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0f172a;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: #0f172a;
            border-bottom: 1px solid #334155;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .logo {
            color: #fbbf24;
            font-weight: bold;
            font-size: 24px;
            letter-spacing: 0.05em;
            text-decoration: none;
        }

        .back-link {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #fbbf24;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 5rem 1rem 2rem;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            font-weight: bold;
            font-size: 48px;
            margin: 0 auto 1rem;
            border: 4px solid #fbbf24;
        }

        h1 {
            color: #fbbf24;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: #94a3b8;
        }

        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card h2 {
            color: #fbbf24;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 0.5rem;
            color: #e2e8f0;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #fbbf24;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #fca5a5;
        }

        .alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid #22c55e;
            color: #86efac;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #fbbf24;
            color: #0f172a;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #f59e0b;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .danger-zone {
            border: 1px solid #ef4444;
            background-color: rgba(239, 68, 68, 0.05);
        }

        .danger-zone h2 {
            color: #ef4444;
        }

        .delete-warning {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            color: #fca5a5;
        }

        .card p {
            color: #94a3b8;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <a href="index.php" class="logo">TREASURE QUEST</a>
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h1><?php echo htmlspecialchars($user['username']); ?></h1>
            <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Profile Information</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>

        <div class="card">
            <h2>Change Password</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>

        <div class="card">
            <h2>Account Actions</h2>
            <p style="margin-bottom: 1.5rem;">Manage your account settings and preferences.</p>
            <a href="logout.php" class="btn" style="background-color: #fbbf24; margin-bottom: 1rem;">Logout</a>
        </div>

        <div class="card danger-zone">
            <h2>Delete Account</h2>
            <div class="delete-warning">
                <strong>⚠️ Warning:</strong> Deleting your account is permanent and cannot be undone. All your data will be lost forever.
            </div>
            <form method="POST" action="" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!');">
                <div class="form-group">
                    <label for="confirm_delete_password">Enter your password to confirm deletion</label>
                    <input type="password" id="confirm_delete_password" name="confirm_delete_password" required>
                </div>
                <button type="submit" name="delete_account" class="btn btn-danger">Delete Account Permanently</button>
            </form>
        </div>
    </div>
</body>
</html>