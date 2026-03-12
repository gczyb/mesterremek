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
        .nav-links a:hover { color: #fbbf24; }

        .container { max-width: 1200px; margin: 100px auto 4rem; padding: 0 1rem; }
        h1 { color: #fbbf24; text-align: center; margin-bottom: 1rem; font-size: 2.5rem; text-transform: uppercase; letter-spacing: 0.1em; }
        .subtitle { text-align: center; color: #94a3b8; margin-bottom: 3rem; }

        .maps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        /* --- UPDATED CARD STYLES START HERE --- */
        
        .map-card {
            /* Fixed height lets the image take up the full vertical space */
            height: 400px; 
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            overflow: hidden;
            position: relative; /* Needed for absolute positioning of children */
            text-decoration: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s;
            display: block;
        }

        .map-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.5);
            border-color: #fbbf24;
        }

        /* Image fills the entire card now */
        .map-preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #0f172a;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 1;
            transition: transform 0.5s ease;
        }

        .map-card:hover .map-preview {
            transform: scale(1.05); /* Subtle zoom effect on hover */
        }

        /* Content sits at the bottom */
        .map-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 2; /* Sits on top of image */
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            
            /* THE FADE EFFECT: */
            /* Transparent at top (0%) -> Dark Blue at bottom (40%-100%) */
            background: linear-gradient(
                to bottom, 
                rgba(30, 41, 59, 0) 0%, 
                rgba(30, 41, 59, 0.8) 25%, 
                #1e293b 50%, 
                #1e293b 100%
            );
            
            /* Add some padding-top to push text into the solid color area */
            padding-top: 4rem; 
        }

        .map-title { 
            color: #fbbf24; 
            font-size: 1.5rem; 
            font-weight: bold; 
            margin-bottom: 0.5rem; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.8); /* Ensures text pops against image */
        }

        .map-desc { 
            color: #cbd5e1; 
            font-size: 0.9rem; 
            margin-bottom: 1rem; 
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Limits text to 2 lines to keep section small */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
        }
        
        .btn-view {
            background-color: rgba(251, 191, 36, 0.1); /* Semi-transparent Gold */
            color: #fbbf24;
            border: 1px solid #fbbf24;
            padding: 0.5rem;
            border-radius: 0.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            backdrop-filter: blur(4px);
        }
        
        .map-card:hover .btn-view { 
            background-color: #fbbf24; 
            color: #0f172a; 
        }

        .back-btn { display: inline-block; margin-bottom: 1rem; color: #94a3b8; text-decoration: none; }
        
        @media (max-width: 768px) { .nav-links { display: none; } }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <a href="index.php" class="logo">TREASURE QUEST</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="leaderboard.php" style="color: #fbbf24;">Leaderboard</a>
                <?php if ($user): ?>
                    <a href="profile.php">My Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
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
                            // Check for background image
                            $bgImage = !empty($map['bg']) ? htmlspecialchars($map['bg']) : 'uploads/maps/default-map.jpg'; 
                        ?>
                        
                        <div class="map-preview" style="background-image: url('<?php echo $bgImage; ?>');"></div>
                        
                        <div class="map-content">
                            <div class="map-title"><?php echo htmlspecialchars($map['name']); ?></div>
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
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>