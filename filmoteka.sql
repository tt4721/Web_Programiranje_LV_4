-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2026 at 02:46 PM
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
-- Database: `filmoteka`
--

-- --------------------------------------------------------

--
-- Table structure for table `filmovi`
--

CREATE TABLE `filmovi` (
  `id` int(11) NOT NULL,
  `naslov` varchar(255) NOT NULL,
  `zanr` varchar(150) NOT NULL,
  `godina` smallint(5) UNSIGNED NOT NULL,
  `trajanje_min` smallint(5) UNSIGNED NOT NULL,
  `ocjena` decimal(3,1) NOT NULL,
  `rezisery` varchar(255) NOT NULL,
  `zemlja_porijekla` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filmovi`
--

INSERT INTO `filmovi` (`id`, `naslov`, `zanr`, `godina`, `trajanje_min`, `ocjena`, `rezisery`, `zemlja_porijekla`) VALUES
(1, 'The Shawshank Redemption', 'Drama', 1994, 142, 9.3, 'Frank Darabont', 'USA'),
(2, 'The Godfather', 'Crime, Drama', 1972, 175, 9.2, 'Francis Ford Coppola', 'USA'),
(3, 'The Dark Knight', 'Action, Crime', 2008, 152, 9.0, 'Christopher Nolan', 'UK/USA'),
(4, 'Schindler\'s List', 'Biography, Drama', 1993, 195, 9.0, 'Steven Spielberg', 'USA'),
(5, '12 Angry Men', 'Crime, Drama', 1957, 96, 9.0, 'Sidney Lumet', 'USA'),
(6, 'Pulp Fiction', 'Crime, Drama', 1994, 154, 8.9, 'Quentin Tarantino', 'USA'),
(7, 'The Lord of the Rings: The Return of the King', 'Action, Adventure', 2003, 201, 9.0, 'Peter Jackson', 'NZ/USA'),
(8, 'Il Buono, il Brutto, il Cattivo', 'Western', 1966, 161, 8.8, 'Sergio Leone', 'Italy'),
(9, 'Fight Club', 'Drama', 1999, 139, 8.8, 'David Fincher', 'USA'),
(10, 'Inception', 'Action, Adventure', 2010, 148, 8.8, 'Christopher Nolan', 'USA/UK'),
(11, 'The Matrix', 'Action, Sci-Fi', 1999, 136, 8.7, 'Lana Wachowski', 'USA'),
(12, 'Goodfellas', 'Biography, Crime', 1990, 145, 8.7, 'Martin Scorsese', 'USA'),
(13, 'One Flew Over the Cuckoo\'s Nest', 'Drama', 1975, 133, 8.7, 'Milos Forman', 'USA'),
(14, 'Seven Samurai', 'Action, Drama', 1954, 207, 8.6, 'Akira Kurosawa', 'Japan'),
(15, 'Se7en', 'Crime, Drama', 1995, 127, 8.6, 'David Fincher', 'USA'),
(16, 'The Silence of the Lambs', 'Crime, Drama', 1991, 118, 8.6, 'Jonathan Demme', 'USA'),
(17, 'City of God', 'Crime, Drama', 2002, 130, 8.6, 'Fernando Meirelles', 'Brazil'),
(18, 'Life Is Beautiful', 'Comedy, Drama', 1997, 116, 8.6, 'Roberto Benigni', 'Italy'),
(19, 'Interstellar', 'Adventure, Drama', 2014, 169, 8.7, 'Christopher Nolan', 'USA/UK'),
(20, 'Saving Private Ryan', 'Drama, War', 1998, 169, 8.6, 'Steven Spielberg', 'USA'),
(21, 'Parasite', 'Drama, Thriller', 2019, 132, 8.5, 'Bong Joon Ho', 'South Korea'),
(22, 'The Green Mile', 'Crime, Drama', 1999, 189, 8.6, 'Frank Darabont', 'USA'),
(23, 'Star Wars: Episode IV - A New Hope', 'Action, Adventure', 1977, 121, 8.6, 'George Lucas', 'USA'),
(24, 'Terminator 2: Judgment Day', 'Action, Sci-Fi', 1991, 137, 8.6, 'James Cameron', 'USA'),
(25, 'Back to the Future', 'Adventure, Comedy', 1985, 116, 8.5, 'Robert Zemeckis', 'USA'),
(26, 'The Pianist', 'Biography, Drama', 2002, 150, 8.5, 'Roman Polanski', 'France/Poland'),
(27, 'Psycho', 'Horror, Mystery', 1960, 109, 8.5, 'Alfred Hitchcock', 'USA'),
(28, 'Gladiator', 'Action, Adventure', 2000, 155, 8.5, 'Ridley Scott', 'USA/UK'),
(29, 'The Lion King', 'Animation, Adventure', 1994, 88, 8.5, 'Roger Allers', 'USA'),
(30, 'The Departed', 'Crime, Drama', 2006, 151, 8.5, 'Martin Scorsese', 'USA');

-- --------------------------------------------------------

--
-- Table structure for table `korisnici`
--

CREATE TABLE `korisnici` (
  `id` int(11) NOT NULL,
  `korisnicko_ime` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `lozinka_hash` varchar(255) NOT NULL,
  `uloga` enum('korisnik','administrator') NOT NULL DEFAULT 'korisnik',
  `datum_registracije` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `korisnici`
--

INSERT INTO `korisnici` (`id`, `korisnicko_ime`, `email`, `lozinka_hash`, `uloga`, `datum_registracije`) VALUES
(1, 'admin', 'admin@filmoteka.hr', '$2y$10$z0nrDed93S2W58FCJtFPR.YYqrTtaukuob63zPxUQa5FgZnRGCJDS', 'administrator', '2026-05-12 18:20:46'),
(5, 'tonitadic', 'tonitadic@gmail.com', '$2y$10$kO/gb6w/o.e5sDsrxHO0Qu/4z2FH2iwbHX.pFrodrajzCThEE07EK', 'korisnik', '2026-05-26 08:24:46'),
(6, 'peroperic', 'peroperic@gmail.com', '$2y$10$JXfxmI.rs1akjVILi1sjI.Hqd7nEEAgQlbl.J5Bc2.0rOh2aDj8aq', 'korisnik', '2026-05-26 08:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `ocjene`
--

CREATE TABLE `ocjene` (
  `id` int(11) NOT NULL,
  `id_korisnik` int(11) NOT NULL,
  `id_slika` int(11) NOT NULL,
  `ocjena` tinyint(4) NOT NULL CHECK (`ocjena` between 1 and 5),
  `vrijeme_ocjene` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ocjene`
--

INSERT INTO `ocjene` (`id`, `id_korisnik`, `id_slika`, `ocjena`, `vrijeme_ocjene`) VALUES
(7, 5, 3, 4, '2026-05-26 08:24:55'),
(8, 6, 3, 5, '2026-05-26 08:25:23'),
(9, 1, 3, 3, '2026-05-26 08:34:43');

-- --------------------------------------------------------

--
-- Table structure for table `slike`
--

CREATE TABLE `slike` (
  `id` int(11) NOT NULL,
  `naziv_datoteke` varchar(255) NOT NULL,
  `opis` varchar(500) DEFAULT NULL,
  `putanja` varchar(500) NOT NULL,
  `izvor` enum('lokalno','api') DEFAULT 'lokalno',
  `datum_dodavanja` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slike`
--

INSERT INTO `slike` (`id`, `naziv_datoteke`, `opis`, `putanja`, `izvor`, `datum_dodavanja`) VALUES
(3, 'fight_club.webp', NULL, 'images/fight_club.webp', 'lokalno', '2026-05-12 18:21:42'),
(4, 'interstellar.webp', NULL, 'images/interstellar.webp', 'lokalno', '2026-05-12 18:21:42'),
(5, 'matrix.webp', NULL, 'images/matrix.webp', 'lokalno', '2026-05-12 18:21:42'),
(6, 'the_dark_knight.webp', NULL, 'images/the_dark_knight.webp', 'lokalno', '2026-05-12 18:21:42'),
(7, 'the_godfather.webp', NULL, 'images/the_godfather.webp', 'lokalno', '2026-05-12 18:21:42'),
(8, 'the_shawshank_redemption.webp', NULL, 'images/the_shawshank_redemption.webp', 'lokalno', '2026-05-12 18:21:42'),
(9, 'slika_6a153d44d59e54.50973792.webp', '', 'slike/slika_6a153d44d59e54.50973792.webp', 'lokalno', '2026-05-26 08:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `zeljeni_filmovi`
--

CREATE TABLE `zeljeni_filmovi` (
  `id` int(11) NOT NULL,
  `id_korisnika` int(11) NOT NULL,
  `id_filma` int(11) NOT NULL,
  `datum_dodavanja` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zeljeni_filmovi`
--

INSERT INTO `zeljeni_filmovi` (`id`, `id_korisnika`, `id_filma`, `datum_dodavanja`) VALUES
(5, 1, 5, '2026-05-26 08:26:50'),
(6, 1, 25, '2026-05-26 08:33:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `filmovi`
--
ALTER TABLE `filmovi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `korisnici`
--
ALTER TABLE `korisnici`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `korisnicko_ime` (`korisnicko_ime`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ocjene`
--
ALTER TABLE `ocjene`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_korisnik_slika` (`id_korisnik`,`id_slika`),
  ADD KEY `id_slika` (`id_slika`);

--
-- Indexes for table `slike`
--
ALTER TABLE `slike`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `naziv_datoteke` (`naziv_datoteke`);

--
-- Indexes for table `zeljeni_filmovi`
--
ALTER TABLE `zeljeni_filmovi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_korisnik_film` (`id_korisnika`,`id_filma`),
  ADD KEY `id_filma` (`id_filma`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `filmovi`
--
ALTER TABLE `filmovi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `korisnici`
--
ALTER TABLE `korisnici`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ocjene`
--
ALTER TABLE `ocjene`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `slike`
--
ALTER TABLE `slike`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `zeljeni_filmovi`
--
ALTER TABLE `zeljeni_filmovi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ocjene`
--
ALTER TABLE `ocjene`
  ADD CONSTRAINT `ocjene_ibfk_1` FOREIGN KEY (`id_korisnik`) REFERENCES `korisnici` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ocjene_ibfk_2` FOREIGN KEY (`id_slika`) REFERENCES `slike` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `zeljeni_filmovi`
--
ALTER TABLE `zeljeni_filmovi`
  ADD CONSTRAINT `zeljeni_filmovi_ibfk_1` FOREIGN KEY (`id_korisnika`) REFERENCES `korisnici` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zeljeni_filmovi_ibfk_2` FOREIGN KEY (`id_filma`) REFERENCES `filmovi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
