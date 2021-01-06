-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 06. Jan 2021 um 15:59
-- Server-Version: 10.4.13-MariaDB
-- PHP-Version: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `autosampler`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `as_status`
--

CREATE TABLE `as_status` (
  `as_status` tinyint(4) NOT NULL,
  `last_contact` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `as_status`
--

INSERT INTO `as_status` (`as_status`, `last_contact`) VALUES
(0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `config`
--

CREATE TABLE `config` (
  `NMRFolder` varchar(255) NOT NULL,
  `NMRIP` varchar(255) NOT NULL,
  `NMRPort` int(11) NOT NULL,
  `ASPort` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ACDFolder` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `config`
--

INSERT INTO `config` (`NMRFolder`, `NMRIP`, `NMRPort`, `ASPort`, `ACDFolder`) VALUES
('D:/BenchtopNMR/', 'localhost', 13000, 'COM4', 'C:/ACDFREE12/');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fnmr_standards`
--

CREATE TABLE `fnmr_standards` (
  `ID` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `shift` double NOT NULL,
  `fluorine_atoms` int(11) NOT NULL,
  `peakwidth_ppm` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `fnmr_standards`
--

INSERT INTO `fnmr_standards` (`ID`, `name`, `shift`, `fluorine_atoms`, `peakwidth_ppm`) VALUES
(1, 'Trifluorotoluene', -63.9, 3, 1),
(2, '2,2,2-Trifluoroethanol', -77.56, 3, 1),
(3, '1,4-Difluorobenzene', -119.9, 2, 1),
(4, 'CFCl3', 0, 1, 1),
(5, '(Trifluoromethoxy)benzene', -57.4, 3, 0.8);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `methods`
--

CREATE TABLE `methods` (
  `ID` int(11) NOT NULL,
  `User` int(11) DEFAULT NULL,
  `LB` float NOT NULL COMMENT 'line broadening',
  `Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `BaseLine` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SpAveraging',
  `BoxHalfWidth` int(11) NOT NULL DEFAULT 50,
  `NoiseFactor` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `peaks`
--

CREATE TABLE `peaks` (
  `ID` int(11) NOT NULL,
  `role` tinyint(4) NOT NULL COMMENT '0 = internal standard\r\n1 = starting material\r\n2 = product',
  `method` int(11) NOT NULL,
  `Eq` float NOT NULL,
  `nF` int(11) NOT NULL,
  `begin_ppm` float NOT NULL,
  `end_ppm` float NOT NULL,
  `reference_ppm` float NOT NULL,
  `reference_tolerance` float NOT NULL,
  `annotation` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `queueabort`
--

CREATE TABLE `queueabort` (
  `QueueStat` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `queueabort`
--

INSERT INTO `queueabort` (`QueueStat`) VALUES
(0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `samples`
--

CREATE TABLE `samples` (
  `ID` bigint(20) NOT NULL,
  `Holder` int(11) NOT NULL,
  `User` int(11) DEFAULT NULL,
  `Name` varchar(100) NOT NULL,
  `Solvent` varchar(100) NOT NULL,
  `Protocol` varchar(100) NOT NULL,
  `Number` int(11) NOT NULL,
  `RepTime` int(11) NOT NULL,
  `Standard` varchar(100) NOT NULL,
  `Eq` float DEFAULT NULL,
  `nF` int(11) DEFAULT NULL,
  `Date` bigint(20) NOT NULL,
  `Status` varchar(15) NOT NULL,
  `Progress` int(11) DEFAULT NULL,
  `SampleType` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Sample',
  `Method` int(11) DEFAULT NULL,
  `Result` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `shimming`
--

CREATE TABLE `shimming` (
  `Shimming` int(11) NOT NULL,
  `LastShim` bigint(20) NOT NULL,
  `ShimProgress` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `shimming`
--

INSERT INTO `shimming` (`Shimming`, `LastShim`, `ShimProgress`) VALUES
(0, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `shortname` varchar(3) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `fnmr_standards`
--
ALTER TABLE `fnmr_standards`
  ADD PRIMARY KEY (`ID`);

--
-- Indizes für die Tabelle `methods`
--
ALTER TABLE `methods`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `User` (`User`);

--
-- Indizes für die Tabelle `peaks`
--
ALTER TABLE `peaks`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `peaks_ibfk_1` (`method`);

--
-- Indizes für die Tabelle `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Method` (`Method`),
  ADD KEY `samples_ibfk_2` (`User`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `fnmr_standards`
--
ALTER TABLE `fnmr_standards`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT für Tabelle `methods`
--
ALTER TABLE `methods`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT für Tabelle `peaks`
--
ALTER TABLE `peaks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT für Tabelle `samples`
--
ALTER TABLE `samples`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `methods`
--
ALTER TABLE `methods`
  ADD CONSTRAINT `methods_ibfk_1` FOREIGN KEY (`User`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `peaks`
--
ALTER TABLE `peaks`
  ADD CONSTRAINT `peaks_ibfk_1` FOREIGN KEY (`method`) REFERENCES `methods` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `samples`
--
ALTER TABLE `samples`
  ADD CONSTRAINT `samples_ibfk_1` FOREIGN KEY (`Method`) REFERENCES `methods` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `samples_ibfk_2` FOREIGN KEY (`User`) REFERENCES `users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
