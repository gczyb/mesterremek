-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 20, 2026 at 02:21 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `treasure_quest`
--

-- --------------------------------------------------------

--
-- Table structure for table `characters`
--

CREATE TABLE `characters` (
  `character_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `class_id` int(11) NOT NULL,
  `ally` tinyint(1) DEFAULT 1,
  `base_hp` int(11) NOT NULL,
  `base_str` int(11) NOT NULL,
  `base_dex` int(11) NOT NULL,
  `base_skill` int(11) NOT NULL,
  `base_def` int(11) NOT NULL,
  `base_luck` int(11) NOT NULL,
  `base_move` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `characters`
--

INSERT INTO `characters` (`character_id`, `name`, `class_id`, `ally`, `base_hp`, `base_str`, `base_dex`, `base_skill`, `base_def`, `base_luck`, `base_move`, `description`, `image_url`) VALUES
(1, 'Tibi', 1, 1, 22, 6, 5, 4, 4, 2, 5, 'A dependable frontline fighter. Good health and strength.', 'uploads/characters/img_69b936f37e425.jpg'),
(2, 'Tibo', 2, 1, 18, 5, 6, 6, 3, 3, 5, 'A sharp-eyed archer. Excels at dealing chip damage from behind the lines.', NULL),
(3, 'Geri', 4, 1, 25, 7, 3, 4, 9, 2, 4, 'A heavily armored knight. Moves slowly but can block chokepoints.', NULL),
(5, 'Goblin Grunt', 1, 0, 18, 4, 4, 3, 2, 0, 5, 'A weak but aggressive forest dweller.', NULL),
(6, 'Goblin Scout', 2, 0, 16, 3, 5, 4, 2, 0, 5, 'A goblin equipped with a crude bow.', NULL),
(7, 'Orc Brute', 4, 0, 26, 8, 2, 2, 5, 0, 4, 'A terrifyingly strong and durable orc boss.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `name`, `description`, `image_url`) VALUES
(1, 'Fighter', 'A balanced frontline warrior with good HP and Strength.', NULL),
(2, 'Archer', 'A ranged specialist who strikes from afar but is vulnerable up close.', NULL),
(3, 'Mage', 'A spellcaster targeting the enemy\'s magical resistance.', NULL),
(4, 'Knight', 'A heavily armored wall. High Defense, but very low Speed and Movement.', NULL),
(5, 'Thief', 'A fragile but incredibly fast rogue. High Dexterity and Luck for dodging.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_weapons`
--

CREATE TABLE `class_weapons` (
  `class_id` int(11) NOT NULL,
  `weapon_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_weapons`
--

INSERT INTO `class_weapons` (`class_id`, `weapon_id`) VALUES
(1, 2),
(2, 5),
(2, 6),
(4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE `maps` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `bg` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maps`
--

INSERT INTO `maps` (`id`, `name`, `bg`, `description`, `created_at`) VALUES
(2, 'Ruins of Eldoria', 'uploads/maps/ruins_of_eldoria.jpg', 'Ancient ruins said to hold magical artifacts.', '2026-01-29 07:45:14'),
(4, 'Forgotten Bastion ', 'uploads/maps/forgotten_bastion.jpg', 'Where the fallen kingdom lies buried in dust.', '2026-04-08 22:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `map_characters`
--

CREATE TABLE `map_characters` (
  `map_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `map_characters`
--

INSERT INTO `map_characters` (`map_id`, `character_id`) VALUES
(1, 1),
(1, 2),
(1, 5),
(1, 6),
(2, 3),
(2, 5),
(2, 7);

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `score_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `map_id` int(11) NOT NULL,
  `turns` int(11) NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`score_id`, `user_id`, `map_id`, `turns`, `date`) VALUES
(4, 2, 2, 25, '2026-01-29 15:00:00'),
(5, 4, 2, 24, '2026-01-28 18:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'uploads/profiles/default.png',
  `api_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `admin`, `reset_token`, `reset_token_expires`, `created_at`, `profile_picture`, `api_token`) VALUES
(1, 'gx', 'geczyba@gmail.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', 1, NULL, NULL, '2026-01-29 07:28:32', 'uploads/profiles/user_1_1773664004.jpg', NULL),
(2, 'SpeedRunner99', 'speed@example.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', 0, NULL, NULL, '2026-01-29 07:45:14', NULL, NULL),
(3, 'CasualGamer', 'casual@example.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', 0, NULL, NULL, '2026-01-29 07:45:14', NULL, NULL),
(4, 'QuestMaster', 'master@example.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', 0, NULL, NULL, '2026-01-29 07:45:14', NULL, NULL),
(5, 'gecseb', 'gecseboti@gmail.com', '$2y$10$5nPUWNls6dhs0VPLHVrZyepi6aHpk7cRktI0crBaDkuCFFSSNoBZe', 0, NULL, NULL, '2026-02-16 12:44:03', '', 'b5a937b82b92823beccdcb3bb877567e64263a50a4a85b81195ef6d3ab3c58f4'),
(9, 'ma', 'bamagx@gmail.com', '$2y$12$//G92EfXL.cETuRAAIzU4u9a8bhd8rjYURVP/Un44382H4HyDcA9m', 1, NULL, NULL, '2026-03-24 09:17:51', 'uploads/profiles/default.png', NULL),
(14, 'molnarm', 'marcell.13.molnar@gmail.com', '$2y$12$ao/e.V.cecF1xspfehlkSemud1G0wwohDPnv8ITeQaSib5qT7W4cu', 0, 'f336c34f426c0b85b5a21bec7e3c3f918e2197086c4a58c533154ac9efd9d100', '2026-04-08 22:25:39', '2026-04-08 21:25:31', 'uploads/profiles/default.png', NULL),
(15, 'test_user', 'user@user.hu', '$2y$12$XD.z5aU6tqUE8dFKn149wuHeHk.qVuvR2826EkjOa8VMRdJAh2AGS', 0, NULL, NULL, '2026-04-20 12:17:18', 'uploads/profiles/default.png', NULL),
(16, 'test_admin', 'admin@admin.hu', '$2y$12$68Ln86zTWkiuQkEjAXqceujUBotZ6im2ja6o5O4Cd/NBWU8l4Cu/W', 1, NULL, NULL, '2026-04-20 12:19:20', 'uploads/profiles/default.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `weapons`
--

CREATE TABLE `weapons` (
  `weapon_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `weapon_type` varchar(20) NOT NULL,
  `atk` int(11) NOT NULL,
  `hit_rate` int(11) NOT NULL,
  `crit_rate` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `min_range` int(11) NOT NULL DEFAULT 1,
  `max_range` int(11) NOT NULL DEFAULT 1,
  `durability` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weapons`
--

INSERT INTO `weapons` (`weapon_id`, `name`, `weapon_type`, `atk`, `hit_rate`, `crit_rate`, `weight`, `min_range`, `max_range`, `durability`, `description`, `image_url`) VALUES
(2, 'Iron Sword', 'Sword', 8, 90, 0, 5, 1, 1, 40, 'A standard issue sword used by infantry. Reliable and balanced.', NULL),
(5, 'Iron Bow', 'Bow', 7, 85, 0, 6, 2, 2, 40, 'A standard bow. Cannot attack enemies right next to the user.', NULL),
(6, 'Iron Dagger', 'Dagger', 4, 95, 5, 2, 1, 1, 30, 'A swift, sturdy blade that relies on speed and precision over raw power.', NULL),
(7, 'Iron Axe', 'Axe', 10, 80, 0, 7, 1, 1, 40, 'A sturdy iron axe that trades precision for raw, devastating power.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wiki_entries`
--

CREATE TABLE `wiki_entries` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wiki_entries`
--

INSERT INTO `wiki_entries` (`id`, `title`, `content`, `image_url`, `created_at`) VALUES
(6, 'The Basics: Move and Fight', 'Ready to start? Here is how you control your hero:\r\nMove: Use WASD or select the tiles to walk around.\r\nAttack: Use Left Click to swing your weapon.\r\nTip: Watch the red bars above enemies. When the bar is empty, they are defeated!', 'uploads/wiki/img_69cad0333bacf.png', '2026-03-30 19:34:11'),
(7, 'Navigating The Game Menu', 'Here is a quick look at what the buttons on the start screen do:\r\nStory: Start your main adventure here.\r\nChallenge: Test your skills in difficult, special levels.\r\nLogin: Sign in to your account to save your progress.\r\nLeaderboard: Opens our website in your browser so you can see the top-ranked players.\r\nHelp: Opens our website for technical support.\r\nTip: If you want to see your name on the Leaderboard, make sure you are logged in while you play!', 'uploads/wiki/img_69cfe4fa573ec.png', '2026-04-03 16:04:10'),
(8, 'Game Objective: How To Complete The Challenge Mode', 'To complete a level in Treasure Quest, you must reach the finish line. Here is your checklist:\r\nDefeat All Enemies: You cannot finish the level until every enemy on the screen is gone. Use teamwork to take them down!\r\nFind Loot & Caches: Search the map for treasure chests and hidden caches. These contain items you need to get stronger.\r\nReach the End: Once the enemies are defeated and the loot is collected, move both players to the exit point to win.\r\nTip: Speed is key! Finishing the map with the least turns is the best way to earn a high score and climb to the top of the Leaderboard.', 'uploads/wiki/img_69d6d7f1c52e5.jpg', '2026-04-06 17:17:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`character_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `class_weapons`
--
ALTER TABLE `class_weapons`
  ADD PRIMARY KEY (`class_id`,`weapon_id`),
  ADD KEY `weapon_id` (`weapon_id`);

--
-- Indexes for table `maps`
--
ALTER TABLE `maps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `map_characters`
--
ALTER TABLE `map_characters`
  ADD PRIMARY KEY (`map_id`,`character_id`),
  ADD KEY `character_id` (`character_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `map_id` (`map_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `weapons`
--
ALTER TABLE `weapons`
  ADD PRIMARY KEY (`weapon_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `wiki_entries`
--
ALTER TABLE `wiki_entries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `characters`
--
ALTER TABLE `characters`
  MODIFY `character_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `maps`
--
ALTER TABLE `maps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `score_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `weapons`
--
ALTER TABLE `weapons`
  MODIFY `weapon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `wiki_entries`
--
ALTER TABLE `wiki_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `class_weapons`
--
ALTER TABLE `class_weapons`
  ADD CONSTRAINT `class_weapons_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_weapons_ibfk_2` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`weapon_id`) ON DELETE CASCADE;

--
-- Constraints for table `map_characters`
--
ALTER TABLE `map_characters`
  ADD CONSTRAINT `map_characters_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`character_id`) ON DELETE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `maps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
