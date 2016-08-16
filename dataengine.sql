-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 16, 2016 at 10:37 PM
-- Server version: 10.0.25-MariaDB
-- PHP Version: 5.6.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dataengine`
--

-- --------------------------------------------------------

--
-- Table structure for table `dataengine_collection`
--

DROP TABLE IF EXISTS `dataengine_collection`;
CREATE TABLE `dataengine_collection` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `charset` varchar(255) NOT NULL,
  `idPlaceholderPrimary` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dataengine_collectionfields`
--

DROP TABLE IF EXISTS `dataengine_collectionfields`;
CREATE TABLE `dataengine_collectionfields` (
  `idCollection` int(11) UNSIGNED NOT NULL,
  `idField` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dataengine_connection`
--

DROP TABLE IF EXISTS `dataengine_connection`;
CREATE TABLE `dataengine_connection` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `hostport` smallint(5) UNSIGNED DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `resource` varchar(255) DEFAULT NULL,
  `extradata` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dataengine_field`
--

DROP TABLE IF EXISTS `dataengine_field`;
CREATE TABLE `dataengine_field` (
  `id` int(11) UNSIGNED NOT NULL,
  `idPlaceholder` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `format` varchar(255) NOT NULL,
  `charset` varchar(255) NOT NULL,
  `source` enum('original','filtered','cloned','') NOT NULL,
  `attributes` enum('primary','unique','index','') DEFAULT NULL,
  `transformation` text,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateEdition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dataengine_link`
--

DROP TABLE IF EXISTS `dataengine_link`;
CREATE TABLE `dataengine_link` (
  `idPlaceholderSrc` int(11) NOT NULL,
  `idPlaceholderDst` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `idFieldSrc0` int(11) NOT NULL,
  `idFieldDst0` int(11) NOT NULL,
  `idFieldSrc1` int(11) DEFAULT NULL,
  `idFieldDst1` int(11) DEFAULT NULL,
  `idFieldSrc2` int(11) DEFAULT NULL,
  `idFieldDst2` int(11) DEFAULT NULL,
  `idFieldSrc3` int(11) DEFAULT NULL,
  `idFieldDst3` int(11) DEFAULT NULL,
  `idFieldSrc4` int(11) DEFAULT NULL,
  `idFieldDst4` int(11) DEFAULT NULL,
  `idFieldSrc5` int(11) DEFAULT NULL,
  `idFieldDst5` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dataengine_placeholder`
--

DROP TABLE IF EXISTS `dataengine_placeholder`;
CREATE TABLE `dataengine_placeholder` (
  `id` int(11) UNSIGNED NOT NULL,
  `idConnection` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateEdition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rowsCount` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `dataengine_view_collections`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `dataengine_view_collections`;
CREATE TABLE `dataengine_view_collections` (
`CollectionId` int(11) unsigned
,`CollectionName` varchar(255)
,`URI` varchar(511)
,`FieldId` int(11) unsigned
,`FieldName` varchar(255)
,`FieldPath` varchar(255)
,`FieldTransformation` text
,`PlaceholderId` int(11) unsigned
,`PlaceholderName` varchar(255)
,`PlaceholderPath` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `dataengine_view_fields`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `dataengine_view_fields`;
CREATE TABLE `dataengine_view_fields` (
`URIPath` varchar(511)
,`URIName` text
,`PlaceholderId` int(11) unsigned
,`PlaceholderName` varchar(255)
,`PlaceholderPath` varchar(255)
,`FieldId` int(11) unsigned
,`FieldName` varchar(255)
,`FieldPath` varchar(255)
,`FieldTransformation` text
,`FieldAttributes` enum('primary','unique','index','')
);

-- --------------------------------------------------------

--
-- Structure for view `dataengine_view_collections`
--
DROP TABLE IF EXISTS `dataengine_view_collections`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dataengine_view_collections`  AS  select `cl`.`id` AS `CollectionId`,`cl`.`name` AS `CollectionName`,concat_ws('.',`p`.`path`,`f`.`path`) AS `URI`,`f`.`id` AS `FieldId`,`f`.`name` AS `FieldName`,`f`.`path` AS `FieldPath`,`f`.`transformation` AS `FieldTransformation`,`p`.`id` AS `PlaceholderId`,`p`.`name` AS `PlaceholderName`,`p`.`path` AS `PlaceholderPath` from (((`dataengine_collection` `cl` left join `dataengine_collectionfields` `clf` on((`clf`.`idCollection` = `cl`.`id`))) left join `dataengine_field` `f` on((`clf`.`idField` = `f`.`id`))) left join `dataengine_placeholder` `p` on((`f`.`idPlaceholder` = `p`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `dataengine_view_fields`
--
DROP TABLE IF EXISTS `dataengine_view_fields`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dataengine_view_fields`  AS  select concat_ws('.',`p`.`path`,`f`.`path`) AS `URIPath`,concat_ws(' - ',`p`.`name`,`f`.`name`) AS `URIName`,`p`.`id` AS `PlaceholderId`,`p`.`name` AS `PlaceholderName`,`p`.`path` AS `PlaceholderPath`,`f`.`id` AS `FieldId`,`f`.`name` AS `FieldName`,`f`.`path` AS `FieldPath`,`f`.`transformation` AS `FieldTransformation`,`f`.`attributes` AS `FieldAttributes` from (`dataengine_field` `f` left join `dataengine_placeholder` `p` on((`f`.`idPlaceholder` = `p`.`id`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dataengine_collection`
--
ALTER TABLE `dataengine_collection`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idFieldPrimary` (`idPlaceholderPrimary`);

--
-- Indexes for table `dataengine_collectionfields`
--
ALTER TABLE `dataengine_collectionfields`
  ADD PRIMARY KEY (`idCollection`,`idField`),
  ADD KEY `fk_de_collection_fields2field` (`idField`);

--
-- Indexes for table `dataengine_connection`
--
ALTER TABLE `dataengine_connection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dataengine_field`
--
ALTER TABLE `dataengine_field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_placeholder_id` (`idPlaceholder`);

--
-- Indexes for table `dataengine_link`
--
ALTER TABLE `dataengine_link`
  ADD PRIMARY KEY (`idPlaceholderSrc`,`idPlaceholderDst`),
  ADD KEY `idFieldDst0` (`idFieldDst0`),
  ADD KEY `idFieldSrc0` (`idFieldSrc0`);

--
-- Indexes for table `dataengine_placeholder`
--
ALTER TABLE `dataengine_placeholder`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dataengine_collection`
--
ALTER TABLE `dataengine_collection`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `dataengine_connection`
--
ALTER TABLE `dataengine_connection`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `dataengine_field`
--
ALTER TABLE `dataengine_field`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=322;
--
-- AUTO_INCREMENT for table `dataengine_placeholder`
--
ALTER TABLE `dataengine_placeholder`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `dataengine_collectionfields`
--
ALTER TABLE `dataengine_collectionfields`
  ADD CONSTRAINT `fk_de_collection_fields2collection` FOREIGN KEY (`idCollection`) REFERENCES `dataengine_collection` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_de_collection_fields2field` FOREIGN KEY (`idField`) REFERENCES `dataengine_field` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataengine_field`
--
ALTER TABLE `dataengine_field`
  ADD CONSTRAINT `fk_de_fields2de_placeholder` FOREIGN KEY (`idPlaceholder`) REFERENCES `dataengine_placeholder` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
