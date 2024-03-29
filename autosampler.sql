-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 07. Jun 2022 um 18:44
-- Server-Version: 10.4.14-MariaDB
-- PHP-Version: 7.4.11

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
-- Tabellenstruktur für Tabelle `methods`
--

CREATE TABLE `methods` (
  `ID` int(11) NOT NULL,
  `User` int(11) DEFAULT NULL,
  `Nucleus` int(11) NOT NULL DEFAULT 19,
  `LB` float NOT NULL COMMENT 'line broadening',
  `Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `BaseLine` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SpAveraging',
  `BoxHalfWidth` int(11) NOT NULL DEFAULT 50,
  `NoiseFactor` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nmr_standards`
--

CREATE TABLE `nmr_standards` (
  `ID` int(11) NOT NULL,
  `nucleus` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `shift` double NOT NULL,
  `number_of_atoms` int(11) NOT NULL,
  `peakwidth_ppm` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `nmr_standards`
--

INSERT INTO `nmr_standards` (`ID`, `nucleus`, `name`, `shift`, `number_of_atoms`, `peakwidth_ppm`) VALUES
(1, 19, 'Trifluorotoluene', -63.9, 3, 1),
(2, 19, '2,2,2-Trifluoroethanol', -77.56, 3, 1),
(3, 19, '1,4-Difluorobenzene', -119.9, 2, 1),
(4, 19, 'CFCl3', 0, 1, 1),
(5, 19, '(Trifluoromethoxy)benzene', -57.4, 3, 0.8),
(9, 1, 'Tetramethylsilane', 0, 12, 0.1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nuclei`
--

CREATE TABLE `nuclei` (
  `Mass` int(11) NOT NULL,
  `FriendlyName` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `nuclei`
--

INSERT INTO `nuclei` (`Mass`, `FriendlyName`) VALUES
(1, '1H'),
(19, '19F');

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
-- Tabellenstruktur für Tabelle `protocols`
--

CREATE TABLE `protocols` (
  `protocolid` int(11) NOT NULL,
  `nucleus` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `xmlKey` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `protocols`
--

INSERT INTO `protocols` (`protocolid`, `nucleus`, `name`, `xmlKey`) VALUES
(1, 1, '1D PROTON+', '1D EXTENDED+'),
(2, 19, '1D FLUORINE+', '1D FLUORINE+'),
(8, NULL, 'FLUORINE T1', 'FLUORINE T1');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `protocol_properties`
--

CREATE TABLE `protocol_properties` (
  `propid` int(11) NOT NULL,
  `protocolid` int(11) NOT NULL,
  `xmlKey` varchar(255) NOT NULL,
  `friendlyName` varchar(100) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `freeText` tinyint(1) NOT NULL DEFAULT 0,
  `defaultValue` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `protocol_properties`
--

INSERT INTO `protocol_properties` (`propid`, `protocolid`, `xmlKey`, `friendlyName`, `options`, `freeText`, `defaultValue`) VALUES
(1, 2, 'Number', 'Scans', '[1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096, 8192, 16384, 32768, 65536, 131072]', 0, '32'),
(2, 2, 'AcquisitionTime', 'Acq. Time [s]', '[0.33, 0.41, 0.65, 0.82, 1.31, 1.64, 2.62, 3.2, 6.4]', 0, '3.2'),
(3, 2, 'RepetitionTime', 'Rep. Time [s]', '[0.5, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 30, 60, 120, 180, 240, 300, 360, 420, 480, 540, 600]', 0, '15'),
(4, 2, 'centerFrequency', 'Center Freq. [ppm]', NULL, 1, '-71'),
(5, 2, 'Bandwidth', 'Bandwidth [ppm]', '[492, 1230, 2460]', 0, '492'),
(6, 8, 'Number', 'Scans', '[2,4,8,16,32,64,128,256,512,1024]', 0, '32'),
(7, 8, 'AcquisitionTime', 'Acq. Time [s]', '[0.4,0.8,1.6,3.2]', 0, '3.2'),
(8, 8, 'RepetitionTime', 'Rep. Time [s]', '[1, 2, 4, 7, 10, 15, 30, 60, 120, 300, 600, 1200, 1800]', 0, '30'),
(9, 8, 'MaximumInversionTime', 'Max. Inversion Time [ms]', '[20, 50, 100, 200, 500, 1000, 2000, 5000, 10000, 20000, 30000]', 0, '20000'),
(10, 8, 'Dummy', 'Dummy Scans', '[0, 2, 4]', 0, '2'),
(11, 8, 'NumberOfSteps', 'Number of Steps', '[5, 7, 9, 11, 15, 21, 27, 41, 64]', 0, '21'),
(12, 8, 'centerFrequency', 'Center Freq. [ppm]', NULL, 1, '-71'),
(13, 2, 'PulseAngle', 'Pulse Angle', '[5, 10, 15, 30, 45, 60, 90]', 0, '90'),
(14, 1, 'Number', 'Scans', '[1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096, 8192, 16384]', 0, '32'),
(15, 1, 'AcquisitionTime', 'Acq. Time [s]', '[0.4, 0.8, 1.6, 3.2, 6.4]', 0, '3.2'),
(16, 1, 'RepetitionTime', 'Rep. Time [s]', '[1, 2, 4, 7, 10, 15, 30, 60, 120, 180, 300, 420, 600]', 0, '10'),
(17, 1, 'PulseAngle', 'Pulse Angle', '[30, 45, 60, 90]', 0, '90');

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
  `Protocol` int(11) DEFAULT NULL,
  `Standard` varchar(100) NOT NULL,
  `Eq` float DEFAULT NULL,
  `nF` int(11) DEFAULT NULL,
  `Date` bigint(20) NOT NULL,
  `Status` varchar(15) NOT NULL,
  `Progress` int(11) DEFAULT NULL,
  `SampleType` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Sample',
  `Method` int(11) DEFAULT NULL,
  `Result` varchar(255) NOT NULL DEFAULT '',
  `StartDate` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sample_properties`
--

CREATE TABLE `sample_properties` (
  `samplepropid` int(11) NOT NULL,
  `sampleid` bigint(20) NOT NULL,
  `propid` int(11) NOT NULL,
  `strvalue` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- Indizes für die Tabelle `methods`
--
ALTER TABLE `methods`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `User` (`User`),
  ADD KEY `Nucleus` (`Nucleus`);

--
-- Indizes für die Tabelle `nmr_standards`
--
ALTER TABLE `nmr_standards`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `nucleus` (`nucleus`);

--
-- Indizes für die Tabelle `nuclei`
--
ALTER TABLE `nuclei`
  ADD PRIMARY KEY (`Mass`);

--
-- Indizes für die Tabelle `peaks`
--
ALTER TABLE `peaks`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `peaks_ibfk_1` (`method`);

--
-- Indizes für die Tabelle `protocols`
--
ALTER TABLE `protocols`
  ADD PRIMARY KEY (`protocolid`),
  ADD KEY `nucleus` (`nucleus`);

--
-- Indizes für die Tabelle `protocol_properties`
--
ALTER TABLE `protocol_properties`
  ADD PRIMARY KEY (`propid`),
  ADD KEY `protocolid` (`protocolid`);

--
-- Indizes für die Tabelle `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Method` (`Method`),
  ADD KEY `samples_ibfk_2` (`User`),
  ADD KEY `Protocol` (`Protocol`);

--
-- Indizes für die Tabelle `sample_properties`
--
ALTER TABLE `sample_properties`
  ADD PRIMARY KEY (`samplepropid`),
  ADD KEY `sampleid` (`sampleid`),
  ADD KEY `propid` (`propid`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `methods`
--
ALTER TABLE `methods`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT für Tabelle `nmr_standards`
--
ALTER TABLE `nmr_standards`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `peaks`
--
ALTER TABLE `peaks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT für Tabelle `protocols`
--
ALTER TABLE `protocols`
  MODIFY `protocolid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT für Tabelle `protocol_properties`
--
ALTER TABLE `protocol_properties`
  MODIFY `propid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT für Tabelle `samples`
--
ALTER TABLE `samples`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT für Tabelle `sample_properties`
--
ALTER TABLE `sample_properties`
  MODIFY `samplepropid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `methods`
--
ALTER TABLE `methods`
  ADD CONSTRAINT `methods_ibfk_1` FOREIGN KEY (`User`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `methods_ibfk_2` FOREIGN KEY (`Nucleus`) REFERENCES `nuclei` (`Mass`);

--
-- Constraints der Tabelle `nmr_standards`
--
ALTER TABLE `nmr_standards`
  ADD CONSTRAINT `nmr_standards_ibfk_1` FOREIGN KEY (`nucleus`) REFERENCES `nuclei` (`Mass`);

--
-- Constraints der Tabelle `peaks`
--
ALTER TABLE `peaks`
  ADD CONSTRAINT `peaks_ibfk_1` FOREIGN KEY (`method`) REFERENCES `methods` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `protocols`
--
ALTER TABLE `protocols`
  ADD CONSTRAINT `protocols_ibfk_1` FOREIGN KEY (`nucleus`) REFERENCES `nuclei` (`Mass`);

--
-- Constraints der Tabelle `protocol_properties`
--
ALTER TABLE `protocol_properties`
  ADD CONSTRAINT `protocol_properties_ibfk_1` FOREIGN KEY (`protocolid`) REFERENCES `protocols` (`protocolid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `samples`
--
ALTER TABLE `samples`
  ADD CONSTRAINT `samples_ibfk_1` FOREIGN KEY (`Method`) REFERENCES `methods` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `samples_ibfk_2` FOREIGN KEY (`User`) REFERENCES `users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `samples_ibfk_3` FOREIGN KEY (`Protocol`) REFERENCES `protocols` (`protocolid`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `sample_properties`
--
ALTER TABLE `sample_properties`
  ADD CONSTRAINT `sample_properties_ibfk_1` FOREIGN KEY (`sampleid`) REFERENCES `samples` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sample_properties_ibfk_2` FOREIGN KEY (`propid`) REFERENCES `protocol_properties` (`propid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
