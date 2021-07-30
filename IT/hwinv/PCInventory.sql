-- MySQL dump 10.13  Distrib 8.0.20, for Win64 (x86_64)
--
-- Host: localhost    Database: mysql-db
-- ------------------------------------------------------
-- Server version	8.0.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `hardware`
--

DROP TABLE IF EXISTS `hardware`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hardware` (
  `hostname` varchar(50) NOT NULL,
  `motherboard` mediumtext,
  `graphics` mediumtext,
  `CPU` mediumtext,
  `RAM_total` int DEFAULT NULL,
  `RAM_specs` mediumtext,
  `storage-primary` mediumtext,
  `storage-additional` mediumtext,
  `chipset` mediumtext,
  `BIOS_ver` double DEFAULT NULL,
  `CPU_specs` mediumtext,
  PRIMARY KEY (`hostname`),
  KEY `hostID_idx` (`hostname`),
  CONSTRAINT `hostname` FOREIGN KEY (`hostname`) REFERENCES `hosts` (`hostname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hosts` (
  `hostID` int NOT NULL AUTO_INCREMENT,
  `hostname` varchar(50) NOT NULL,
  `adminPIN` varchar(50) DEFAULT NULL,
  `serviceTag-serialNumber` varchar(50) DEFAULT NULL,
  `modelID` int DEFAULT '0',
  `typeID` int DEFAULT '0',
  `propertyTag` int DEFAULT '0',
  `deploydate` date DEFAULT NULL,
  `ldapUID` int DEFAULT '0',
  PRIMARY KEY (`hostname`),
  KEY `modelID` (`modelID`),
  KEY `typeID` (`typeID`),
  KEY `ldapUID` (`ldapUID`),
  KEY `hostID` (`hostID`),
  CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`modelID`) REFERENCES `models` (`modelID`),
  CONSTRAINT `hosts_ibfk_2` FOREIGN KEY (`typeID`) REFERENCES `hosttype` (`typeID`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hosttype`
--

DROP TABLE IF EXISTS `hosttype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hosttype` (
  `typeID` int NOT NULL DEFAULT '0',
  `hostType` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`typeID`),
  KEY `typeID` (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `macaddresses`
--

DROP TABLE IF EXISTS `macaddresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `macaddresses` (
  `macAddress` varchar(50) NOT NULL,
  `hostID` int DEFAULT '0',
  PRIMARY KEY (`macAddress`),
  KEY `hostID` (`hostID`),
  CONSTRAINT `macaddresses_ibfk_1` FOREIGN KEY (`hostID`) REFERENCES `hosts` (`hostID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `models` (
  `modelID` int NOT NULL AUTO_INCREMENT,
  `vendorID` int DEFAULT '0',
  `model` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`modelID`),
  KEY `vendorID` (`vendorID`),
  CONSTRAINT `models_ibfk_1` FOREIGN KEY (`vendorID`) REFERENCES `vendors` (`vendorID`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `note` (
  `noteID` int NOT NULL AUTO_INCREMENT,
  `ldapUID` int DEFAULT '0',
  `date-note` datetime DEFAULT NULL,
  `hostID` int NOT NULL DEFAULT '0',
  `text-note` longtext,
  PRIMARY KEY (`noteID`),
  KEY `hostID` (`hostID`),
  KEY `ldapUID` (`ldapUID`),
  KEY `ID` (`noteID`),
  CONSTRAINT `note_ibfk_1` FOREIGN KEY (`hostID`) REFERENCES `hosts` (`hostID`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status` (
  `statusID` int NOT NULL DEFAULT '0',
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`statusID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `vendorID` int NOT NULL AUTO_INCREMENT,
  `vendor` varchar(255) NOT NULL,
  PRIMARY KEY (`vendor`),
  KEY `vendorID` (`vendorID`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `windowskeys`
--

DROP TABLE IF EXISTS `windowskeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `windowskeys` (
  `windowsKey` varchar(50) NOT NULL,
  `hostID` int DEFAULT '0',
  `hostIDSticker` int DEFAULT '0',
  `winVerID` int DEFAULT '0',
  `statusID` int DEFAULT '0',
  PRIMARY KEY (`windowsKey`),
  KEY `hostID` (`hostID`),
  KEY `statusID` (`statusID`),
  KEY `winVerID` (`winVerID`),
  CONSTRAINT `windowskeys_ibfk_1` FOREIGN KEY (`hostID`) REFERENCES `hosts` (`hostID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `windowsversions`
--

DROP TABLE IF EXISTS `windowsversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `windowsversions` (
  `winVerID` int NOT NULL AUTO_INCREMENT,
  `windowsVersion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`winVerID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-03-12 14:25:01
