<?php
require_once 'config.php';
$user = getCurrentUser();
$conn = getDBConnection();

// Fetch Wiki Entries
$wiki_result = $conn->query("SELECT * FROM wiki_entries ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Wiki - Treasure Quest</title>
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

        /* Wiki Page Specific Styles */
        .container { max-width: 800px; margin: 100px auto 4rem; padding: 0 1rem; }
        .page-header { text-align: center; margin-bottom: 3rem; }
        .page-header p { color: #94a3b8; font-family: sans-serif; margin-top: 1rem; }

        .wiki-card { background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); }
        .wiki-card h2 { font-size: 1.2rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #334155; }
        .wiki-content { color: #cbd5e1; font-size: 1.05rem; white-space: pre-wrap; font-family: sans-serif; }
        .wiki-meta { font-size: 0.85rem; color: #64748b; margin-top: 1.5rem; text-align: right; font-family: sans-serif; border-top: 1px dashed #334155; padding-top: 0.5rem;}
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
                <a href="wiki.php" class="active">Wiki</a>
                
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
        <div class="page-header">
            <h1>The Grand Wiki</h1>
            <p>Lore, guides, and knowledge compiled by the Realm Masters.</p>
        </div>

        <?php if ($wiki_result && $wiki_result->num_rows > 0): ?>
            <?php while($wiki = $wiki_result->fetch_assoc()): ?>
                <div class="wiki-card">
                    <h2><?php echo htmlspecialchars($wiki['title']); ?></h2>
                    <div class="wiki-content"><?php echo nl2br(htmlspecialchars($wiki['content'])); ?></div>
                    <div class="wiki-meta">Added: <?php echo date('M j, Y', strtotime($wiki['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; color: #64748b; padding: 3rem;">
                <h3 style="font-family: sans-serif;">The archives are empty.</h3>
                <p>Check back later for new guides and lore!</p>
            </div>
        <?php endif; ?>
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