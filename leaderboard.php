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
        /* Import the Press Start 2P font to match the rest of the site */
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

        /* Core Theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0f172a;
            color: #cbd5e1;
            line-height: 1.6;
        }

        /* Apply Pixel Font to Headings Only */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Press Start 2P', system-ui, sans-serif;
            line-height: 1.4;
        }

        /* --- Synced Navigation Styles --- */
        .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background-color: #0f172a; border-bottom: 1px solid #334155; }
        .nav-container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        
        .logo { display: flex; align-items: center; height: 100%; text-decoration: none; }
        .logo h2 { color: #fbbf24; font-size: 1.2rem; margin: 0; white-space: nowrap; }
        
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { color: #e2e8f0; text-decoration: none; transition: color 0.3s; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        .nav-links a:hover { color: #fbbf24; }
        .nav-links a.active { color: #fbbf24; }

        .container { max-width: 1200px; margin: 100px auto 4rem; padding: 0 1rem; }
        h1 { color: #fbbf24; text-align: center; margin-bottom: 1rem; font-size: 1.8rem; letter-spacing: 0.1em; }
        .subtitle { text-align: center; color: #94a3b8; margin-bottom: 3rem; }

        .maps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        /* --- CARD STYLES --- */
        .map-card {
            height: 400px; 
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            overflow: hidden;
            position: relative; 
            text-decoration: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s;
            display: block;
        }

        .map-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.5);
            border-color: #fbbf24;
        }

        /* Uses object-fit: cover since we are using an actual <img> tag now */
        .map-preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
            transition: transform 0.5s ease;
        }

        .map-card:hover .map-preview {
            transform: scale(1.05); 
        }

        .map-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 2; 
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            background: linear-gradient(
                to bottom, 
                rgba(30, 41, 59, 0) 0%, 
                rgba(30, 41, 59, 0.8) 35%, 
                #1e293b 70%, 
                #1e293b 100%
            );
            padding-top: 5rem; 
        }

        .map-title { 
            color: #fbbf24; 
            font-size: 1.2rem; 
            margin-bottom: 0.5rem; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.8); 
        }

        .map-desc { 
            color: #cbd5e1; 
            font-size: 0.9rem; 
            margin-bottom: 1rem; 
            display: -webkit-box;
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .btn-view {
            background-color: rgba(251, 191, 36, 0.1); 
            color: #fbbf24;
            border: 1px solid #fbbf24;
            padding: 0.5rem;
            border-radius: 0.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            backdrop-filter: blur(4px);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .map-card:hover .btn-view { 
            background-color: #fbbf24; 
            color: #0f172a; 
        }

        .back-btn { display: inline-block; margin-bottom: 1rem; color: #94a3b8; text-decoration: none; transition: color 0.3s;}
        .back-btn:hover { color: #fbbf24; }
        
        @media (max-width: 768px) { .nav-links { display: none; } }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <h2>TREASURE QUEST</h2>
            </a>
            <div class="nav-links">
                <a href="index.php#home">Home</a>
                <a href="index.php#about">About</a>
                <a href="index.php#features">Features</a>
                <a href="index.php#gallery">Gallery</a>
                <a href="leaderboard.php" class="active">Leaderboard</a>
                
                <?php if ($user): ?>
                    <a href="profile.php" style="margin-left: 1rem;">My Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="margin-left: 1rem; background-color: transparent; border: 1px solid #fbbf24; color: #fbbf24; padding: 0.5rem 1.5rem; border-radius: 0.375rem;">Login</a>
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
                            // The image is stored as a BLOB, so we encode it to base64 to display it inline
                            if (!empty($map['bg'])) {
                                $base64 = base64_encode($map['bg']);
                                // We tell the browser it's an image data string
                                $bgImage = 'data:image/jpeg;base64,' . $base64; 
                            } else {
                                // Make sure you have a default image here just in case a map has no BLOB!
                                $bgImage = 'img/default-map.jpg'; 
                            }
                        ?>
                        
                        <img src="<?php echo $bgImage; ?>" alt="<?php echo htmlspecialchars($map['name']); ?>" class="map-preview">
                        
                        <div class="map-content">
                            <h2 class="map-title"><?php echo htmlspecialchars($map['name']); ?></h2>
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