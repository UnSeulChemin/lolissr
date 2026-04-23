-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 21 avr. 2026 à 09:50
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `lolissr`
--

-- --------------------------------------------------------

--
-- Structure de la table `manga`
--

DROP TABLE IF EXISTS `manga`;
CREATE TABLE IF NOT EXISTS `manga` (
  `id` int NOT NULL AUTO_INCREMENT,
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `extension` enum('webp','jpg','png') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `livre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `numero` int NOT NULL,
  `jacquette` tinyint DEFAULT NULL,
  `livre_note` tinyint DEFAULT NULL,
  `note` tinyint DEFAULT NULL,
  `commentaire` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `manga`
--

INSERT INTO `manga` (`id`, `thumbnail`, `extension`, `slug`, `livre`, `numero`, `jacquette`, `livre_note`, `note`, `commentaire`, `created_at`) VALUES
(6, 'to-love-darkness-01', 'webp', 'to-love-darkness', 'To LOVE Darkness', 1, 1, 1, 2, NULL, '2026-04-10 14:42:54'),
(5, 'berserk-02', 'webp', 'berserk', 'Berserk', 2, 4, 5, 9, NULL, '2026-04-10 14:42:54'),
(4, 'berserk-01', 'webp', 'berserk', 'Berserk', 1, 4, 5, 9, NULL, '2026-04-10 14:42:54'),
(3, 'berserk-03', 'webp', 'berserk', 'Berserk', 3, NULL, NULL, 0, NULL, '2026-04-10 14:42:54'),
(2, 'berserk-04', 'webp', 'berserk', 'Berserk', 4, 1, 1, 2, NULL, '2026-04-10 14:42:54'),
(1, 'to-love-darkness-02', 'webp', 'to-love-darkness', 'To LOVE Darkness', 2, 1, 1, 2, NULL, '2026-04-10 14:42:54'),
(7, 'rave-01', 'webp', 'rave', 'Rave', 1, 1, 1, 2, NULL, '2026-04-10 14:42:54'),
(8, 'rave-02', 'webp', 'rave', 'Rave', 2, NULL, NULL, 0, NULL, '2026-04-11 12:25:08'),
(9, 'rave-03', 'webp', 'rave', 'Rave', 3, NULL, NULL, 0, NULL, '2026-04-11 12:28:41'),
(10, 'rave-04', 'webp', 'rave', 'Rave', 4, NULL, NULL, 0, NULL, '2026-04-11 12:30:40'),
(11, 'rave-05', 'webp', 'rave', 'Rave', 5, NULL, NULL, 0, NULL, '2026-04-11 12:37:13'),
(12, 'rave-06', 'webp', 'rave', 'Rave', 6, NULL, NULL, 0, NULL, '2026-04-11 12:37:23'),
(13, 'rave-07', 'webp', 'rave', 'Rave', 7, NULL, NULL, 0, NULL, '2026-04-11 12:37:30'),
(14, 'rave-08', 'webp', 'rave', 'Rave', 8, 1, 1, 2, NULL, '2026-04-11 12:37:38'),
(15, 'rave-09', 'webp', 'rave', 'Rave', 9, 1, 1, 2, NULL, '2026-04-11 12:37:46'),
(16, 'rave-10', 'webp', 'rave', 'Rave', 10, 1, 1, 2, NULL, '2026-04-11 12:37:54'),
(17, 'rave-11', 'webp', 'rave', 'Rave', 11, 1, 1, 2, NULL, '2026-04-11 12:38:01'),
(18, 'rave-12', 'webp', 'rave', 'Rave', 12, 1, 1, 2, NULL, '2026-04-11 12:38:15'),
(19, 'boys-abyss-01', 'webp', 'boys-abyss', 'Boy\'s Abyss', 1, NULL, NULL, NULL, NULL, '2026-04-14 02:00:25'),
(20, 'boys-abyss-02', 'webp', 'boys-abyss', 'Boy\'s Abyss', 2, NULL, NULL, NULL, NULL, '2026-04-14 02:00:49'),
(21, 'boys-abyss-03', 'webp', 'boys-abyss', 'Boy\'s Abyss', 3, NULL, NULL, NULL, NULL, '2026-04-14 02:01:18'),
(22, 'boys-abyss-04', 'webp', 'boys-abyss', 'Boy\'s Abyss', 4, NULL, NULL, NULL, NULL, '2026-04-14 02:01:31'),
(23, 'boys-abyss-05', 'webp', 'boys-abyss', 'Boy\'s Abyss', 5, NULL, NULL, NULL, NULL, '2026-04-14 02:01:38'),
(24, 'boys-abyss-06', 'webp', 'boys-abyss', 'Boy\'s Abyss', 6, NULL, NULL, NULL, NULL, '2026-04-14 02:01:43'),
(25, 'boys-abyss-07', 'webp', 'boys-abyss', 'Boy\'s Abyss', 7, NULL, NULL, NULL, NULL, '2026-04-14 02:02:05'),
(26, 'boys-abyss-08', 'webp', 'boys-abyss', 'Boy\'s Abyss', 8, NULL, NULL, NULL, NULL, '2026-04-14 02:02:16'),
(27, 'boys-abyss-09', 'webp', 'boys-abyss', 'Boy\'s Abyss', 9, NULL, NULL, NULL, NULL, '2026-04-14 02:03:18'),
(28, 'boys-abyss-10', 'webp', 'boys-abyss', 'Boy\'s Abyss', 10, NULL, NULL, NULL, NULL, '2026-04-14 02:03:25'),
(29, 'boys-abyss-11', 'webp', 'boys-abyss', 'Boy\'s Abyss', 11, 1, 1, 2, NULL, '2026-04-14 02:03:31'),
(30, 'boys-abyss-12', 'webp', 'boys-abyss', 'Boy\'s Abyss', 12, NULL, NULL, NULL, NULL, '2026-04-14 02:03:37'),
(31, 'boys-abyss-13', 'webp', 'boys-abyss', 'Boy\'s Abyss', 13, NULL, NULL, NULL, NULL, '2026-04-14 02:03:43'),
(32, 'boys-abyss-14', 'webp', 'boys-abyss', 'Boy\'s Abyss', 14, NULL, NULL, NULL, NULL, '2026-04-14 02:03:50'),
(33, 'boys-abyss-15', 'webp', 'boys-abyss', 'Boy\'s Abyss', 15, NULL, NULL, NULL, NULL, '2026-04-14 02:03:56'),
(34, 'boys-abyss-16', 'webp', 'boys-abyss', 'Boy\'s Abyss', 16, NULL, NULL, NULL, NULL, '2026-04-14 02:04:11'),
(35, 'boys-abyss-17', 'webp', 'boys-abyss', 'Boy\'s Abyss', 17, NULL, NULL, NULL, NULL, '2026-04-14 02:04:17'),
(36, 'boys-abyss-18', 'webp', 'boys-abyss', 'Boy\'s Abyss', 18, 1, 1, 2, NULL, '2026-04-14 02:04:23'),
(37, 'no-control-01', 'webp', 'no-control', 'No Control!', 1, NULL, NULL, NULL, NULL, '2026-04-14 02:07:45'),
(38, 'saint-seiya-les-chevaliers-du-zodiaque-01', 'webp', 'saint-seiya-les-chevaliers-du-zodiaque', 'Saint Seiya - Les Chevaliers du Zodiaque', 1, NULL, NULL, NULL, NULL, '2026-04-15 14:06:20'),
(39, 'saint-seiya-les-chevaliers-du-zodiaque-02', 'webp', 'saint-seiya-les-chevaliers-du-zodiaque', 'Saint Seiya - Les Chevaliers du Zodiaque', 2, NULL, NULL, NULL, NULL, '2026-04-15 14:06:26'),
(40, 'saint-seiya-les-chevaliers-du-zodiaque-03', 'webp', 'saint-seiya-les-chevaliers-du-zodiaque', 'Saint Seiya - Les Chevaliers du Zodiaque', 3, NULL, NULL, NULL, NULL, '2026-04-15 14:06:34'),
(41, 'saint-seiya-les-chevaliers-du-zodiaque-04', 'webp', 'saint-seiya-les-chevaliers-du-zodiaque', 'Saint Seiya - Les Chevaliers du Zodiaque', 4, NULL, NULL, NULL, NULL, '2026-04-15 14:06:55'),
(42, 'saint-seiya-les-chevaliers-du-zodiaque-05', 'webp', 'saint-seiya-les-chevaliers-du-zodiaque', 'Saint Seiya - Les Chevaliers du Zodiaque', 5, NULL, NULL, NULL, NULL, '2026-04-15 14:07:24'),
(43, 'saint-seiya-les-chevaliers-du-zodiaque-06', 'webp', 'saint-seiya-les-chevaliers-du-zodiaque', 'Saint Seiya - Les Chevaliers du Zodiaque', 6, NULL, NULL, NULL, NULL, '2026-04-15 14:07:31'),
(44, 'blood-crawling-princess-01', 'webp', 'blood-crawling-princess', 'Blood-Crawling Princess', 1, NULL, NULL, NULL, NULL, '2026-04-15 14:10:08'),
(45, 'blood-crawling-princess-02', 'webp', 'blood-crawling-princess', 'Blood-Crawling Princess', 2, NULL, NULL, NULL, NULL, '2026-04-15 14:10:48'),
(46, 'blood-crawling-princess-03', 'webp', 'blood-crawling-princess', 'Blood-Crawling Princess', 3, NULL, NULL, NULL, NULL, '2026-04-15 14:10:54'),
(47, 'blood-crawling-princess-04', 'webp', 'blood-crawling-princess', 'Blood-Crawling Princess', 4, NULL, NULL, NULL, NULL, '2026-04-15 14:10:59'),
(48, 'i-want-to-see-you-shy-01', 'webp', 'i-want-to-see-you-shy', 'I want to see U shy', 1, NULL, NULL, NULL, NULL, '2026-04-15 14:14:26'),
(49, 'i-want-to-see-you-shy-02', 'webp', 'i-want-to-see-you-shy', 'I want to see U shy', 2, NULL, NULL, NULL, NULL, '2026-04-15 14:14:33'),
(50, 'i-want-to-see-you-shy-03', 'webp', 'i-want-to-see-you-shy', 'I want to see U shy', 3, 1, 1, 2, NULL, '2026-04-15 14:14:39'),
(51, 'i-want-to-see-you-shy-04', 'webp', 'i-want-to-see-you-shy', 'I want to see U shy', 4, NULL, NULL, NULL, NULL, '2026-04-15 14:14:44'),
(52, 'i-want-to-see-you-shy-05', 'webp', 'i-want-to-see-you-shy', 'I want to see U shy', 5, NULL, NULL, NULL, NULL, '2026-04-15 14:14:48'),
(53, 'i-want-to-see-you-shy-06', 'webp', 'i-want-to-see-you-shy', 'I want to see U shy', 6, NULL, NULL, NULL, NULL, '2026-04-15 14:15:00'),
(54, 'brisee-par-ton-amour-01', 'webp', 'brisee-par-ton-amour', 'Brisée par ton amour...', 1, NULL, NULL, NULL, NULL, '2026-04-15 14:15:26'),
(55, 'brisee-par-ton-amour-02', 'webp', 'brisee-par-ton-amour', 'Brisée par ton amour...', 2, 1, 1, 2, NULL, '2026-04-15 14:15:31'),
(56, 'brisee-par-ton-amour-03', 'webp', 'brisee-par-ton-amour', 'Brisée par ton amour...', 3, 1, 1, 2, NULL, '2026-04-15 14:15:35'),
(57, 'brisee-par-ton-amour-04', 'webp', 'brisee-par-ton-amour', 'Brisée par ton amour...', 4, NULL, NULL, NULL, NULL, '2026-04-15 14:15:41'),
(58, 'brisee-par-ton-amour-05', 'webp', 'brisee-par-ton-amour', 'Brisée par ton amour...', 5, 1, 1, 2, NULL, '2026-04-15 14:15:46'),
(59, 'one-piece-01', 'webp', 'one-piece', 'One Piece', 1, 1, 1, 2, NULL, '2026-04-16 08:58:42'),
(60, 'kingdom-01', 'webp', 'kingdom', 'Kingdom', 1, 1, 1, 2, NULL, '2026-04-16 11:54:42'),
(61, 'one-piece-02', 'webp', 'one-piece', 'One Piece', 2, NULL, NULL, NULL, NULL, '2026-04-16 12:16:18'),
(62, 'one-piece-03', 'webp', 'one-piece', 'One Piece', 3, NULL, NULL, NULL, NULL, '2026-04-16 12:17:41'),
(63, 'one-piece-04', 'webp', 'one-piece', 'One Piece', 4, NULL, NULL, NULL, NULL, '2026-04-16 12:17:48'),
(64, 'one-piece-05', 'webp', 'one-piece', 'One Piece', 5, NULL, NULL, NULL, NULL, '2026-04-16 12:17:54'),
(65, 'one-piece-06', 'webp', 'one-piece', 'One Piece', 6, NULL, NULL, NULL, NULL, '2026-04-16 12:18:00'),
(66, 'one-piece-07', 'webp', 'one-piece', 'One Piece', 7, NULL, NULL, NULL, NULL, '2026-04-16 12:18:08'),
(67, 'one-piece-08', 'webp', 'one-piece', 'One Piece', 8, NULL, NULL, NULL, NULL, '2026-04-16 12:18:20'),
(68, 'one-piece-09', 'webp', 'one-piece', 'One Piece', 9, NULL, NULL, NULL, NULL, '2026-04-16 12:18:35'),
(69, 'one-piece-10', 'webp', 'one-piece', 'One Piece', 10, NULL, NULL, NULL, NULL, '2026-04-16 12:18:40'),
(70, 'one-piece-11', 'webp', 'one-piece', 'One Piece', 11, NULL, NULL, NULL, NULL, '2026-04-16 19:36:29'),
(71, 'one-piece-12', 'webp', 'one-piece', 'One Piece', 12, NULL, NULL, NULL, NULL, '2026-04-16 19:36:37'),
(72, 'one-piece-13', 'webp', 'one-piece', 'One Piece', 13, NULL, NULL, NULL, 'z', '2026-04-16 19:36:45'),
(73, 'one-piece-14', 'webp', 'one-piece', 'One Piece', 14, NULL, NULL, NULL, NULL, '2026-04-16 19:36:52'),
(74, 'one-piece-15', 'webp', 'one-piece', 'One Piece', 15, NULL, NULL, NULL, NULL, '2026-04-16 19:36:58'),
(75, 'one-piece-16', 'webp', 'one-piece', 'One Piece', 16, NULL, NULL, NULL, NULL, '2026-04-16 19:37:09'),
(76, 'one-piece-17', 'webp', 'one-piece', 'One Piece', 17, NULL, NULL, NULL, 'd', '2026-04-16 19:37:23'),
(77, 'one-piece-18', 'webp', 'one-piece', 'One Piece', 18, NULL, NULL, NULL, NULL, '2026-04-16 19:37:29'),
(78, 'one-piece-19', 'webp', 'one-piece', 'One Piece', 19, NULL, NULL, NULL, 'ee', '2026-04-16 19:37:34'),
(79, 'one-piece-20', 'webp', 'one-piece', 'One Piece', 20, 1, 1, 2, NULL, '2026-04-16 19:37:41');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
