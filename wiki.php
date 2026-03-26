<?php
require_once 'config.php';
$user = getCurrentUser();
$conn = getDBConnection();

// Simple BBCode Parser
function parseBBCode($text) {
    // 1. Secure the text first
    $text = htmlspecialchars($text);
    
    // 2. Apply line breaks
    $text = nl2br($text);
    
    // 3. Define BBCode rules (Regex to HTML)
    $bbcode = [
        '/\[b\](.*?)\[\/b\]/is' => '<strong>$1</strong>',
        '/\[i\](.*?)\[\/i\]/is' => '<em>$1</em>',
        '/\[u\](.*?)\[\/u\]/is' => '<u>$1</u>',
        '/\[h3\](.*?)\[\/h3\]/is' => '<h3 style="color:#fbbf24; margin-top:1rem;">$1</h3>',
        '/\[color=(.*?)\](.*?)\[\/color\]/is' => '<span style="color:$1;">$2</span>',
        '/\[url=(.*?)\](.*?)\[\/url\]/is' => '<a href="$1" target="_blank" style="color:#fbbf24; text-decoration:underline;">$2</a>',
        '/\[img\](.*?)\[\/img\]/is' => '<img src="$1" style="max-width:100%; border-radius:0.5rem; margin:1rem 0;">'
    ];
    
    // 4. Run the replacement
    return preg_replace(array_keys($bbcode), array_values($bbcode), $text);
}

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
    <style>
        /* --- Responsive Wiki Card Layouts --- */
        
        .wiki-card h2 {
            border-bottom: none !important;
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
            display: block !important;
        }

        .wiki-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #334155;
        }
        
        .wiki-card-header .title-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .wiki-card-header .action-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Desktop Table Actions */
        .table-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            white-space: nowrap;
        }

        /* Mobile View Rules - Triggered for ALL cards & tables simultaneously at 768px */
        @media (max-width: 768px) {
            
            /* General Card Header Adjustments */
            .wiki-card-header {
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                width: 100%;
                gap: 2rem;
            }
            .wiki-card-header .title-group {
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                width: 100%;
                gap: 1rem;
            }
            .wiki-card-header .action-group {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: 0.5rem;
            }
            .wiki-card-header .action-group > a.btn,
            .wiki-card-header .action-group > form {
                width: 100%;
                margin: 0;
                display: block;
            }
            .wiki-card-header .action-group form .btn {
                width: 100%;
                margin: 0;
                display: flex;
                justify-content: center;
            }
            
            /* UPDATED: Characters Tab Side-by-Side Mobile Layout */
            .char-flex {
                flex-direction: row !important; /* Force side-by-side on mobile */
                align-items: flex-start !important;
                text-align: left;
                flex-wrap: nowrap !important; /* Stop wrapping to enforce side-by-side */
                gap: 0.5rem !important; /* Very tight gap on mobile */
                margin-bottom: 0.25rem !important; /* Very tight space before table */
            }
            
            /* Scale down portrait slightly on mobile to make room for text */
            .char-flex img.char-img {
                flex-shrink: 0;
                width: 80px !important;
                height: 80px !important;
            }
            
            .char-flex .wiki-content {
                width: auto;
                flex: 1 1 auto;
                min-width: 0; /* Prevent text overflow breaking layout */
            }

            /* --- Characters Tab: 2-Column Stat Table Mobile Layout --- */
            .char-stat-table thead {
                display: none;
            }
            .char-stat-table,
            .char-stat-table tbody,
            .char-stat-table tr {
                display: block;
                width: 100%;
            }
            .char-stat-table tr {
                border-radius: 0.5rem;
                overflow: hidden;
                border: 1px solid #334155;
                background-color: rgba(0, 0, 0, 0.2);
            }
            .char-stat-table td {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                border: none !important;
                border-bottom: 1px solid #334155 !important;
                border-radius: 0 !important;
                background-color: transparent !important;
                padding: 0.75rem 1rem !important;
                text-align: right !important;
            }
            .char-stat-table td:last-child {
                border-bottom: none !important;
            }
            .char-stat-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #fbbf24;
                text-transform: uppercase;
                font-size: 0.85rem;
                text-align: left;
            }

            /* --- Table to Card Layout for Mobile (Classes & Weapons) --- */
            .table-cards-mobile, 
            .table-cards-mobile tbody, 
            .table-cards-mobile tr, 
            .table-cards-mobile td {
                display: block;
                width: 100%;
                min-width: 0 !important; /* Overrides the 600px/800px inline styles */
            }
            
            .table-cards-mobile thead {
                display: none; /* Hide standard headers */
            }
            
            .table-cards-mobile tr {
                background-color: rgba(0, 0, 0, 0.2);
                border: 1px solid #334155;
                border-radius: 0.5rem;
                margin-bottom: 1.5rem;
                padding: 0.5rem;
            }
            
            .table-cards-mobile td {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                border-bottom: 1px solid rgba(255,255,255,0.05);
                padding: 0.75rem;
                white-space: normal !important; /* Allows text to wrap */
                text-align: left;
            }
            
            .table-cards-mobile td:last-child {
                border-bottom: none;
            }
            
            /* Injects the column label before the data natively */
            .table-cards-mobile td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #fbbf24;
                text-transform: uppercase;
                font-size: 0.75rem;
                margin-bottom: 0.25rem;
                display: block;
                width: 100%;
            }
            
            /* Admin Buttons inside the mobile card */
            .table-cards-mobile td.admin-actions-cell::before {
                display: none; /* Hides "Admin" text before buttons */
            }
            .admin-actions-cell .table-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
                width: 100%;
                margin-top: 0.5rem;
            }
            .admin-actions-cell .table-actions > a.btn, 
            .admin-actions-cell .table-actions > form {
                width: 100%;
                display: block;
                margin: 0;
            }
            .admin-actions-cell .table-actions > form button {
                width: 100%;
                display: flex;
                justify-content: center;
            }
        }
    </style>
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
                <?php if (!$user): ?>
                    <a href="login.php" class="btn btn-outline" style="padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important;">Login</a>
                <?php endif; ?>
                
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
                        
                        <div class="wiki-card-header">
                            <div class="title-group">
                                <h2><?php echo htmlspecialchars($wiki['title']); ?></h2>
                            </div>
                            
                            <?php if ($isAdmin): ?>
                                <div class="action-group">
                                    <a href="admin.php?tab=articles&edit=<?php echo $wiki['id']; ?>" class="btn">Edit</a>
                                    <form method="POST" action="admin.php" onsubmit="return confirm('Delete this article?');" style="margin:0;">
                                        <input type="hidden" name="action" value="delete_record">
                                        <input type="hidden" name="table" value="wiki_entries">
                                        <input type="hidden" name="id_col" value="id">
                                        <input type="hidden" name="id_val" value="<?php echo $wiki['id']; ?>">
                                        <input type="hidden" name="return_to" value="wiki.php?tab=articles">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($wiki['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($wiki['image_url']); ?>" alt="Article Image" class="wiki-img-large">
                        <?php endif; ?>
                        
                        <div class="wiki-content"><?php echo parseBBCode($wiki['content']); ?></div>
                        
                        <div class="wiki-meta"><?php echo date('M j, Y', strtotime($wiki['created_at'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <?php elseif ($activeTab === 'characters'): ?>
    <?php if ($wiki_data && $wiki_data->num_rows > 0): ?>
        <?php while($char = $wiki_data->fetch_assoc()): ?>
            <div class="wiki-card character-card">
                <div class="wiki-card-header">
                    <div class="title-group">
                        <h2><?php echo htmlspecialchars($char['name']); ?></h2>
                        <span class="badge <?php echo $char['ally'] ? 'badge-ally' : 'badge-enemy'; ?>">
                            <?php echo $char['ally'] ? 'Player Ally' : 'Enemy Unit'; ?>
                        </span>
                    </div>

                    <?php if ($isAdmin): ?>
                        <div class="action-group">
                            <a href="admin.php?tab=characters&edit=<?php echo $char['character_id']; ?>" class="btn">Edit</a>
                            <form method="POST" action="admin.php" onsubmit="return confirm('Delete character?');" style="margin:0;">
                                <input type="hidden" name="action" value="delete_record">
                                <input type="hidden" name="table" value="characters">
                                <input type="hidden" name="id_col" value="character_id">
                                <input type="hidden" name="id_val" value="<?php echo $char['character_id']; ?>">
                                <input type="hidden" name="return_to" value="wiki.php?tab=characters">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="char-layout-horizontal">
                    <?php if (!empty($char['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($char['image_url']); ?>" alt="Portrait" class="char-img-side">
                    <?php endif; ?>
                    
                    <div class="char-text-side">
                        <div class="char-class-line">
                            <strong>Class:</strong> 
                            <?php
                                $cName = strtolower($char['class_name']);
                                $cIcon = '';
                                if (strpos($cName, 'archer') !== false) $cIcon = 'uploads/classes/archer-icon.png';
                                elseif (strpos($cName, 'fighter') !== false) $cIcon = 'uploads/classes/fighter-icon.png';
                                elseif (strpos($cName, 'knight') !== false) $cIcon = 'uploads/classes/knight-icon.png';
                                elseif (strpos($cName, 'mage') !== false) $cIcon = 'uploads/classes/mage-icon.png';
                                elseif (strpos($cName, 'thief') !== false) $cIcon = 'uploads/classes/thief-icon.png';
                            ?>
                            <?php if ($cIcon): ?>
                                <img src="<?php echo $cIcon; ?>" class="class-inline-icon" alt="Class Icon">
                            <?php endif; ?>
                            <span style="color: #fbbf24;"><?php echo htmlspecialchars($char['class_name']); ?></span>
                        </div>
                        <p class="char-description-text">
                            <?php echo htmlspecialchars($char['description']); ?>
                        </p>
                    </div>
                </div>
                
                <div class="char-table-container">
                    <table class="data-table char-stat-table">
                        <thead>
                            <tr><th>HP</th><th>Str</th><th>Dex</th><th>Skill</th><th>Def</th><th>Luck</th><th>Move</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="HP"><?php echo $char['base_hp']; ?></td>
                                <td data-label="Str"><?php echo $char['base_str']; ?></td>
                                <td data-label="Dex"><?php echo $char['base_dex']; ?></td>
                                <td data-label="Skill"><?php echo $char['base_skill']; ?></td>
                                <td data-label="Def"><?php echo $char['base_def']; ?></td>
                                <td data-label="Luck"><?php echo $char['base_luck']; ?></td>
                                <td data-label="Move"><?php echo $char['base_move']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

        <?php elseif ($activeTab === 'classes'): ?>
            <?php if ($wiki_data && $wiki_data->num_rows > 0): ?>
                <div class="wiki-card" style="padding: 0; overflow: hidden;">
                    <div style="overflow-x: auto; width: 100%;">
                        <table class="data-table table-cards-mobile" style="margin-top: 0; min-width: 600px;">
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
                                        <td data-label="Icon">
                                            <?php 
                                                $cName = strtolower($class['name']);
                                                $cIcon = '';
                                                if (strpos($cName, 'archer') !== false) $cIcon = 'uploads/classes/archer-icon.png';
                                                elseif (strpos($cName, 'fighter') !== false) $cIcon = 'uploads/classes/fighter-icon.png';
                                                elseif (strpos($cName, 'knight') !== false) $cIcon = 'uploads/classes/knight-icon.png';
                                                elseif (strpos($cName, 'mage') !== false) $cIcon = 'uploads/classes/mage-icon.png';
                                                elseif (strpos($cName, 'thief') !== false) $cIcon = 'uploads/classes/thief-icon.png';
                                                else $cIcon = !empty($class['image_url']) ? $class['image_url'] : '';
                                            ?>
                                            <?php if (!empty($cIcon)): ?>
                                                <img src="<?php echo htmlspecialchars($cIcon); ?>" class="icon-img" alt="<?php echo htmlspecialchars($class['name']); ?> Icon">
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Class Name" style="color: #fbbf24; font-weight: bold;"><?php echo htmlspecialchars($class['name']); ?></td>
                                        <td data-label="Description" style="color: #cbd5e1;"><?php echo htmlspecialchars($class['description']); ?></td>
                                        <td data-label="Usable Weapons" style="color: #94a3b8; font-style: italic;">
                                            <?php echo !empty($class['usable_weapons']) ? htmlspecialchars($class['usable_weapons']) : 'None specific'; ?>
                                        </td>
                                        
                                        <?php if ($isAdmin): ?>
                                        <td data-label="Admin" class="admin-actions-cell" style="white-space: nowrap;">
                                            <div class="table-actions">
                                                <a href="admin.php?tab=classes&edit=<?php echo $class['class_id']; ?>" class="btn">Edit</a>
                                                <form method="POST" action="admin.php" onsubmit="return confirm('Delete class?');" style="margin: 0; display: inline-block;">
                                                    <input type="hidden" name="action" value="delete_record">
                                                    <input type="hidden" name="table" value="classes">
                                                    <input type="hidden" name="id_col" value="class_id">
                                                    <input type="hidden" name="id_val" value="<?php echo $class['class_id']; ?>">
                                                    <input type="hidden" name="return_to" value="wiki.php?tab=classes">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($activeTab === 'weapons'): ?>
            <?php if ($wiki_data && $wiki_data->num_rows > 0): ?>
                <div class="wiki-card" style="padding: 0; overflow: hidden;">
                    <div style="overflow-x: auto; width: 100%;">
                        <table class="data-table table-cards-mobile" style="margin-top: 0; min-width: 800px;">
                            <thead>
                                <tr>
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
                                        <td data-label="Weapon">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #fbbf24; font-weight: bold;">
                                                <?php if (!empty($weapon['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($weapon['image_url']); ?>" class="class-inline-icon" alt="Icon">
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($weapon['name']); ?></span>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;"><?php echo htmlspecialchars($weapon['description']); ?></div>
                                        </td>
                                        <td data-label="Type"><span class="badge badge-stat"><?php echo htmlspecialchars($weapon['weapon_type']); ?></span></td>
                                        <td data-label="Atk" style="font-weight: bold; color: #ef4444;"><?php echo $weapon['atk']; ?></td>
                                        <td data-label="Hit%" style="color: #4ade80;"><?php echo $weapon['hit_rate']; ?></td>
                                        <td data-label="Crit%" style="color: #a855f7;"><?php echo $weapon['crit_rate']; ?></td>
                                        <td data-label="Range" style="color: #cbd5e1;"><?php echo $weapon['min_range'] === $weapon['max_range'] ? $weapon['min_range'] : $weapon['min_range'] . '-' . $weapon['max_range']; ?></td>
                                        <td data-label="Durability" style="color: #94a3b8;"><?php echo $weapon['durability']; ?></td>
                                        
                                        <?php if ($isAdmin): ?>
                                        <td data-label="Admin" class="admin-actions-cell" style="white-space: nowrap;">
                                            <div class="table-actions">
                                                <a href="admin.php?tab=weapons&edit=<?php echo $weapon['weapon_id']; ?>" class="btn">Edit</a>
                                                <form method="POST" action="admin.php" onsubmit="return confirm('Delete weapon?');" style="margin: 0; display: inline-block;">
                                                    <input type="hidden" name="action" value="delete_record">
                                                    <input type="hidden" name="table" value="weapons">
                                                    <input type="hidden" name="id_col" value="weapon_id">
                                                    <input type="hidden" name="id_val" value="<?php echo $weapon['weapon_id']; ?>">
                                                    <input type="hidden" name="return_to" value="wiki.php?tab=weapons">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
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