<?php
require_once 'config.php';
$user = getCurrentUser();
$conn = getDBConnection();
$currentPage = basename($_SERVER['PHP_SELF']);
$isHomePage = ($currentPage === 'index.php' || $currentPage === '');

// Get Map ID
$map_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Fetch Map Info
$map_stmt = $conn->prepare("SELECT * FROM maps WHERE id = ?");
$map_stmt->bind_param("i", $map_id);
$map_stmt->execute();
$map_info = $map_stmt->get_result()->fetch_assoc();

if (!$map_info) {
    die("Map not found.");
}

// Get Map Background
if (!empty($map_info['bg'])) {
    $bgImage = htmlspecialchars($map_info['bg']); 
} else {
    $bgImage = 'uploads/maps/default-map.jpg'; 
}

// 2. Fetch Characters on this Map
$char_sql = "SELECT c.name, cl.name as class_name, c.ally 
             FROM map_characters mc 
             JOIN characters c ON mc.character_id = c.character_id 
             JOIN classes cl ON c.class_id = cl.class_id 
             WHERE mc.map_id = ?";
$char_stmt = $conn->prepare($char_sql);
$char_stmt->bind_param("i", $map_id);
$char_stmt->execute();
$chars_result = $char_stmt->get_result();

// 3. Fetch Leaderboard Scores
$score_sql = "SELECT s.turns, s.date, u.username, u.profile_picture 
              FROM scores s 
              JOIN users u ON s.user_id = u.id 
              WHERE s.map_id = ? 
              ORDER BY s.turns ASC, s.date DESC 
              LIMIT 50";
$score_stmt = $conn->prepare($score_sql);
$score_stmt->bind_param("i", $map_id);
$score_stmt->execute();
$scores_result = $score_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($map_info['name']); ?> - Leaderboard</title>
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

        /* --- Synced Navigation Styles --- */
        .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background-color: #0f172a; border-bottom: 1px solid #334155; }
        
        .nav-container { 
            max-width: 1280px; 
            margin: 0 auto; 
            padding: 0 1rem; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 64px; 
            position: relative; 
        }
        
        .logo { 
            position: absolute; 
            left: 1rem;         
            display: flex; 
            align-items: center; 
            height: 100%; 
            text-decoration: none; 
        }
        
        .logo h2 { color: #fbbf24; font-size: 1.2rem; margin: 0; white-space: nowrap; }
        .logo a { color: inherit; text-decoration: none; }
        .nav-links { display: none; gap: 3rem; align-items: center; }
        .nav-links a { 
            color: #e2e8f0; 
            text-decoration: none; 
            transition: color 0.3s; 
            font-family: 'Press Start 2P', system-ui, sans-serif; 
            font-size: 1rem; 
        }
        
        .nav-links a:hover { color: #fbbf24; }

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

        /* Mobile Controls wrapper */
        .mobile-controls {
            display: none;
            position: absolute;
            right: 1rem;
            align-items: center;
            gap: 1rem;
        }

        /* Mobile Menu Elements */
        .mobile-menu-btn { display: block; background: none; border: none; color: #e2e8f0; cursor: pointer; }
        .mobile-menu { display: none; background-color: #1e293b; padding: 1rem; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        .mobile-menu.active { display: block; }
        .mobile-menu a { display: block; color: #e2e8f0; text-decoration: none; padding: 0.75rem; transition: color 0.3s; }
        .mobile-menu a:hover { color: #fbbf24; }
        .mobile-menu .btn { width: 100%; margin-top: 0.5rem; }

        @media (min-width: 1025px) {
            .nav-links { display: flex; }
            .mobile-controls { display: none; }
        }

        @media (max-width: 1024px) {
            .mobile-controls { display: flex; }
        }

        /* --- Page Content Styles --- */
        .container { max-width: 1000px; margin: 100px auto 4rem; padding: 0 1rem; }
        
        .map-header { background-color: #1e293b; background-size: cover; background-position: center; background-repeat: no-repeat; border: 1px solid #334155; border-radius: 1rem; overflow: hidden; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); padding: 4rem 2rem 2rem; position: relative; }
        .map-header-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(15, 23, 42, 0.3) 0%, rgba(15, 23, 42, 0.9) 70%, #0f172a 100%); z-index: 1; }
        .map-header-content { position: relative; z-index: 2; }

        .map-title { color: #fbbf24; font-size: 1.8rem; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5); letter-spacing: 0.05em; }
        .map-desc { color: #e2e8f0; font-size: 1.1rem; max-width: 800px; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        
        .char-container { margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .char-tag { background: rgba(0, 0, 0, 0.4); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.85rem; color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 0.5rem; backdrop-filter: blur(4px); }
        .char-ally { border-left: 3px solid #fbbf24; }
        .char-enemy { border-left: 3px solid #ef4444; }
        
        .table-wrapper { background-color: #1e293b; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #334155; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 1.25rem 1rem; border-bottom: 1px solid #334155; }
        th { background-color: #0f172a; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.05em; }
        tr:hover { background-color: #334155; }
        tr:last-child td { border-bottom: none; }
        
        .rank-1 { color: #fbbf24; font-weight: bold; }
        .rank-2 { color: #94a3b8; font-weight: bold; }
        .rank-3 { color: #b45309; font-weight: bold; }
        
        .user-flex { display: flex; align-items: center; gap: 1rem; font-weight: 500; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background: #fbbf24; color: #0f172a; display: grid; place-items: center; font-weight: bold; overflow: hidden;}
        
        .back-btn { display: inline-block; margin-bottom: 1rem; color: #fbbf24; text-decoration: none; font-weight: 500; }
        .back-btn:hover { text-decoration: underline; }
        .empty-state { padding: 3rem; text-align: center; color: #64748b; }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            
            <div class="nav-links">
                <?php if (!$isHomePage): ?>
                    <a href="index.php#home">Home</a>
                <?php endif; ?>
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
            
            <div class="mobile-controls">
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                
                <?php if ($user): ?>
                    <div class="user-menu">
                        <div class="user-avatar" onclick="toggleMobileUserMenu(event)">
                            <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'uploads/profiles/default.png'): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-dropdown" id="mobileUserDropdown">
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
                <?php endif; ?>
            </div>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <?php if (!$isHomePage): ?>
                <a href="index.php#home">Home</a>
            <?php endif; ?>
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
        <a href="leaderboard.php" class="back-btn">← Back to Map Selection</a>
        
        <div class="map-header" style="background-image: url('<?php echo $bgImage; ?>');">
            <div class="map-header-overlay"></div>
            
            <div class="map-header-content">
                <h1 class="map-title"><?php echo htmlspecialchars($map_info['name']); ?></h1>
                <p class="map-desc"><?php echo htmlspecialchars($map_info['description']); ?></p>
                
                <?php if ($chars_result->num_rows > 0): ?>
                    <div class="char-container">
                        <span style="color: #cbd5e1; font-size: 0.9rem; margin-right: 0.5rem; align-self: center;">Featured Characters:</span>
                        <?php while($char = $chars_result->fetch_assoc()): ?>
                            <div class="char-tag <?php echo $char['ally'] ? 'char-ally' : 'char-enemy'; ?>">
                                <?php echo $char['ally'] ? '🛡️' : '⚔️'; ?>
                                <?php echo htmlspecialchars($char['name']); ?> 
                                <span style="opacity: 0.7; font-size: 0.8em;">(<?php echo htmlspecialchars($char['class_name']); ?>)</span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-wrapper">
            <?php if ($scores_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th width="10%">Rank</th>
                        <th width="50%">Player</th>
                        <th width="20%">Turns</th>
                        <th width="20%">Date Achieved</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    while($row = $scores_result->fetch_assoc()): 
                        $rankClass = $rank <= 3 ? "rank-$rank" : "";
                    ?>
                    <tr>
                        <td class="<?php echo $rankClass; ?>">#<?php echo $rank; ?></td>
                        <td>
                            <div class="user-flex">
                                <div class="avatar">
                                    <?php if (!empty($row['profile_picture']) && $row['profile_picture'] !== 'uploads/profiles/default.png'): ?>
                                        <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </div>
                        </td>
                        <td style="font-family: monospace; font-size: 1.1rem; color: #fbbf24;">
                            <?php echo htmlspecialchars($row['turns']); ?>
                        </td>
                        <td>
                            <?php echo date('M j, Y', strtotime($row['date'])); ?>
                        </td>
                    </tr>
                    <?php $rank++; endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Records Yet</h3>
                    <p>Be the first to conquer this map and claim your spot on the leaderboard!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleMobileMenu() { document.getElementById('mobileMenu').classList.toggle('active'); }
        function toggleUserMenu(e) { e.stopPropagation(); document.getElementById('userDropdown').classList.toggle('active'); }
        function toggleMobileUserMenu(e) { e.stopPropagation(); document.getElementById('mobileUserDropdown').classList.toggle('active'); }
        
        document.addEventListener('click', function(e) {
            document.querySelectorAll('.user-dropdown').forEach(dropdown => {
                if (!dropdown.parentElement.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>