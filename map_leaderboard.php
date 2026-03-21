<?php
require_once 'config.php';
$user = getCurrentUser();
$conn = getDBConnection();
$currentPage = basename($_SERVER['PHP_SELF']);
$isHomePage = ($currentPage === 'index.php' || $currentPage === '');

$map_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$map_stmt = $conn->prepare("SELECT * FROM maps WHERE id = ?");
$map_stmt->bind_param("i", $map_id);
$map_stmt->execute();
$map_info = $map_stmt->get_result()->fetch_assoc();

if (!$map_info) die("Map not found.");

$derivedFileName = strtolower(str_replace(' ', '_', $map_info['name'])) . '.jpg';
$derivedPath = 'uploads/maps/' . $derivedFileName;

if (file_exists($derivedPath)) {
    $bgImage = $derivedPath;
} elseif (!empty($map_info['bg'])) {
    $bgImage = htmlspecialchars(strpos($map_info['bg'], 'uploads/maps/') !== false ? $map_info['bg'] : 'uploads/maps/' . basename($map_info['bg']));
} else {
    $bgImage = 'uploads/maps/default-map.jpg'; 
}

$char_sql = "SELECT c.name, cl.name as class_name, c.ally FROM map_characters mc JOIN characters c ON mc.character_id = c.character_id JOIN classes cl ON c.class_id = cl.class_id WHERE mc.map_id = ?";
$char_stmt = $conn->prepare($char_sql);
$char_stmt->bind_param("i", $map_id);
$char_stmt->execute();
$chars_result = $char_stmt->get_result();

$score_sql = "SELECT s.turns, s.date, u.username, u.profile_picture FROM scores s JOIN users u ON s.user_id = u.id WHERE s.map_id = ? ORDER BY s.turns ASC, s.date DESC LIMIT 50";
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

    <div class="map-leaderboard-container">
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
                    <tr><th width="10%">Rank</th><th width="50%">Player</th><th width="20%">Turns</th><th width="20%">Date Achieved</th></tr>
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
                        <td style="font-family: monospace; font-size: 1.1rem; color: #fbbf24;"><?php echo htmlspecialchars($row['turns']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['date'])); ?></td>
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