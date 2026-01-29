<?php
require_once 'config.php';
$user = getCurrentUser();
$conn = getDBConnection();

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

// --- UPDATED BACKGROUND IMAGE LOGIC (BLOB) ---
// Default fallback image
$bgImage = 'assets/images/default-map.jpg'; 

// Check if 'bg' column has binary data
if (!empty($map_info['bg'])) {
    // Convert binary BLOB data to a base64 Data URI
    // We assume image/jpeg based on your previous data, but this works for most standard formats
    $bgImage = 'data:image/jpeg;base64,' . base64_encode($map_info['bg']);
}
// ---------------------------------------------

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
        /* Core Theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0f172a;
            color: #cbd5e1;
            line-height: 1.6;
        }
        .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background-color: #0f172a; border-bottom: 1px solid #334155; }
        .nav-container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .logo { color: #fbbf24; font-weight: bold; font-size: 24px; text-decoration: none; }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { color: #e2e8f0; text-decoration: none; transition: color 0.3s; }
        
        .container { max-width: 1000px; margin: 100px auto 4rem; padding: 0 1rem; }
        
        /* --- MAP HEADER WITH BACKGROUND --- */
        .map-header {
            background-color: #1e293b;
            /* Positioning for overlay */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
            border: 1px solid #334155;
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            
            padding: 4rem 2rem 2rem; 
            position: relative;
        }

        /* Dark overlay to ensure text is readable */
        .map-header-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(
                to bottom,
                rgba(15, 23, 42, 0.3) 0%,   
                rgba(15, 23, 42, 0.9) 70%,  
                #0f172a 100%
            );
            z-index: 1;
        }

        .map-header-content {
            position: relative;
            z-index: 2;
        }

        .map-title { 
            color: #fbbf24; 
            font-size: 2.5rem; 
            margin-bottom: 0.5rem; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }
        
        .map-desc { 
            color: #e2e8f0; 
            font-size: 1.1rem; 
            max-width: 800px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        
        /* Character List (Tags) */
        .char-container { margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .char-tag {
            background: rgba(0, 0, 0, 0.4);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex; align-items: center; gap: 0.5rem;
            backdrop-filter: blur(4px);
        }
        .char-ally { border-left: 3px solid #fbbf24; }
        .char-enemy { border-left: 3px solid #ef4444; }
        
        /* Leaderboard Table */
        .table-wrapper {
            background-color: #1e293b;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #334155;
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 1.25rem 1rem; border-bottom: 1px solid #334155; }
        th { background-color: #0f172a; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.05em; }
        tr:hover { background-color: #334155; }
        tr:last-child td { border-bottom: none; }
        
        .rank-1 { color: #fbbf24; font-weight: bold; }
        .rank-2 { color: #94a3b8; font-weight: bold; }
        .rank-3 { color: #b45309; font-weight: bold; }
        
        .user-flex { display: flex; align-items: center; gap: 1rem; font-weight: 500; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background: #fbbf24; color: #0f172a; display: grid; place-items: center; font-weight: bold; }
        
        .back-btn { display: inline-block; margin-bottom: 1rem; color: #fbbf24; text-decoration: none; font-weight: 500; }
        .back-btn:hover { text-decoration: underline; }
        
        .empty-state { padding: 3rem; text-align: center; color: #64748b; }

        @media (max-width: 768px) { .nav-links { display: none; } }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <a href="index.php" class="logo">TREASURE QUEST</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="leaderboard.php">Leaderboard</a>
            </div>
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
                                <span style="opacity: 0.7; font-size: 0.8em;">(<?php echo $char['class_name']; ?>)</span>
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
                                <div class="avatar"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></div>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </div>
                        </td>
                        <td style="font-family: monospace; font-size: 1.1rem; color: #fbbf24;">
                            <?php echo number_format($row['turns']); ?>
                        </td>
                        <td style="color: #94a3b8; font-size: 0.9rem;">
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
</body>
</html>
<?php $conn->close(); ?>