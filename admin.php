<?php
require_once 'config.php';

// 1. Security Check: Must be logged in AND be an admin
$user = getCurrentUser();
if (!$user || !isset($user['admin']) || $user['admin'] != 1) {
    header("HTTP/1.1 403 Forbidden");
    die("<div style='background:#0f172a; color:#ef4444; padding:2rem; font-family:sans-serif; text-align:center;'>
            <h1>403 Forbidden</h1><p>You do not have permission to access this dashboard.</p>
            <a href='index.php' style='color:#fbbf24;'>Return Home</a>
         </div>");
}

$conn = getDBConnection();
$message = '';
$error = '';

// Handle Admin Actions (Delete User / Toggle Admin / Wiki Management)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
    
    if (isset($_POST['action'])) {
        
        // --- USER MANAGEMENT ---
        if ($_POST['action'] === 'delete') {
            if ($target_id === $user['id']) {
                $error = "You cannot delete yourself.";
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $target_id);
                if ($stmt->execute()) {
                    $message = "User deleted successfully.";
                } else {
                    $error = "Failed to delete user.";
                }
                $stmt->close();
            }
        } 
        elseif ($_POST['action'] === 'toggle_admin') {
            if ($target_id === $user['id']) {
                $error = "You cannot change your own admin status.";
            } else {
                $check_stmt = $conn->prepare("SELECT admin FROM users WHERE id = ?");
                $check_stmt->bind_param("i", $target_id);
                $check_stmt->execute();
                $target_user = $check_stmt->get_result()->fetch_assoc();
                $check_stmt->close();

                if ($target_user) {
                    $new_status = $target_user['admin'] ? 0 : 1;
                    $update_stmt = $conn->prepare("UPDATE users SET admin = ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $new_status, $target_id);
                    if ($update_stmt->execute()) {
                        $message = "User admin status updated.";
                    } else {
                        $error = "Failed to update user status.";
                    }
                    $update_stmt->close();
                }
            }
        }
        
        // --- WIKI MANAGEMENT ---
        elseif ($_POST['action'] === 'add_wiki') {
            $title = trim($_POST['wiki_title']);
            $content = trim($_POST['wiki_content']);
            
            if (!empty($title) && !empty($content)) {
                $stmt = $conn->prepare("INSERT INTO wiki_entries (title, content) VALUES (?, ?)");
                $stmt->bind_param("ss", $title, $content);
                if ($stmt->execute()) {
                    $message = "Wiki entry added successfully.";
                } else {
                    $error = "Failed to add wiki entry.";
                }
                $stmt->close();
            } else {
                $error = "Wiki title and content cannot be empty.";
            }
        }
        elseif ($_POST['action'] === 'delete_wiki') {
            $stmt = $conn->prepare("DELETE FROM wiki_entries WHERE id = ?");
            $stmt->bind_param("i", $target_id);
            if ($stmt->execute()) {
                $message = "Wiki entry deleted.";
            } else {
                $error = "Failed to delete wiki entry.";
            }
            $stmt->close();
        }
    }
}

// Fetch Stats
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'scores' => $conn->query("SELECT COUNT(*) as count FROM scores")->fetch_assoc()['count'],
    'maps' => $conn->query("SELECT COUNT(*) as count FROM maps")->fetch_assoc()['count']
];

// Fetch Data for Tables
$users_result = $conn->query("SELECT id, username, email, admin, created_at FROM users ORDER BY created_at DESC");
$wiki_result = $conn->query("SELECT * FROM wiki_entries ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Treasure Quest</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f172a; color: #cbd5e1; line-height: 1.6; }
        h1, h2, h3 { font-family: 'Press Start 2P', system-ui, sans-serif; line-height: 1.4; color: #fbbf24; }
        
        /* Universal Navbar CSS */
        .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background-color: #0f172a; border-bottom: 1px solid #334155; }
        .nav-container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .logo { display: flex; align-items: center; height: 100%; text-decoration: none; }
        .logo h2 { color: #fbbf24; font-size: 1.2rem; margin: 0; white-space: nowrap; }
        .logo a { color: inherit; text-decoration: none; }
        .nav-links { display: none; gap: 2rem; align-items: center; }
        .nav-links a { color: #e2e8f0; text-decoration: none; transition: color 0.3s; font-family: -apple-system, sans-serif; }
        .nav-links a:hover { color: #fbbf24; }
        .nav-links a.active { color: #fbbf24; }
        .btn { background-color: #fbbf24; color: #0f172a; border: none; padding: 0.5rem 1.5rem; border-radius: 0.375rem; cursor: pointer; font-weight: bold; transition: background-color 0.3s; text-decoration: none; display: inline-block; font-family: -apple-system, sans-serif; }
        .btn:hover { background-color: #f59e0b; }
        .btn-outline { background-color: transparent; color: #fbbf24; border: 1px solid #fbbf24; }
        .btn-outline:hover { background-color: rgba(251, 191, 36, 0.1); }

        /* User Menu & Avatar */
        .user-menu { position: relative; display: flex; align-items: center; font-family: -apple-system, sans-serif; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #fbbf24, #f59e0b); display: flex; align-items: center; justify-content: center; color: #0f172a; font-weight: bold; cursor: pointer; border: 2px solid #fbbf24; position: relative; z-index: 100; transition: all 0.3s; }
        .user-avatar:hover { transform: scale(1.05); }
        .user-dropdown { display: none; position: absolute; top: 50px; right: 0; background-color: #1e293b; border: 1px solid #334155; border-radius: 0.5rem; min-width: 200px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); z-index: 1001; }
        .user-dropdown.active { display: block; }
        .user-dropdown a { display: block; padding: 0.75rem 1rem; color: #e2e8f0; text-decoration: none; transition: background-color 0.3s; cursor: pointer; }
        .user-dropdown a:hover { background-color: #334155; }
        .user-dropdown .user-info { padding: 0.75rem 1rem; border-bottom: 1px solid #334155; color: #94a3b8; }
        .user-dropdown .user-info strong { display: block; color: #fbbf24; margin-bottom: 0.25rem; }

        /* Mobile Menu Elements */
        .mobile-menu-btn { display: block; background: none; border: none; color: #e2e8f0; cursor: pointer; }
        .mobile-menu { display: none; background-color: #1e293b; padding: 1rem; font-family: -apple-system, sans-serif; }
        .mobile-menu.active { display: block; }
        .mobile-menu a { display: block; color: #e2e8f0; text-decoration: none; padding: 0.75rem; transition: color 0.3s; }
        .mobile-menu a:hover { color: #fbbf24; }
        .mobile-menu .btn { width: 100%; margin-top: 0.5rem; }
        
        @media (min-width: 1025px) { .nav-links { display: flex; } .mobile-menu-btn { display: none; } }

        /* Admin Page Specific Styles */
        .container { max-width: 1200px; margin: 100px auto 2rem; padding: 0 1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
        .stat-card { background: #1e293b; border: 1px solid #334155; border-radius: 0.5rem; padding: 1.5rem; text-align: center; }
        .stat-card h3 { color: #94a3b8; font-size: 1rem; font-family: sans-serif; margin-bottom: 0.5rem; }
        .stat-card .value { font-size: 2.5rem; color: #fbbf24; font-weight: bold; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; padding: 2rem; overflow-x: auto; margin-bottom: 2rem;}
        
        table { width: 100%; border-collapse: collapse; text-align: left; min-width: 800px; }
        th, td { padding: 1rem; border-bottom: 1px solid #334155; }
        th { background-color: #0f172a; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background-color: #334155; }
        
        .badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: bold; }
        .badge-admin { background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid #fbbf24; }
        .badge-user { background: rgba(148, 163, 184, 0.2); color: #94a3b8; border: 1px solid #94a3b8; }

        .action-btn { background: none; border: 1px solid; padding: 0.4rem 0.8rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.8rem; transition: all 0.2s; color: white; margin-right: 0.5rem;}
        .btn-toggle { border-color: #3b82f6; background-color: rgba(59, 130, 246, 0.1); color: #60a5fa; }
        .btn-toggle:hover { background-color: #3b82f6; color: white; }
        .btn-delete { border-color: #ef4444; background-color: rgba(239, 68, 68, 0.1); color: #f87171; }
        .btn-delete:hover { background-color: #ef4444; color: white; }
        
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid #22c55e; color: #86efac; }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #fca5a5; }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <div class="logo">
                <h2><a href="index.php">TREASURE QUEST</a></h2>
            </div>
            
            <div class="nav-links">
                <a href="index.php#home">Home</a>
                <a href="index.php#about">About</a>
                <a href="index.php#features">Features</a>
                <a href="index.php#gallery">Gallery</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="wiki.php">Wiki</a>
                
                <?php if ($user): ?>
                    <div class="user-menu" style="margin-left: 1rem;">
                        <div class="user-avatar" onclick="toggleUserMenu(event)">
                            <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'uploads/profiles/default.png'): ?>
                                <?php 
                                    $profileData = $user['profile_picture'];
                                    if (strpos($profileData, '.') === false && strlen($profileData) > 100) {
                                        $avatarSrc = 'data:image/jpeg;base64,' . base64_encode($profileData);
                                    } else {
                                        $avatarSrc = htmlspecialchars($profileData);
                                    }
                                ?>
                                <img src="<?php echo $avatarSrc; ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <a href="profile.php">My Profile</a>
                            <a href="profile.php">Settings</a>
                            <?php if (isset($user['admin']) && $user['admin'] == 1): ?>
                                <a href="admin.php" style="color: #fbbf24; border-top: 1px solid #334155;">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline" style="cursor: pointer; margin-left: 1rem;">Login</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <a href="index.php#home">Home</a>
            <a href="index.php#about">About</a>
            <a href="index.php#features">Features</a>
            <a href="index.php#gallery">Gallery</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="wiki.php">Wiki</a>
            <?php if ($user): ?>
                <a href="profile.php">Profile (<?php echo htmlspecialchars($user['username']); ?>)</a>
                <?php if (isset($user['admin']) && $user['admin'] == 1): ?>
                    <a href="admin.php" style="color: #fbbf24;">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1 style="margin-bottom: 2rem; font-size: 1.5rem;">System Dashboard</h1>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?php echo $stats['users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Scores Logged</h3>
                <div class="value"><?php echo $stats['scores']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Realms (Maps)</h3>
                <div class="value"><?php echo $stats['maps']; ?></div>
            </div>
        </div>

        <h2 style="margin-bottom: 1.5rem; font-size: 1.2rem;">User Management</h2>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td style="color: #94a3b8;">#<?php echo $row['id']; ?></td>
                        <td style="font-weight: bold; color: #e2e8f0;"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <?php if ($row['admin']): ?>
                                <span class="badge badge-admin">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-user">Player</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: #94a3b8;"><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <?php if ($row['id'] !== $user['id']): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to change this user\'s role?');">
                                    <input type="hidden" name="target_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_admin">
                                    <button type="submit" class="action-btn btn-toggle">
                                        <?php echo $row['admin'] ? 'Revoke Admin' : 'Make Admin'; ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you absolutely sure you want to delete this user? This cannot be undone.');">
                                    <input type="hidden" name="target_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="action-btn btn-delete">Delete</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #64748b; font-style: italic; font-size: 0.85rem;">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin: 3rem 0 1.5rem; font-size: 1.2rem;">Wiki Management</h2>
        
        <div class="card">
            <h3 style="color: #94a3b8; font-family: sans-serif; margin-bottom: 1rem;">Add New Entry</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_wiki">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; color: #cbd5e1; font-family: sans-serif; margin-bottom: 0.5rem;">Title</label>
                    <input type="text" name="wiki_title" required style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #e2e8f0;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; color: #cbd5e1; font-family: sans-serif; margin-bottom: 0.5rem;">Content (Supports basic HTML like &lt;b&gt;, &lt;br&gt;)</label>
                    <textarea name="wiki_content" required rows="5" style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #e2e8f0; resize: vertical;"></textarea>
                </div>
                <button type="submit" class="btn">Post to Wiki</button>
            </form>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($wiki_result && $wiki_result->num_rows > 0): ?>
                        <?php while($wiki = $wiki_result->fetch_assoc()): ?>
                        <tr>
                            <td style="color: #94a3b8;">#<?php echo $wiki['id']; ?></td>
                            <td style="font-weight: bold; color: #e2e8f0;"><?php echo htmlspecialchars($wiki['title']); ?></td>
                            <td style="color: #94a3b8;"><?php echo date('M j, Y', strtotime($wiki['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this wiki entry?');">
                                    <input type="hidden" name="target_id" value="<?php echo $wiki['id']; ?>">
                                    <input type="hidden" name="action" value="delete_wiki">
                                    <button type="submit" class="action-btn btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b;">No wiki entries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function toggleMobileMenu() { document.getElementById('mobileMenu').classList.toggle('active'); }
        function toggleUserMenu(e) { e.stopPropagation(); document.getElementById('userDropdown').classList.toggle('active'); }
        document.addEventListener('click', function(e) {
            const d = document.getElementById('userDropdown'), u = document.querySelector('.user-menu');
            if (d && u && !u.contains(e.target)) d.classList.remove('active');
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>