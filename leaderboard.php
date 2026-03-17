<?php
require_once 'config.php';
$currentPage = basename($_SERVER['PHP_SELF']);
$isHomePage = ($currentPage === 'index.php' || $currentPage === '');
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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <div class="nav-links">
                <?php if (!$isHomePage): ?><a href="index.php#home">Home</a><?php endif; ?>
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
            <?php if (!$isHomePage): ?><a href="index.php#home">Home</a><?php endif; ?>
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
                <a href="login.php" class="btn btn-block">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="leaderboard-container">
        <a href="index.php" class="back-btn">← Back to Menu</a>
        <h1>Select a Realm</h1>
        <p class="subtitle">Choose a map to view the legends who conquered it.</p>

        <div class="maps-grid">
            <?php if ($maps_result && $maps_result->num_rows > 0): ?>
                <?php while($map = $maps_result->fetch_assoc()): ?>
                    <a href="map_leaderboard.php?id=<?php echo $map['id']; ?>" class="map-card">
                        <?php 
                            $derivedFileName = strtolower(str_replace(' ', '_', $map['name'])) . '.jpg';
                            $derivedPath = 'uploads/maps/' . $derivedFileName;
                            
                            if (file_exists($derivedPath)) {
                                $bgImage = $derivedPath;
                            } elseif (!empty($map['bg'])) {
                                $bgImage = htmlspecialchars(strpos($map['bg'], 'uploads/maps/') !== false ? $map['bg'] : 'uploads/maps/' . basename($map['bg']));
                            } else {
                                $bgImage = 'uploads/maps/default-map.jpg';
                            }
                        ?>
                        <img src="<?php echo $bgImage; ?>" alt="<?php echo htmlspecialchars($map['name']); ?>" class="map-preview">
                        <div class="map-content">
                            <h2 class="map-title"><?php echo htmlspecialchars($map['name']); ?></h2>
                            <div class="map-desc"><?php echo htmlspecialchars($map['description'] ?? 'A mysterious land awaiting conquest.'); ?></div>
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
<?php if(isset($conn)) $conn->close(); ?>