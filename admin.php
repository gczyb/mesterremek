<?php
require_once 'config.php';

// Security Check: Must be logged in AND be an admin
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
$currentPage = basename($_SERVER['PHP_SELF']);
$isHomePage = ($currentPage === 'index.php' || $currentPage === '');

// Helper function for image uploads
function handleAdminUpload($file, $folder) {
    if (isset($file) && $file['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $dir = 'uploads/' . $folder . '/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $path = $dir . uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $path)) {
                return $path;
            }
        }
    }
    return null;
}

// ---------------------------------------------------------
// POST HANDLERS (CRUD Operations)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // GENERIC DELETE (Handles all tables securely)
    if ($action === 'delete_record') {
        $table = $_POST['table'];
        $id_col = $_POST['id_col'];
        $id_val = (int)$_POST['id_val'];
        $allowed_tables = ['wiki_entries', 'characters', 'classes', 'weapons', 'users'];
        
        if (in_array($table, $allowed_tables)) {
            
            // --- NEW CODE START: DELETE ASSOCIATED WIKI IMAGE ---
            if ($table === 'wiki_entries') {
                // Get the image path from the database before deleting the record
                $stmt_img = $conn->prepare("SELECT image_url FROM wiki_entries WHERE id = ?");
                $stmt_img->bind_param("i", $id_val);
                $stmt_img->execute();
                $result_img = $stmt_img->get_result();
                if ($row_img = $result_img->fetch_assoc()) {
                    $image_path = $row_img['image_url'];
                    if (!empty($image_path)) {
                        // Construct the full server path
                        // This assumes admin.php is in the project root, same as wiki.php.
                        // If admin.php is in an 'admin' folder, use: __DIR__ . '/../' . $image_path
                        $full_path = __DIR__ . '/' . $image_path; 

                        // Check if file exists and delete it
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                }
                $stmt_img->close();
            }
            // --- NEW CODE END ---

            $stmt = $conn->prepare("DELETE FROM $table WHERE $id_col = ?");
            $stmt->bind_param("i", $id_val);
            $stmt->execute();
            $message = "Record deleted successfully.";
            
            // Redirect back to wiki if requested
            if (!empty($_POST['return_to'])) {
                header("Location: " . $_POST['return_to']);
                exit;
            }
        }
    }

    // TOGGLE ADMIN PRIVILEGES
    elseif ($action === 'toggle_admin') {
        $target_id = (int)$_POST['user_id'];
        $new_status = (int)$_POST['admin_status'];
        
        // Safeguard: Prevent the active admin from demoting themselves
        if ($target_id === $user['id'] && $new_status === 0) {
            $error = "You cannot revoke your own admin privileges.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET admin = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_status, $target_id);
            $stmt->execute();
            $message = "User admin privileges updated successfully.";
        }
    }

    // SAVE ARTICLE
    elseif ($action === 'save_article') {
        $id = (int)$_POST['article_id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $img = handleAdminUpload($_FILES['image'] ?? null, 'wiki');

        if ($id > 0) {
            if ($img) {
                $stmt = $conn->prepare("UPDATE wiki_entries SET title=?, content=?, image_url=? WHERE id=?");
                $stmt->bind_param("sssi", $title, $content, $img, $id);
            } else {
                $stmt = $conn->prepare("UPDATE wiki_entries SET title=?, content=? WHERE id=?");
                $stmt->bind_param("ssi", $title, $content, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO wiki_entries (title, content, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $content, $img);
        }
        $stmt->execute();
        $message = "Article saved successfully.";
    }

    // SAVE CLASS
    elseif ($action === 'save_class') {
        $id = (int)$_POST['class_id'];
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        $img = handleAdminUpload($_FILES['image'] ?? null, 'classes');

        if ($id > 0) {
            if ($img) {
                $stmt = $conn->prepare("UPDATE classes SET name=?, description=?, image_url=? WHERE class_id=?");
                $stmt->bind_param("sssi", $name, $desc, $img, $id);
            } else {
                $stmt = $conn->prepare("UPDATE classes SET name=?, description=? WHERE class_id=?");
                $stmt->bind_param("ssi", $name, $desc, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO classes (name, description, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $desc, $img);
        }
        $stmt->execute();
        $message = "Class saved.";
    }

    // SAVE WEAPON
    elseif ($action === 'save_weapon') {
        $id = (int)$_POST['weapon_id'];
        $name = $_POST['name']; $type = $_POST['weapon_type']; $atk = (int)$_POST['atk'];
        $hit = (int)$_POST['hit_rate']; $crit = (int)$_POST['crit_rate']; $wt = (int)$_POST['weight'];
        $min_r = (int)$_POST['min_range']; $max_r = (int)$_POST['max_range']; $dur = (int)$_POST['durability'];
        $desc = $_POST['description']; $img = handleAdminUpload($_FILES['image'] ?? null, 'weapons');

        if ($id > 0) {
            if ($img) {
                $stmt = $conn->prepare("UPDATE weapons SET name=?, weapon_type=?, atk=?, hit_rate=?, crit_rate=?, weight=?, min_range=?, max_range=?, durability=?, description=?, image_url=? WHERE weapon_id=?");
                $stmt->bind_param("ssiiiiiiissi", $name, $type, $atk, $hit, $crit, $wt, $min_r, $max_r, $dur, $desc, $img, $id);
            } else {
                $stmt = $conn->prepare("UPDATE weapons SET name=?, weapon_type=?, atk=?, hit_rate=?, crit_rate=?, weight=?, min_range=?, max_range=?, durability=?, description=? WHERE weapon_id=?");
                $stmt->bind_param("ssiiiiiiisi", $name, $type, $atk, $hit, $crit, $wt, $min_r, $max_r, $dur, $desc, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO weapons (name, weapon_type, atk, hit_rate, crit_rate, weight, min_range, max_range, durability, description, image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssiiiiiiiss", $name, $type, $atk, $hit, $crit, $wt, $min_r, $max_r, $dur, $desc, $img);
        }
        $stmt->execute();
        $message = "Weapon saved.";
    }

    // SAVE CHARACTER
    elseif ($action === 'save_character') {
        $id = (int)$_POST['character_id'];
        $name = $_POST['name']; $c_id = (int)$_POST['class_id'];
        $ally = (int)$_POST['ally']; $hp = (int)$_POST['base_hp']; $str = (int)$_POST['base_str'];
        $dex = (int)$_POST['base_dex'];
        $skill = (int)$_POST['base_skill']; $def = (int)$_POST['base_def'];
        $luck = (int)$_POST['base_luck']; $move = (int)$_POST['base_move']; $desc = $_POST['description'];
        $img = handleAdminUpload($_FILES['image'] ?? null, 'characters');

        if ($id > 0) {
            if ($img) {
                $stmt = $conn->prepare("UPDATE characters SET name=?, class_id=?, ally=?, base_hp=?, base_str=?, base_dex=?, base_skill=?, base_def=?, base_luck=?, base_move=?, description=?, image_url=? WHERE character_id=?");
                $stmt->bind_param("siiiiiiiiissi", $name, $c_id, $ally, $hp, $str, $dex, $skill, $def, $luck, $move, $desc, $img, $id);
            } else {
                $stmt = $conn->prepare("UPDATE characters SET name=?, class_id=?, ally=?, base_hp=?, base_str=?, base_dex=?, base_skill=?, base_def=?, base_luck=?, base_move=?, description=? WHERE character_id=?");
                $stmt->bind_param("siiiiiiiiisi", $name, $c_id, $ally, $hp, $str, $dex, $skill, $def, $luck, $move, $desc, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO characters (name, class_id, ally, base_hp, base_str, base_dex, base_skill, base_def, base_luck, base_move, description, image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("siiiiiiiiiss", $name, $c_id, $ally, $hp, $str, $dex, $skill, $def, $luck, $move, $desc, $img);
        }
        $stmt->execute();
        $message = "Character saved.";
    }
}

// Setup Data Fetching for Editor
$tab = $_GET['tab'] ?? 'users';
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    if ($tab === 'articles') $edit_data = $conn->query("SELECT * FROM wiki_entries WHERE id = $edit_id")->fetch_assoc();
    if ($tab === 'classes') $edit_data = $conn->query("SELECT * FROM classes WHERE class_id = $edit_id")->fetch_assoc();
    if ($tab === 'weapons') $edit_data = $conn->query("SELECT * FROM weapons WHERE weapon_id = $edit_id")->fetch_assoc();
    if ($tab === 'characters') $edit_data = $conn->query("SELECT * FROM characters WHERE character_id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Treasure Quest</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="admin-page">
    <nav class="nav">
        <div class="nav-container">
            <div class="nav-links">
                <?php if (!$isHomePage): ?><a href="index.php#home">Home</a><?php endif; ?>
                <a href="index.php#about">About</a>
                <a href="index.php#features">Features</a>
                <a href="index.php#gallery">Gallery</a>
                <a href="leaderboard.php">Leaderboard</a>
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
                            <a href="admin.php" style="color: #fbbf24; border-top: 1px solid #334155;">Admin Dashboard</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
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
                            <a href="admin.php" style="color: #fbbf24; border-top: 1px solid #334155;">Admin Dashboard</a>
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
                <a href="admin.php" style="color: #fbbf24;">Admin Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="admin-container">
        <h1>Admin Panel</h1>

        <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

        <div class="admin-tabs">
            <a href="?tab=articles" class="admin-tab <?php echo $tab=='articles'?'active':'';?>">Articles</a>
            <a href="?tab=users" class="admin-tab <?php echo $tab=='users'?'active':'';?>">Users</a>
            <a href="?tab=characters" class="admin-tab <?php echo $tab=='characters'?'active':'';?>">Characters</a>
            <a href="?tab=classes" class="admin-tab <?php echo $tab=='classes'?'active':'';?>">Classes</a>
            <a href="?tab=weapons" class="admin-tab <?php echo $tab=='weapons'?'active':'';?>">Weapons</a>
        </div>

        <?php if ($tab == 'articles'): ?>
        <div class="card">
            <h3><?php echo $edit_data ? 'Edit Article' : 'New Article'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_article">
                <input type="hidden" name="article_id" value="<?php echo $edit_data['id'] ?? 0; ?>">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Article Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($edit_data['title'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group form-full">
                        <label>Header Image (Optional)</label>
                        <?php if(!empty($edit_data['image_url'])): ?><img src="<?php echo $edit_data['image_url']; ?>" style="height:60px; margin-bottom:0.5rem; border-radius:0.25rem;"><br><?php endif; ?>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    <div class="form-group form-full">
                        <label>Content</label>
                        <textarea name="content" rows="12" placeholder="Write your guide here..." required><?php echo htmlspecialchars($edit_data['content'] ?? ''); ?></textarea>
                        <div style="text-align: right; margin-top: 0.5rem;">
                            <a href="https://www.phpbb.com/community/help/bbcode#f1r0" style="color: #fbbf24; text-decoration: underline; font-size: 0.85rem;">Help</a>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn" style="margin-top: 1rem;">Save Article</button>
            </form>
        </div>

        <?php elseif ($tab == 'users'): 
            $search = $_GET['search'] ?? '';
            
            if ($search !== '') {
                // Prepared statement for secure searching
                $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ?");
                $likeSearch = "%" . $search . "%";
                $stmt->bind_param("ss", $likeSearch, $likeSearch);
                $stmt->execute();
                $users = $stmt->get_result();
            } else {
                $users = $conn->query("SELECT * FROM users");
            }
        ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; margin-bottom: 1.5rem; padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                <h3 style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">Registered Users</h3>
                
                <form method="GET" action="admin.php" style="display: flex; gap: 0.5rem; flex: 1; max-width: 400px; margin: 0;">
                    <input type="hidden" name="tab" value="users">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search username or email..." style="flex: 1; padding: 0.5rem 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #e2e8f0; font-family: -apple-system, sans-serif;">
                    <button type="submit" class="btn" style="padding: 0.5rem 1rem !important;">Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="admin.php?tab=users" class="btn btn-outline" style="padding: 0.5rem 1rem !important; display: flex; align-items: center;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Admin</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($users->num_rows > 0): ?>
                    <?php while($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="badge-stat" data-label="ID"><?php echo $u['id']; ?></td>
                        <td data-label="Username"><?php echo htmlspecialchars($u['username']); ?></td>
                        <td data-label="Email"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td data-label="Admin"><span class="badge <?php echo $u['admin'] ? 'badge-ally' : 'badge-enemy'; ?>"><?php echo $u['admin'] ? 'Yes' : 'No'; ?></span></td>
                        <td data-label="Actions">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="action" value="toggle_admin">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="admin_status" value="<?php echo $u['admin'] ? 0 : 1; ?>">
                                    <button type="submit" class="badge <?php echo $u['admin'] ? 'badge-enemy' : 'badge-ally'; ?>" 
                                            onclick="return confirm('Change admin status for this user?');">
                                        <?php echo $u['admin'] ? 'Revoke Admin' : 'Make Admin'; ?>
                                    </button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Delete user?');" style="margin:0;">
                                    <input type="hidden" name="action" value="delete_record">
                                    <input type="hidden" name="table" value="users">
                                    <input type="hidden" name="id_col" value="id">
                                    <input type="hidden" name="id_val" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="badge badge-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; padding: 2rem;">No users found matching "<?php echo htmlspecialchars($search); ?>"</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($tab == 'classes'): ?>
        <div class="card">
            <h3><?php echo $edit_data ? 'Edit Class' : 'New Class'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_class">
                <input type="hidden" name="class_id" value="<?php echo $edit_data['class_id'] ?? 0; ?>">
                <div class="form-grid">
                    <div class="form-group"><label>Class Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>" required></div>
                    <div class="form-group">
                        <label>Class Icon/Image</label>
                        <?php if(!empty($edit_data['image_url'])): ?><img src="<?php echo $edit_data['image_url']; ?>" style="height:40px; margin-bottom:0.5rem; border-radius:0.25rem;"><br><?php endif; ?>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    <div class="form-group form-full"><label>Description</label><textarea name="description" rows="3"><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea></div>
                </div>
                <button type="submit" class="btn" style="margin-top: 1rem;">Save Class</button>
            </form>
        </div>

        <?php elseif ($tab == 'weapons'): ?>
        <div class="card">
            <h3><?php echo $edit_data ? 'Edit Weapon' : 'New Weapon'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_weapon">
                <input type="hidden" name="weapon_id" value="<?php echo $edit_data['weapon_id'] ?? 0; ?>">
                <div class="form-grid">
                    <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Type (Sword, Bow, etc)</label><input type="text" name="weapon_type" value="<?php echo htmlspecialchars($edit_data['weapon_type'] ?? ''); ?>" required></div>
                    
                    <div class="form-group"><label>Attack (Mt)</label><input type="number" name="atk" value="<?php echo $edit_data['atk'] ?? 0; ?>" required></div>
                    <div class="form-group"><label>Hit Rate %</label><input type="number" name="hit_rate" value="<?php echo $edit_data['hit_rate'] ?? 90; ?>" required></div>
                    <div class="form-group"><label>Crit Rate %</label><input type="number" name="crit_rate" value="<?php echo $edit_data['crit_rate'] ?? 0; ?>" required></div>
                    <div class="form-group"><label>Weight (Wt)</label><input type="number" name="weight" value="<?php echo $edit_data['weight'] ?? 5; ?>" required></div>
                    
                    <div class="form-group"><label>Min Range</label><input type="number" name="min_range" value="<?php echo $edit_data['min_range'] ?? 1; ?>" required></div>
                    <div class="form-group"><label>Max Range</label><input type="number" name="max_range" value="<?php echo $edit_data['max_range'] ?? 1; ?>" required></div>
                    <div class="form-group"><label>Durability</label><input type="number" name="durability" value="<?php echo $edit_data['durability'] ?? 30; ?>" required></div>
                    
                    <div class="form-group">
                        <label>Icon Image</label>
                        <?php if(!empty($edit_data['image_url'])): ?><img src="<?php echo $edit_data['image_url']; ?>" style="height:40px;"><br><?php endif; ?>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    
                    <div class="form-group form-full"><label>Description</label><textarea name="description" rows="2"><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea></div>
                </div>
                <button type="submit" class="btn" style="margin-top: 1rem;">Save Weapon</button>
            </form>
        </div>

        <?php elseif ($tab == 'characters'): 
            $classes = $conn->query("SELECT class_id, name FROM classes");
        ?>
        <div class="card">
            <h3><?php echo $edit_data ? 'Edit Character' : 'New Character'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_character">
                <input type="hidden" name="character_id" value="<?php echo $edit_data['character_id'] ?? 0; ?>">
                <div class="form-grid">
                    <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>" required></div>
                    <div class="form-group">
                        <label>Class</label>
                        <select name="class_id">
                            <?php while($c = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $c['class_id']; ?>" <?php echo (isset($edit_data) && $edit_data['class_id'] == $c['class_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Allegiance</label>
                        <select name="ally">
                            <option value="1" <?php echo (isset($edit_data) && $edit_data['ally'] == 1) ? 'selected' : ''; ?>>Player Ally</option>
                            <option value="0" <?php echo (isset($edit_data) && $edit_data['ally'] == 0) ? 'selected' : ''; ?>>Enemy Unit</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Portrait Image</label>
                        <?php if(!empty($edit_data['image_url'])): ?><img src="<?php echo $edit_data['image_url']; ?>" style="height:40px;"><br><?php endif; ?>
                        <input type="file" name="image" accept="image/*">
                    </div>

                    <div class="form-group"><label>Base HP</label><input type="number" name="base_hp" value="<?php echo $edit_data['base_hp'] ?? 20; ?>" required></div>
                    <div class="form-group"><label>Strength (Str)</label><input type="number" name="base_str" value="<?php echo $edit_data['base_str'] ?? 5; ?>" required></div>
                    <div class="form-group"><label>Dexterity (Dex)</label><input type="number" name="base_dex" value="<?php echo $edit_data['base_dex'] ?? 5; ?>" required></div>
                    <div class="form-group"><label>Skill</label><input type="number" name="base_skill" value="<?php echo $edit_data['base_skill'] ?? 5; ?>" required></div>
                    <div class="form-group"><label>Defense (Def)</label><input type="number" name="base_def" value="<?php echo $edit_data['base_def'] ?? 3; ?>" required></div>
                    <div class="form-group"><label>Luck</label><input type="number" name="base_luck" value="<?php echo $edit_data['base_luck'] ?? 2; ?>" required></div>
                    <div class="form-group"><label>Movement (Move)</label><input type="number" name="base_move" value="<?php echo $edit_data['base_move'] ?? 5; ?>" required></div>
                    
                    <div class="form-group form-full"><label>Description</label><textarea name="description" rows="2"><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea></div>
                </div>
                <button type="submit" class="btn" style="margin-top: 1rem;">Save Character</button>
            </form>
        </div>
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