<?php
require_once 'config.php';
$user = getCurrentUser();

$currentPage = basename($_SERVER['PHP_SELF']);
$isHomePage = ($currentPage === 'index.php' || $currentPage === ''); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasure Quest - Epic 2D Adventure</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
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
                
                <?php if (!$user): ?>
                    <a href="login.php" class="btn btn-outline" style="padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important;">Login</a>
                <?php endif; ?>
                
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
        </div>
    </nav>

    <section id="home" class="hero">
        <img src="img/bckg.gif" alt="Background" class="hero-bg">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <a href="index.php">
                <img src="img/logo.png" alt="Treasure Quest Logo" class="hero-logo">
            </a>
            <p>Embark on an epic 2D adventure through treacherous dungeons, mystical forests, and ancient ruins. Master the art of combat, discover legendary treasures, and become the hero of legend.</p>
            <div class="video-backdrop" id="videoBackdrop" onclick="minimizeVideo()"></div>
            <div class="video-wrapper">
                <div id="mainVideo" class="video-placeholder initial-state" onclick="startVideo()">
                    <div class="video-container">
                        <div class="video-overlay" id="videoOverlay"></div>
                        <video id="localVideo" playsinline>
                            <source src="your-downloaded-video.mp4" type="video/mp4">
                        </video>
                        <div id="playBtnContent">
                            <div class="control-btn initial-play-btn" title="Watch Gameplay Trailer">
                                <svg class="btn-icon" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </div>
                            <p>Watch Gameplay Trailer</p>
                        </div>
                        <div id="controlsGroup" class="controls-group">
                            <button id="playPauseBtn" class="control-btn" onclick="togglePlayPause(event)" title="Play/Pause Video">
                                <svg id="iconPause" class="btn-icon" fill="currentColor" viewBox="0 0 24 24" style="display: none;"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                                <svg id="iconPlay" class="btn-icon" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </button>
                            <button id="sizeToggleBtn" class="control-btn" onclick="toggleVideoSize(event)" title="Toggle Fullscreen">
                                <svg class="icon-maximize btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                                <svg class="icon-minimize btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-buttons">
                <a href="downloads/TreasureQuestLauncher.zip" download="TreasureQuestLauncher.zip" class="btn">Download game</a>
            </div>
        </div>
    </section>

    <section id="about">
        <div class="section-container">
            <div class="about-grid">
                <div class="about-content">
                    <h2>About the Game</h2>
                    <p><span class="highlight">Treasure Quest</span> is a love letter to classic 2D sidescrolling adventures, combining tight platforming mechanics with deep RPG elements and a charming retro aesthetic.</p>
                    <p>Armed with your trusty weapon and an arsenal of magical abilities, you'll traverse dangerous lands, battle fierce enemies, and uncover the secrets of a forgotten civilization.</p>
                    <p>Every level is meticulously crafted to challenge your skills while rewarding exploration and creativity. Whether you're speedrunning through levels or hunting for every last collectible, Treasure Quest offers endless replayability.</p>
                    <div class="about-info">
                        <div class="info-grid">
                            <div class="info-item"><p>Genre</p><p>2D Action Platformer RPG</p></div>
                            <div class="info-item"><p>Platform</p><p>PC, Console</p></div>
                            <div class="info-item"><p>Players</p><p>Single Player</p></div>
                            <div class="info-item"><p>Release</p><p>2025</p></div>
                        </div>
                    </div>
                </div>
                <div class="about-image-container">
                    <div class="about-image">
                        <img src="https://images.unsplash.com/photo-1759171052927-83f3b3a72b2b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=1080" alt="Game artwork">
                    </div>
                    <div class="glow-effect glow-bottom-right"></div>
                    <div class="glow-effect glow-top-left"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="features">
        <div class="section-container">
            <div class="section-header">
                <h2>Game Features</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6.2 8.9a3 3 0 0 1 5.6 0M5 21h14a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2z"></path></svg></div>
                    <h3>Epic Combat System</h3>
                    <p>Master a variety of weapons and abilities. Perfect your timing with precise controls and devastating combo attacks.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
                    <h3>Vast World to Explore</h3>
                    <p>Journey through diverse environments from dark dungeons to mystical forests, each with unique challenges and secrets.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
                    <h3>Memorable Characters</h3>
                    <p>Meet a cast of quirky allies and fearsome bosses, each with their own stories and personalities.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path></svg></div>
                    <h3>Magical Abilities</h3>
                    <p>Unlock powerful spells and special abilities. Combine them creatively to overcome any obstacle.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></div>
                    <h3>Challenging & Fair</h3>
                    <p>Experience difficulty that rewards skill and perseverance. Every defeat is a lesson, every victory is earned.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg></div>
                    <h3>Secrets & Collectibles</h3>
                    <p>Hunt for hidden treasures, rare items, and secret areas. Completionists will find endless content to discover.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="gallery">
        <div class="section-container">
            <div class="section-header">
                <h2>Screenshot Gallery</h2>
            </div>
            
            <div class="carousel">
                <div class="carousel-container">
                    <div class="carousel-track" id="carouselTrack">
                        <div class="carousel-item"><div class="video-container"><img src="https://images.unsplash.com/photo-1759171052927-83f3b3a72b2b?w=1080" alt="Epic Boss Battles"><div class="carousel-caption"><h3>Epic Boss Battles</h3></div></div></div>
                        <div class="carousel-item"><div class="video-container"><img src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1080" alt="Retro Inspired Graphics"><div class="carousel-caption"><h3>Retro Inspired Graphics</h3></div></div></div>
                        <div class="carousel-item"><div class="video-container"><img src="https://images.unsplash.com/photo-1553986782-9f6de60b51b4?w=1080" alt="Heroic Adventures"><div class="carousel-caption"><h3>Heroic Adventures</h3></div></div></div>
                        <div class="carousel-item"><div class="video-container"><img src="https://images.unsplash.com/photo-1707042711207-2b38f5d93974?w=1080" alt="Mystical Worlds"><div class="carousel-caption"><h3>Mystical Worlds</h3></div></div></div>
                    </div>
                </div>
                <button class="carousel-btn carousel-btn-prev" onclick="previousSlide()"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg></button>
                <button class="carousel-btn carousel-btn-next" onclick="nextSlide()"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></button>
            </div>
        </div>
    </section>

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

        let currentSlide = 0;
        const totalSlides = 4;
        function updateCarousel() { const t = document.getElementById('carouselTrack'); if (t) t.style.transform = `translateX(-${currentSlide * 100}%)`; }
        function nextSlide() { currentSlide = (currentSlide + 1) % totalSlides; updateCarousel(); }
        function previousSlide() { currentSlide = (currentSlide - 1 + totalSlides) % totalSlides; updateCarousel(); }
        setInterval(nextSlide, 5000);

        let isVideoExpanded = false;
        const localVideo = document.getElementById('localVideo');
        const iconPause = document.getElementById('iconPause');
        const iconPlay = document.getElementById('iconPlay');

        if(localVideo) {
            localVideo.addEventListener('play', () => { iconPlay.style.display = 'none'; iconPause.style.display = 'block'; });
            localVideo.addEventListener('pause', () => { iconPause.style.display = 'none'; iconPlay.style.display = 'block'; });
            localVideo.addEventListener('ended', () => { iconPause.style.display = 'none'; iconPlay.style.display = 'block'; });
        }

        function startVideo() {
            const placeholder = document.getElementById('mainVideo');
            if (placeholder.classList.contains('is-playing')) return; 
            const overlayContent = document.getElementById('playBtnContent');
            const overlayBackground = document.getElementById('videoOverlay');
            const sizeToggleBtn = document.getElementById('sizeToggleBtn');
            const playPauseBtn = document.getElementById('playPauseBtn');

            placeholder.classList.add('is-playing');
            placeholder.classList.remove('initial-state');
            overlayContent.style.opacity = '0';
            
            setTimeout(() => {
                overlayContent.style.display = 'none';
                overlayBackground.style.display = 'none'; 
                sizeToggleBtn.classList.add('visible');
                playPauseBtn.classList.add('visible');
                localVideo.style.display = 'block';
                localVideo.play(); 
            }, 300);
        }

        function togglePlayPause(event) {
            event.stopPropagation();
            if (localVideo.paused) localVideo.play();
            else localVideo.pause();
        }

        function toggleVideoSize(event) {
            event.stopPropagation(); 
            if (!isVideoExpanded) maximizeVideo();
            else minimizeVideo();
        }

        function maximizeVideo() {
            const placeholder = document.getElementById('mainVideo');
            const backdrop = document.getElementById('videoBackdrop');
            placeholder.classList.add('is-expanded');
            backdrop.classList.add('active');
            document.body.style.overflow = 'hidden'; 
            isVideoExpanded = true;
        }

        function minimizeVideo() {
            if (!isVideoExpanded) return;
            const placeholder = document.getElementById('mainVideo');
            const backdrop = document.getElementById('videoBackdrop');
            placeholder.classList.remove('is-expanded');
            backdrop.classList.remove('active');
            document.body.style.overflow = ''; 
            isVideoExpanded = false;
        }
    </script>
</body>
</html>