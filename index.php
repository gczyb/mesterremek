<?php
require_once 'config.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasure Quest - Epic 2D Adventure</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0f172a;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: #0f172a;
            border-bottom: 1px solid #334155;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .logo {
            color: #fbbf24;
            font-weight: bold;
            font-size: 24px;
            letter-spacing: 0.05em;
        }

        .nav-links {
            display: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: #e2e8f0;
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #fbbf24;
        }

        .btn {
            background-color: #fbbf24;
            color: #0f172a;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background-color: #f59e0b;
        }

        .btn-outline {
            background-color: transparent;
            color: #fbbf24;
            border: 1px solid #fbbf24;
        }

        .btn-outline:hover {
            background-color: rgba(251, 191, 36, 0.1);
        }

        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            font-weight: bold;
            cursor: pointer;
            border: 2px solid #fbbf24;
            position: relative;
            z-index: 100;
        }

        .user-avatar:hover {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            transform: scale(1.05);
            transition: all 0.3s;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 0.5rem;
            min-width: 200px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            z-index: 1001;
        }

        .user-dropdown.active {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 0.75rem 1rem;
            color: #e2e8f0;
            text-decoration: none;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .user-dropdown a:hover {
            background-color: #334155;
        }

        .user-dropdown .user-info {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #334155;
            color: #94a3b8;
        }

        .user-dropdown .user-info strong {
            display: block;
            color: #fbbf24;
            margin-bottom: 0.25rem;
        }

        .mobile-menu-btn {
            display: block;
            background: none;
            border: none;
            color: #e2e8f0;
            cursor: pointer;
        }

        .mobile-menu {
            display: none;
            background-color: #1e293b;
            padding: 1rem;
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu a {
            display: block;
            color: #e2e8f0;
            text-decoration: none;
            padding: 0.75rem;
            transition: color 0.3s;
        }

        .mobile-menu a:hover {
            color: #fbbf24;
        }

        .mobile-menu .btn {
            width: 100%;
            margin-top: 0.5rem;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: flex;
            }
            .mobile-menu-btn {
                display: none;
            }
        }

        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding-top: 64px;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.3;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent, transparent, #0f172a);
        }

        .hero-content {
            position: relative;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
            text-align: center;
            z-index: 10;
        }

        .hero h1 {
            color: #fbbf24;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .hero p {
            color: #cbd5e1;
            max-width: 42rem;
            margin: 0 auto 2rem;
        }

        .video-placeholder {
            max-width: 56rem;
            margin: 0 auto 2rem;
        }

        .video-container {
            position: relative;
            aspect-ratio: 16/9;
            background-color: #1e293b;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 4px solid rgba(251, 191, 36, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .video-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to bottom right, #1e293b, #0f172a);
        }

        .play-button {
            background-color: rgba(251, 191, 36, 0.2);
            backdrop-filter: blur(4px);
            border-radius: 50%;
            padding: 2rem;
            margin-bottom: 1rem;
        }

        .play-icon {
            width: 64px;
            height: 64px;
            color: #fbbf24;
        }

        .hero-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: center;
        }

        @media (min-width: 640px) {
            .hero-buttons {
                flex-direction: row;
            }
        }

        #about {
            background-color: #0f172a;
        }

        .about-grid {
            display: grid;
            gap: 3rem;
            align-items: center;
        }

        @media (min-width: 768px) {
            .about-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .about-content {
            color: #cbd5e1;
        }

        .about-content p {
            margin-bottom: 1rem;
        }

        .about-content .highlight {
            color: #fbbf24;
        }

        .about-info {
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid #334155;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .info-item p:first-child {
            color: #fbbf24;
            margin-bottom: 0.25rem;
        }

        .info-item p:last-child {
            color: #94a3b8;
        }

        .about-image-container {
            position: relative;
        }

        .about-image {
            aspect-ratio: 1;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 4px solid rgba(251, 191, 36, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .glow-effect {
            position: absolute;
            width: 128px;
            height: 128px;
            background-color: rgba(251, 191, 36, 0.1);
            border-radius: 0.5rem;
            filter: blur(60px);
        }

        .glow-bottom-right {
            bottom: -24px;
            right: -24px;
        }

        .glow-top-left {
            top: -24px;
            left: -24px;
        }

        #features {
            background-color: #1e293b;
        }

        h2 {
            color: #fbbf24;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header p {
            max-width: 42rem;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .features-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .feature-card {
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 0.5rem;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: rgba(251, 191, 36, 0.5);
        }

        .feature-icon {
            display: inline-flex;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: rgba(251, 191, 36, 0.1);
            margin-bottom: 1rem;
        }

        .feature-icon svg {
            width: 32px;
            height: 32px;
            color: #fbbf24;
        }

        .feature-card h3 {
            color: #f1f5f9;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }

        .feature-card p {
            color: #94a3b8;
        }

        #gallery {
            background-color: #0f172a;
        }

        .carousel {
            position: relative;
            max-width: 64rem;
            margin: 0 auto;
        }

        .carousel-container {
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
        }

        .carousel-track {
            display: flex;
            transition: transform 0.5s ease;
        }

        .carousel-item {
            min-width: 100%;
            position: relative;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.9), transparent);
            padding: 1.5rem;
        }

        .carousel-caption h3 {
            color: #fbbf24;
            font-size: 1.5rem;
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: #1e293b;
            border: 1px solid rgba(251, 191, 36, 0.5);
            color: #fbbf24;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .carousel-btn:hover {
            background-color: #334155;
        }

        .carousel-btn-prev {
            left: 1rem;
        }

        .carousel-btn-next {
            right: 1rem;
        }

        .gallery-video {
            margin-top: 4rem;
            max-width: 56rem;
            margin-left: auto;
            margin-right: auto;
        }

        .gallery-video h3 {
            text-align: center;
            color: #fbbf24;
            margin-bottom: 1.5rem;
        }

        footer {
            background-color: #020617;
            border-top: 1px solid #1e293b;
            padding: 3rem 0;
        }

        .footer-grid {
            display: grid;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        footer h3 {
            color: #fbbf24;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        footer h4 {
            color: #e2e8f0;
            margin-bottom: 1rem;
        }

        footer p {
            color: #94a3b8;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            color: #94a3b8;
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: #fbbf24;
        }

        .social-links svg {
            width: 24px;
            height: 24px;
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid #1e293b;
            text-align: center;
            color: #64748b;
        }

        section {
            padding: 5rem 0;
        }

        .section-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <div class="logo">TREASURE QUEST</div>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="#gallery">Gallery</a>
                <button class="btn">Get Game</button>
                
                <?php if ($user): ?>
                    <div class="user-menu">
                        <div class="user-avatar" onclick="toggleUserMenu(event)">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <a href="profile.php">My Profile</a>
                            <a href="profile.php">Settings</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline" style="cursor: pointer;">Login</a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#features">Features</a>
            <a href="#gallery">Gallery</a>
            <?php if ($user): ?>
                <a href="profile.php">Profile (<?php echo htmlspecialchars($user['username']); ?>)</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <section id="home" class="hero">
        <img src="https://i.redd.it/tried-to-create-some-landscape-pixel-art-so-i-attempted-to-v0-qln4rxoc2m291.png?width=2600&format=png&auto=webp&s=f64d50fdbcc3a18d49ec3cc01943bf64c8f07739" alt="Background" class="hero-bg">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>TREASURE QUEST</h1>
            <p>Embark on an epic 2D adventure through treacherous dungeons, mystical forests, and ancient ruins. Master the art of combat, discover legendary treasures, and become the hero of legend.</p>
            
            <div class="video-placeholder">
                <div class="video-container">
                    <div class="video-overlay">
                        <div style="text-align: center;">
                            <div class="play-button">
                                <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </div>
                            <p style="color: #94a3b8;">Gameplay Trailer</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-buttons">
                <button class="btn">Buy Now</button>
                <button class="btn btn-outline">Watch Trailer</button>
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
                            <div class="info-item">
                                <p>Genre</p>
                                <p>2D Action Platformer RPG</p>
                            </div>
                            <div class="info-item">
                                <p>Platform</p>
                                <p>PC, Console</p>
                            </div>
                            <div class="info-item">
                                <p>Players</p>
                                <p>Single Player</p>
                            </div>
                            <div class="info-item">
                                <p>Release</p>
                                <p>2025</p>
                            </div>
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
                <p>Discover what makes Treasure Quest an unforgettable adventure</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6.2 8.9a3 3 0 0 1 5.6 0M5 21h14a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2z"></path>
                        </svg>
                    </div>
                    <h3>Epic Combat System</h3>
                    <p>Master a variety of weapons and abilities. Perfect your timing with precise controls and devastating combo attacks.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3>Vast World to Explore</h3>
                    <p>Journey through diverse environments from dark dungeons to mystical forests, each with unique challenges and secrets.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Memorable Characters</h3>
                    <p>Meet a cast of quirky allies and fearsome bosses, each with their own stories and personalities.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path>
                        </svg>
                    </div>
                    <h3>Magical Abilities</h3>
                    <p>Unlock powerful spells and special abilities. Combine them creatively to overcome any obstacle.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                        </svg>
                    </div>
                    <h3>Challenging & Fair</h3>
                    <p>Experience difficulty that rewards skill and perseverance. Every defeat is a lesson, every victory is earned.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                        </svg>
                    </div>
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
                <p>Take a peek at the beautiful pixel art and exciting gameplay</p>
            </div>
            
            <div class="carousel">
                <div class="carousel-container">
                    <div class="carousel-track" id="carouselTrack">
                        <div class="carousel-item">
                            <div class="video-container">
                                <img src="https://images.unsplash.com/photo-1759171052927-83f3b3a72b2b?w=1080" alt="Epic Boss Battles">
                                <div class="carousel-caption">
                                    <h3>Epic Boss Battles</h3>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="video-container">
                                <img src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1080" alt="Retro Inspired Graphics">
                                <div class="carousel-caption">
                                    <h3>Retro Inspired Graphics</h3>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="video-container">
                                <img src="https://images.unsplash.com/photo-1553986782-9f6de60b51b4?w=1080" alt="Heroic Adventures">
                                <div class="carousel-caption">
                                    <h3>Heroic Adventures</h3>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="video-container">
                                <img src="https://images.unsplash.com/photo-1707042711207-2b38f5d93974?w=1080" alt="Mystical Worlds">
                                <div class="carousel-caption">
                                    <h3>Mystical Worlds</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-btn carousel-btn-prev" onclick="previousSlide()">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="carousel-btn carousel-btn-next" onclick="nextSlide()">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            <div class="gallery-video">
                <h3>Gameplay Showcase</h3>
                <div class="video-container">
                    <div class="video-overlay">
                        <div style="text-align: center;">
                            <div style="background-color: rgba(251, 191, 36, 0.2); backdrop-filter: blur(4px); border-radius: 0.5rem; padding: 3rem; display: inline-block;">
                                <p style="color: #cbd5e1; margin-bottom: 0.5rem;">Extended Gameplay Video</p>
                                <p style="color: #64748b;">Coming Soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="section-container">
            <div class="footer-grid">
                <div>
                    <h3>Treasure Quest</h3>
                    <p>An epic 2D adventure awaits. Begin your quest today.</p>
                </div>
                
                <div>
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#" aria-label="Twitter">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                            </svg>
                        </a>
                        <a href="#" aria-label="YouTube">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.33z"></path>
                                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                            </svg>
                        </a>
                        <a href="#" aria-label="Twitch">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21 2H3v16h5v4l4-4h5l4-4V2zm-10 9V7h2v4h-2zm5 0V7h2v4h-2z"></path>
                            </svg>
                        </a>
                        <a href="#" aria-label="Email">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Treasure Quest. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('active');
        }

        function toggleUserMenu(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (dropdown && userMenu && !userMenu.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Carousel functionality
        let currentSlide = 0;
        const totalSlides = 4;

        function updateCarousel() {
            const track = document.getElementById('carouselTrack');
            if (track) {
                track.style.transform = `translateX(-${currentSlide * 100}%)`;
            }
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }

        function previousSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        // Auto-play carousel
        setInterval(nextSlide, 5000);
    </script>
</body>
</html>