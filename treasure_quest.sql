-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Már 12. 11:28
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `treasure_quest`
--
CREATE DATABASE IF NOT EXISTS `treasure_quest` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `treasure_quest`;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `characters`
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
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `characters`
--

INSERT INTO `characters` (`character_id`, `name`, `class_id`, `ally`, `base_hp`, `base_str`, `base_dex`, `base_skill`, `base_def`, `base_luck`, `base_move`, `description`) VALUES
(1, 'Aric the Bold', 1, 1, 35, 15, 10, 12, 14, 8, 5, 'A brave knight from the capital.'),
(2, 'Elara Moon', 2, 1, 25, 20, 12, 15, 6, 10, 5, 'A mysterious sorceress seeking ancient scrolls.'),
(3, 'Garret Shadow', 3, 1, 28, 12, 18, 16, 8, 15, 6, 'A thief with a heart of gold.'),
(4, 'Brother Thomas', 4, 1, 30, 10, 8, 10, 10, 12, 5, 'A humble priest on a pilgrimage.'),
(5, 'Goblin Grunt', 1, 0, 20, 8, 12, 8, 5, 5, 5, 'A low-ranking goblin soldier.'),
(6, 'Dark Sorcerer', 2, 0, 40, 22, 14, 18, 10, 5, 6, 'The evil mastermind guarding the treasure.');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `classes`
--

INSERT INTO `classes` (`class_id`, `name`, `description`) VALUES
(1, 'Warrior', 'A strong fighter skilled in melee combat and defense.'),
(2, 'Mage', 'A spellcaster who wields powerful magic but has low defense.'),
(3, 'Rogue', 'A quick and stealthy fighter who relies on speed and critical hits.'),
(4, 'Cleric', 'A holy warrior who specializes in healing and support.');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `class_weapons`
--

CREATE TABLE `class_weapons` (
  `class_id` int(11) NOT NULL,
  `weapon_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `class_weapons`
--

INSERT INTO `class_weapons` (`class_id`, `weapon_id`) VALUES
(1, 1),
(1, 2),
(2, 3),
(3, 4),
(3, 5),
(4, 3);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `maps`
--

CREATE TABLE `maps` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `bg` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `maps`
--

INSERT INTO `maps` (`id`, `name`, `bg`, `description`, `created_at`) VALUES
(1, 'Goblin Forest', 'uploads/maps/goblin_forest.jpg', 'A dense forest teeming with goblin patrols.', '2026-01-29 07:45:14'),
(2, 'Ruins of Eldoria', 'uploads/maps/ruins_of_eldoria.jpg', 'Ancient ruins said to hold magical artifacts.', '2026-01-29 07:45:14'),
(3, 'Dragon Peak', 'uploads/maps/dragon_peak.jpg', 'A treacherous mountain path leading to the dragon\'s lair.', '2026-01-29 07:45:14');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `map_characters`
--

CREATE TABLE `map_characters` (
  `map_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `map_characters`
--

INSERT INTO `map_characters` (`map_id`, `character_id`) VALUES
(1, 1),
(1, 5),
(2, 2),
(2, 3),
(2, 5),
(3, 1),
(3, 4),
(3, 6);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `scores`
--

CREATE TABLE `scores` (
  `score_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `map_id` int(11) NOT NULL,
  `turns` int(11) NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `scores`
--

INSERT INTO `scores` (`score_id`, `user_id`, `map_id`, `turns`, `date`) VALUES
(1, 1, 1, 15, '2026-01-28 10:00:00'),
(2, 2, 1, 12, '2026-01-29 14:30:00'),
(3, 3, 1, 20, '2026-01-29 09:15:00'),
(4, 2, 2, 25, '2026-01-29 15:00:00'),
(5, 4, 2, 24, '2026-01-28 18:45:00'),
(6, 1, 3, 40, '2026-01-29 08:00:00'),
(7, 4, 3, 38, '2026-01-29 11:20:00'),
(8, 5, 1, 1, '2026-02-16 14:09:58'),
(9, 5, 1, 2, '2026-02-17 11:52:12'),
(10, 6, 1, 1, '2026-02-17 12:58:25'),
(11, 6, 1, 2, '2026-02-18 12:56:15');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'uploads/profiles/default.png',
  `api_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `reset_token`, `reset_token_expires`, `created_at`, `profile_picture`, `api_token`) VALUES
(1, 'gx', 'geczyba@gmail.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', NULL, NULL, '2026-01-29 07:28:32', '', NULL),
(2, 'SpeedRunner99', 'speed@example.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', NULL, NULL, '2026-01-29 07:45:14', NULL, NULL),
(3, 'CasualGamer', 'casual@example.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', NULL, NULL, '2026-01-29 07:45:14', NULL, NULL),
(4, 'QuestMaster', 'master@example.com', '$2y$10$LN8GWt8Q3qmjctp8pShKR.6V1yeiX79JTl0e4/U2luoJz9QkJDos.', NULL, NULL, '2026-01-29 07:45:14', NULL, NULL),
(5, 'gecseb', 'gecseboti@gmail.com', '$2y$10$5nPUWNls6dhs0VPLHVrZyepi6aHpk7cRktI0crBaDkuCFFSSNoBZe', NULL, NULL, '2026-02-16 12:44:03', '', 'b5a937b82b92823beccdcb3bb877567e64263a50a4a85b81195ef6d3ab3c58f4'),
(6, 'aasfadsadada', 'a@a.a', '$2y$10$nuE40heApBAmmESbA.zALuJLgg4OhePCQw.pybjZveza86i7nUo8K', NULL, NULL, '2026-02-17 11:56:37', NULL, 'c16d33a74ae22276528ad698e0d44755274c983fa5aab434f8c6cbb8d7124a2d'),
(7, 'csuma', 'csuma@a.a', '$2y$10$CKWl4gCmnIDZdRm.SDuPHua/.6cdk.PNWY8HwJWf27s9nRpBhh6Iy', NULL, NULL, '2026-03-12 09:58:57', 'uploads/profiles/user_7_1773310521.png', NULL),
(8, 'asdasd', 'asd@a.a', '$2y$10$GZVlgEAe5FxMG2KXSLBxXuAKna/ptvWgFMh1x.Szr1kswtRGN.EQq', NULL, NULL, '2026-03-12 10:17:16', 'uploads/profiles/default.png', NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `weapons`
--

CREATE TABLE `weapons` (
  `weapon_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `base_attack` int(11) DEFAULT 0,
  `base_speed` int(11) DEFAULT 0,
  `base_accuracy` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `weapons`
--

INSERT INTO `weapons` (`weapon_id`, `name`, `description`, `base_attack`, `base_speed`, `base_accuracy`) VALUES
(1, 'Iron Sword', 'A standard soldier sword.', 8, 5, 90),
(2, 'Steel Axe', 'A heavy axe that deals great damage.', 12, 3, 75),
(3, 'Oak Staff', 'A wooden staff for channeling magic.', 6, 6, 95),
(4, 'Silver Dagger', 'A lightweight dagger for quick strikes.', 5, 10, 95),
(5, 'Longbow', 'A large bow for attacking from distance.', 9, 7, 85);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`character_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `class_id` (`class_id`);

--
-- A tábla indexei `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- A tábla indexei `class_weapons`
--
ALTER TABLE `class_weapons`
  ADD PRIMARY KEY (`class_id`,`weapon_id`),
  ADD KEY `weapon_id` (`weapon_id`);

--
-- A tábla indexei `maps`
--
ALTER TABLE `maps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- A tábla indexei `map_characters`
--
ALTER TABLE `map_characters`
  ADD PRIMARY KEY (`map_id`,`character_id`),
  ADD KEY `character_id` (`character_id`);

--
-- A tábla indexei `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `map_id` (`map_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A tábla indexei `weapons`
--
ALTER TABLE `weapons`
  ADD PRIMARY KEY (`weapon_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `characters`
--
ALTER TABLE `characters`
  MODIFY `character_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `maps`
--
ALTER TABLE `maps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `scores`
--
ALTER TABLE `scores`
  MODIFY `score_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT a táblához `weapons`
--
ALTER TABLE `weapons`
  MODIFY `weapon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `class_weapons`
--
ALTER TABLE `class_weapons`
  ADD CONSTRAINT `class_weapons_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_weapons_ibfk_2` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`weapon_id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `map_characters`
--
ALTER TABLE `map_characters`
  ADD CONSTRAINT `map_characters_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `maps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `map_characters_ibfk_2` FOREIGN KEY (`character_id`) REFERENCES `characters` (`character_id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `maps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
