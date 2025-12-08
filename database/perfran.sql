-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 05:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perfran`
--

-- --------------------------------------------------------

--
-- Table structure for table `dictionaries`
--

CREATE TABLE `dictionaries` (
  `wid` int(11) NOT NULL,
  `word` text NOT NULL,
  `type` text NOT NULL,
  `difficulty` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dictionaries`
--

INSERT INTO `dictionaries` (`wid`, `word`, `type`, `difficulty`) VALUES
(1, 'la mère', 'Nom/G.N', 'easy'),
(2, 'un', 'Déterminant', 'easy'),
(3, 'beau', 'Adjectif', 'easy'),
(4, 'prendre', 'Verbe', 'easy'),
(5, 'aller', 'Verbe', 'easy'),
(6, 'travail', 'Nom/G.N', 'easy'),
(7, 'travailler', 'Verbe', 'easy'),
(8, 'triste', 'Adjectif', 'easy'),
(9, 'calculer', 'Verbe', 'easy'),
(10, 'une porte', 'Nom/G.N', 'easy'),
(11, 'trouver', 'Verbe', 'easy'),
(12, 'les jouets', 'Nom/G.N', 'easy'),
(13, 'la', 'Déterminant', 'easy'),
(14, 'heureux', 'Adjectif', 'easy'),
(15, 'le', 'Déterminant', 'easy'),
(16, 'l\'', 'Déterminant', 'easy'),
(17, 'partir', 'Verbe', 'easy'),
(18, 'une piscine', 'Nom/G.N', 'easy'),
(19, 'le stade', 'Nom/G.N', 'easy'),
(20, 'des', 'Déterminant', 'easy'),
(21, 'notre chien', 'Nom/G.N', 'easy'),
(22, 'lire', 'Verbe', 'easy'),
(23, 'livre', 'Nom/G.N', 'easy'),
(24, 'la rentrée', 'Nom/G.N', 'easy'),
(25, 'fier', 'Adjectif', 'easy'),
(26, 'levons', 'Verbe', 'easy'),
(27, 'l\'estime', 'Nom/G.N', 'easy'),
(28, 'paresseux', 'Adjectif', 'easy'),
(29, 'douceur', 'Nom/G.N', 'easy'),
(30, 'élèves', 'Nom/G.N', 'easy'),
(31, 'expliquer', 'Verbe', 'easy'),
(32, 'donner', 'Verbe', 'easy'),
(33, 'ma', 'Déterminant', 'easy'),
(34, 'sa soeur', 'Nom/G.N', 'easy'),
(35, 'seul', 'Adjectif', 'easy'),
(36, 'soleil', 'Nom/G.N', 'easy'),
(37, 'son père', 'Nom/G.N', 'easy'),
(38, 'sévère', 'Adjectif', 'easy'),
(39, 'peur', 'Nom/G.N', 'easy'),
(40, 'écrire', 'Verbe', 'easy'),
(41, 'une histoire', 'Nom/G.N', 'easy'),
(42, 'un personnage', 'Nom/G.N', 'easy'),
(43, 'rions', 'Verbe', 'easy'),
(44, 'notre', 'Déterminant', 'easy'),
(45, 'tes frères', 'Nom/G.N', 'easy'),
(46, 'une ville', 'Nom/G.N', 'easy'),
(47, 'la villa', 'Nom/G.N', 'easy'),
(48, 'déserte', 'Adjectif', 'easy'),
(49, 'chaud', 'Adjectif', 'easy'),
(50, 'balle', 'Nom/G.N', 'easy'),
(51, 'le collège', 'Nom/G.N', 'easy'),
(52, 'vos', 'Déterminant', 'easy'),
(53, 'votre famille', 'Nom/G.N', 'easy'),
(54, 'naitre', 'Verbe', 'easy'),
(55, 'maitre', 'Nom/G.N', 'easy'),
(56, 'mettre', 'Verbe', 'easy'),
(57, 'laid', 'Adjectif', 'easy'),
(58, 'le', 'Déterminant', 'easy'),
(59, 'l\'eau', 'Nom/G.N', 'easy'),
(60, 'le lait', 'Nom/G.N', 'easy'),
(61, 'boire', 'Verbe', 'easy'),
(62, 'la classe', 'Verbe', 'easy'),
(63, 'leur', 'Déterminant', 'easy'),
(64, 'un directeur', 'Nom/G.N', 'easy'),
(65, 'gentil', 'Adjectif', 'easy'),
(66, 'les professeurs', 'Nom/G.N', 'easy'),
(67, 'bon', 'Adjectif', 'easy'),
(68, 'bonheur', 'Nom/G.N', 'easy'),
(69, 'arriver', 'Verbe', 'easy'),
(70, 'l\'arrivée', 'Nom/G.N', 'easy'),
(71, 'un texte', 'Nom/G.N', 'easy'),
(72, 'facile', 'Adjectif', 'easy'),
(73, 'difficile', 'Adjectif', 'easy'),
(74, 'la campagne', 'Nom/G.N', 'easy'),
(75, 'plein', 'Adjectif', 'easy'),
(76, 'courageux', 'Adjectif', 'easy'),
(77, 'mes', 'Déterminant', 'easy'),
(78, 'fiche', 'Nom/G.N', 'easy'),
(79, 'une question', 'Nom/G.N', 'easy'),
(80, 'élégant', 'Adjectif', 'medium'),
(81, 'intélligent', 'Adjectif', 'medium'),
(82, 'immobilier', 'Nom/G.N', 'medium'),
(83, 'atelier', 'Nom/G.N', 'medium'),
(84, 'planner', 'Verbe', 'medium'),
(85, 'définir', 'Verbe', 'medium'),
(86, 'verser', 'Verbe', 'medium'),
(87, 'pour', 'Préposition', 'medium'),
(88, 'de', 'Préposition', 'medium'),
(89, 'à', 'Préposition', 'medium'),
(90, 'par', 'Préposition', 'medium'),
(91, 'sans', 'Préposition', 'medium'),
(92, 'rapidement', 'Adverbe', 'medium'),
(93, 'prendre', 'Verbe', 'medium'),
(94, 'calculer', 'Verbe', 'medium'),
(95, 'trouver', 'Verbe', 'medium'),
(96, 'heureux', 'Adjectif', 'medium'),
(97, 'le', 'Déterminant', 'medium'),
(98, 'partir', 'Verbe', 'medium'),
(99, 'notre chien', 'Nom/G.N', 'medium'),
(100, 'levons', 'Verbe', 'medium'),
(101, 'paresseux', 'Adjectif', 'medium'),
(102, 'douceur', 'Nom/G.N', 'medium'),
(103, 'expliquer', 'Verbe', 'medium'),
(104, 'seul', 'Adjectif', 'medium'),
(105, 'sévère', 'Adjectif', 'medium'),
(106, 'peur', 'Nom/G.N', 'medium'),
(107, 'écrire', 'Verbe', 'medium'),
(108, 'rions', 'Verbe', 'medium'),
(109, 'notre', 'Déterminant', 'medium'),
(110, 'déserte', 'Adjectif', 'medium'),
(111, 'chaud', 'Adjectif', 'medium'),
(112, 'vos', 'Déterminant', 'medium'),
(113, 'naitre', 'Verbe', 'medium'),
(114, 'maitre', 'Nom/G.N', 'medium'),
(115, 'mettre', 'Verbe', 'medium'),
(116, 'laid', 'Adjectif', 'medium'),
(117, 'boire', 'Verbe', 'medium'),
(118, 'leur', 'Déterminant', 'medium'),
(119, 'arriver', 'Verbe', 'medium'),
(120, 'facile', 'Adjectif', 'medium'),
(121, 'difficile', 'Adjectif', 'medium'),
(122, 'plein', 'Adjectif', 'medium'),
(123, 'courageux', 'Adjectif', 'medium'),
(124, 'mes', 'Déterminant', 'medium'),
(125, 'hiver', 'Nom/G.N', 'medium'),
(126, 'hier', 'Adverbe', 'medium'),
(127, 's\'habiller', '', 'medium'),
(128, 'ce', 'Déterminant', 'medium'),
(129, 'la lune', 'Nom/G.N', 'medium'),
(130, 'ces', 'Déterminant', 'medium'),
(131, 'heureusement', 'Adverbe', 'medium'),
(132, 'maintenant', 'Adverbe', 'medium'),
(133, 'demain', 'Adverbe', 'medium'),
(134, 'aujourd\'hui', 'Adverbe', 'medium'),
(135, 'calmement', 'Adverbe', 'medium'),
(136, 'l\'angoisse', 'Nom/G.N', 'medium'),
(137, 'la joie', 'Nom/G.N', 'medium'),
(138, 'l\'anxiété', 'Nom/G.N', 'medium'),
(139, 'printemps', 'Nom/G.N', 'medium'),
(140, 'proposer', 'Verbe', 'medium'),
(141, 'suivre', 'Verbe', 'medium'),
(142, 'don', 'Nom/G.N', 'medium'),
(143, 'l\'été', 'Nom/G.N', 'medium'),
(144, 'l\'automne', 'Nom/G.N', 'medium'),
(145, 'la terre', 'Nom/G.N', 'medium'),
(146, 'gentiment', 'Adverbe', 'medium'),
(147, 'une rivière', 'Nom/G.N', 'medium'),
(148, 'la forêt', 'Nom/G.N', 'medium'),
(149, 'admirer', 'Verbe', 'medium'),
(150, 'admirable', 'Adjectif', 'medium'),
(151, 'amitié', 'Nom/G.N', 'medium'),
(152, 'amical', 'Adjectif', 'medium'),
(153, 'procurer', 'Verbe', 'medium'),
(154, 'rendre', 'Verbe', 'medium'),
(155, 'saisir', 'Verbe', 'medium'),
(156, 'crainte', 'Nom/G.N', 'medium'),
(157, 'craindre', 'Verbe', 'medium'),
(158, 'chant', 'Nom/G.N', 'medium'),
(159, 'chanter', 'Verbe', 'medium'),
(160, 'champs', 'Nom/G.N', 'medium'),
(161, 'chanteur', 'Nom/G.N', 'medium'),
(162, 'vers', 'Préposition', 'medium'),
(163, 'verte', 'Adjectif', 'medium'),
(164, 'propreté', 'Nom/G.N', 'medium'),
(165, 'dense', 'Adjectif', 'medium'),
(166, 'danser', 'Verbe', 'medium'),
(167, 'danseur', 'Nom/G.N', 'medium'),
(168, 'somme', 'Nom/G.N', 'medium'),
(169, 'pré', 'Nom/G.N', 'medium'),
(170, 'personne', 'Nom/G.N', 'medium'),
(171, 'tout à coup', 'Adverbe', 'medium'),
(172, 'puis', 'Adverbe', 'medium'),
(173, 'ensuite', 'Adverbe', 'medium'),
(174, 'dans', 'Préposition', 'medium'),
(175, 'après', 'Préposition', 'medium'),
(176, 'régulièrement', 'Adverbe', 'medium'),
(177, 'définir', 'Verbe', 'hard'),
(178, 'verser', 'Verbe', 'hard'),
(179, 'pour', 'Préposition', 'hard'),
(180, 'de', 'Préposition', 'hard'),
(181, 'à', 'Préposition', 'hard'),
(182, 'par', 'Préposition', 'hard'),
(183, 'sans', 'Préposition', 'hard'),
(184, 'rapidement', 'Adverbe', 'hard'),
(185, 'prendre', 'Verbe', 'hard'),
(186, 'heureux', 'Adjectif', 'hard'),
(187, 'partir', 'Verbe', 'hard'),
(188, 'levons', 'Verbe', 'hard'),
(189, 'paresseux', 'Adjectif', 'hard'),
(190, 'douceur', 'Nom/G.N', 'hard'),
(191, 'expliquer', 'Verbe', 'hard'),
(192, 'seul', 'Adjectif', 'hard'),
(193, 'sévère', 'Adjectif', 'hard'),
(194, 'peur', 'Nom/G.N', 'hard'),
(195, 'écrire', 'Verbe', 'hard'),
(196, 'rions', 'Verbe', 'hard'),
(197, 'déserte', 'Adjectif', 'hard'),
(198, 'naitre', 'Verbe', 'hard'),
(199, 'maitre', 'Nom/G.N', 'hard'),
(200, 'laid', 'Adjectif', 'hard'),
(201, 'leur', 'Déterminant', 'hard'),
(202, 'arriver', 'Verbe', 'hard'),
(203, 'facile', 'Adjectif', 'hard'),
(204, 'difficile', 'Adjectif', 'hard'),
(205, 'plein', 'Adjectif', 'hard'),
(206, 'mes', 'Déterminant', 'hard'),
(207, 'hiver', 'Nom/G.N', 'hard'),
(208, 'hier', 'Adverbe', 'hard'),
(209, 'heureusement', 'Adverbe', 'hard'),
(210, 'maintenant', 'Adverbe', 'hard'),
(211, 'demain', 'Adverbe', 'hard'),
(212, 'aujourd\'hui', 'Adverbe', 'hard'),
(213, 'calmement', 'Adverbe', 'hard'),
(214, 'printemps', 'Nom/G.N', 'hard'),
(215, 'proposer', 'Verbe', 'hard'),
(216, 'suivre', 'Verbe', 'hard'),
(217, 'l\'été', 'Nom/G.N', 'hard'),
(218, 'la terre', 'Nom/G.N', 'hard'),
(219, 'gentiment', 'Adverbe', 'hard'),
(220, 'une rivière', 'Nom/G.N', 'hard'),
(221, 'la forêt', 'Nom/G.N', 'hard'),
(222, 'admirer', 'Verbe', 'hard'),
(223, 'admirable', 'Adjectif', 'hard'),
(224, 'amitié', 'Nom/G.N', 'hard'),
(225, 'amical', 'Adjectif', 'hard'),
(226, 'procurer', 'Verbe', 'hard'),
(227, 'rendre', 'Verbe', 'hard'),
(228, 'saisir', 'Verbe', 'hard'),
(229, 'crainte', 'Nom/G.N', 'hard'),
(230, 'craindre', 'Verbe', 'hard'),
(231, 'chant', 'Nom/G.N', 'hard'),
(232, 'chanter', 'Verbe', 'hard'),
(233, 'champs', 'Nom/G.N', 'hard'),
(234, 'chanteur', 'Nom/G.N', 'hard'),
(235, 'vers', 'Préposition', 'hard'),
(236, 'verte', 'Adjectif', 'hard'),
(237, 'propreté', 'Nom/G.N', 'hard'),
(238, 'danser', 'Verbe', 'hard'),
(239, 'danseur', 'Nom/G.N', 'hard'),
(240, 'somme', 'Nom/G.N', 'hard'),
(241, 'pré', 'Nom/G.N', 'hard'),
(242, 'personne', 'Nom/G.N', 'hard'),
(243, 'tout à coup', 'Adverbe', 'hard'),
(244, 'puis', 'Adverbe', 'hard'),
(245, 'ensuite', 'Adverbe', 'hard'),
(246, 'dans', 'Préposition', 'hard'),
(247, 'après', 'Préposition', 'hard'),
(248, 'régulièrement', 'Adverbe', 'hard'),
(249, 'et', 'Conjonction', 'hard'),
(250, 'ou', 'Conjonction', 'hard'),
(251, 'mais', 'Conjonction', 'hard'),
(252, 'car', 'Conjonction', 'hard'),
(253, 'ni', 'Conjonction', 'hard'),
(254, 'donc', 'Conjonction', 'hard'),
(255, 'or', 'Conjonction', 'hard'),
(256, 'quelques', 'Déterminant', 'hard'),
(257, 'plusieurs', 'Déterminant', 'hard'),
(258, 'quelqu\'un', 'Pronom', 'hard'),
(259, 'aucun', 'Pronom', 'hard'),
(260, 'chacun', 'Pronom', 'hard'),
(261, 'celui', 'Pronom', 'hard'),
(262, 'celle', 'Pronom', 'hard'),
(263, 'ceux', 'Pronom', 'hard'),
(264, 'eux', 'Pronom', 'hard'),
(265, 'qui', 'Pronom', 'hard'),
(266, 'quand', 'Conjonction', 'hard'),
(267, 'ceci', 'Pronom', 'hard'),
(268, 'celà', 'Pronom', 'hard'),
(269, 'celui-ci', 'Pronom', 'hard'),
(270, 'celui', 'Pronom', 'hard'),
(271, 'là', 'Adverbe', 'hard'),
(272, 'lequel', 'Pronom', 'hard'),
(273, 'lesquels', 'Pronom', 'hard'),
(274, 'patrie', 'Nom/G.N', 'hard'),
(275, 'patrimoine', 'Nom/G.N', 'hard'),
(276, 'chaleureux', 'Adjectif', 'hard'),
(277, 'portail', 'Nom/G.N', 'hard'),
(278, 'portier', 'Nom/G.N', 'hard'),
(279, 'mélancolique', 'Adjectif', 'hard'),
(280, 'divinité', 'Nom/G.N', 'hard'),
(281, 'divin', 'Adjectif', 'hard'),
(282, 'le nôtre', 'Pronom', 'hard'),
(283, 'les miens', 'Pronom', 'hard'),
(284, 'les vôtres', 'Pronom', 'hard'),
(285, 'la sienne', 'Pronom', 'hard'),
(286, 'chaine', 'Nom/G.N', 'hard'),
(287, 'chance', 'Nom/G.N', 'hard'),
(288, 'chaos', 'Nom/G.N', 'hard');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `eid` int(11) NOT NULL,
  `title` text NOT NULL,
  `endDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gift1` int(11) NOT NULL,
  `gift2` text NOT NULL,
  `gift3` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_players`
--

CREATE TABLE `event_players` (
  `epid` int(11) NOT NULL,
  `eid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gamelogs`
--

CREATE TABLE `gamelogs` (
  `gid` int(11) NOT NULL,
  `player1id` int(11) NOT NULL,
  `player2id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `duration` int(11) NOT NULL,
  `winner` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `punishments`
--

CREATE TABLE `punishments` (
  `pid` int(11) NOT NULL,
  `punishedID` int(11) NOT NULL,
  `rid` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `qid` int(11) NOT NULL,
  `paragraph` text NOT NULL,
  `nbBlanks` tinyint(4) NOT NULL CHECK (`nbBlanks` between 3 and 8),
  `difficulty` text NOT NULL COMMENT 'easy, medium, hard',
  `approved` int(11) DEFAULT NULL CHECK (`approved` between 0 and 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_blanks`
--

CREATE TABLE `quiz_blanks` (
  `bid` int(11) NOT NULL,
  `qid` int(11) DEFAULT NULL,
  `position` tinyint(4) NOT NULL,
  `correctAnswer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `rid` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `reporterID` int(11) NOT NULL,
  `reportedID` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '0:not solved\r\n1: solved',
  `pid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `texts`
--

CREATE TABLE `texts` (
  `tid` int(11) NOT NULL,
  `text` text NOT NULL,
  `question` text NOT NULL,
  `answer1` text NOT NULL COMMENT 'Always the correct answer',
  `answer2` text NOT NULL,
  `answer3` text DEFAULT NULL COMMENT 'can be null'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `texts`
--

INSERT INTO `texts` (`tid`, `text`, `question`, `answer1`, `answer2`, `answer3`) VALUES
(1, 'La Tour Eiffel est un monument parisien situé sur le Champ-de-Mars. Construite par Gustave Eiffel pour l\'Exposition universelle de 1889, elle est initialement critiquée par certains artistes de l\'époque. Haute de 330 mètres, elle est restée le plus haut monument du monde pendant 41 ans. La tour reçoit environ 7 millions de visiteurs chaque année, ce qui en fait le monument payant le plus visité au monde. Sa structure métallique pèse environ 10 100 tonnes.', 'Qui est l\'architecte de la Tour Eiffel ?', 'Gustave Eiffel', 'Georges Haussmann', 'Auguste Rodin'),
(2, 'La photosynthèse est un processus utilisé par les plantes pour convertir l\'énergie lumineuse en énergie chimique. Ce processus nécessite de l\'eau, du dioxyde de carbone et de la lumière solaire. La chlorophylle, pigment vert présent dans les feuilles, absorbe l\'énergie lumineuse. Les produits finaux de la photosynthèse sont le glucose et l\'oxygène. Ce mécanisme est essentiel à la vie sur Terre car il produit l\'oxygène que nous respirons.', 'La photosynthèse produit-elle de l\'oxygène ?', 'Vrai', 'Faux', ''),
(3, 'Le réchauffement climatique désigne l\'augmentation des températures moyennes à la surface de la Terre. Ce phénomène est principalement dû aux émissions de gaz à effet de serre comme le CO2. Les conséquences incluent la fonte des glaciers, l\'élévation du niveau des mers et l\'augmentation des phénomènes météorologiques extrêmes. Le GIEC publie régulièrement des rapports sur l\'évolution de ce phénomène. La réduction des énergies fossiles est une solution importante pour limiter ce réchauffement.', 'Quelle est la principale cause du réchauffement climatique selon le texte ?', 'Les émissions de gaz à effet de serre', 'La déforestation', 'Les cycles naturels du climat'),
(4, 'Victor Hugo est un écrivain français né en 1802 et mort en 1885. Il est considéré comme l\'un des plus importants auteurs de la littérature française. Parmi ses œuvres majeures figurent \'Les Misérables\' et \'Notre-Dame de Paris\'. Il a également été engagé politiquement et a lutté contre la peine de mort. Son œuvre couvre plusieurs genres : poésie, théâtre et romans. Il a passé une partie de sa vie en exil pendant le Second Empire.', 'Victor Hugo a-t-il lutté contre la peine de mort ?', 'Vrai', 'Faux', ''),
(5, 'La Grande Muraille de Chine est une série de fortifications militaires construites en plusieurs siècles. Elle s\'étend sur environ 21 000 km et fut principalement édifiée pour protéger la frontière nord de l\'empire Chine. Sa construction a débuté au IIIe siècle av. J.-C. et s\'est poursuivie jusqu\'au XVIIe siècle. Contrairement à une croyance populaire, elle n\'est pas visible à l\'œil nu depuis la Lune. Elle est classée au patrimoine mondial de l\'UNESCO depuis 1987.', 'Quelle est la longueur approximative de la Grande Muraille ?', 'Environ 21 000 km', 'Environ 10 000 km', 'Environ 5 000 km');

-- --------------------------------------------------------

--
-- Table structure for table `type`
--

CREATE TABLE `type` (
  `wid` int(11) NOT NULL,
  `type` text NOT NULL,
  `difficulty` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `type`
--

INSERT INTO `type` (`wid`, `type`, `difficulty`) VALUES
(1, 'Nom/G.N', 'easy'),
(2, 'Déterminant', 'easy'),
(3, 'Adjectif', 'easy'),
(4, 'Verbe', 'easy'),
(5, 'Verbe', 'easy'),
(6, 'Nom/G.N', 'easy'),
(7, 'Verbe', 'easy'),
(8, 'Adjectif', 'easy'),
(9, 'Verbe', 'easy'),
(10, 'Nom/G.N', 'easy'),
(11, 'Verbe', 'easy'),
(12, 'Nom/G.N', 'easy'),
(13, 'Déterminant', 'easy'),
(14, 'Adjectif', 'easy'),
(15, 'Déterminant', 'easy'),
(16, 'Déterminant', 'easy'),
(17, 'Verbe', 'easy'),
(18, 'Nom/G.N', 'easy'),
(19, 'Nom/G.N', 'easy'),
(20, 'Déterminant', 'easy'),
(21, 'Nom/G.N', 'easy'),
(22, 'Verbe', 'easy'),
(23, 'Nom/G.N', 'easy'),
(24, 'Nom/G.N', 'easy'),
(25, 'Adjectif', 'easy'),
(26, 'Verbe', 'easy'),
(27, 'Nom/G.N', 'easy'),
(28, 'Adjectif', 'easy'),
(29, 'Nom/G.N', 'easy'),
(30, 'Nom/G.N', 'easy'),
(31, 'Verbe', 'easy'),
(32, 'Verbe', 'easy'),
(33, 'Déterminant', 'easy'),
(34, 'Nom/G.N', 'easy'),
(35, 'Adjectif', 'easy'),
(36, 'Nom/G.N', 'easy'),
(37, 'Nom/G.N', 'easy'),
(38, 'Adjectif', 'easy'),
(39, 'Nom/G.N', 'easy'),
(40, 'Verbe', 'easy'),
(41, 'Nom/G.N', 'easy'),
(42, 'Nom/G.N', 'easy'),
(43, 'Verbe', 'easy'),
(44, 'Déterminant', 'easy'),
(45, 'Nom/G.N', 'easy'),
(46, 'Nom/G.N', 'easy'),
(47, 'Nom/G.N', 'easy'),
(48, 'Adjectif', 'easy'),
(49, 'Adjectif', 'easy'),
(50, 'Nom/G.N', 'easy'),
(51, 'Nom/G.N', 'easy'),
(52, 'Déterminant', 'easy'),
(53, 'Nom/G.N', 'easy'),
(54, 'Verbe', 'easy'),
(55, 'Nom/G.N', 'easy'),
(56, 'Verbe', 'easy'),
(57, 'Adjectif', 'easy'),
(58, 'Déterminant', 'easy'),
(59, 'Nom/G.N', 'easy'),
(60, 'Nom/G.N', 'easy'),
(61, 'Verbe', 'easy'),
(62, 'Verbe', 'easy'),
(63, 'Déterminant', 'easy'),
(64, 'Nom/G.N', 'easy'),
(65, 'Adjectif', 'easy'),
(66, 'Nom/G.N', 'easy'),
(67, 'Adjectif', 'easy'),
(68, 'Nom/G.N', 'easy'),
(69, 'Verbe', 'easy'),
(70, 'Nom/G.N', 'easy'),
(71, 'Nom/G.N', 'easy'),
(72, 'Adjectif', 'easy'),
(73, 'Adjectif', 'easy'),
(74, 'Nom/G.N', 'easy'),
(75, 'Adjectif', 'easy'),
(76, 'Adjectif', 'easy'),
(77, 'Déterminant', 'easy'),
(78, 'Nom/G.N', 'easy'),
(79, 'Nom/G.N', 'easy'),
(80, 'Adjectif', 'medium'),
(81, 'Adjectif', 'medium'),
(82, 'Nom/G.N', 'medium'),
(83, 'Nom/G.N', 'medium'),
(84, 'Verbe', 'medium'),
(85, 'Verbe', 'medium'),
(86, 'Verbe', 'medium'),
(87, 'Préposition', 'medium'),
(88, 'Préposition', 'medium'),
(89, 'Préposition', 'medium'),
(90, 'Préposition', 'medium'),
(91, 'Préposition', 'medium'),
(92, 'Adverbe', 'medium'),
(93, 'Verbe', 'medium'),
(94, 'Verbe', 'medium'),
(95, 'Verbe', 'medium'),
(96, 'Adjectif', 'medium'),
(97, 'Déterminant', 'medium'),
(98, 'Verbe', 'medium'),
(99, 'Nom/G.N', 'medium'),
(100, 'Verbe', 'medium'),
(101, 'Adjectif', 'medium'),
(102, 'Nom/G.N', 'medium'),
(103, 'Verbe', 'medium'),
(104, 'Adjectif', 'medium'),
(105, 'Adjectif', 'medium'),
(106, 'Nom/G.N', 'medium'),
(107, 'Verbe', 'medium'),
(108, 'Verbe', 'medium'),
(109, 'Déterminant', 'medium'),
(110, 'Adjectif', 'medium'),
(111, 'Adjectif', 'medium'),
(112, 'Déterminant', 'medium'),
(113, 'Verbe', 'medium'),
(114, 'Nom/G.N', 'medium'),
(115, 'Verbe', 'medium'),
(116, 'Adjectif', 'medium'),
(117, 'Verbe', 'medium'),
(118, 'Déterminant', 'medium'),
(119, 'Verbe', 'medium'),
(120, 'Adjectif', 'medium'),
(121, 'Adjectif', 'medium'),
(122, 'Adjectif', 'medium'),
(123, 'Adjectif', 'medium'),
(124, 'Déterminant', 'medium'),
(125, 'Nom/G.N', 'medium'),
(126, 'Adverbe', 'medium'),
(127, 'Verbe', 'medium'),
(128, 'Déterminant', 'medium'),
(129, 'Nom/G.N', 'medium'),
(130, 'Déterminant', 'medium'),
(131, 'Adverbe', 'medium'),
(132, 'Adverbe', 'medium'),
(133, 'Adverbe', 'medium'),
(134, 'Adverbe', 'medium'),
(135, 'Adverbe', 'medium'),
(136, 'Nom/G.N', 'medium'),
(137, 'Nom/G.N', 'medium'),
(138, 'Nom/G.N', 'medium'),
(139, 'Nom/G.N', 'medium'),
(140, 'Verbe', 'medium'),
(141, 'Verbe', 'medium'),
(142, 'Nom/G.N', 'medium'),
(143, 'Nom/G.N', 'medium'),
(144, 'Nom/G.N', 'medium'),
(145, 'Nom/G.N', 'medium'),
(146, 'Adverbe', 'medium'),
(147, 'Nom/G.N', 'medium'),
(148, 'Nom/G.N', 'medium'),
(149, 'Verbe', 'medium'),
(150, 'Adjectif', 'medium'),
(151, 'Nom/G.N', 'medium'),
(152, 'Adjectif', 'medium'),
(153, 'Verbe', 'medium'),
(154, 'Verbe', 'medium'),
(155, 'Verbe', 'medium'),
(156, 'Nom/G.N', 'medium'),
(157, 'Verbe', 'medium'),
(158, 'Nom/G.N', 'medium'),
(159, 'Verbe', 'medium'),
(160, 'Nom/G.N', 'medium'),
(161, 'Nom/G.N', 'medium'),
(162, 'Préposition', 'medium'),
(163, 'Adjectif', 'medium'),
(164, 'Nom/G.N', 'medium'),
(165, 'Adjectif', 'medium'),
(166, 'Verbe', 'medium'),
(167, 'Nom/G.N', 'medium'),
(168, 'Nom/G.N', 'medium'),
(169, 'Nom/G.N', 'medium'),
(170, 'Nom/G.N', 'medium'),
(171, 'Adverbe', 'medium'),
(172, 'Adverbe', 'medium'),
(173, 'Adverbe', 'medium'),
(174, 'Préposition', 'medium'),
(175, 'Préposition', 'medium'),
(176, 'Adverbe', 'medium'),
(177, 'Verbe', 'hard'),
(178, 'Verbe', 'hard'),
(179, 'Préposition', 'hard'),
(180, 'Préposition', 'hard'),
(181, 'Préposition', 'hard'),
(182, 'Préposition', 'hard'),
(183, 'Préposition', 'hard'),
(184, 'Adverbe', 'hard'),
(185, 'Verbe', 'hard'),
(186, 'Adjectif', 'hard'),
(187, 'Verbe', 'hard'),
(188, 'Verbe', 'hard'),
(189, 'Adjectif', 'hard'),
(190, 'Nom/G.N', 'hard'),
(191, 'Verbe', 'hard'),
(192, 'Adjectif', 'hard'),
(193, 'Adjectif', 'hard'),
(194, 'Nom/G.N', 'hard'),
(195, 'Verbe', 'hard'),
(196, 'Verbe', 'hard'),
(197, 'Adjectif', 'hard'),
(198, 'Verbe', 'hard'),
(199, 'Nom/G.N', 'hard'),
(200, 'Adjectif', 'hard'),
(201, 'Déterminant', 'hard'),
(202, 'Verbe', 'hard'),
(203, 'Adjectif', 'hard'),
(204, 'Adjectif', 'hard'),
(205, 'Adjectif', 'hard'),
(206, 'Déterminant', 'hard'),
(207, 'Nom/G.N', 'hard'),
(208, 'Adverbe', 'hard'),
(209, 'Adverbe', 'hard'),
(210, 'Adverbe', 'hard'),
(211, 'Adverbe', 'hard'),
(212, 'Adverbe', 'hard'),
(213, 'Adverbe', 'hard'),
(214, 'Nom/G.N', 'hard'),
(215, 'Verbe', 'hard'),
(216, 'Verbe', 'hard'),
(217, 'Nom/G.N', 'hard'),
(218, 'Nom/G.N', 'hard'),
(219, 'Adverbe', 'hard'),
(220, 'Nom/G.N', 'hard'),
(221, 'Nom/G.N', 'hard'),
(222, 'Verbe', 'hard'),
(223, 'Adjectif', 'hard'),
(224, 'Nom/G.N', 'hard'),
(225, 'Adjectif', 'hard'),
(226, 'Verbe', 'hard'),
(227, 'Verbe', 'hard'),
(228, 'Verbe', 'hard'),
(229, 'Nom/G.N', 'hard'),
(230, 'Verbe', 'hard'),
(231, 'Nom/G.N', 'hard'),
(232, 'Verbe', 'hard'),
(233, 'Nom/G.N', 'hard'),
(234, 'Nom/G.N', 'hard'),
(235, 'Préposition', 'hard'),
(236, 'Adjectif', 'hard'),
(237, 'Nom/G.N', 'hard'),
(238, 'Verbe', 'hard'),
(239, 'Nom/G.N', 'hard'),
(240, 'Nom/G.N', 'hard'),
(241, 'Nom/G.N', 'hard'),
(242, 'Nom/G.N', 'hard'),
(243, 'Adverbe', 'hard'),
(244, 'Adverbe', 'hard'),
(245, 'Adverbe', 'hard'),
(246, 'Préposition', 'hard'),
(247, 'Préposition', 'hard'),
(248, 'Adverbe', 'hard'),
(249, 'Conjonction', 'hard'),
(250, 'Conjonction', 'hard'),
(251, 'Conjonction', 'hard'),
(252, 'Conjonction', 'hard'),
(253, 'Conjonction', 'hard'),
(254, 'Conjonction', 'hard'),
(255, 'Conjonction', 'hard'),
(256, 'Déterminant', 'hard'),
(257, 'Déterminant', 'hard'),
(258, 'Pronom', 'hard'),
(259, 'Pronom', 'hard'),
(260, 'Pronom', 'hard'),
(261, 'Pronom', 'hard'),
(262, 'Pronom', 'hard'),
(263, 'Pronom', 'hard'),
(264, 'Pronom', 'hard'),
(265, 'Pronom', 'hard'),
(266, 'Conjonction', 'hard'),
(267, 'Pronom', 'hard'),
(268, 'Pronom', 'hard'),
(269, 'Pronom', 'hard'),
(270, 'Pronom', 'hard'),
(271, 'Adverbe', 'hard'),
(272, 'Pronom', 'hard'),
(273, 'Pronom', 'hard'),
(274, 'Nom/G.N', 'hard'),
(275, 'Nom/G.N', 'hard'),
(276, 'Adjectif', 'hard'),
(277, 'Nom/G.N', 'hard'),
(278, 'Nom/G.N', 'hard'),
(279, 'Adjectif', 'hard'),
(280, 'Nom/G.N', 'hard'),
(281, 'Adjectif', 'hard'),
(282, 'Pronom', 'hard'),
(283, 'Pronom', 'hard'),
(284, 'Pronom', 'hard'),
(285, 'Pronom', 'hard'),
(286, 'Nom/G.N', 'hard'),
(287, 'Nom/G.N', 'hard'),
(288, 'Nom/G.N', 'hard');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `firstName` text DEFAULT NULL,
  `lastName` text DEFAULT NULL,
  `password_hash` char(40) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` int(11) DEFAULT NULL,
  `role` int(11) NOT NULL DEFAULT 0 COMMENT '0: player,\r\n1: admin',
  `totalScore1` int(11) NOT NULL DEFAULT 0,
  `totalScore2` int(11) NOT NULL DEFAULT 0,
  `totalScore3` int(11) NOT NULL DEFAULT 0,
  `dailyScore1` int(11) NOT NULL DEFAULT 0,
  `dailyScore2` int(11) NOT NULL DEFAULT 0,
  `dailyScore3` int(11) NOT NULL DEFAULT 0,
  `streak` int(11) NOT NULL DEFAULT 0,
  `gamesPlayed1` int(11) NOT NULL DEFAULT 0,
  `gamesPlayed2` int(11) NOT NULL DEFAULT 0,
  `gamesPlayed3` int(11) NOT NULL DEFAULT 0,
  `wins` int(11) NOT NULL DEFAULT 0,
  `losses` int(11) NOT NULL DEFAULT 0,
  `creationDate` timestamp(2) NOT NULL DEFAULT current_timestamp(2),
  `bannedUntil` datetime DEFAULT NULL COMMENT 'Date until unbanned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dictionaries`
--
ALTER TABLE `dictionaries`
  ADD PRIMARY KEY (`wid`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`eid`);

--
-- Indexes for table `event_players`
--
ALTER TABLE `event_players`
  ADD PRIMARY KEY (`epid`),
  ADD UNIQUE KEY `unique_event_user` (`eid`,`uid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `gamelogs`
--
ALTER TABLE `gamelogs`
  ADD PRIMARY KEY (`gid`);

--
-- Indexes for table `punishments`
--
ALTER TABLE `punishments`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `FKrid` (`rid`),
  ADD KEY `FKpunishedID` (`punishedID`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`qid`);

--
-- Indexes for table `quiz_blanks`
--
ALTER TABLE `quiz_blanks`
  ADD PRIMARY KEY (`bid`),
  ADD UNIQUE KEY `uniqueExercisePosition` (`bid`,`position`),
  ADD KEY `FKqid` (`qid`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`rid`),
  ADD KEY `FKreporterID` (`reporterID`),
  ADD KEY `FKreportedID` (`reportedID`),
  ADD KEY `FKgid` (`gid`),
  ADD KEY `FKpid` (`pid`);

--
-- Indexes for table `texts`
--
ALTER TABLE `texts`
  ADD PRIMARY KEY (`tid`);

--
-- Indexes for table `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`wid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dictionaries`
--
ALTER TABLE `dictionaries`
  MODIFY `wid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=293;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `eid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_players`
--
ALTER TABLE `event_players`
  MODIFY `epid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gamelogs`
--
ALTER TABLE `gamelogs`
  MODIFY `gid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `punishments`
--
ALTER TABLE `punishments`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT;

--
--
-- Dumping data for table `quiz`
--

-- Quiz Facile 1
INSERT INTO `quiz` (`qid`, `paragraph`, `nbBlanks`, `difficulty`, `approved`) VALUES
(1, 'La [mère] de mon ami est très gentille. Elle aime [lire] des livres et aller à la [piscine].', 3, 'easy', 1),
-- Quiz Facile 2
(2, 'Le [chien] de mon [voisin] est très joueur. Il aime courir après les ballons et [jouer] avec les enfants.', 3, 'easy', 1),

-- Quiz Moyen 1
(3, 'Les [élèves] doivent [travailler] dur pour réussir leurs examens. Ils doivent être [sérieux] et ne pas être [paresseux].', 4, 'medium', 1),
-- Quiz Moyen 2
(4, 'Le [soleil] brille fort aujourd\'hui. Il fait [chaud] et le ciel est [bleu]. Je vais boire de [l\'eau] pour me rafraîchir.', 4, 'medium', 1),

-- Quiz Difficile 1
(5, 'La [propreté] de la chambre est essentielle pour maintenir un environnement sain. Il est important de [ranger] régulièrement ses affaires et de [nettoyer] les surfaces pour éviter l\'accumulation de [poussière].', 4, 'hard', 1),
-- Quiz Difficile 2
(6, 'Le [maître] d\'école a demandé aux élèves de [réfléchir] avant de répondre aux questions. Cette [démarche] permet de mieux [comprendre] les exercices et d\'éviter les erreurs [inutiles].', 5, 'hard', 1);

--
-- Dumping data for table `quiz_blanks`
--

-- Pour le quiz facile 1
INSERT INTO `quiz_blanks` (`bid`, `qid`, `position`, `correctAnswer`) VALUES
(1, 1, 0, 'mère'),
(2, 1, 1, 'lire'),
(3, 1, 2, 'piscine');

-- Pour le quiz facile 2
INSERT INTO `quiz_blanks` (`bid`, `qid`, `position`, `correctAnswer`) VALUES
(4, 2, 0, 'chien'),
(5, 2, 1, 'voisin'),
(6, 2, 2, 'jouer');

-- Pour le quiz moyen 1
INSERT INTO `quiz_blanks` (`bid`, `qid`, `position`, `correctAnswer`) VALUES
(7, 3, 0, 'élèves'),
(8, 3, 1, 'travailler'),
(9, 3, 2, 'sérieux'),
(10, 3, 3, 'paresseux');

-- Pour le quiz moyen 2
INSERT INTO `quiz_blanks` (`bid`, `qid`, `position`, `correctAnswer`) VALUES
(11, 4, 0, 'soleil'),
(12, 4, 1, 'chaud'),
(13, 4, 2, 'bleu'),
(14, 4, 3, 'l\'eau');

-- Pour le quiz difficile 1
INSERT INTO `quiz_blanks` (`bid`, `qid`, `position`, `correctAnswer`) VALUES
(15, 5, 0, 'propreté'),
(16, 5, 1, 'ranger'),
(17, 5, 2, 'nettoyer'),
(18, 5, 3, 'poussière');

-- Pour le quiz difficile 2
INSERT INTO `quiz_blanks` (`bid`, `qid`, `position`, `correctAnswer`) VALUES
(19, 6, 0, 'maître'),
(20, 6, 1, 'réfléchir'),
(21, 6, 2, 'démarche'),
(22, 6, 3, 'comprendre'),
(23, 6, 4, 'inutiles');

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `qid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quiz_blanks`
--
ALTER TABLE `quiz_blanks`
  MODIFY `bid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `rid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `texts`
--
ALTER TABLE `texts`
  MODIFY `tid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `type`
--
ALTER TABLE `type`
  MODIFY `wid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_players`
--
ALTER TABLE `event_players`
  ADD CONSTRAINT `event_players_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `events` (`eid`),
  ADD CONSTRAINT `event_players_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `punishments`
--
ALTER TABLE `punishments`
  ADD CONSTRAINT `FKpunishedID` FOREIGN KEY (`punishedID`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `FKrid` FOREIGN KEY (`rid`) REFERENCES `reports` (`rid`);

--
-- Constraints for table `quiz_blanks`
--
ALTER TABLE `quiz_blanks`
  ADD CONSTRAINT `FKqid` FOREIGN KEY (`qid`) REFERENCES `quiz` (`qid`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `FKgid` FOREIGN KEY (`gid`) REFERENCES `gamelogs` (`gid`),
  ADD CONSTRAINT `FKpid` FOREIGN KEY (`pid`) REFERENCES `punishments` (`pid`),
  ADD CONSTRAINT `FKreportedID` FOREIGN KEY (`reportedID`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `FKreporterID` FOREIGN KEY (`reporterID`) REFERENCES `users` (`uid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
