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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background-color: #0f172a; color: #cbd5e1; line-height: 1.6; }
        h1, h2, h3 { font-family: 'Press Start 2P', system-ui, sans-serif; line-height: 1.4; color: #fbbf24; }
        
        .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background-color: #0f172a; border-bottom: 1px solid #334155; }
        .nav-container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; display: flex; align-items: center; justify-content: center; height: 64px; position: relative; }
        .logo { position: absolute; left: 1rem; display: flex; align-items: center; height: 100%; text-decoration: none; }
        .logo h2 { color: #fbbf24; font-size: 1.2rem; margin: 0; white-space: nowrap; }
        .logo a { color: inherit; text-decoration: none; }
        .nav-links { display: none; gap: 2rem; align-items: center; }
        .nav-links a { color: #e2e8f0; text-decoration: none; transition: color 0.3s; font-family: 'Press Start 2P', sans-serif; font-size: 1rem; }
        .nav-links a:hover, .nav-links a.active { color: #fbbf24; }
        .btn { background-color: #fbbf24; color: #0f172a; border: none; padding: 0.5rem 1.5rem; border-radius: 0.375rem; cursor: pointer; font-weight: bold; text-decoration: none; }
        .btn-outline { background-color: transparent; color: #fbbf24; border: 1px solid #fbbf24; }
        
        .user-menu { position: relative; display: flex; align-items: center; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #fbbf24, #f59e0b); display: flex; align-items: center; justify-content: center; color: #0f172a; font-weight: bold; cursor: pointer; border: 2px solid #fbbf24; position: relative; z-index: 100; }
        .user-dropdown { display: none; position: absolute; top: 50px; right: 0; background-color: #1e293b; border: 1px solid #334155; border-radius: 0.5rem; min-width: 200px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); z-index: 1001; }
        .user-dropdown.active { display: block; }
        .user-dropdown a { display: block; padding: 0.75rem 1rem; color: #e2e8f0; text-decoration: none; }
        .user-dropdown a:hover { background-color: #334155; }
        .user-info { padding: 0.75rem 1rem; border-bottom: 1px solid #334155; color: #94a3b8; }
        .user-info strong { display: block; color: #fbbf24; }

        .mobile-menu-btn { display: block; background: none; border: none; color: #e2e8f0; position: absolute; right: 1rem;}
        .mobile-menu { display: none; background-color: #1e293b; padding: 1rem; }
        .mobile-menu.active { display: block; }
        .mobile-menu a { display: block; color: #e2e8f0; text-decoration: none; padding: 0.75rem; font-family: 'Press Start 2P', sans-serif; font-size: 0.7rem;}
        @media (min-width: 1025px) { .nav-links { display: flex; } .mobile-menu-btn { display: none; } }

        .container { max-width: 1000px; margin: 100px auto 4rem; padding: 0 1rem; }
        .page-header { text-align: center; margin-bottom: 2rem; }
        .page-header p { color: #94a3b8; margin-top: 1rem; }

        .wiki-tabs { display: flex; justify-content: center; gap: 1rem; margin-bottom: 3rem; border-bottom: 2px solid #334155; padding-bottom: 1rem; flex-wrap: wrap;}
        .wiki-tab { padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; font-weight: bold; border-radius: 0.5rem; transition: all 0.2s; }
        .wiki-tab:hover { background-color: rgba(251, 191, 36, 0.1); color: #fbbf24; }
        .wiki-tab.active { background-color: #fbbf24; color: #0f172a; }

        .wiki-card { background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .wiki-card h2 { font-size: 1.2rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center;}
        .wiki-content { color: #cbd5e1; font-size: 1.05rem; white-space: pre-wrap; }
        .wiki-meta { font-size: 0.85rem; color: #64748b; margin-top: 1.5rem; text-align: right; border-top: 1px dashed #334155; padding-top: 0.5rem;}
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .data-table th, .data-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #334155; vertical-align: middle; }
        .data-table th { color: #fbbf24; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .data-table tr:hover { background-color: rgba(255,255,255,0.05); }

        .badge { padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; font-weight: bold; }
        .badge-ally { background-color: rgba(34,197,94,0.2); color: #4ade80; border: 1px solid #4ade80;}
        .badge-enemy { background-color: rgba(239,68,68,0.2); color: #f87171; border: 1px solid #f87171;}
        .badge-stat { background-color: #0f172a; color: #cbd5e1; border: 1px solid #334155; text-align: center; text-decoration: none;}
        .badge-danger { background-color: rgba(239,68,68,0.2); color: #f87171; border: 1px solid #f87171; cursor: pointer;}
        
        .wiki-img-large { max-width: 100%; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid #334155; }
        .char-flex { display: flex; gap: 1.5rem; align-items: flex-start; margin-bottom: 1.5rem; }
        .char-img { width: 120px; height: 120px; object-fit: cover; border-radius: 0.5rem; border: 2px solid #334155; background-color: #0f172a; }
        .icon-img { width: 40px; height: 40px; object-fit: contain; border-radius: 0.25rem; background-color: #0f172a; padding: 2px; border: 1px solid #334155; }
        
        /* Inline Admin Buttons CSS */
        .admin-controls { display: flex; gap: 0.5rem; float: right; align-items: center;}
    </style>
</head>
<body>
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
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
                            <?php if ($isAdmin): ?>
                                <a href="admin.php" style="color: #fbbf24; border-top: 1px solid #334155;">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline" style="margin-left: 1rem;">Login</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <?php if (!$isHomePage): ?><a href="index.php#home">Home</a><?php endif; ?>
            <a href="index.php#about">About</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="wiki.php">Wiki</a>
            <?php if ($user): ?>
                <a href="profile.php">Profile</a>
                <?php if ($isAdmin): ?><a href="admin.php" style="color: #fbbf24;">Admin Dashboard</a><?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
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
        document.addEventListener('click', function(e) {
            const d = document.getElementById('userDropdown'), u = document.querySelector('.user-menu');
            if (d && u && !u.contains(e.target)) d.classList.remove('active');
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>