<?php
require_once 'config.php';
$user = getCurrentUser();
$conn = getDBConnection();
// Fetch all maps
$maps_result = $conn->query("SELECT * FROM maps ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select a Realm - Leaderboard</title>
    <style>
        /* Import the Press Start 2P font */
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        
        /* Core Theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0f172a;
            color: #cbd5e1;
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Press Start 2P', system-ui, sans-serif;
            line-height: 1.4;
        }

        /* --- Universal Navigation Styles --- */
        .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background-color: #0f172a; border-bottom: 1px solid #334155; }
        .nav-container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        
        .logo { display: flex; align-items: center; height: 100%; text-decoration: none; }
        .logo h2 { color: #fbbf24; font-size: 1.2rem; margin: 0; white-space: nowrap; }
        .logo a { color: inherit; text-decoration: none; }
        
        .nav-links { display: none; gap: 2rem; align-items: center; }
        .nav-links a { color: #e2e8f0; text-decoration: none; transition: color 0.3s; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        .nav-links a:hover { color: #fbbf24; }
        .nav-links a.active { color: #fbbf24; }

        .btn { background-color: #fbbf24; color: #0f172a; border: none; padding: 0.5rem 1.5rem; border-radius: 0.375rem; cursor: pointer; font-weight: bold; transition: background-color 0.3s; text-decoration: none; display: inline-block; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        .btn:hover { background-color: #f59e0b; }
        .btn-outline { background-color: transparent; color: #fbbf24; border: 1px solid #fbbf24; }
        .btn-outline:hover { background-color: rgba(251, 191, 36, 0.1); }

        /* User Menu & Avatar */
        .user-menu { position: relative; display: flex; align-items: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
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
        .mobile-menu { display: none; background-color: #1e293b; padding: 1rem; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        .mobile-menu.active { display: block; }
        .mobile-menu a { display: block; color: #e2e8f0; text-decoration: none; padding: 0.75rem; transition: color 0.3s; }
        .mobile-menu a:hover { color: #fbbf24; }
        .mobile-menu .btn { width: 100%; margin-top: 0.5rem; }

        @media (min-width: 1025px) {
            .nav-links { display: flex; }
            .mobile-menu-btn { display: none; }
        }

        /* --- Leaderboard specific styles --- */
        .container { max-width: 1200px; margin: 100px auto 4rem; padding: 0 1rem; }
        h1 { color: #fbbf24; text-align: center; margin-bottom: 1rem; font-size: 1.8rem; letter-spacing: 0.1em; }
        .subtitle { text-align: center; color: #94a3b8; margin-bottom: 3rem; }

        .maps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }

        .map-card { height: 400px; background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; overflow: hidden; position: relative; text-decoration: none; transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s; display: block; }
        .map-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.5); border-color: #fbbf24; }

        .map-preview { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; transition: transform 0.5s ease; }
        .map-card:hover .map-preview { transform: scale(1.05); }

        .map-content { position: absolute; bottom: 0; left: 0; width: 100%; z-index: 2; padding: 1.5rem; display: flex; flex-direction: column; background: linear-gradient(to bottom, rgba(30, 41, 59, 0) 0%, rgba(30, 41, 59, 0.8) 35%, #1e293b 70%, #1e293b 100%); padding-top: 5rem; }
        .map-title { color: #fbbf24; font-size: 1.2rem; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        .map-desc { color: #cbd5e1; font-size: 0.9rem; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-shadow: 0 1px 2px rgba(0,0,0,0.8); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        
        .btn-view { background-color: rgba(251, 191, 36, 0.1); color: #fbbf24; border: 1px solid #fbbf24; padding: 0.5rem; border-radius: 0.5rem; text-align: center; font-weight: 600; font-size: 0.9rem; transition: all 0.3s; backdrop-filter: blur(4px); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        .map-card:hover .btn-view { background-color: #fbbf24; color: #0f172a; }

        .back-btn { display: inline-block; margin-bottom: 1rem; color: #94a3b8; text-decoration: none; transition: color 0.3s;}
        .back-btn:hover { color: #fbbf24; }
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
                <a href="leaderboard.php" class="active">Leaderboard</a>
                <a href="wiki.php">Wiki</a>
                
                <?php if ($user): ?>
                    <div class="user-menu" style="margin-left: 1rem;">
                        <div class="user-avatar" onclick="toggleUserMenu(event)">
                            <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'uploads/profiles/default.png'): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
        <a href="index.php" class="back-btn">← Back to Menu</a>
        <h1>Select a Realm</h1>
        <p class="subtitle">Choose a map to view the legends who conquered it.</p>

        <div class="maps-grid">
            <?php if ($maps_result && $maps_result->num_rows > 0): ?>
                <?php while($map = $maps_result->fetch_assoc()): ?>
                    <a href="map_leaderboard.php?id=<?php echo $map['id']; ?>" class="map-card">
                        <?php 
                            $bgImage = !empty($map['bg']) ? htmlspecialchars($map['bg']) : 'uploads/maps/default-map.jpg';
                        ?>
                        
                        <img src="<?php echo $bgImage; ?>" alt="<?php echo htmlspecialchars($map['name']); ?>" class="map-preview">
                        
                        <div class="map-content">
                            <h2 class="map-title"><?php echo htmlspecialchars($map['name']); ?></h2>
                            <div class="map-desc">
                                <?php echo htmlspecialchars($map['description'] ?? 'A mysterious land awaiting conquest.'); ?>
                            </div>
                            <div class="btn-view">View Leaderboard →</div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1/-1;">No maps available.</p>
            <?php endif; ?>
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
<?php if(isset($conn)) $conn->close(); ?>