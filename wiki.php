<?php
require_once 'config.php';
$user = getCurrentUser();
$conn = getDBConnection();
$currentPage = basename($_SERVER['PHP_SELF']);
$isHomePage = ($currentPage === 'index.php' || $currentPage === '');

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'articles';
$allowedTabs = ['articles', 'characters', 'classes', 'weapons'];
if (!in_array($activeTab, $allowedTabs)) $activeTab = 'articles';

$wiki_data = null;
if ($activeTab === 'articles') $wiki_data = $conn->query("SELECT * FROM wiki_entries ORDER BY created_at DESC");
elseif ($activeTab === 'characters') $wiki_data = $conn->query("SELECT ch.*, cl.name as class_name FROM characters ch JOIN classes cl ON ch.class_id = cl.class_id ORDER BY ch.ally DESC, ch.character_id ASC");
elseif ($activeTab === 'classes') $wiki_data = $conn->query("SELECT c.*, GROUP_CONCAT(w.name SEPARATOR ', ') as usable_weapons FROM classes c LEFT JOIN class_weapons cw ON c.class_id = cw.class_id LEFT JOIN weapons w ON cw.weapon_id = w.weapon_id GROUP BY c.class_id");
elseif ($activeTab === 'weapons') $wiki_data = $conn->query("SELECT * FROM weapons ORDER BY weapon_type, name");

$isAdmin = ($user && isset($user['admin']) && $user['admin'] == 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Wiki - Treasure Quest</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="wiki-page">
    <nav class="nav">
        <div class="nav-container">
            <div class="nav-links">
                <?php if (!$isHomePage): ?><a href="index.php#home">Home</a><?php endif; ?>
                <a href="index.php#about">About</a>
                <a href="index.php#features">Features</a>
                <a href="index.php#gallery">Gallery</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="wiki.php" class="active">Wiki</a>
                
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
                            <?php if ($isAdmin): ?>
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
                            <?php if ($isAdmin): ?>
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
                <?php if ($isAdmin): ?>
                    <a href="admin.php" style="color: #fbbf24;">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-block">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="wiki-container">
        <div class="page-header">
            <h1>The Grand Wiki</h1>
            <p>Lore, guides, and raw statistics compiled by the Realm Masters.</p>
        </div>

        <div class="wiki-tabs">
            <a href="wiki.php?tab=articles" class="wiki-tab <?php echo $activeTab === 'articles' ? 'active' : ''; ?>">Guides</a>
            <a href="wiki.php?tab=characters" class="wiki-tab <?php echo $activeTab === 'characters' ? 'active' : ''; ?>">Characters</a>
            <a href="wiki.php?tab=classes" class="wiki-tab <?php echo $activeTab === 'classes' ? 'active' : ''; ?>">Classes</a>
            <a href="wiki.php?tab=weapons" class="wiki-tab <?php echo $activeTab === 'weapons' ? 'active' : ''; ?>">Weapons & Items</a>
        </div>

        <?php if ($activeTab === 'articles'): ?>
            <?php if ($wiki_data && $wiki_data->num_rows > 0): ?>
                <?php while($wiki = $wiki_data->fetch_assoc()): ?>
                    <div class="wiki-card">
                        <h2>
                            <?php echo htmlspecialchars($wiki['title']); ?>
                            
                            <?php if ($isAdmin): ?>
                                <span class="admin-controls">
                                    <a href="admin.php?tab=articles&edit=<?php echo $wiki['id']; ?>" class="badge badge-stat">✏️ Edit</a>
                                    <form method="POST" action="admin.php" onsubmit="return confirm('Delete this article?');" style="margin:0;">
                                        <input type="hidden" name="action" value="delete_record">
                                        <input type="hidden" name="table" value="wiki_entries">
                                        <input type="hidden" name="id_col" value="id">
                                        <input type="hidden" name="id_val" value="<?php echo $wiki['id']; ?>">
                                        <input type="hidden" name="return_to" value="wiki.php?tab=articles">
                                        <button type="submit" class="badge badge-danger">🗑️</button>
                                    </form>
                                </span>
                            <?php endif; ?>
                        </h2>
                        
                        <?php if (!empty($wiki['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($wiki['image_url']); ?>" alt="Article Image" class="wiki-img-large">
                        <?php endif; ?>
                        
                        <div class="wiki-content"><?php echo nl2br(htmlspecialchars($wiki['content'])); ?></div>
                        <div class="wiki-meta">Posted: <?php echo date('M j, Y', strtotime($wiki['created_at'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

        <?php elseif ($activeTab === 'characters'): ?>
            <?php if ($wiki_data && $wiki_data->num_rows > 0): ?>
                <?php while($char = $wiki_data->fetch_assoc()): ?>
                    <div class="wiki-card">
                        <h2>
                            <span>
                                <?php echo htmlspecialchars($char['name']); ?>
                                <span class="badge <?php echo $char['ally'] ? 'badge-ally' : 'badge-enemy'; ?>">
                                    <?php echo $char['ally'] ? 'Player Ally' : 'Enemy Unit'; ?>
                                </span>
                            </span>

                            <?php if ($isAdmin): ?>
                                <span class="admin-controls">
                                    <a href="admin.php?tab=characters&edit=<?php echo $char['character_id']; ?>" class="badge badge-stat">✏️ Edit</a>
                                    <form method="POST" action="admin.php" onsubmit="return confirm('Delete character?');" style="margin:0;">
                                        <input type="hidden" name="action" value="delete_record">
                                        <input type="hidden" name="table" value="characters">
                                        <input type="hidden" name="id_col" value="character_id">
                                        <input type="hidden" name="id_val" value="<?php echo $char['character_id']; ?>">
                                        <input type="hidden" name="return_to" value="wiki.php?tab=characters">
                                        <button type="submit" class="badge badge-danger">🗑️</button>
                                    </form>
                                </span>
                            <?php endif; ?>
                        </h2>
                        
                        <div class="char-flex">
                            <?php if (!empty($char['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($char['image_url']); ?>" alt="Portrait" class="char-img">
                            <?php endif; ?>
                            <div class="wiki-content" style="color: #94a3b8; flex: 1;">
                                <strong>Class:</strong> <span style="color: #fbbf24;"><?php echo htmlspecialchars($char['class_name']); ?></span><br><br>
                                <?php echo htmlspecialchars($char['description']); ?>
                            </div>
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr><th>HP</th><th>Str</th><th>Dex</th><th>Skill</th><th>Def</th><th>Luck</th><th>Move</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="badge-stat"><?php echo $char['base_hp']; ?></td>
                                    <td class="badge-stat"><?php echo $char['base_str']; ?></td>
                                    <td class="badge-stat"><?php echo $char['base_dex']; ?></td>
                                    <td class="badge-stat"><?php echo $char['base_skill']; ?></td>
                                    <td class="badge-stat"><?php echo $char['base_def']; ?></td>
                                    <td class="badge-stat"><?php echo $char['base_luck']; ?></td>
                                    <td class="badge-stat"><?php echo $char['base_move']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

        <?php elseif ($activeTab === 'classes'): ?>
            <?php if ($wiki_data && $wiki_data->num_rows > 0): ?>
                <div class="wiki-card" style="padding: 0; overflow: hidden;">
                    <table class="data-table" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th width="60">Icon</th>
                                <th>Class Name</th>
                                <th>Description</th>
                                <th>Usable Weapons</th>
                                <?php if($isAdmin) echo '<th>Admin</th>'; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($class = $wiki_data->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($class['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($class['image_url']); ?>" class="icon-img">
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #fbbf24; font-weight: bold;"><?php echo htmlspecialchars($class['name']); ?></td>
                                    <td style="color: #cbd5e1;"><?php echo htmlspecialchars($class['description']); ?></td>
                                    <td style="color: #94a3b8; font-style: italic;">
                                        <?php echo !empty($class['usable_weapons']) ? htmlspecialchars($class['usable_weapons']) : 'None specific'; ?>
                                    </td>
                                    
                                    <?php if ($isAdmin): ?>
                                    <td style="white-space:nowrap;">
                                        <a href="admin.php?tab=classes&edit=<?php echo $class['class_id']; ?>" class="badge badge-stat">✏️</a>
                                        <form method="POST" action="admin.php" onsubmit="return confirm('Delete class?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_record">
                                            <input type="hidden" name="table" value="classes">
                                            <input type="hidden" name="id_col" value="class_id">
                                            <input type="hidden" name="id_val" value="<?php echo $class['class_id']; ?>">
                                            <input type="hidden" name="return_to" value="wiki.php?tab=classes">
                                            <button type="submit" class="badge badge-danger">🗑️</button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php elseif ($activeTab === 'weapons'): ?>
            <?php if ($wiki_data && $wiki_data->num_rows > 0): ?>
                <div class="wiki-card" style="padding: 0; overflow: auto;">
                    <table class="data-table" style="margin-top: 0; min-width: 800px;">
                        <thead>
                            <tr>
                                <th width="60">Icon</th>
                                <th>Weapon</th>
                                <th>Type</th>
                                <th>Atk</th>
                                <th>Hit%</th>
                                <th>Crit%</th>
                                <th>Range</th>
                                <th>Dur</th>
                                <?php if($isAdmin) echo '<th>Admin</th>'; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($weapon = $wiki_data->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($weapon['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($weapon['image_url']); ?>" class="icon-img">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="color: #fbbf24; font-weight: bold;"><?php echo htmlspecialchars($weapon['name']); ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($weapon['description']); ?></div>
                                    </td>
                                    <td><span class="badge badge-stat"><?php echo htmlspecialchars($weapon['weapon_type']); ?></span></td>
                                    <td style="font-weight: bold; color: #ef4444;"><?php echo $weapon['atk']; ?></td>
                                    <td style="color: #4ade80;"><?php echo $weapon['hit_rate']; ?></td>
                                    <td style="color: #a855f7;"><?php echo $weapon['crit_rate']; ?></td>
                                    <td style="color: #cbd5e1;"><?php echo $weapon['min_range'] === $weapon['max_range'] ? $weapon['min_range'] : $weapon['min_range'] . '-' . $weapon['max_range']; ?></td>
                                    <td style="color: #94a3b8;"><?php echo $weapon['durability']; ?></td>
                                    
                                    <?php if ($isAdmin): ?>
                                    <td style="white-space:nowrap;">
                                        <a href="admin.php?tab=weapons&edit=<?php echo $weapon['weapon_id']; ?>" class="badge badge-stat">✏️</a>
                                        <form method="POST" action="admin.php" onsubmit="return confirm('Delete weapon?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_record">
                                            <input type="hidden" name="table" value="weapons">
                                            <input type="hidden" name="id_col" value="weapon_id">
                                            <input type="hidden" name="id_val" value="<?php echo $weapon['weapon_id']; ?>">
                                            <input type="hidden" name="return_to" value="wiki.php?tab=weapons">
                                            <button type="submit" class="badge badge-danger">🗑️</button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

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