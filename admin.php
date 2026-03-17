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

// Helper function for image uploads
function handleAdminUpload($file, $folder) {
    if (isset($file) && $file['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $dir = 'uploads/' . $folder . '/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $path = $dir . uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $path)) return $path;
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

    // SAVE ARTICLE
    elseif ($action === 'save_article') {
        $id = (int)$_POST['id'];
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
        $message = "Article saved.";
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
        $id = (int)$_POST['character_id']; $name = $_POST['name']; $c_id = (int)$_POST['class_id'];
        $ally = (int)$_POST['ally']; $hp = (int)$_POST['base_hp']; $str = (int)$_POST['base_str'];
        $dex = (int)$_POST['base_dex']; $skill = (int)$_POST['base_skill']; $def = (int)$_POST['base_def'];
        $luck = (int)$_POST['base_luck']; $move = (int)$_POST['base_move']; $desc = $_POST['description'];
        $img = handleAdminUpload($_FILES['image'] ?? null, 'characters');

        if ($id > 0) {
            if ($img) {
                $stmt = $conn->prepare("UPDATE characters SET name=?, class_id=?, ally=?, base_hp=?, base_str=?, base_dex=?, base_skill=?, base_def=?, base_luck=?, base_move=?, description=?, image_url=? WHERE character_id=?");
                // FIXED: 9 'i's instead of 10
                $stmt->bind_param("siiiiiiiiissi", $name, $c_id, $ally, $hp, $str, $dex, $skill, $def, $luck, $move, $desc, $img, $id);
            } else {
                $stmt = $conn->prepare("UPDATE characters SET name=?, class_id=?, ally=?, base_hp=?, base_str=?, base_dex=?, base_skill=?, base_def=?, base_luck=?, base_move=?, description=? WHERE character_id=?");
                // FIXED: 9 'i's instead of 10
                $stmt->bind_param("siiiiiiiiisi", $name, $c_id, $ally, $hp, $str, $dex, $skill, $def, $luck, $move, $desc, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO characters (name, class_id, ally, base_hp, base_str, base_dex, base_skill, base_def, base_luck, base_move, description, image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            // FIXED: 9 'i's instead of 10
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
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; background: rgba(34,197,94,0.1); border: 1px solid #22c55e; color: #86efac; }
    </style>
</head>
<body>
    <a href="index.php">← Back to Home</a>
    <h1 style="margin-top: 1rem; font-size: 1.5rem;">Database Manager</h1>

    <?php if ($message): ?><div class="alert"><?php echo $message; ?></div><?php endif; ?>

    <div class="admin-tabs">
        <a href="?tab=users" class="admin-tab <?php echo $tab=='users'?'active':'';?>">Users</a>
        <a href="?tab=articles" class="admin-tab <?php echo $tab=='articles'?'active':'';?>">Articles</a>
        <a href="?tab=characters" class="admin-tab <?php echo $tab=='characters'?'active':'';?>">Characters</a>
        <a href="?tab=classes" class="admin-tab <?php echo $tab=='classes'?'active':'';?>">Classes</a>
        <a href="?tab=weapons" class="admin-tab <?php echo $tab=='weapons'?'active':'';?>">Weapons</a>
    </div>

    <?php if ($tab == 'users'): 
        $users = $conn->query("SELECT * FROM users");
    ?>
    <div class="card">
        <h3>Registered Users</h3>
        <table style="width: 100%; text-align: left;">
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Admin</th><th>Actions</th></tr>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo $u['admin'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete user?');">
                        <input type="hidden" name="action" value="delete_record">
                        <input type="hidden" name="table" value="users">
                        <input type="hidden" name="id_col" value="id">
                        <input type="hidden" name="id_val" value="<?php echo $u['id']; ?>">
                        <button type="submit" style="background:#ef4444; color:white; border:none; padding:0.25rem 0.5rem; border-radius:0.25rem; cursor:pointer;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php elseif ($tab == 'articles'): ?>
    <div class="card">
        <h3><?php echo $edit_data ? 'Edit Article' : 'New Article'; ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_article">
            <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? 0; ?>">
            <div class="form-grid">
                <div class="form-group form-full"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($edit_data['title'] ?? ''); ?>" required></div>
                <div class="form-group form-full"><label>Content (HTML allowed)</label><textarea name="content" rows="6" required><?php echo htmlspecialchars($edit_data['content'] ?? ''); ?></textarea></div>
                <div class="form-group form-full">
                    <label>Cover Image</label>
                    <?php if(!empty($edit_data['image_url'])): ?><img src="<?php echo $edit_data['image_url']; ?>" style="height:60px; margin-bottom:1rem; border-radius:0.5rem;"><br><?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>
            </div>
            <button type="submit" class="btn">Save Article</button>
        </form>
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
            <button type="submit" class="btn">Save Class</button>
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
            <button type="submit" class="btn">Save Weapon</button>
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
            <button type="submit" class="btn">Save Character</button>
        </form>
    </div>
    <?php endif; ?>

</body>
</html>