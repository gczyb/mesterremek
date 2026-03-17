<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filesize = $_FILES['profile_picture']['size'];

        if (!in_array(strtolower($filetype), $allowed)) {
            $error = 'Only JPG, JPEG, PNG, and GIF files are allowed';
        } elseif ($filesize > 5000000) { 
            $error = 'File size must be less than 5MB';
        } else {
            $upload_dir = 'uploads/profiles/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $new_filename = 'user_' . $user['id'] . '_' . time() . '.' . $filetype;
            $target_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                $conn = getDBConnection();
                if (!empty($user['profile_picture']) && file_exists($user['profile_picture']) && strpos($user['profile_picture'], 'default.png') === false) {
                    unlink($user['profile_picture']);
                }

                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $target_path, $user['id']);
                
                if ($stmt->execute()) {
                    $success = 'Profile picture updated successfully!';
                    $user = getCurrentUser();
                } else {
                    $error = 'Failed to update database.';
                }
                $stmt->close();
                $conn->close();
            } else {
                $error = 'Failed to move uploaded file.';
            }
        }
    } else {
        $error = 'Please select an image file';
    }
}

// Handle profile picture removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_picture'])) {
    if (!empty($user['profile_picture']) && file_exists($user['profile_picture']) && strpos($user['profile_picture'], 'default.png') === false) {
        unlink($user['profile_picture']);
    }

    $conn = getDBConnection();
    $default_pic = 'uploads/profiles/default.png';
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $default_pic, $user['id']);

    if ($stmt->execute()) {
        $success = 'Profile picture removed successfully!';
        $user['profile_picture'] = $default_pic;
    } else {
        $error = 'Failed to remove profile picture';
    }
    $stmt->close();
    $conn->close();
}

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
    <link rel="stylesheet" href="styles.css">
</head>
<body class="profile-page">
    <nav class="nav">
        <div class="nav-container profile-nav">
            <a href="index.php" class="logo profile-logo">TREASURE QUEST</a>
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                <?php endif; ?>
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
            <h2>Account Actions</h2>
            
            <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #334155;">
                <h3 style="color: #cbd5e1; font-size: 1.125rem; margin-bottom: 1rem;">Profile Picture</h3>
                <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #fbbf24, #f59e0b); display: flex; align-items: center; justify-content: center; border: 3px solid #fbbf24; overflow: hidden;">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="font-size: 32px; color: #0f172a; font-weight: bold;"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <form method="POST" action="" enctype="multipart/form-data" style="display: inline-block;">
                            <input type="file" name="profile_picture" accept="image/*" required style="display: none;" id="profilePicInput" onchange="this.form.submit()">
                            <label for="profilePicInput" class="btn" style="cursor: pointer; display: inline-block; margin-right: 0.5rem;">Upload New Picture</label>
                            <input type="hidden" name="upload_picture" value="1">
                        </form>
                        
                        <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'uploads/profiles/default.png'): ?>
                            <form method="POST" action="" style="display: inline-block;">
                                <button type="submit" name="remove_picture" class="btn btn-danger" onclick="return confirm('Remove profile picture?')">Remove Picture</button>
                            </form>
                        <?php endif; ?>
                        
                        <p style="color: #94a3b8; font-size: 0.875rem; margin-top: 0.5rem;">JPG, PNG or GIF. Max 5MB.</p>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #334155;">
                <h3 style="color: #cbd5e1; font-size: 1.125rem; margin-bottom: 1rem;">Change Username</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">New Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-block">Update Profile</button>
                </form>
            </div>

            <div>
                <h3 style="color: #cbd5e1; font-size: 1.125rem; margin-bottom: 1rem;">Change Password</h3>
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
                    <button type="submit" name="change_password" class="btn btn-block">Change Password</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>Logout</h2>
            <p style="margin-bottom: 1.5rem;">End your current session and return to the homepage.</p>
            <a href="logout.php" class="btn" style="background-color: #fbbf24; display:block; text-align:center;">Logout</a>
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