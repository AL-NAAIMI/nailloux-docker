-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2025 at 05:55 PM
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
-- Database: `nailloux`
--

-- --------------------------------------------------------

--
-- Table structure for table `commentaire_p`
--

CREATE TABLE `commentaire_p` (
  `id_commentaire_p` int(11) NOT NULL,
  `texte` text NOT NULL,
  `date_heure` timestamp NOT NULL DEFAULT current_timestamp(),
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE `document` (
  `id_doccument` int(11) NOT NULL,
  `nom` varchar(70) NOT NULL,
  `chemin` varchar(250) NOT NULL,
  `date_depot` timestamp NOT NULL DEFAULT current_timestamp(),
  `uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document`
--

INSERT INTO `document` (`id_doccument`, `nom`, `chemin`, `date_depot`, `uid`) VALUES
(23, 'Documents-testing', '../../upload/file/Documents-test.pdf', '2025-02-07 14:10:39', 1),
(24, 'Documents-tests', '../../upload/file/Documents-test.docx', '2025-02-07 14:10:57', 1),
(25, 'New Microsoft Word Document', '../../upload/file/New Microsoft Word Document.docx', '2025-02-07 14:11:29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `evenement`
--

CREATE TABLE `evenement` (
  `id_evenement` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `date_heure` datetime NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `descriptif` text DEFAULT NULL,
  `type` enum('Cours','Sortie à thème','Expo','Réunion','Info ext','Collaboration ext','Visionnage') DEFAULT 'Réunion',
  `officiel` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evenement`
--

INSERT INTO `evenement` (`id_evenement`, `uid`, `titre`, `date_heure`, `lieu`, `descriptif`, `type`, `officiel`) VALUES
(457, 1, 'Exposition', '2025-01-20 10:01:00', 'Toulouse', 'Exposition', 'Expo', 0),
(462, 1, 'Visionnage', '2025-01-27 12:14:00', 'Toulouse', 'un evenement', 'Visionnage', 1),
(472, 1, 'ss', '2025-02-15 02:51:00', 'ss', 's', 'Visionnage', 0),
(473, 1, 'xc', '2025-02-15 15:14:00', 'xc', 'xc', 'Cours', 0);

-- --------------------------------------------------------

--
-- Table structure for table `evenement_participants`
--

CREATE TABLE `evenement_participants` (
  `id` int(11) NOT NULL,
  `id_evenement` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `date_inscription` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evenement_participants`
--

INSERT INTO `evenement_participants` (`id`, `id_evenement`, `uid`, `date_inscription`) VALUES
(35, 472, 2, '2025-02-07 16:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `photos_evenement`
--

CREATE TABLE `photos_evenement` (
  `id_photo` int(11) NOT NULL,
  `id_evenement` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `chemin_photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `photos_evenement`
--

INSERT INTO `photos_evenement` (`id_photo`, `id_evenement`, `uid`, `chemin_photo`) VALUES
(36, 462, 1, 'photo_678a3bbe4bfa05.37543448.JPG'),
(37, 462, 1, 'photo_678a3bbfc55ad1.86441527.JPG'),
(39, 472, 1, 'photo_67a60f9ed93118.30814810.jpg'),
(40, 472, 1, 'photo_67a60f9f32f126.18798795.jpg'),
(41, 472, 1, 'photo_67a60f9f336893.86230321.jpg'),
(42, 472, 1, 'photo_67a60f9f557e98.68659436.jpg'),
(43, 472, 1, 'photo_67a60f9f934b60.48106223.jpg'),
(44, 472, 1, 'photo_67a60f9fae58d1.03138584.png'),
(45, 472, 1, 'photo_67a60fa00d1b11.84626199.jpg'),
(46, 472, 1, 'photo_67a60fa05b1810.03889663.jpg'),
(47, 472, 1, 'photo_67a60fa05b78d9.86421653.jpg'),
(48, 472, 1, 'photo_67a612b2a2ceb4.83422166.jpg'),
(49, 472, 2, 'photo_67a6226dbf86f6.61706358.jpg'),
(50, 472, 2, 'photo_67a6226e7e67c1.73276330.jpg'),
(51, 472, 2, 'photo_67a6226ed4f112.03680011.jpg'),
(52, 472, 2, 'photo_67a6226f4f1237.59260938.jpg'),
(53, 472, 2, 'photo_67a6226fb73687.09304031.jpg'),
(54, 472, 2, 'photo_67a622706512a7.70855810.jpg'),
(55, 472, 2, 'photo_67a62270b61b10.56531159.jpg'),
(56, 472, 2, 'photo_67a62270b68de2.80803660.jpg'),
(57, 472, 2, 'photo_67a62278295ae9.37818993.jpg'),
(58, 472, 2, 'photo_67a6227880ebf6.75842608.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `pid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `msg` mediumtext NOT NULL,
  `image` varchar(50) DEFAULT NULL,
  `type` varchar(1) NOT NULL DEFAULT 'p',
  `dop` timestamp NOT NULL DEFAULT current_timestamp(),
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `nom_photographe` varchar(100) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `date_capture` date DEFAULT NULL,
  `nom_auteur` varchar(100) NOT NULL,
  `mots_clés` text NOT NULL,
  `format_image` varchar(20) DEFAULT 'Non disponible',
  `mode_image` varchar(20) DEFAULT 'Non disponible',
  `taille_image` varchar(20) DEFAULT 'Non disponible',
  `donnees_exif` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`donnees_exif`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(20) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `email` varchar(256) NOT NULL,
  `password` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `photo_profil` varchar(20) NOT NULL,
  `telephone` int(10) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Invite',
  `statut` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `pseudo`, `prenom`, `nom`, `email`, `password`, `status`, `photo_profil`, `telephone`, `role`, `statut`) VALUES
(1, 'LeoM', ' Leo', 'Martin', 'LeoM@gmail.com', '$2y$10$i034JxpRjyl2pn75OVZ.N.yuCVJuo2jDEknGi7tF5KkvUihnbgTuu', 0, '', NULL, 'Administrateur', 0),
(2, 'MaxB', 'Max', 'Blanc', 'MaxB@gmail.com', '$2y$10$wB49ubfACgcHcKvEfzGuVuA4U5FToSefXT7cSwyqHcEXwh.iI12O.', 0, '', NULL, 'Membre', 0),
(372, 'AnnaB', 'Anna', 'Bernards', 'AnnaB@gmail.com', '$2y$10$JfzwtqbbRUYvHbojpAhsd.b.b4spGpkAJ8.guzVLk3oT1YmQzlctW', 0, '', 0, 'Administrateur', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `commentaire_p`
--
ALTER TABLE `commentaire_p`
  ADD PRIMARY KEY (`id_commentaire_p`),
  ADD KEY `uid` (`uid`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id_doccument`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`id_evenement`),
  ADD KEY `fk_evenement_utilisateur` (`uid`);

--
-- Indexes for table `evenement_participants`
--
ALTER TABLE `evenement_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_signup` (`id_evenement`,`uid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `photos_evenement`
--
ALTER TABLE `photos_evenement`
  ADD PRIMARY KEY (`id_photo`),
  ADD KEY `id_evenement` (`id_evenement`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `user_post` (`uid`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `commentaire_p`
--
ALTER TABLE `commentaire_p`
  MODIFY `id_commentaire_p` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `document`
--
ALTER TABLE `document`
  MODIFY `id_doccument` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `id_evenement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=474;

--
-- AUTO_INCREMENT for table `evenement_participants`
--
ALTER TABLE `evenement_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `photos_evenement`
--
ALTER TABLE `photos_evenement`
  MODIFY `id_photo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=853;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=531;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commentaire_p`
--
ALTER TABLE `commentaire_p`
  ADD CONSTRAINT `commentaire_p_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `publication` (`pid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `commentaire_p_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `evenement`
--
ALTER TABLE `evenement`
  ADD CONSTRAINT `fk_evenement_utilisateur` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `evenement_participants`
--
ALTER TABLE `evenement_participants`
  ADD CONSTRAINT `evenement_participants_ibfk_1` FOREIGN KEY (`id_evenement`) REFERENCES `evenement` (`id_evenement`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_participants_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `photos_evenement`
--
ALTER TABLE `photos_evenement`
  ADD CONSTRAINT `photos_evenement_ibfk_1` FOREIGN KEY (`id_evenement`) REFERENCES `evenement` (`id_evenement`),
  ADD CONSTRAINT `photos_evenement_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`id`);

--
-- Constraints for table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `user_post` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
