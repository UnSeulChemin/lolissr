-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 03 juin 2025 à 22:59
-- Version du serveur : 8.0.31
-- Version de PHP : 8.2.0

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
-- Structure de la table `anime`
--

DROP TABLE IF EXISTS `anime`;
CREATE TABLE IF NOT EXISTS `anime` (
  `id` int NOT NULL AUTO_INCREMENT,
  `anime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `origin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `season` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `episode` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `end` enum('Y','N','?') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `note` enum('1','2','3','4','5') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `anime`
--

INSERT INTO `anime` (`id`, `anime`, `origin`, `season`, `episode`, `end`, `note`, `created_at`) VALUES
(1, 'Eromanga Sensei', 'Light Novel', '1', '12', 'Y', '4', '2024-08-25 00:24:53'),
(2, 'Demon Slayer', 'Manga', '4', '8', 'N', '3', '2024-08-25 00:57:56'),
(3, 'Tokyo Ghoul', 'Seinen Manga', '3', '24', 'Y', '4', '2024-08-25 01:08:43'),
(4, 'Gimai Seikatsu', 'Light Novel', '1', '12', 'Y', '3', '2024-09-01 03:21:00');

-- --------------------------------------------------------

--
-- Structure de la table `chinese`
--

DROP TABLE IF EXISTS `chinese`;
CREATE TABLE IF NOT EXISTS `chinese` (
  `id` int NOT NULL AUTO_INCREMENT,
  `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `french` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `english` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `example` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chinese`
--

INSERT INTO `chinese` (`id`, `word`, `type`, `french`, `english`, `example`, `created_at`) VALUES
(1, '准确 zhǔn què', 'adjectif', 'exact / précis', 'accurate / exact / precise', '他的英语发音准确', '2024-08-05 06:17:55'),
(2, '简称 jiǎn chēng', 'nom / verbe', 'abréviation', 'abbreviation / short form', '少女萝莉简称少萝', '2024-08-06 05:19:36');

-- --------------------------------------------------------

--
-- Structure de la table `english`
--

DROP TABLE IF EXISTS `english`;
CREATE TABLE IF NOT EXISTS `english` (
  `id` int NOT NULL AUTO_INCREMENT,
  `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `french` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `example` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `english`
--

INSERT INTO `english` (`id`, `word`, `type`, `french`, `example`, `created_at`) VALUES
(1, 'essentially', 'adverbe', 'essentiellement', 'he was, essentially, a teacher.', '2024-08-20 06:00:40'),
(2, 'regarding', 'préposition', 'concernant', 'calling you regarding the e-mail.', '2024-09-23 23:59:50'),
(3, 'bootleg', 'adjectif / verbe', 'de contrebande', 'for selling bootlegs online.', '2024-09-24 00:04:34'),
(4, 'freebies', 'nom', 'cadeaux', 'is getting lots of freebies.', '2024-09-24 00:06:19');

-- --------------------------------------------------------

--
-- Structure de la table `french`
--

DROP TABLE IF EXISTS `french`;
CREATE TABLE IF NOT EXISTS `french` (
  `id` int NOT NULL AUTO_INCREMENT,
  `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `definition` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `example` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `french`
--

INSERT INTO `french` (`id`, `word`, `type`, `definition`, `example`, `created_at`) VALUES
(1, 'intéressant', 'adjectif / nom', 'qui retient l\'attention', 'un livre intéressant.', '2024-08-06 00:27:23'),
(2, 'primauté', 'nom féminin', 'donner la priorité au rendement', 'la primauté du spirituel.', '2024-09-16 03:06:07'),
(3, 'prude', 'adjectif / nom féminin', 'fait preuve d\'une vertu sévère', 'jeanne est quelqu\'un de prude.', '2024-08-06 00:54:19'),
(4, 'factice', 'adjectif / nom masculin', 'qui est faux, imité', 'les chocs étaient factices.', '2024-08-06 00:54:23'),
(5, 'parcimonie', 'nom féminin', 'épargne, économie minutieuse', 'faire des éloges avec parcimonie.', '2024-08-06 00:54:36'),
(6, 'explicite', 'adjectif', 'suffisamment clair et précis', 'une information explicite.', '2024-08-06 00:54:49'),
(7, 'implicite', 'adjectif', 'ce qui n\'est pas clairement exprimé', 'une condition implicite.', '2024-08-13 06:37:19'),
(8, 'altruiste', 'adjectif / nom', 's\'intéresser et à se dévouer à autrui', 'elle est particulièrement altruiste.', '2024-08-25 00:59:31'),
(9, 'protagoniste', 'nom', 'personne qui joue le rôle principal', 'vous pouvez voir le protagoniste.', '2024-09-13 21:04:54'),
(10, 'individualiste', 'adjectif / nom', 'qui donne la primauté à l\'individu', 'était un individualiste absolu.', '2024-09-03 22:07:31'),
(11, 'hameau', 'nom masculin', 'petit groupe d\'habitations isolées', 'dans de petits hameaux calmes.', '2024-09-08 12:17:34'),
(12, 'rêche', 'adjectif', 'rude de caractère', 'avoir un côté un peu rêche.', '2024-09-08 12:29:59'),
(13, 'aduler', 'verbe transitif', 'combler de louanges', 'elle était adulée de son mari.', '2024-09-08 12:40:37'),
(14, 'inadvertance', 'nom féminin', 'faute commise par inattention', 'une erreur d\'inadvertance.', '2024-09-09 23:07:54'),
(15, 'rizière', 'nom féminin', 'terrain où l\'on cultive le riz', 'les rizières passent à côté de nous.', '2024-09-09 23:10:05'),
(16, 'fortuit', 'adjectif', 'imprévu, qui arrive par hasard', 'une rencontre fortuite.', '2024-09-09 23:12:25'),
(17, 'décrépitude', 'nom féminin', 'déchéance, décadence', 'la décrépitude d\'une civilisation.', '2024-09-09 23:13:20'),
(18, 'revêche', 'adjectif', 'qui manifeste un mauvais caractère', 'l\'esprit revêche de cette femme.', '2024-09-13 05:32:16'),
(19, 'antagoniste', 'adjectif / nom', 'opposé, rival', 'son professeur est son antagoniste. ', '2024-09-13 21:03:53'),
(20, 'vertu', 'nom féminin', 'faire le bien et à fuir le mal', 'les vertus des plantes médicinales.', '2024-09-16 03:07:33'),
(21, 'inculte', 'adjectif', 'qui n\'est pas cultivé', 'je suis inculte.', '2024-09-17 00:55:34'),
(22, ' foutoir', 'nom masculin', 'grand désordre', 'c\'était un grand foutoir.', '2024-09-17 19:24:24'),
(23, 'reluisant', 'adjectif', 'peu brillant, peu prometteur', 'un avenir peu reluisant.', '2024-09-29 00:26:48'),
(24, ' lucratif', 'adjectif', 'qui procure un gain, des bénéfices', 'travail lucratif.', '2024-10-04 00:21:56'),
(25, ' échéancier', 'nom masculin', 'registre des paiements à effectuer', 'réaliser un échéancier de paiement.', '2024-10-05 01:04:15'),
(26, 'taiseux', 'nom / adjectif', 'qui par nature ne parle guère', 'les taiseux sont des personnes.', '2024-10-18 03:12:38'),
(27, 'mainmise', 'nom féminin', 'action de s\'emparer.', 'la mainmise d\'un pays.', '2024-10-19 22:47:49'),
(28, ' candide', 'adjectif', 'qui a de la candeur', 'air candide.', '2024-10-20 07:08:02'),
(29, 'adultère', 'nom masculin / adjectif', 'rapports sexuels autre que conjoint', 'un époux adultère.', '2024-10-20 23:34:15'),
(30, 'hébété', 'adjectif / nom', 'être abasourdi, ahuri, stupide', 'le choc l\'avait hébété.', '2024-10-25 23:26:35'),
(31, 'tacite', 'adjectif', 'non exprimé, sous-entendu.', 'un consentement tacite.', '2024-10-25 23:29:41'),
(32, 'huppé', 'adjectif', 'de haut rang, spécialement riche', 'des gens très huppés.', '2024-10-25 23:37:26'),
(33, 'lubie', 'nom féminin', 'Idée, envie capricieuse', 'c\'est sa dernière lubie.', '2024-10-25 23:38:49'),
(34, 'insipide (1)', 'adjectif', 'qui manque d’intérêt.', 'je trouve cette comédie insipide.', '2024-10-27 04:58:49'),
(35, 'insipide (2)', 'adjectif', 'qui n\'a aucune saveur, aucun goût', 'un breuvage insipide.', '2024-10-27 04:59:15'),
(36, 'misandre', 'adjectif / nom', 'qui rejette les hommes', 'ne seriez-vous pas misandre ?', '2024-10-27 05:08:44'),
(37, 'misogyne', 'adjectif / nom', 'qui rejette les femmes', 'ne seriez-vous pas misogyne ?', '2024-10-27 05:10:01'),
(38, 'torride', 'adjectif', 'où la chaleur est extrême', 'un climat torride.', '2024-10-28 21:19:13'),
(39, 'lauréat', 'nom / adjectif', 'remporté un prix dans un concours', 'les lauréats du prix Nobel.', '2024-10-28 22:13:09'),
(40, 'passade', 'nom féminin', 'liaison amoureuse de courte durée', 'n\'aura été qu\'une passade.', '2024-10-28 22:14:22'),
(41, 'jaser', 'verbe transitif', 'babiller sans arrêt', 'ils sont toujours à jaser.', '2024-10-28 22:18:15'),
(42, 'ahuri', 'adjectif', 'être stupéfait, rendu stupide', 'avoir l\'air ahuri.', '2024-10-29 05:15:24'),
(43, 'succinctement', 'adverbe', 'd\'une manière succincte', 'exprimer succinctement sa pensée.', '2025-01-27 05:57:14'),
(44, 'limpide', 'adjectif', 'parfaitement clair', 'explication limpide.', '2025-01-28 19:11:43'),
(45, 'odieux', 'adjectif', 'très désagréable', 'un enfant odieux.', '2025-01-29 07:55:57'),
(46, 'anodin', 'adjectif', 'sans importance, insignifiant', 'des propos anodins.', '2025-01-29 07:57:22'),
(47, 'éthylisme', 'nom masculin', 'alcoolisme', 'accentués par un éthylisme.', '2025-01-31 02:13:19'),
(48, 'sédatif', 'adjectif / nom masculin', 'remède calmant', 'un sédatif sur une plaie.', '2025-01-31 02:16:44'),
(49, 'kérosène', 'nom masculin', 'pétrolier des d\'avions', 'brûler du kérosène.', '2025-01-31 02:19:37'),
(50, 'amertume', 'nom féminin', 'saveur amère', 'l\'amertume des endives.', '2025-01-31 04:16:43'),
(51, 'estival', 'adjectif', 'propre à l\'été, d\'été', 'en cette période estivale.', '2025-01-31 04:44:00'),
(52, 'maussade', 'adjectif', 'qui inspire de l\'ennui', 'ciel, temps maussade.', '2025-01-31 04:48:22'),
(53, 'antiquaire', 'nom', 'marchand de décoration anciens', 'l\'antiquaire de 54 ans.', '2025-01-31 05:46:26'),
(59, 'indolence', 'nom féminin', 'éviter l\'effort physique ou moral', 'fait preuve d\'une indolence.', '2025-02-01 22:23:14'),
(60, 'rustre', 'nom / adjectif', 'individu grossier et brutal', 'il a l\'air rustre.', '2025-02-01 22:24:32');

-- --------------------------------------------------------

--
-- Structure de la table `goddess`
--

DROP TABLE IF EXISTS `goddess`;
CREATE TABLE IF NOT EXISTS `goddess` (
  `id` int NOT NULL AUTO_INCREMENT,
  `thumbnail` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `extension` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `character` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `serie` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rarity` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `note` enum('1','2','3','4','5') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `set` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `goddess`
--

INSERT INTO `goddess` (`id`, `thumbnail`, `extension`, `character`, `serie`, `rarity`, `note`, `set`, `date`, `created_at`) VALUES
(1, 'NS-02-M12[005](PR)', 'png', 'Rize Tedeza', 'Is the Order a Rabbit?', 'PR', '5', 'NS-02-M12', '2024-03-22', '2024-08-30 05:25:42'),
(2, 'NS-02-M12[003](PR)', 'png', 'Nico Yazawa', 'Love Live!', 'PR', '5', 'NS-02-M12', '2024-03-22', '2024-09-16 21:41:43'),
(3, 'NS-02-M11[005](PR)', 'png', 'Sora Kasugano', 'Yosuga no Sora', 'PR', '5', 'NS-02-M11', '2013-12-18\n', '2024-09-16 21:45:27'),
(4, 'NS-02-M10[011](PR)', 'png', 'Hitori Gotou', 'Bocchi the Rock!', 'PR', '5', 'NS-02-M10', '2023-09-01', '2024-09-16 21:51:10'),
(5, 'NS-02-M10[009](PR)', 'png', 'Rinze Morino', 'The Idolm@ster', 'PR', '5', 'NS-02-M10', '2023-09-01', '2024-09-16 21:54:17'),
(6, 'NS-02-M10[008](PR)', 'png', 'Madoka Higuchi', 'The Idolm@ster', 'PR', '5', 'NS-02-M10', '2023-09-01', '2024-09-16 21:57:01'),
(7, 'NS-02-M10[007](PR)', 'png', 'Mejiro McQueen', 'Uma Musume Pretty Derby', 'PR', '5', 'NS-02-M10', '2023-09-01', '2024-09-16 22:10:27'),
(8, 'NS-02-M10[006](PR)', 'png', 'Tokai Teio', 'Uma Musume Pretty Derby', 'PR', '5', 'NS-02-M10', '2023-09-01', '2024-09-16 22:14:02'),
(9, 'NS-02-M10[003](PR)', 'png', 'Lumine', 'Genshin Impact', 'PR', '5', 'NS-02-M10', '2023-09-01', '2024-09-16 22:20:03'),
(10, 'NS-02-M10[002](PR)', 'png', 'Hu Tao', 'Genshin Impact', 'PR', '5', 'NS-02-M10', '2023-09-01', '2024-09-16 22:34:04'),
(11, 'NS-02-M09[012](PR)', 'png', 'Rice Shower', 'Uma Musume Pretty Derby', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:07:06'),
(12, 'NS-02-M09[010](PR)', 'png', 'Laffey', 'Azur Lane', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:09:46'),
(13, 'NS-02-M09[009](PR)', 'png', 'Unicorn', 'Azur Lane', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:12:51'),
(14, 'NS-02-M09[008](PR)', 'png', 'Atago', 'Azur Lane', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:16:06'),
(15, 'NS-02-M09[007](PR)', 'png', 'Kamisato Ayaka', 'Genshin Impact', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:21:19'),
(16, 'NS-02-M09[006](PR)', 'png', 'Miku Nakano', 'The Quintessential Quintuplets', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:35:28'),
(17, 'NS-02-M09[005](PR)', 'png', 'Rikka Takarada', 'SSSS.GRIDMAN', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:38:26'),
(18, 'NS-02-M09[001](PR)', 'png', 'Yukihana Lamy', 'Hololive', 'PR', '5', 'NS-02-M09', '2023-06-01', '2024-09-16 23:40:39'),
(19, 'NS-02-M08[009](PR)', 'png', 'Yoshiko Tsushima', 'Love Live!', 'PR', '5', 'NS-02-M08', '2023-02-09', '2024-09-16 23:45:21'),
(20, 'NS-02-M08[006](PR)', 'png', 'Chiyoko Sonoda', 'The Idolm@ster', 'PR', '5', 'NS-02-M08', '2023-02-09', '2024-09-16 23:47:45'),
(21, 'NS-02-M08[004](PR)', 'png', 'Alisa Mikhailovna Kujou', 'Alya, Who Sits Next to Me, Sometimes Whispers Sweet Nothings in Russian', 'PR', '5', 'NS-02-M08', '2023-02-09', '2024-09-16 23:49:36'),
(22, 'NS-05-M06[006](PR)', 'png', 'Saratoga', 'Azur Lane', 'PR', '4', 'NS-05-M06', '2022-06-14', '2024-10-02 21:13:24'),
(23, 'NS-05-M06[003](PR)', 'png', 'Klee', 'Genshin Impact', 'PR', '3', 'NS-05-M06', '2022-06-14', '2024-10-02 21:16:36'),
(24, 'NS-05-M06[002](PR)', 'png', 'Qiqi', 'Genshin Impact', 'PR', '4', 'NS-05-M06', '2022-06-14', '2024-10-02 21:18:10'),
(25, 'NS-02-M07[008](PR)', 'png', 'Elaina', 'The Journey of Elaina', 'PR', '3', 'NS-02-M07', '2022-05-31', '2024-10-02 21:40:40'),
(26, 'NS-10-M03[018](PR)', 'png', 'Tenka Osaki', 'The Idolm@ster', 'PR', '2', 'NS-10-M03', '2022-05-04', '2024-10-02 21:45:39'),
(27, 'NS-10-M03[017](PR)', 'png', 'Rin Shibuya', 'The Idolm@ster', 'PR', '5', 'NS-10-M03', '2022-05-04', '2024-10-02 21:48:14'),
(28, 'NS-10-M03[016](PR)', 'png', 'Rinze Morino', 'The Idolm@ster', 'PR', '3', 'NS-10-M03', '2022-05-04', '2024-10-02 21:53:43'),
(29, 'NS-10-M03[015](PR)', 'png', 'Misato Kurihara', 'Didn\'t I Say to Make My Abilities Average in the Next Life?!', 'PR', '5', 'NS-10-M03', '2022-05-04', '2024-10-02 21:55:23'),
(30, 'NS-10-M03[014](PR)', 'png', 'Skadi', 'Arknights', 'PR', '4', 'NS-10-M03', '2022-05-04', '2024-10-02 21:56:46'),
(31, 'NS-10-M03[013](PR)', 'png', 'Angelina', 'Arknights', 'PR', '5', 'NS-10-M03', '2022-05-04', '2024-10-02 22:00:37'),
(32, 'NS-10-M03[012](PR)', 'png', 'Noshiro', 'Azur Lane', 'PR', '4', 'NS-10-M03', '2022-05-04', '2024-10-02 22:01:51'),
(33, 'NS-10-M03[011](PR)', 'png', 'Chiyo Sakura', 'Chiyo Sakura', 'PR', '5', 'NS-10-M03', '2022-05-04', '2024-10-02 22:03:16'),
(34, 'NS-10-M03[010](PR)', 'png', 'Mahiru Shiina', 'The Angel Next Door Spoils Me Rotten', 'PR', '5', 'NS-10-M03', '2022-05-04', '2024-10-02 22:06:04'),
(35, 'NS-10-M03[007](PR)', 'png', 'Lumine', 'Genshin Impact', 'PR', '4', 'NS-10-M03', '2022-05-04', '2024-10-02 22:07:36'),
(36, 'NS-10-M03[006](PR)', 'png', 'Yui Yuigahama', 'My Youth Romantic Comedy Is Wrong As I Expected', 'PR', '5', 'NS-10-M03', '2022-05-04', '2024-10-02 22:09:13'),
(37, 'NS-10-M03[004](PR)', 'png', 'Ai Enma', 'Hell Girl', 'PR', '4', 'NS-10-M03', '2022-05-04', '2024-10-02 22:12:05'),
(38, 'NS-10-M03[003](PR)', 'png', 'Manaka Mukaido', 'Nagi-Asu: A Lull in the Sea', 'PR', '3', 'NS-10-M03', '2022-05-04', '2024-10-02 22:13:26'),
(39, 'NS-10-M03[002](PR)', 'png', 'Akane Shinjo', 'SSSS.GRIDMAN', 'PR', '5', 'NS-10-M03', '2022-05-04', '2024-10-02 22:14:36'),
(40, 'NS-02-M06[009](PR)', 'png', 'Miqo\'te Race', 'Granblue Fantasy', 'PR', '5', 'NS-02-M06', '2022-04-18', '2024-10-02 22:31:42'),
(41, 'NS-02-M06[007](PR)', 'png', 'Chocola', 'Nekopara', 'PR', '5', 'NS-02-M06', '2022-04-18', '2024-10-02 22:33:25'),
(42, 'NS-02-M06[006](PR)', 'png', 'Satono Diamond', 'Uma Musume Pretty Derby', 'PR', '5', 'NS-02-M06', '2022-04-18', '2024-10-02 22:35:13'),
(43, 'NS-05-M05[017](PR)', 'png', 'Hamakaze', 'Kantai Collection', 'PR', '4', 'NS-05-M05', '2022-03-22', '2024-10-02 22:41:17'),
(44, 'NS-05-M05[010](PR)', 'png', 'Kaede Takagaki', 'The Idolm@ster', 'PR', '3', 'NS-05-M05', '2022-03-22', '2024-10-02 22:44:32'),
(45, 'NS-05-M05[009](PR)', 'png', 'Flandre Scarlet', 'Touhou Project', 'PR', '3', 'NS-05-M05', '2022-03-22', '2024-10-02 22:46:00'),
(46, 'NS-05-M05[005](PR)', 'png', 'Shizuru Hoshino', 'Princess Connect Re:Dive', 'PR', '4', 'NS-05-M05', '2022-03-22', '2024-10-02 22:49:03'),
(47, 'NS-02-M04[012](PR)', 'png', 'Gold Ship', 'Uma Musume Pretty Derby', 'PR', '3', 'NS-02-M04', '2021-09-23', '2024-10-02 22:51:58'),
(48, 'NS-02-M04[011](PR)', 'png', 'Rin Tohsaka', 'Fate Series', 'PR', '4', 'NS-02-M04', '2021-09-23', '2024-10-02 22:54:08'),
(49, 'NS-02-M04[009](PR)', 'png', 'Chino Kafuu', 'Is the Order a Rabbit?', 'PR', '4', 'NS-02-M04', '2021-09-23', '2024-10-02 22:57:02'),
(50, 'NS-02-M04[007](PR)', 'png', 'Yukina Minato', 'BanG Dream!', 'PR', '3', 'NS-02-M04', '2021-09-23', '2024-10-02 22:58:38'),
(51, 'NS-02-M04[004](PR)', 'png', 'Honami Ichinose', 'Classroom of the Elite', 'PR', '3', 'NS-02-M04', '2021-09-23', '2024-10-02 23:00:14'),
(52, 'NS-02-M04[001](PR)', 'png', 'Tenka Yatogami', 'Date A Live', 'PR', '2', 'NS-02-M04', '2021-09-23', '2024-10-02 23:09:54'),
(53, 'NS-10-M01[009](PR)', 'png', '1WS2000', 'Girls\' Frontline', 'PR', '2', 'NS-10-M01', '2021-08-12', '2024-10-02 23:12:47'),
(54, 'NS-02-M03[004](PR)', 'png', 'Kokoro Natsume (Kokkoro)', 'Princess Connect Re:Dive', 'PR', '4', 'NS-02-M03', '2021-07-12', '2024-10-02 23:16:02'),
(55, 'NS-02-M02[016](PR)', 'png', 'Rin', 'Muse Dash', 'PR', '3', 'NS-02-M02', '2021-04-20', '2024-10-02 23:21:16'),
(56, 'NS-02-M02[009](PR)', 'png', 'Vanilla', 'Nekopara', 'PR', '3', 'NS-02-M02', '2021-04-20', '2024-10-02 23:22:39'),
(57, 'NS-02-M02[008](PR)', 'png', 'Chocola', 'Nekopara', 'PR', '4', 'NS-02-M02', '2021-04-20', '2024-10-02 23:23:39'),
(58, 'NS-02-M05[016](PR)', 'png', 'Juliet Persia', 'Boarding School Juliet', 'PR', '3', 'NS-02-M05', '2022-01-01', '2024-10-02 23:29:12'),
(59, 'NS-02-M05[010](PR)', 'png', 'Lewis Gun', 'Girls\' Frontline', 'PR', '5', 'NS-02-M05', '2021-01-01', '2024-10-02 23:31:12'),
(60, 'NS-10-M02[004](SP)', 'png', 'Yoimiya', 'Genshin Impact', 'SP', '4', 'NS-10-M02', '2022-01-22', '2024-10-02 23:37:16'),
(61, 'NS-10-M02[005](SP)', 'png', 'Keqing', 'Genshin Impact', 'SP', '5', 'NS-10-M02', '2022-01-22', '2024-10-02 23:39:15'),
(62, 'NS-10-M04[015](PR)', 'png', 'Akane Shinjo', 'SSSS.GRIDMAN', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(63, 'NS-10-M04[014](PR)', 'png', 'Yui Yuigahama', 'My Youth Romantic Comedy Is Wrong As I Expected', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:47:43'),
(64, 'NS-10-M04[013](PR)', 'png', 'Yukino Yukinoshita', 'My Youth Romantic Comedy Is Wrong As I Expected', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-03 01:06:36'),
(65, 'NS-10-M04[010](PR)', 'png', 'Tomoyo Daidouji', 'Cardcaptor Sakura', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-03 01:07:52'),
(66, 'NS-10-M04[008](PR)', 'png', 'Qiqi', 'Genshin Impact', 'CP', '5', 'NS-10-M04', '2022-11-07\r\n', '2024-10-03 01:09:09'),
(67, 'NS-10-M04[002](PR)', 'png', 'Madoka Kaname', 'Puella Magi Madoka Magica', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-03 01:11:50'),
(68, 'NS-02-M12[001](SER)', 'png', 'Red Camellia (Nakiri Ayame)', 'Hololive', 'SER', '5', 'NS-02-M12', '2024-03-22', '2024-10-03 01:24:56'),
(69, 'NS-11[003](SER)', 'png', 'White Magnolia', 'Original', 'SER', '5', 'NS-11', '2023-11-27', '2024-10-03 01:31:06'),
(70, 'NS-11[002](SER)', 'png', 'Yellow Sunflower', 'Original', 'SER', '5', 'NS-11', '2023-11-27', '2024-10-03 01:32:33'),
(71, 'NS-11[001](SER)', 'png', 'Red Freesia', 'Original', 'SER', '5', 'NS-11', '2023-11-27', '2024-10-03 01:34:10'),
(72, 'NS-02-M10[005](SER)', 'png', 'Purple Lily (Emori Miku)', 'Original', 'SER', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 01:39:33'),
(73, 'NS-02-M10[003](SER)', 'png', 'Blue Rose', 'Original', 'SER', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 01:41:35'),
(74, 'NS-02-M10[002](SER)', 'png', 'Pink Rose', 'Original', 'SER', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 01:43:47'),
(75, 'NS-02-M10[001](SER)', 'png', 'Red Camellia (Inui Toko)', 'Original', 'SER', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 01:46:58'),
(76, 'NS-10[003](SER)', 'png', 'Pink Rose', 'Original', 'SER', '5', 'NS-10', '2023-08-18', '2024-10-03 02:35:30'),
(77, 'NS-10[002](SER)', 'png', 'Red Camelia', 'Original', 'SER', '5', 'NS-10', '2023-08-18', '2024-10-03 02:37:10'),
(78, 'NS-10[001](SER)', 'png', 'Red Rose', 'Original', 'SER', '5', 'NS-10', '2023-08-18', '2024-10-03 02:39:26'),
(79, 'NS-02-M09[006](SER)', 'png', 'Rose Red Lily', 'Original', 'SER', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 02:49:35'),
(80, 'NS-02-M09[005](SER)', 'png', 'Red Camellia', 'Original', 'SER', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 02:50:41'),
(81, 'NS-02-M09[004](SER)', 'png', 'Purple Peony', 'Original', 'SER', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 02:52:36'),
(82, 'NS-02-M09[003](SER)', 'png', 'Red Rose', 'Original', 'SER', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 03:04:17'),
(83, 'NS-02-M09[002](SER)', 'png', 'Purple Camellia', 'Original', 'SER', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 03:06:12'),
(84, 'NS-02-M09[001](SER)', 'png', 'Red Peony', 'Original', 'SER', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 03:08:26'),
(85, 'NS-09[001](SER)', 'png', 'Red Rose', 'Original', 'SER', '5', 'NS-09', '2023-04-01', '2024-10-03 03:19:08'),
(86, 'NS-09[002](SER)', 'png', 'Blue Rose', 'Original', 'SER', '5', 'NS-09', '2023-04-01', '2024-10-03 03:20:23'),
(87, 'NS-09[003](SER)', 'png', 'Purple Rose', 'Original', 'SER', '5', 'NS-09', '2023-04-01', '2024-10-03 03:21:53'),
(88, 'NS-05-M07[014](MR)', 'png', 'Keqing', 'Genshin Impact', 'MR', '5', 'NS-05-M07', '2023-06-02', '2024-10-03 03:28:54'),
(89, 'NS-05-M06[026](MR)', 'png', 'Kotonoha Aoi', 'Vocaloid', 'MR', '5', 'NS-05-M06', '2022-06-14', '2024-10-03 03:33:22'),
(90, 'NS-05-M06[025](MR)', 'png', 'Kotonoha Akane', 'Vocaloid', 'MR', '5', 'NS-05-M06', '2022-06-14', '2024-10-03 03:35:42'),
(91, 'NS-05-M06[014](MR)', 'png', 'Smart Falcon', 'Uma Musume Pretty Derby', 'MR', '5', 'NS-05-M06', '2022-06-14', '2024-10-03 03:38:27'),
(92, 'NS-05-M05[020](MR)', 'png', 'Raiden Shogun', 'Genshin Impact', 'MR', '5', 'NS-05-M05', '2022-03-22', '2024-10-03 03:42:39'),
(93, 'NS-10-M02[034](MR)', 'png', 'Hiei', 'Azur Lane', 'MR', '5', 'NS-10-M02', '2022-01-22', '2024-10-03 03:50:29'),
(94, 'NS-10-M02[032](MR)', 'png', 'Dusk', 'Arknights', 'MR', '5', 'NS-10-M02', '2022-01-22', '2024-10-03 03:52:01'),
(95, 'NS-10-M02[026](MR)', 'png', 'Reimu Hakurei', 'Touhou Project', 'MR', '5', 'NS-10-M02', '2022-01-22', '2024-10-03 03:53:53'),
(96, 'NS-10-M02[025](MR)', 'png', 'Pecorine', 'Princess Connect Re:Dive', 'MR', '5', 'NS-10-M02', '2022-01-22', '2024-10-03 03:57:10'),
(97, 'NS-02-M10[008](PTR)', 'png', 'Kafka', 'Honkai: Star Rail', 'PTR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 04:18:18'),
(98, 'NS-02-M10[007](PTR)', 'png', 'Silver Wolf', 'Honkai: Star Rail', 'PTR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 04:24:02'),
(99, 'NS-02-M10[005](PTR)', 'png', 'Ai Hoshino', 'Oshi No Ko (My idol\'s Child)', 'PTR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 04:40:16'),
(100, 'NS-02-M10[004](PTR)', 'png', 'Toki Asuma', 'Blue Archive', 'PTR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 04:42:32'),
(101, 'NS-02-M10[003](PTR)', 'png', 'Shinobu Kochou', 'Demon Slayer', 'PTR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 04:43:49'),
(102, 'NS-02-M10[001](PTR)', 'png', 'Sakura Kinomoto', 'Cardcaptor Sakura', 'PTR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 04:45:36'),
(103, 'NS-02-M09[008](PTR)', 'png', 'Mitsuri Kanroji', 'Demon Slayer', 'PTR', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 04:49:24'),
(104, 'NS-02-M09[007](PTR)', 'png', 'Dehya', 'Genshin Impact', 'PTR', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 04:50:40'),
(105, 'NS-02-M09[002](PTR)', 'png', 'Nijika Ijichi', 'Bocchi the Rock!', 'PTR', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 04:52:11'),
(106, 'NS-02-M09[001](PTR)', 'png', 'Hitori Gotou', 'Bocchi the Rock!', 'PTR', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 04:53:32'),
(107, 'NS-05-M06[028](PTR)', 'png', 'Rikka Takanashi', 'Love, Chunibyo & Other Delusions!', 'PTR', '5', 'NS-05-M06', '2022-06-14', '2024-10-03 05:03:50'),
(108, 'NS-05-M06[037](PTR)', 'png', 'Sorakado Ao', 'Summer Pockets', 'PTR', '5', 'NS-05-M06', '2022-06-14', '2024-10-03 05:05:28'),
(109, 'NS-02-M07[006](PTR)', 'png', 'Taiga Aisaka', 'Toradora!', 'PTR', '5', 'NS-02-M07', '2022-05-31', '2024-10-03 05:12:41'),
(110, 'NS-02-M06[008](PTR)', 'png', 'Mejiro McQueen', 'Uma Musume Pretty Derby\r\n', 'PTR', '5', 'NS-02-M06', '2022-04-18', '2024-10-03 05:15:51'),
(111, 'NS-05-M05[044](PTR)', 'png', 'Kanna Kamui', 'Miss Kobayashi\'s Dragon Maid', 'PTR', '5', 'NS-05-M05', '2022-03-22', '2024-10-03 05:19:00'),
(112, 'NS-02-M10[006](UR)', 'png', 'Kirara', 'Genshin Impact', 'UR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 05:23:08'),
(113, 'NS-02-M10[004](UR)', 'png', 'Stella', 'Honkai: Star Rail', 'UR', '5', 'NS-02-M10', '2023-09-01', '2024-10-03 05:24:33'),
(114, 'NS-02-M09[006](UR)', 'png', 'Echidna', 'Re:Zero', 'UR', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 05:28:51'),
(115, 'NS-02-M09[005](UR)', 'png', 'Hitori Gotou', 'Bocchi the Rock!', 'UR', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 05:30:48'),
(116, 'NS-02-M09[001](UR)', 'png', 'Shiroko Sunaookami', 'Blue Archive', 'UR', '5', 'NS-02-M09', '2023-06-01', '2024-10-03 06:00:28'),
(117, 'NS-02-M11[003](MSR)', 'png', 'Ganyu', 'Genshin Impact', 'MSR', '5', 'NS-02-M11', '2023-12-18', '2024-10-03 06:04:46'),
(118, 'NS-02-M11[004](MSR)', 'png', 'Hitori Gotou', 'Bocchi the Rock!', 'MSR', '5', 'NS-02-M11', '2023-12-18', '2024-10-03 06:07:09'),
(119, 'NS-02-M12[009](PTR)', 'png', 'Rikka Takanashi', 'Love, Chunibyo & Other Delusions!', 'PTR', '5', 'NS-02-M12', '2024-03-22', '2024-10-09 18:23:25'),
(120, 'NS-02-M04[010](PR)', 'png', 'Yukino Yukinoshita', 'My Youth Romantic Comedy Is Wrong As I Expected', 'PR', '5', 'NS-02-M04', '2021-09-23', '2024-10-02 23:00:14'),
(121, 'NS-02-M04[005](PR)', 'png', 'Kei Shirogane', 'Kaguya-sama: Love is War', 'PR', '5', 'NS-02-M04', '2021-09-23', '2024-10-02 23:00:14'),
(122, 'NS-02-M07[006](PR)', 'png', 'Marisa Kirisame', 'Touhou Project', 'PR', '5', 'NS-02-M07', '2022-05-31', '2024-10-02 21:40:40'),
(123, 'NS-02-M07[005](PR)', 'png', 'Louise', 'Zero no tsukaima', 'PR', '5', 'NS-02-M07', '2022-05-31', '2024-10-02 21:40:40'),
(124, 'NS-02-M08[008](PR)', 'png', 'Umi Sonoda', 'Love Live!', 'PR', '5', 'NS-02-M08', '2023-02-09', '2024-09-16 23:49:36'),
(125, 'NS-02-M12[012](PR)', 'png', 'Tohru', 'Miss Kobayashi\'s Dragon Maid', 'PR', '4', 'NS-02-M12', '2024-03-22', '2024-09-16 21:41:43'),
(126, 'NS-02-M12[004](PR)', 'png', 'Junko Enoshima', 'Danganronpa', 'PR', '4', 'NS-02-M12', '2024-03-22', '2024-09-16 21:41:43'),
(127, 'NS-10-M04[001](PR)', 'png', 'Akemi Homura', 'Puella Magi Madoka Magica', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(128, 'NS-10-M04[004](PR)', 'png', 'Kuroko Shirai', 'A Certain Magical Index (Toaru Kagaku no Railgun)', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(129, 'NS-10-M04[005](PR)', 'png', 'Raiden Shogun', 'Genshin Impact', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(130, 'NS-10-M04[006](PR)', 'png', 'Yae Miko', 'Genshin Impact', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(131, 'NS-10-M04[007](PR)', 'png', 'Klee', 'Genshin Impact', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(132, 'NS-10-M04[009](PR)', 'png', 'Sakura Kinomoto', 'Cardcaptor Sakura', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(133, 'NS-10-M04[012](PR)', 'png', 'Chika Fujiwara', 'Kaguya-sama: Love is War', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(134, 'NS-10-M04[016](PR)', 'png', 'Rikka Takarada', 'SSSS.GRIDMAN', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20'),
(135, 'NS-10-M04[017](PR)', 'png', 'Rem', 'Re:Zero', 'CP', '5', 'NS-10-M04', '2022-11-07', '2024-10-02 23:46:20');

-- --------------------------------------------------------

--
-- Structure de la table `manga`
--

DROP TABLE IF EXISTS `manga`;
CREATE TABLE IF NOT EXISTS `manga` (
  `id` int NOT NULL AUTO_INCREMENT,
  `manga` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `house` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tome` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `next` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `end` enum('Y','N','?') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `manga`
--

INSERT INTO `manga` (`id`, `manga`, `house`, `tome`, `next`, `end`, `created_at`) VALUES
(1, 'Boy\'s Abyss', 'Kana', '14', '11/07/2025', 'N', '2024-09-12 03:48:23'),
(2, 'Tokyo Ghoul L', 'Glénat', '1', 'N', 'Y', '2024-09-13 00:53:28'),
(3, 'Tokyo Ghoul:re L', 'Glénat', '1', 'N', 'Y', '2024-09-13 00:55:16'),
(4, 'Rave', 'Glénat', '7', '02/07/2025', 'N', '2024-09-13 01:00:01'),
(5, 'Partners 2.0', 'Kurokawa', '8', '03/07/2025', 'N', '2024-09-13 01:04:13'),
(6, 'Brisée par ton amour...', 'Meian', '5', 'N', 'Y', '2024-10-17 01:37:26'),
(7, 'Children', 'Omaké Books', '2', 'N', 'Y', '2024-10-21 00:12:33'),
(8, 'No Control!', 'Delcourt/Tonkam', '1', 'N', 'Y', '2024-10-27 18:01:59'),
(9, 'Elle n\'est rien qu\'à moi', 'Soleil', '5', '20/08/2025', 'N', '2024-10-28 07:24:07'),
(10, 'Nozokiana L', 'Kurokawa', '2', 'N', 'Y', '2024-11-25 06:18:59'),
(11, 'One Piece', 'Glénat', '12', 'B', 'N', '2025-06-03 22:46:31');

-- --------------------------------------------------------

--
-- Structure de la table `nendoroid`
--

DROP TABLE IF EXISTS `nendoroid`;
CREATE TABLE IF NOT EXISTS `nendoroid` (
  `id` int NOT NULL AUTO_INCREMENT,
  `thumbnail` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `extension` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `serie` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` int NOT NULL,
  `date` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `stock` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `obtained` enum('Y','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N',
  `estimated` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `love` enum('Y','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N',
  `hololive` enum('Y','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N',
  `link` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `nendoroid`
--

INSERT INTO `nendoroid` (`id`, `thumbnail`, `extension`, `serie`, `brand`, `price`, `date`, `stock`, `obtained`, `estimated`, `love`, `hololive`, `link`, `created_at`) VALUES
(1, 'FIGURE-1596', 'jpg', 'Yu-Gi-Oh!', 'Good Smile Company', 41, 'Jun-2024', 'Order Closed', 'Y', '/', 'Y', 'N', 'https://www.amiami.com/eng/detail/?scode=FIGURE-165059', '2024-08-12 19:28:19'),
(2, 'FIGURE-2301', 'jpg', 'Hololive', 'Good Smile Company', 38, 'Jul-2024', 'Order Closed', 'Y', '/', 'N', 'Y', 'https://www.amiami.com/eng/detail/?scode=FIGURE-167209', '2024-08-12 19:39:09'),
(3, 'FIGURE-2534', 'jpg', 'Hatsune Miku', 'Good Smile Company', 54, 'Dec-2024', 'Available', 'Y', '/', 'Y', 'N', 'https://www.amiami.com/eng/detail/?scode=FIGURE-172561', '2024-08-23 06:42:09'),
(4, 'FIGURE-2454', 'jpg', 'Date A Live', 'Good Smile Company', 37, 'Sep-2024', 'Order Closed', 'Y', '/', 'Y', 'N', 'https://www.amiami.com/eng/detail/?scode=FIGURE-168587', '2024-08-23 06:42:09'),
(5, 'FIGURE-2523', 'jpg', 'Toradora!', 'Good Smile Company', 36, 'Nov-2024', 'Available', 'Y', '/', 'Y', 'N', 'https://www.amiami.com/eng/detail/?scode=FIGURE-172134', '2024-08-23 06:42:09'),
(6, 'FIGURE-1777', 'jpg', 'Hatsune Miku', 'Good Smile Company', 111, '?', 'Available', 'Y', '/', 'Y', 'N', 'https://www.amazon.fr/dp/B09QC5GD2K', '2024-08-23 06:42:09'),
(7, 'FIGURE-2222', 'jpg', 'Hatsune Miku', 'Good Smile Company', 145, '?', 'Available', 'Y', '/', 'Y', 'N', 'https://www.amazon.fr/dp/B0CDC45Q3W', '2024-08-23 06:42:09'),
(8, 'FIGURE-2430', 'jpg', 'Hatsune Miku', 'Good Smile Company', 54, 'Sep-2024', 'Order Closed', 'Y', '/', 'Y', 'N', 'https://www.amiami.com/eng/detail/?scode=FIGURE-167876', '2024-08-23 06:42:09'),
(9, 'FIGURE-2613', 'jpg', 'NoriPro', 'Good Smile Company', 38, 'Apr-2025', 'Available', 'Y', '/', 'Y', 'N', 'https://www.amiami.com/eng/detail/?scode=FIGURE-175771', '2024-08-23 06:42:09');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
