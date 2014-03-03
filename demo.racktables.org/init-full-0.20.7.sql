-- MySQL dump 10.13  Distrib 5.5.31, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: racktables_db
-- ------------------------------------------------------
-- Server version	5.5.31-0ubuntu0.12.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Atom`
--

DROP TABLE IF EXISTS `Atom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Atom` (
  `molecule_id` int(10) unsigned DEFAULT NULL,
  `rack_id` int(10) unsigned DEFAULT NULL,
  `unit_no` int(10) unsigned DEFAULT NULL,
  `atom` enum('front','interior','rear') COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `Atom-FK-molecule_id` (`molecule_id`),
  KEY `Atom-FK-rack_id` (`rack_id`),
  CONSTRAINT `Atom-FK-molecule_id` FOREIGN KEY (`molecule_id`) REFERENCES `Molecule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `Atom-FK-rack_id` FOREIGN KEY (`rack_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Atom`
--

LOCK TABLES `Atom` WRITE;
/*!40000 ALTER TABLE `Atom` DISABLE KEYS */;
/*!40000 ALTER TABLE `Atom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Attribute`
--

DROP TABLE IF EXISTS `Attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('string','uint','float','dict','date') COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Attribute`
--
-- ORDER BY:  `id`

LOCK TABLES `Attribute` WRITE;
/*!40000 ALTER TABLE `Attribute` DISABLE KEYS */;
INSERT INTO `Attribute` VALUES (1,'string','OEM S/N 1');
INSERT INTO `Attribute` VALUES (2,'dict','HW type');
INSERT INTO `Attribute` VALUES (3,'string','FQDN');
INSERT INTO `Attribute` VALUES (4,'dict','SW type');
INSERT INTO `Attribute` VALUES (5,'string','SW version');
INSERT INTO `Attribute` VALUES (6,'uint','number of ports');
INSERT INTO `Attribute` VALUES (7,'float','max. current, Ampers');
INSERT INTO `Attribute` VALUES (8,'float','power load, percents');
INSERT INTO `Attribute` VALUES (13,'float','max power, Watts');
INSERT INTO `Attribute` VALUES (14,'string','contact person');
INSERT INTO `Attribute` VALUES (16,'uint','flash memory, MB');
INSERT INTO `Attribute` VALUES (17,'uint','DRAM, MB');
INSERT INTO `Attribute` VALUES (18,'uint','CPU, MHz');
INSERT INTO `Attribute` VALUES (20,'string','OEM S/N 2');
INSERT INTO `Attribute` VALUES (21,'date','support contract expiration');
INSERT INTO `Attribute` VALUES (22,'date','HW warranty expiration');
INSERT INTO `Attribute` VALUES (24,'date','SW warranty expiration');
INSERT INTO `Attribute` VALUES (25,'string','UUID');
INSERT INTO `Attribute` VALUES (26,'dict','Hypervisor');
INSERT INTO `Attribute` VALUES (27,'uint','Height, units');
INSERT INTO `Attribute` VALUES (28,'string','Slot number');
INSERT INTO `Attribute` VALUES (29,'uint','Sort order');
INSERT INTO `Attribute` VALUES (30,'dict','Mgmt type');
INSERT INTO `Attribute` VALUES (9999,'string','base MAC address');
/*!40000 ALTER TABLE `Attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AttributeMap`
--

DROP TABLE IF EXISTS `AttributeMap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AttributeMap` (
  `objtype_id` int(10) unsigned NOT NULL DEFAULT '1',
  `attr_id` int(10) unsigned NOT NULL DEFAULT '1',
  `chapter_id` int(10) unsigned DEFAULT NULL,
  `sticky` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  UNIQUE KEY `objtype_id` (`objtype_id`,`attr_id`),
  KEY `attr_id` (`attr_id`),
  KEY `chapter_id` (`chapter_id`),
  CONSTRAINT `AttributeMap-FK-chapter_id` FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`),
  CONSTRAINT `AttributeMap-FK-attr_id` FOREIGN KEY (`attr_id`) REFERENCES `Attribute` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AttributeMap`
--
-- ORDER BY:  `objtype_id`,`attr_id`

LOCK TABLES `AttributeMap` WRITE;
/*!40000 ALTER TABLE `AttributeMap` DISABLE KEYS */;
INSERT INTO `AttributeMap` VALUES (2,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (2,2,27,'no');
INSERT INTO `AttributeMap` VALUES (2,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (2,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,2,11,'no');
INSERT INTO `AttributeMap` VALUES (4,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,4,13,'no');
INSERT INTO `AttributeMap` VALUES (4,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,24,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,25,NULL,'no');
INSERT INTO `AttributeMap` VALUES (4,26,29,'yes');
INSERT INTO `AttributeMap` VALUES (4,28,NULL,'yes');
INSERT INTO `AttributeMap` VALUES (5,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (5,2,18,'no');
INSERT INTO `AttributeMap` VALUES (6,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (6,2,19,'no');
INSERT INTO `AttributeMap` VALUES (6,20,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,2,17,'no');
INSERT INTO `AttributeMap` VALUES (7,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,4,16,'no');
INSERT INTO `AttributeMap` VALUES (7,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,16,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,17,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,18,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (7,24,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,1,NULL,'yes');
INSERT INTO `AttributeMap` VALUES (8,2,12,'yes');
INSERT INTO `AttributeMap` VALUES (8,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,4,14,'yes');
INSERT INTO `AttributeMap` VALUES (8,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,16,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,17,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,18,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,20,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,24,NULL,'no');
INSERT INTO `AttributeMap` VALUES (8,28,NULL,'yes');
INSERT INTO `AttributeMap` VALUES (9,6,NULL,'no');
INSERT INTO `AttributeMap` VALUES (12,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (12,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (12,7,NULL,'no');
INSERT INTO `AttributeMap` VALUES (12,8,NULL,'no');
INSERT INTO `AttributeMap` VALUES (12,13,NULL,'no');
INSERT INTO `AttributeMap` VALUES (12,20,NULL,'no');
INSERT INTO `AttributeMap` VALUES (15,2,23,'no');
INSERT INTO `AttributeMap` VALUES (445,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (445,2,21,'no');
INSERT INTO `AttributeMap` VALUES (445,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (445,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (445,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (445,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (447,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (447,2,9999,'no');
INSERT INTO `AttributeMap` VALUES (447,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (447,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (447,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (447,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,2,24,'no');
INSERT INTO `AttributeMap` VALUES (798,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,16,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,17,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,18,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,20,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,24,NULL,'no');
INSERT INTO `AttributeMap` VALUES (798,28,NULL,'yes');
INSERT INTO `AttributeMap` VALUES (965,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (965,2,25,'no');
INSERT INTO `AttributeMap` VALUES (965,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (965,4,37,'no');
INSERT INTO `AttributeMap` VALUES (1055,2,26,'no');
INSERT INTO `AttributeMap` VALUES (1055,28,NULL,'yes');
INSERT INTO `AttributeMap` VALUES (1323,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1323,2,28,'no');
INSERT INTO `AttributeMap` VALUES (1323,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1323,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1397,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1397,2,34,'no');
INSERT INTO `AttributeMap` VALUES (1397,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1397,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1397,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1398,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1398,2,35,'no');
INSERT INTO `AttributeMap` VALUES (1398,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1398,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1398,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1502,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1502,2,31,'no');
INSERT INTO `AttributeMap` VALUES (1502,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1502,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1502,20,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1502,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1502,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,2,30,'no');
INSERT INTO `AttributeMap` VALUES (1503,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,4,14,'no');
INSERT INTO `AttributeMap` VALUES (1503,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,16,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,17,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,18,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,20,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1503,24,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1504,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1504,4,13,'no');
INSERT INTO `AttributeMap` VALUES (1504,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1504,24,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1505,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1506,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1506,17,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1506,18,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1507,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1507,2,32,'no');
INSERT INTO `AttributeMap` VALUES (1507,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1507,4,33,'no');
INSERT INTO `AttributeMap` VALUES (1507,5,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1507,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1507,20,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1507,21,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1507,22,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1560,27,NULL,'yes');
INSERT INTO `AttributeMap` VALUES (1560,29,NULL,'yes');
INSERT INTO `AttributeMap` VALUES (1562,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1644,1,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1644,2,36,'no');
INSERT INTO `AttributeMap` VALUES (1644,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1787,3,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1787,14,NULL,'no');
INSERT INTO `AttributeMap` VALUES (1787,30,38,'yes');
/*!40000 ALTER TABLE `AttributeMap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AttributeValue`
--

DROP TABLE IF EXISTS `AttributeValue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AttributeValue` (
  `object_id` int(10) unsigned NOT NULL,
  `object_tid` int(10) unsigned NOT NULL DEFAULT '0',
  `attr_id` int(10) unsigned NOT NULL,
  `string_value` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uint_value` int(10) unsigned DEFAULT NULL,
  `float_value` float DEFAULT NULL,
  PRIMARY KEY (`object_id`,`attr_id`),
  KEY `attr_id-uint_value` (`attr_id`,`uint_value`),
  KEY `attr_id-string_value` (`attr_id`,`string_value`(12)),
  KEY `id-tid` (`object_id`,`object_tid`),
  KEY `object_tid-attr_id` (`object_tid`,`attr_id`),
  CONSTRAINT `AttributeValue-FK-map` FOREIGN KEY (`object_tid`, `attr_id`) REFERENCES `AttributeMap` (`objtype_id`, `attr_id`),
  CONSTRAINT `AttributeValue-FK-object` FOREIGN KEY (`object_id`, `object_tid`) REFERENCES `Object` (`id`, `objtype_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AttributeValue`
--
-- ORDER BY:  `object_id`,`attr_id`

LOCK TABLES `AttributeValue` WRITE;
/*!40000 ALTER TABLE `AttributeValue` DISABLE KEYS */;
/*!40000 ALTER TABLE `AttributeValue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CachedPAV`
--

DROP TABLE IF EXISTS `CachedPAV`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CachedPAV` (
  `object_id` int(10) unsigned NOT NULL,
  `port_name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`port_name`,`vlan_id`),
  KEY `vlan_id` (`vlan_id`),
  CONSTRAINT `CachedPAV-FK-object-port` FOREIGN KEY (`object_id`, `port_name`) REFERENCES `CachedPVM` (`object_id`, `port_name`) ON DELETE CASCADE,
  CONSTRAINT `CachedPAV-FK-vlan_id` FOREIGN KEY (`vlan_id`) REFERENCES `VLANValidID` (`vlan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CachedPAV`
--
-- ORDER BY:  `object_id`,`port_name`,`vlan_id`

LOCK TABLES `CachedPAV` WRITE;
/*!40000 ALTER TABLE `CachedPAV` DISABLE KEYS */;
/*!40000 ALTER TABLE `CachedPAV` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CachedPNV`
--

DROP TABLE IF EXISTS `CachedPNV`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CachedPNV` (
  `object_id` int(10) unsigned NOT NULL,
  `port_name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`port_name`,`vlan_id`),
  UNIQUE KEY `port_id` (`object_id`,`port_name`),
  CONSTRAINT `CachedPNV-FK-compound` FOREIGN KEY (`object_id`, `port_name`, `vlan_id`) REFERENCES `CachedPAV` (`object_id`, `port_name`, `vlan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CachedPNV`
--
-- ORDER BY:  `object_id`,`port_name`,`vlan_id`

LOCK TABLES `CachedPNV` WRITE;
/*!40000 ALTER TABLE `CachedPNV` DISABLE KEYS */;
/*!40000 ALTER TABLE `CachedPNV` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CachedPVM`
--

DROP TABLE IF EXISTS `CachedPVM`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CachedPVM` (
  `object_id` int(10) unsigned NOT NULL,
  `port_name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `vlan_mode` enum('access','trunk') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'access',
  PRIMARY KEY (`object_id`,`port_name`),
  CONSTRAINT `CachedPVM-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CachedPVM`
--
-- ORDER BY:  `object_id`,`port_name`

LOCK TABLES `CachedPVM` WRITE;
/*!40000 ALTER TABLE `CachedPVM` DISABLE KEYS */;
/*!40000 ALTER TABLE `CachedPVM` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CactiGraph`
--

DROP TABLE IF EXISTS `CactiGraph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CactiGraph` (
  `object_id` int(10) unsigned NOT NULL,
  `server_id` int(10) unsigned NOT NULL,
  `graph_id` int(10) unsigned NOT NULL,
  `caption` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`server_id`,`graph_id`),
  KEY `object_id` (`object_id`),
  KEY `graph_id` (`graph_id`),
  CONSTRAINT `CactiGraph-FK-server_id` FOREIGN KEY (`server_id`) REFERENCES `CactiServer` (`id`),
  CONSTRAINT `CactiGraph-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CactiGraph`
--
-- ORDER BY:  `server_id`,`graph_id`

LOCK TABLES `CactiGraph` WRITE;
/*!40000 ALTER TABLE `CactiGraph` DISABLE KEYS */;
/*!40000 ALTER TABLE `CactiGraph` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CactiServer`
--

DROP TABLE IF EXISTS `CactiServer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CactiServer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `base_url` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CactiServer`
--
-- ORDER BY:  `id`

LOCK TABLES `CactiServer` WRITE;
/*!40000 ALTER TABLE `CactiServer` DISABLE KEYS */;
/*!40000 ALTER TABLE `CactiServer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Chapter`
--

DROP TABLE IF EXISTS `Chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sticky` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `name` char(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Chapter`
--
-- ORDER BY:  `id`

LOCK TABLES `Chapter` WRITE;
/*!40000 ALTER TABLE `Chapter` DISABLE KEYS */;
INSERT INTO `Chapter` VALUES (1,'yes','ObjectType');
INSERT INTO `Chapter` VALUES (2,'yes','PortOuterInterface');
INSERT INTO `Chapter` VALUES (11,'no','server models');
INSERT INTO `Chapter` VALUES (12,'no','network switch models');
INSERT INTO `Chapter` VALUES (13,'no','server OS type');
INSERT INTO `Chapter` VALUES (14,'no','switch OS type');
INSERT INTO `Chapter` VALUES (16,'no','router OS type');
INSERT INTO `Chapter` VALUES (17,'no','router models');
INSERT INTO `Chapter` VALUES (18,'no','disk array models');
INSERT INTO `Chapter` VALUES (19,'no','tape library models');
INSERT INTO `Chapter` VALUES (21,'no','KVM switch models');
INSERT INTO `Chapter` VALUES (23,'no','console models');
INSERT INTO `Chapter` VALUES (24,'no','network security models');
INSERT INTO `Chapter` VALUES (25,'no','wireless models');
INSERT INTO `Chapter` VALUES (26,'no','fibre channel switch models');
INSERT INTO `Chapter` VALUES (27,'no','PDU models');
INSERT INTO `Chapter` VALUES (28,'no','Voice/video hardware');
INSERT INTO `Chapter` VALUES (29,'no','Yes/No');
INSERT INTO `Chapter` VALUES (30,'no','network chassis models');
INSERT INTO `Chapter` VALUES (31,'no','server chassis models');
INSERT INTO `Chapter` VALUES (32,'no','virtual switch models');
INSERT INTO `Chapter` VALUES (33,'no','virtual switch OS type');
INSERT INTO `Chapter` VALUES (34,'no','power supply chassis models');
INSERT INTO `Chapter` VALUES (35,'no','power supply models');
INSERT INTO `Chapter` VALUES (36,'no','serial console server models');
INSERT INTO `Chapter` VALUES (37,'no','wireless OS type');
INSERT INTO `Chapter` VALUES (38,'no','management interface type');
INSERT INTO `Chapter` VALUES (9999,'no','multiplexer models');
/*!40000 ALTER TABLE `Chapter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Config`
--

DROP TABLE IF EXISTS `Config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Config` (
  `varname` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `varvalue` text COLLATE utf8_unicode_ci NOT NULL,
  `vartype` enum('string','uint') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'string',
  `emptyok` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `is_hidden` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `is_userdefined` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`varname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Config`
--
-- ORDER BY:  `varname`

LOCK TABLES `Config` WRITE;
/*!40000 ALTER TABLE `Config` DISABLE KEYS */;
INSERT INTO `Config` VALUES ('8021Q_DEPLOY_MAXAGE','3600','uint','no','no','no','802.1Q deploy maximum age');
INSERT INTO `Config` VALUES ('8021Q_DEPLOY_MINAGE','300','uint','no','no','no','802.1Q deploy minimum age');
INSERT INTO `Config` VALUES ('8021Q_DEPLOY_RETRY','10800','uint','no','no','no','802.1Q deploy retry timer');
INSERT INTO `Config` VALUES ('8021Q_EXTSYNC_LISTSRC','false','string','yes','no','no','List source: objects with extended 802.1Q sync');
INSERT INTO `Config` VALUES ('8021Q_INSTANT_DEPLOY','no','string','no','no','yes','802.1Q: instant deploy');
INSERT INTO `Config` VALUES ('8021Q_MULTILINK_LISTSRC','false','string','yes','no','no','List source: IPv4/IPv6 networks allowing multiple VLANs from same domain');
INSERT INTO `Config` VALUES ('8021Q_WRI_AFTER_CONFT_LISTSRC','false','string','no','no','no','802.1Q: save device configuration after deploy (RackCode)');
INSERT INTO `Config` VALUES ('ADDNEW_AT_TOP','yes','string','no','no','yes','Render \"add new\" line at top of the list');
INSERT INTO `Config` VALUES ('ASSETWARN_LISTSRC','{$typeid_4} or {$typeid_7} or {$typeid_8}','string','yes','no','no','List source: objects for that asset tag should be set');
INSERT INTO `Config` VALUES ('AUTOPORTS_CONFIG','4 = 1*33*kvm + 2*24*eth%u;15 = 1*446*kvm','string','yes','no','no','AutoPorts configuration');
INSERT INTO `Config` VALUES ('CACTI_LISTSRC','false','string','yes','no','no','List of object with Cacti graphs');
INSERT INTO `Config` VALUES ('CDP_RUNNERS_LISTSRC','','string','yes','no','no','List of devices running CDP');
INSERT INTO `Config` VALUES ('DATETIME_FORMAT','%Y-%m-%d','string','no','no','yes','PHP strftime() format to use for date output');
INSERT INTO `Config` VALUES ('DATETIME_ZONE','UTC','string','yes','no','yes','Timezone to use for displaying/calculating dates');
INSERT INTO `Config` VALUES ('DB_VERSION','0.20.7','string','no','yes','no','Database version.');
INSERT INTO `Config` VALUES ('DEFAULT_IPV4_RS_INSERVICE','no','string','no','no','yes','Inservice status for new SLB real servers');
INSERT INTO `Config` VALUES ('DEFAULT_OBJECT_TYPE','4','uint','yes','no','yes','Default object type for new objects');
INSERT INTO `Config` VALUES ('DEFAULT_PORT_IIF_ID','1','uint','no','no','no','Default port inner interface ID');
INSERT INTO `Config` VALUES ('DEFAULT_PORT_OIF_IDS','1=24; 3=1078; 4=1077; 5=1079; 6=1080; 8=1082; 9=1084; 10=1588; 11=1668','string','no','no','no','Default port outer interface IDs');
INSERT INTO `Config` VALUES ('DEFAULT_RACK_HEIGHT','42','uint','yes','no','yes','Default rack height');
INSERT INTO `Config` VALUES ('DEFAULT_SLB_RS_PORT','','uint','yes','no','yes','Default port of SLB real server');
INSERT INTO `Config` VALUES ('DEFAULT_SLB_VS_PORT','','uint','yes','no','yes','Default port of SLB virtual service');
INSERT INTO `Config` VALUES ('DEFAULT_SNMP_COMMUNITY','public','string','no','no','no','Default SNMP Community string');
INSERT INTO `Config` VALUES ('DEFAULT_VDOM_ID','','uint','yes','no','yes','Default VLAN domain ID');
INSERT INTO `Config` VALUES ('DEFAULT_VST_ID','','uint','yes','no','yes','Default VLAN switch template ID');
INSERT INTO `Config` VALUES ('DETECT_URLS','no','string','yes','no','yes','Detect URLs in text fields');
INSERT INTO `Config` VALUES ('ENABLE_BULKPORT_FORM','yes','string','no','no','yes','Enable \"Bulk Port\" form');
INSERT INTO `Config` VALUES ('ENABLE_MULTIPORT_FORM','no','string','no','no','yes','Enable \"Add/update multiple ports\" form');
INSERT INTO `Config` VALUES ('enterprise','MyCompanyName','string','no','no','no','Organization name');
INSERT INTO `Config` VALUES ('EXT_IPV4_VIEW','yes','string','no','no','yes','Extended IPv4 view');
INSERT INTO `Config` VALUES ('FILTER_DEFAULT_ANDOR','and','string','no','no','yes','Default list filter boolean operation (or/and)');
INSERT INTO `Config` VALUES ('FILTER_PREDICATE_SIEVE','','string','yes','no','yes','Predicate sieve regex(7)');
INSERT INTO `Config` VALUES ('FILTER_RACKLIST_BY_TAGS','yes','string','yes','no','yes','Rackspace: show only racks matching the current object\'s tags');
INSERT INTO `Config` VALUES ('FILTER_SUGGEST_ANDOR','yes','string','no','no','yes','Suggest and/or selector in list filter');
INSERT INTO `Config` VALUES ('FILTER_SUGGEST_EXTRA','yes','string','no','no','yes','Suggest extra expression in list filter');
INSERT INTO `Config` VALUES ('FILTER_SUGGEST_PREDICATES','yes','string','no','no','yes','Suggest predicates in list filter');
INSERT INTO `Config` VALUES ('FILTER_SUGGEST_TAGS','yes','string','no','no','yes','Suggest tags in list filter');
INSERT INTO `Config` VALUES ('IPV4_ADDRS_PER_PAGE','256','uint','no','no','yes','IPv4 addresses per page');
INSERT INTO `Config` VALUES ('IPV4_AUTO_RELEASE','1','uint','no','no','yes','Auto-release IPv4 addresses on allocation');
INSERT INTO `Config` VALUES ('IPV4_ENABLE_KNIGHT','yes','string','no','no','yes','Enable IPv4 knight feature');
INSERT INTO `Config` VALUES ('IPV4_JAYWALK','no','string','no','no','no','Enable IPv4 address allocations w/o covering network');
INSERT INTO `Config` VALUES ('IPV4_TREE_RTR_AS_CELL','no','string','no','no','yes','Show full router info for each network in IPv4 tree view');
INSERT INTO `Config` VALUES ('IPV4_TREE_SHOW_USAGE','no','string','no','no','yes','Show address usage in IPv4 tree');
INSERT INTO `Config` VALUES ('IPV4_TREE_SHOW_VLAN','yes','string','no','no','yes','Show VLAN for each network in IPv4 tree');
INSERT INTO `Config` VALUES ('IPV4LB_LISTSRC','false','string','yes','no','no','List source: IPv4 load balancers');
INSERT INTO `Config` VALUES ('IPV4NAT_LISTSRC','{$typeid_4} or {$typeid_7} or {$typeid_8} or {$typeid_798}','string','yes','no','no','List source: IPv4 NAT performers');
INSERT INTO `Config` VALUES ('IPV4OBJ_LISTSRC','not ({$typeid_3} or {$typeid_9} or {$typeid_10} or {$typeid_11})','string','yes','no','no','List source: IPv4-enabled objects');
INSERT INTO `Config` VALUES ('LLDP_RUNNERS_LISTSRC','','string','yes','no','no','List of devices running LLDP');
INSERT INTO `Config` VALUES ('MASSCOUNT','8','uint','no','no','yes','&quot;Fast&quot; form is this many records tall');
INSERT INTO `Config` VALUES ('MAX_UNFILTERED_ENTITIES','0','uint','no','no','yes','Max item count to display on unfiltered result page');
INSERT INTO `Config` VALUES ('MAXSELSIZE','30','uint','no','no','yes','&lt;SELECT&gt; lists height');
INSERT INTO `Config` VALUES ('MGMT_PROTOS','ssh: {$typeid_4}; telnet: {$typeid_8}','string','yes','no','yes','Mapping of management protocol to devices');
INSERT INTO `Config` VALUES ('MUNIN_LISTSRC','false','string','yes','no','no','List of object with Munin graphs');
INSERT INTO `Config` VALUES ('NAMEWARN_LISTSRC','{$typeid_4} or {$typeid_7} or {$typeid_8}','string','yes','no','no','List source: objects for that common name should be set');
INSERT INTO `Config` VALUES ('NEAREST_RACKS_CHECKBOX','yes','string','yes','no','yes','Enable nearest racks in port list filter by default');
INSERT INTO `Config` VALUES ('PORT_EXCLUSION_LISTSRC','{$typeid_3} or {$typeid_10} or {$typeid_11} or {$typeid_1505} or {$typeid_1506}','string','yes','no','no','List source: objects without ports');
INSERT INTO `Config` VALUES ('PREVIEW_IMAGE_MAXPXS','320','uint','yes','no','yes','Max pixels per axis for image file preview');
INSERT INTO `Config` VALUES ('PREVIEW_TEXT_COLS','80','uint','yes','no','yes','Columns for text file preview');
INSERT INTO `Config` VALUES ('PREVIEW_TEXT_MAXCHARS','10240','uint','yes','no','yes','Max chars for text file preview');
INSERT INTO `Config` VALUES ('PREVIEW_TEXT_ROWS','25','uint','yes','no','yes','Rows for text file preview');
INSERT INTO `Config` VALUES ('PROXIMITY_RANGE','0','uint','yes','no','yes','Proximity range (0 is current rack only)');
INSERT INTO `Config` VALUES ('QUICK_LINK_PAGES','depot,ipv4space,rackspace','string','yes','no','yes','List of pages to dislay in quick links');
INSERT INTO `Config` VALUES ('RACK_PRESELECT_THRESHOLD','1','uint','no','no','yes','Rack pre-selection threshold');
INSERT INTO `Config` VALUES ('RACKS_PER_ROW','12','uint','yes','no','yes','Racks per row');
INSERT INTO `Config` VALUES ('REVERSED_RACKS_LISTSRC','false','string','yes','no','no','List of racks with reversed (top to bottom) units order');
INSERT INTO `Config` VALUES ('ROW_SCALE','2','uint','no','no','yes','Picture scale for rack row display');
INSERT INTO `Config` VALUES ('SEARCH_DOMAINS','','string','yes','no','yes','DNS domain list (comma-separated) to search in FQDN attributes');
INSERT INTO `Config` VALUES ('SHOW_AUTOMATIC_TAGS','no','string','no','no','yes','Show automatic tags');
INSERT INTO `Config` VALUES ('SHOW_EXPLICIT_TAGS','yes','string','no','no','yes','Show explicit tags');
INSERT INTO `Config` VALUES ('SHOW_IMPLICIT_TAGS','yes','string','no','no','yes','Show implicit tags');
INSERT INTO `Config` VALUES ('SHOW_LAST_TAB','yes','string','yes','no','yes','Remember last tab shown for each page');
INSERT INTO `Config` VALUES ('SHRINK_TAG_TREE_ON_CLICK','yes','string','no','no','yes','Dynamically hide useless tags in tagtree');
INSERT INTO `Config` VALUES ('STATIC_FILTER','yes','string','no','no','yes','Enable Filter Caching');
INSERT INTO `Config` VALUES ('SYNC_802Q_LISTSRC','','string','yes','no','no','List of VLAN switches sync is enabled on');
INSERT INTO `Config` VALUES ('SYNCDOMAIN_MAX_PROCESSES','0','uint','yes','no','no','How many worker proceses syncdomain cron script should create');
INSERT INTO `Config` VALUES ('TAGS_QUICKLIST_SIZE','20','uint','no','no','yes','Tags quick list size');
INSERT INTO `Config` VALUES ('TAGS_QUICKLIST_THRESHOLD','50','uint','yes','no','yes','Tags quick list threshold');
INSERT INTO `Config` VALUES ('TAGS_TOPLIST_SIZE','50','uint','yes','no','yes','Tags top list size');
INSERT INTO `Config` VALUES ('TREE_THRESHOLD','25','uint','yes','no','yes','Tree view auto-collapse threshold');
INSERT INTO `Config` VALUES ('VENDOR_SIEVE','','string','yes','no','yes','Vendor sieve configuration');
INSERT INTO `Config` VALUES ('VIRTUAL_OBJ_LISTSRC','1504,1505,1506,1507','string','no','no','no','List source: virtual objects');
INSERT INTO `Config` VALUES ('VLANIPV4NET_LISTSRC','','string','yes','no','yes','List of VLAN-based IPv4 networks');
INSERT INTO `Config` VALUES ('VLANSWITCH_LISTSRC','','string','yes','no','yes','List of VLAN running switches');
/*!40000 ALTER TABLE `Config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Dictionary`
--

DROP TABLE IF EXISTS `Dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Dictionary` (
  `chapter_id` int(10) unsigned NOT NULL,
  `dict_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dict_sticky` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `dict_value` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`dict_key`),
  UNIQUE KEY `dict_unique` (`chapter_id`,`dict_value`,`dict_sticky`),
  CONSTRAINT `Dictionary-FK-chapter_id` FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Dictionary`
--
-- ORDER BY:  `dict_key`

LOCK TABLES `Dictionary` WRITE;
/*!40000 ALTER TABLE `Dictionary` DISABLE KEYS */;
INSERT INTO `Dictionary` VALUES (1,1,'yes','BlackBox');
INSERT INTO `Dictionary` VALUES (1,2,'yes','PDU');
INSERT INTO `Dictionary` VALUES (1,3,'yes','Shelf');
INSERT INTO `Dictionary` VALUES (1,4,'yes','Server');
INSERT INTO `Dictionary` VALUES (1,5,'yes','DiskArray');
INSERT INTO `Dictionary` VALUES (1,6,'yes','TapeLibrary');
INSERT INTO `Dictionary` VALUES (1,7,'yes','Router');
INSERT INTO `Dictionary` VALUES (1,8,'yes','Network switch');
INSERT INTO `Dictionary` VALUES (1,9,'yes','PatchPanel');
INSERT INTO `Dictionary` VALUES (1,10,'yes','CableOrganizer');
INSERT INTO `Dictionary` VALUES (1,11,'yes','spacer');
INSERT INTO `Dictionary` VALUES (1,12,'yes','UPS');
INSERT INTO `Dictionary` VALUES (1,13,'yes','Modem');
INSERT INTO `Dictionary` VALUES (1,14,'yes','MediaConverter');
INSERT INTO `Dictionary` VALUES (1,15,'yes','console');
INSERT INTO `Dictionary` VALUES (2,16,'yes','AC-in');
INSERT INTO `Dictionary` VALUES (2,17,'yes','10Base2');
INSERT INTO `Dictionary` VALUES (2,18,'yes','10Base-T');
INSERT INTO `Dictionary` VALUES (2,19,'yes','100Base-TX');
INSERT INTO `Dictionary` VALUES (2,24,'yes','1000Base-T');
INSERT INTO `Dictionary` VALUES (2,29,'yes','RS-232 (RJ-45)');
INSERT INTO `Dictionary` VALUES (2,30,'yes','10GBase-SR');
INSERT INTO `Dictionary` VALUES (2,31,'yes','virtual bridge');
INSERT INTO `Dictionary` VALUES (2,32,'yes','sync serial');
INSERT INTO `Dictionary` VALUES (2,33,'yes','KVM (host)');
INSERT INTO `Dictionary` VALUES (2,34,'yes','1000Base-ZX');
INSERT INTO `Dictionary` VALUES (2,35,'yes','10GBase-ER');
INSERT INTO `Dictionary` VALUES (2,36,'yes','10GBase-LR');
INSERT INTO `Dictionary` VALUES (2,37,'yes','10GBase-LRM');
INSERT INTO `Dictionary` VALUES (2,38,'yes','10GBase-ZR');
INSERT INTO `Dictionary` VALUES (2,39,'yes','10GBase-LX4');
INSERT INTO `Dictionary` VALUES (2,40,'yes','10GBase-CX4');
INSERT INTO `Dictionary` VALUES (2,41,'yes','10GBase-Kx');
INSERT INTO `Dictionary` VALUES (11,42,'yes','noname/unknown');
INSERT INTO `Dictionary` VALUES (11,43,'yes','IBM xSeries%GPASS%305');
INSERT INTO `Dictionary` VALUES (11,44,'yes','IBM xSeries%GPASS%306');
INSERT INTO `Dictionary` VALUES (11,45,'yes','IBM xSeries%GPASS%306m');
INSERT INTO `Dictionary` VALUES (11,46,'yes','IBM xSeries%GPASS%326m');
INSERT INTO `Dictionary` VALUES (11,47,'yes','IBM xSeries%GPASS%330');
INSERT INTO `Dictionary` VALUES (11,48,'yes','IBM xSeries%GPASS%335');
INSERT INTO `Dictionary` VALUES (11,49,'yes','Sun%GPASS%Ultra 10');
INSERT INTO `Dictionary` VALUES (11,50,'yes','Sun%GPASS%Enterprise 420R');
INSERT INTO `Dictionary` VALUES (11,51,'yes','Sun%GPASS%Fire X2100');
INSERT INTO `Dictionary` VALUES (11,52,'yes','Sun%GPASS%Fire E4900');
INSERT INTO `Dictionary` VALUES (11,53,'yes','Sun%GPASS%Netra X1');
INSERT INTO `Dictionary` VALUES (11,54,'yes','IBM xSeries%GPASS%346');
INSERT INTO `Dictionary` VALUES (11,55,'yes','Dell PowerEdge%GPASS%1650');
INSERT INTO `Dictionary` VALUES (11,56,'yes','Dell PowerEdge%GPASS%2850');
INSERT INTO `Dictionary` VALUES (11,57,'yes','Sun%GPASS%Fire V210');
INSERT INTO `Dictionary` VALUES (11,58,'yes','Sun%GPASS%Fire V240');
INSERT INTO `Dictionary` VALUES (11,59,'yes','IBM xSeries%GPASS%326');
INSERT INTO `Dictionary` VALUES (11,60,'yes','Sun%GPASS%Netra t1 105');
INSERT INTO `Dictionary` VALUES (11,61,'yes','Sun%GPASS%Enterprise 4500');
INSERT INTO `Dictionary` VALUES (11,62,'yes','Dell PowerEdge%GPASS%1950');
INSERT INTO `Dictionary` VALUES (11,63,'yes','Dell PowerEdge%GPASS%1550');
INSERT INTO `Dictionary` VALUES (11,64,'yes','Sun%GPASS%Ultra 5');
INSERT INTO `Dictionary` VALUES (11,65,'yes','Dell PowerEdge%GPASS%2950');
INSERT INTO `Dictionary` VALUES (11,66,'yes','Dell PowerEdge%GPASS%650');
INSERT INTO `Dictionary` VALUES (11,67,'yes','Dell PowerEdge%GPASS%4600');
INSERT INTO `Dictionary` VALUES (11,68,'yes','IBM xSeries%GPASS%3250');
INSERT INTO `Dictionary` VALUES (11,69,'yes','IBM xSeries%GPASS%3455');
INSERT INTO `Dictionary` VALUES (11,70,'yes','IBM xSeries%GPASS%3550');
INSERT INTO `Dictionary` VALUES (11,71,'yes','IBM xSeries%GPASS%3650');
INSERT INTO `Dictionary` VALUES (11,72,'yes','IBM xSeries%GPASS%3655');
INSERT INTO `Dictionary` VALUES (11,73,'yes','IBM xSeries%GPASS%3650 T');
INSERT INTO `Dictionary` VALUES (11,74,'yes','IBM xSeries%GPASS%3755');
INSERT INTO `Dictionary` VALUES (11,75,'yes','IBM xSeries%GPASS%3850');
INSERT INTO `Dictionary` VALUES (11,76,'yes','Sun%GPASS%Fire X4600');
INSERT INTO `Dictionary` VALUES (11,77,'yes','Sun%GPASS%Fire X4500');
INSERT INTO `Dictionary` VALUES (11,78,'yes','Sun%GPASS%Fire X4200');
INSERT INTO `Dictionary` VALUES (11,79,'yes','Sun%GPASS%Fire X4100');
INSERT INTO `Dictionary` VALUES (11,80,'yes','Sun%GPASS%Fire X2100 M2');
INSERT INTO `Dictionary` VALUES (11,81,'yes','Sun%GPASS%Fire X2200 M2');
INSERT INTO `Dictionary` VALUES (11,82,'yes','Sun%GPASS%Fire V40z');
INSERT INTO `Dictionary` VALUES (11,83,'yes','Sun%GPASS%Fire V125');
INSERT INTO `Dictionary` VALUES (11,84,'yes','Sun%GPASS%Fire V215');
INSERT INTO `Dictionary` VALUES (11,85,'yes','Sun%GPASS%Fire V245');
INSERT INTO `Dictionary` VALUES (11,86,'yes','Sun%GPASS%Fire V445');
INSERT INTO `Dictionary` VALUES (11,87,'yes','Sun%GPASS%Fire V440');
INSERT INTO `Dictionary` VALUES (11,88,'yes','Sun%GPASS%Fire V490');
INSERT INTO `Dictionary` VALUES (11,89,'yes','Sun%GPASS%Fire V890');
INSERT INTO `Dictionary` VALUES (11,90,'yes','Sun%GPASS%Fire E2900');
INSERT INTO `Dictionary` VALUES (11,91,'yes','Sun%GPASS%Fire V1280');
INSERT INTO `Dictionary` VALUES (11,92,'yes','IBM pSeries%GPASS%185');
INSERT INTO `Dictionary` VALUES (11,93,'yes','IBM pSeries%GPASS%505');
INSERT INTO `Dictionary` VALUES (11,94,'yes','IBM pSeries%GPASS%505Q');
INSERT INTO `Dictionary` VALUES (11,95,'yes','IBM pSeries%GPASS%510');
INSERT INTO `Dictionary` VALUES (11,96,'yes','IBM pSeries%GPASS%510Q');
INSERT INTO `Dictionary` VALUES (11,97,'yes','IBM pSeries%GPASS%520');
INSERT INTO `Dictionary` VALUES (11,98,'yes','IBM pSeries%GPASS%520Q');
INSERT INTO `Dictionary` VALUES (11,99,'yes','IBM pSeries%GPASS%550');
INSERT INTO `Dictionary` VALUES (11,100,'yes','IBM pSeries%GPASS%550Q');
INSERT INTO `Dictionary` VALUES (11,101,'yes','HP ProLiant%GPASS%DL140');
INSERT INTO `Dictionary` VALUES (11,102,'yes','HP ProLiant%GPASS%DL145');
INSERT INTO `Dictionary` VALUES (11,103,'yes','HP ProLiant%GPASS%DL320');
INSERT INTO `Dictionary` VALUES (11,104,'yes','HP ProLiant%GPASS%DL360');
INSERT INTO `Dictionary` VALUES (11,105,'yes','HP ProLiant%GPASS%DL380');
INSERT INTO `Dictionary` VALUES (11,106,'yes','HP ProLiant%GPASS%DL385');
INSERT INTO `Dictionary` VALUES (11,107,'yes','HP ProLiant%GPASS%DL580');
INSERT INTO `Dictionary` VALUES (11,108,'yes','HP ProLiant%GPASS%DL585');
INSERT INTO `Dictionary` VALUES (11,109,'yes','HP ProLiant%GPASS%ML110');
INSERT INTO `Dictionary` VALUES (11,110,'yes','HP ProLiant%GPASS%ML150');
INSERT INTO `Dictionary` VALUES (11,111,'yes','HP ProLiant%GPASS%ML310');
INSERT INTO `Dictionary` VALUES (11,112,'yes','HP ProLiant%GPASS%ML350');
INSERT INTO `Dictionary` VALUES (11,113,'yes','HP ProLiant%GPASS%ML370');
INSERT INTO `Dictionary` VALUES (11,114,'yes','HP ProLiant%GPASS%ML570');
INSERT INTO `Dictionary` VALUES (12,115,'yes','noname/unknown');
INSERT INTO `Dictionary` VALUES (12,116,'yes','Foundry%GPASS%FastIron WorkGroup');
INSERT INTO `Dictionary` VALUES (12,117,'yes','Foundry%GPASS%FastIron II');
INSERT INTO `Dictionary` VALUES (12,118,'yes','Foundry%GPASS%ServerIron');
INSERT INTO `Dictionary` VALUES (12,119,'yes','Foundry%GPASS%ServerIron XL');
INSERT INTO `Dictionary` VALUES (12,120,'yes','Foundry%GPASS%ServerIron 350');
INSERT INTO `Dictionary` VALUES (12,121,'yes','Foundry%GPASS%FastIron Edge 2402');
INSERT INTO `Dictionary` VALUES (12,122,'yes','Foundry%GPASS%FastIron Edge 4802');
INSERT INTO `Dictionary` VALUES (12,123,'yes','Foundry%GPASS%FastIron Edge X424');
INSERT INTO `Dictionary` VALUES (12,124,'yes','Cisco%GPASS%Catalyst 2924XL');
INSERT INTO `Dictionary` VALUES (12,125,'yes','Foundry%GPASS%FastIron SuperX');
INSERT INTO `Dictionary` VALUES (12,126,'yes','Cisco%GPASS%Catalyst 2912XL');
INSERT INTO `Dictionary` VALUES (12,127,'yes','Foundry%GPASS%FastIron GS 648P');
INSERT INTO `Dictionary` VALUES (12,128,'yes','Foundry%GPASS%FastIron Edge 2402-PREM');
INSERT INTO `Dictionary` VALUES (12,129,'yes','Foundry%GPASS%FastIron GS 624P');
INSERT INTO `Dictionary` VALUES (12,130,'yes','Foundry%GPASS%FastIron GS 624P-POE');
INSERT INTO `Dictionary` VALUES (12,131,'yes','Foundry%GPASS%FastIron GS 648P-POE');
INSERT INTO `Dictionary` VALUES (12,132,'yes','Foundry%GPASS%ServerIron 4G');
INSERT INTO `Dictionary` VALUES (12,133,'yes','Foundry%GPASS%ServerIron 4G-SSL');
INSERT INTO `Dictionary` VALUES (12,134,'yes','Foundry%GPASS%FastIron Edge X448');
INSERT INTO `Dictionary` VALUES (12,135,'yes','Foundry%GPASS%FastIron Edge X424HF');
INSERT INTO `Dictionary` VALUES (12,136,'yes','Foundry%GPASS%FastIron Edge X424-POE');
INSERT INTO `Dictionary` VALUES (12,137,'yes','Foundry%GPASS%FastIron SX 800');
INSERT INTO `Dictionary` VALUES (12,138,'yes','Foundry%GPASS%FastIron SX 1600');
INSERT INTO `Dictionary` VALUES (12,139,'yes','Cisco%GPASS%Catalyst WS-C3560-8PC');
INSERT INTO `Dictionary` VALUES (12,140,'yes','Cisco%GPASS%Catalyst 2960-48TC-S');
INSERT INTO `Dictionary` VALUES (12,141,'yes','Cisco%GPASS%Catalyst 3560-E');
INSERT INTO `Dictionary` VALUES (12,142,'yes','Cisco%GPASS%Catalyst Express 500-24LC');
INSERT INTO `Dictionary` VALUES (12,143,'yes','Cisco%GPASS%Catalyst 3750-24TS');
INSERT INTO `Dictionary` VALUES (12,144,'yes','Cisco%GPASS%Catalyst 3750-E');
INSERT INTO `Dictionary` VALUES (12,145,'yes','Cisco%GPASS%Catalyst 4503');
INSERT INTO `Dictionary` VALUES (12,146,'yes','Cisco%GPASS%Catalyst 6513');
INSERT INTO `Dictionary` VALUES (12,147,'yes','[[Cisco%GPASS%Catalyst 4948 | http://www.cisco.com/en/US/products/ps6026/index.html]]');
INSERT INTO `Dictionary` VALUES (30,148,'yes','Cisco%GPASS%Catalyst 6509-E%L9,1H%');
INSERT INTO `Dictionary` VALUES (30,149,'yes','Cisco%GPASS%Catalyst 6509-NEB-A%L9,1H%');
INSERT INTO `Dictionary` VALUES (30,150,'yes','Cisco%GPASS%Catalyst 6506-E%L6,1H%');
INSERT INTO `Dictionary` VALUES (30,151,'yes','Cisco%GPASS%Catalyst 6504-E%L4,1H%');
INSERT INTO `Dictionary` VALUES (30,152,'yes','Cisco%GPASS%Catalyst 6503-E%L3,1H%');
INSERT INTO `Dictionary` VALUES (30,153,'yes','Cisco%GPASS%Catalyst 6503%L3,1H%');
INSERT INTO `Dictionary` VALUES (30,154,'yes','Cisco%GPASS%Catalyst 6506%L6,1H%');
INSERT INTO `Dictionary` VALUES (30,155,'yes','Cisco%GPASS%Catalyst 6509-NEB%L9,1H%');
INSERT INTO `Dictionary` VALUES (30,156,'yes','Cisco%GPASS%Catalyst 4506%L6,1H%');
INSERT INTO `Dictionary` VALUES (30,157,'yes','Cisco%GPASS%Catalyst 4507R%L7,1H%');
INSERT INTO `Dictionary` VALUES (30,158,'yes','Cisco%GPASS%Catalyst 4510R%L10,1H%');
INSERT INTO `Dictionary` VALUES (12,159,'yes','Cisco%GPASS%Catalyst Express 500-24PC');
INSERT INTO `Dictionary` VALUES (12,160,'yes','Cisco%GPASS%Catalyst Express 500-24TT');
INSERT INTO `Dictionary` VALUES (12,161,'yes','Cisco%GPASS%Catalyst Express 500G-12TC');
INSERT INTO `Dictionary` VALUES (12,164,'yes','Cisco%GPASS%Catalyst 2960-24TT-L');
INSERT INTO `Dictionary` VALUES (12,165,'yes','Cisco%GPASS%Catalyst 2960-8TC-L');
INSERT INTO `Dictionary` VALUES (12,166,'yes','Cisco%GPASS%Catalyst 2960G-48TC-L');
INSERT INTO `Dictionary` VALUES (12,167,'yes','Cisco%GPASS%Catalyst 2960G-24TC-L');
INSERT INTO `Dictionary` VALUES (12,168,'yes','Cisco%GPASS%Catalyst 2960G-8TC-L');
INSERT INTO `Dictionary` VALUES (12,169,'yes','Cisco%GPASS%Catalyst 3560-24TS');
INSERT INTO `Dictionary` VALUES (12,170,'yes','Cisco%GPASS%Catalyst 3560-48TS');
INSERT INTO `Dictionary` VALUES (12,171,'yes','Cisco%GPASS%Catalyst 3560-24PS');
INSERT INTO `Dictionary` VALUES (12,172,'yes','Cisco%GPASS%Catalyst 3560-48PS');
INSERT INTO `Dictionary` VALUES (12,173,'yes','Cisco%GPASS%Catalyst 3560G-24TS');
INSERT INTO `Dictionary` VALUES (12,174,'yes','Cisco%GPASS%Catalyst 3560G-48TS');
INSERT INTO `Dictionary` VALUES (12,175,'yes','Cisco%GPASS%Catalyst 3560G-24PS');
INSERT INTO `Dictionary` VALUES (12,176,'yes','Cisco%GPASS%Catalyst 3560G-48PS');
INSERT INTO `Dictionary` VALUES (12,177,'yes','Cisco%GPASS%Catalyst 3750-48TS');
INSERT INTO `Dictionary` VALUES (12,178,'yes','Cisco%GPASS%Catalyst 3750-24PS');
INSERT INTO `Dictionary` VALUES (12,179,'yes','Cisco%GPASS%Catalyst 3750-48PS');
INSERT INTO `Dictionary` VALUES (12,180,'yes','Cisco%GPASS%Catalyst 3750-24FS');
INSERT INTO `Dictionary` VALUES (12,181,'yes','Cisco%GPASS%Catalyst 3750G-24T');
INSERT INTO `Dictionary` VALUES (12,182,'yes','Cisco%GPASS%Catalyst 3750G-24TS');
INSERT INTO `Dictionary` VALUES (12,183,'yes','Cisco%GPASS%Catalyst 3750G-24TS-1U');
INSERT INTO `Dictionary` VALUES (12,184,'yes','Cisco%GPASS%Catalyst 3750G-48TS');
INSERT INTO `Dictionary` VALUES (12,185,'yes','Cisco%GPASS%Catalyst 3750G-24PS');
INSERT INTO `Dictionary` VALUES (12,186,'yes','Cisco%GPASS%Catalyst 3750G-48PS');
INSERT INTO `Dictionary` VALUES (12,187,'yes','Cisco%GPASS%Catalyst 3750G-16TD');
INSERT INTO `Dictionary` VALUES (12,188,'yes','Cisco%GPASS%Catalyst 3750G-12S');
INSERT INTO `Dictionary` VALUES (12,189,'yes','Cisco%GPASS%Catalyst 3750G-24WS');
INSERT INTO `Dictionary` VALUES (12,190,'yes','Foundry%GPASS%EdgeIron 2402CF');
INSERT INTO `Dictionary` VALUES (12,191,'yes','Foundry%GPASS%EdgeIron 24G');
INSERT INTO `Dictionary` VALUES (12,192,'yes','Foundry%GPASS%EdgeIron 4802CF');
INSERT INTO `Dictionary` VALUES (12,193,'yes','Foundry%GPASS%EdgeIron 48G');
INSERT INTO `Dictionary` VALUES (12,194,'yes','Foundry%GPASS%EdgeIron 24GS');
INSERT INTO `Dictionary` VALUES (12,195,'yes','Foundry%GPASS%EdgeIron 48GS');
INSERT INTO `Dictionary` VALUES (12,196,'yes','Foundry%GPASS%EdgeIron 8X10G');
INSERT INTO `Dictionary` VALUES (12,197,'yes','Foundry%GPASS%FastIron Edge 4802-PREM');
INSERT INTO `Dictionary` VALUES (12,198,'yes','Foundry%GPASS%FastIron Edge 12GCF');
INSERT INTO `Dictionary` VALUES (12,199,'yes','Foundry%GPASS%FastIron Edge 12GCF-PREM');
INSERT INTO `Dictionary` VALUES (12,200,'yes','Foundry%GPASS%FastIron Edge 9604');
INSERT INTO `Dictionary` VALUES (12,201,'yes','Foundry%GPASS%FastIron Edge 9604-PREM');
INSERT INTO `Dictionary` VALUES (12,202,'yes','Foundry%GPASS%FastIron Edge 2402-POE');
INSERT INTO `Dictionary` VALUES (12,203,'yes','Foundry%GPASS%FastIron Edge 4802-POE');
INSERT INTO `Dictionary` VALUES (12,204,'yes','Foundry%GPASS%FastIron Workgroup X424');
INSERT INTO `Dictionary` VALUES (12,205,'yes','Foundry%GPASS%FastIron Workgroup X448');
INSERT INTO `Dictionary` VALUES (12,206,'yes','Foundry%GPASS%ServerIron 450');
INSERT INTO `Dictionary` VALUES (12,207,'yes','Foundry%GPASS%ServerIron 850');
INSERT INTO `Dictionary` VALUES (12,208,'yes','Foundry%GPASS%ServerIron GT C');
INSERT INTO `Dictionary` VALUES (12,209,'yes','Foundry%GPASS%ServerIron GT E');
INSERT INTO `Dictionary` VALUES (12,210,'yes','Cisco%GPASS%Catalyst 2970G-24T');
INSERT INTO `Dictionary` VALUES (12,211,'yes','Cisco%GPASS%Catalyst 2970G-24TS');
INSERT INTO `Dictionary` VALUES (13,212,'yes','RH Fedora%GSKIP%Fedora C1');
INSERT INTO `Dictionary` VALUES (13,213,'yes','RH Fedora%GSKIP%Fedora C2');
INSERT INTO `Dictionary` VALUES (13,214,'yes','RH Fedora%GSKIP%Fedora C3');
INSERT INTO `Dictionary` VALUES (13,215,'yes','RH Fedora%GSKIP%Fedora C4');
INSERT INTO `Dictionary` VALUES (13,216,'yes','RH Fedora%GSKIP%Fedora C5');
INSERT INTO `Dictionary` VALUES (13,217,'yes','RH Fedora%GSKIP%Fedora C6');
INSERT INTO `Dictionary` VALUES (13,218,'yes','Sun Solaris%GSKIP%Solaris 8');
INSERT INTO `Dictionary` VALUES (13,219,'yes','Sun Solaris%GSKIP%Solaris 9');
INSERT INTO `Dictionary` VALUES (13,220,'yes','Sun Solaris%GSKIP%Solaris 10');
INSERT INTO `Dictionary` VALUES (13,221,'yes','MicroSoft%GSKIP%Windows 2000');
INSERT INTO `Dictionary` VALUES (13,222,'yes','MicroSoft%GSKIP%Windows XP');
INSERT INTO `Dictionary` VALUES (13,223,'yes','MicroSoft%GSKIP%Windows 2003');
INSERT INTO `Dictionary` VALUES (13,224,'yes','MicroSoft%GSKIP%Windows Vista');
INSERT INTO `Dictionary` VALUES (13,225,'yes','Red Hat Enterprise%GSKIP%RHEL V1');
INSERT INTO `Dictionary` VALUES (13,226,'yes','Red Hat Enterprise%GSKIP%RHEL V2');
INSERT INTO `Dictionary` VALUES (13,227,'yes','Red Hat Enterprise%GSKIP%RHEL V3');
INSERT INTO `Dictionary` VALUES (13,228,'yes','Red Hat Enterprise%GSKIP%RHEL V4');
INSERT INTO `Dictionary` VALUES (13,229,'yes','ALT_Linux%GSKIP%ALTLinux Master 2.0');
INSERT INTO `Dictionary` VALUES (13,230,'yes','ALT_Linux%GSKIP%ALTLinux Master 2.2');
INSERT INTO `Dictionary` VALUES (13,231,'yes','ALT_Linux%GSKIP%ALTLinux Master 2.4');
INSERT INTO `Dictionary` VALUES (13,232,'yes','RH Fedora%GSKIP%Fedora 7');
INSERT INTO `Dictionary` VALUES (13,233,'yes','SUSE Enterprise%GSKIP%SLES10');
INSERT INTO `Dictionary` VALUES (13,234,'yes','[[Debian%GSKIP%Debian 3.0 (woody) | http://debian.org/releases/woody/]]');
INSERT INTO `Dictionary` VALUES (13,235,'yes','[[Debian%GSKIP%Debian 3.1 (sarge) | http://debian.org/releases/sarge/]]');
INSERT INTO `Dictionary` VALUES (13,236,'yes','FreeBSD%GSKIP%FreeBSD 1.x');
INSERT INTO `Dictionary` VALUES (13,237,'yes','FreeBSD%GSKIP%FreeBSD 2.x');
INSERT INTO `Dictionary` VALUES (13,238,'yes','FreeBSD%GSKIP%FreeBSD 3.x');
INSERT INTO `Dictionary` VALUES (13,239,'yes','FreeBSD%GSKIP%FreeBSD 4.x');
INSERT INTO `Dictionary` VALUES (13,240,'yes','FreeBSD%GSKIP%FreeBSD 5.x');
INSERT INTO `Dictionary` VALUES (13,241,'yes','FreeBSD%GSKIP%FreeBSD 6.x');
INSERT INTO `Dictionary` VALUES (13,242,'yes','RH Fedora%GSKIP%Fedora 8');
INSERT INTO `Dictionary` VALUES (13,243,'yes','ALT_Linux%GSKIP%ALTLinux Master 4.0');
INSERT INTO `Dictionary` VALUES (14,244,'yes','Cisco IOS 12.0');
INSERT INTO `Dictionary` VALUES (14,245,'yes','Foundry SLB');
INSERT INTO `Dictionary` VALUES (14,246,'yes','Foundry WXM');
INSERT INTO `Dictionary` VALUES (14,247,'yes','Foundry L2');
INSERT INTO `Dictionary` VALUES (14,248,'yes','Foundry full L3');
INSERT INTO `Dictionary` VALUES (14,249,'yes','Foundry basic L3');
INSERT INTO `Dictionary` VALUES (14,250,'yes','Cisco IOS 11.2');
INSERT INTO `Dictionary` VALUES (14,251,'yes','Cisco IOS 12.1');
INSERT INTO `Dictionary` VALUES (14,252,'yes','Cisco IOS 12.2');
INSERT INTO `Dictionary` VALUES (14,253,'yes','Cisco IOS 11.3');
INSERT INTO `Dictionary` VALUES (16,254,'yes','Cisco IOS 12.0');
INSERT INTO `Dictionary` VALUES (16,255,'yes','Cisco IOS 12.1');
INSERT INTO `Dictionary` VALUES (16,256,'yes','Cisco IOS 12.2');
INSERT INTO `Dictionary` VALUES (16,257,'yes','Cisco IOS 12.3');
INSERT INTO `Dictionary` VALUES (16,258,'yes','Cisco IOS 12.4');
INSERT INTO `Dictionary` VALUES (16,259,'yes','Foundry L3');
INSERT INTO `Dictionary` VALUES (16,260,'yes','Cisco IOS 11.2');
INSERT INTO `Dictionary` VALUES (16,261,'yes','Cisco IOS 11.3');
INSERT INTO `Dictionary` VALUES (17,262,'yes','Foundry%GPASS%BigIron 4000');
INSERT INTO `Dictionary` VALUES (17,263,'yes','Cisco%GPASS%7609');
INSERT INTO `Dictionary` VALUES (17,264,'yes','Cisco%GPASS%2610XM');
INSERT INTO `Dictionary` VALUES (17,265,'yes','Cisco%GPASS%2611XM');
INSERT INTO `Dictionary` VALUES (17,266,'yes','Cisco%GPASS%3620');
INSERT INTO `Dictionary` VALUES (17,267,'yes','Cisco%GPASS%3640');
INSERT INTO `Dictionary` VALUES (17,268,'yes','Cisco%GPASS%2621XM');
INSERT INTO `Dictionary` VALUES (17,269,'yes','Cisco%GPASS%7206VXR');
INSERT INTO `Dictionary` VALUES (17,270,'yes','Cisco%GPASS%2651XM');
INSERT INTO `Dictionary` VALUES (17,271,'yes','Cisco%GPASS%7204VXR');
INSERT INTO `Dictionary` VALUES (17,272,'yes','Cisco%GPASS%2612');
INSERT INTO `Dictionary` VALUES (17,273,'yes','Cisco%GPASS%2620XM');
INSERT INTO `Dictionary` VALUES (17,274,'yes','Cisco%GPASS%2650XM');
INSERT INTO `Dictionary` VALUES (17,275,'yes','Cisco%GPASS%2691');
INSERT INTO `Dictionary` VALUES (17,276,'yes','Cisco%GPASS%7603');
INSERT INTO `Dictionary` VALUES (17,277,'yes','Cisco%GPASS%7606');
INSERT INTO `Dictionary` VALUES (17,278,'yes','Cisco%GPASS%7613');
INSERT INTO `Dictionary` VALUES (17,279,'yes','Cisco%GPASS%2801');
INSERT INTO `Dictionary` VALUES (17,280,'yes','Cisco%GPASS%2811');
INSERT INTO `Dictionary` VALUES (17,281,'yes','Cisco%GPASS%2821');
INSERT INTO `Dictionary` VALUES (17,282,'yes','Cisco%GPASS%2851');
INSERT INTO `Dictionary` VALUES (17,283,'yes','Cisco%GPASS%3725');
INSERT INTO `Dictionary` VALUES (17,284,'yes','Cisco%GPASS%3745');
INSERT INTO `Dictionary` VALUES (17,285,'yes','Cisco%GPASS%3825');
INSERT INTO `Dictionary` VALUES (17,286,'yes','Cisco%GPASS%3845');
INSERT INTO `Dictionary` VALUES (24,287,'yes','Juniper%GPASS%NetScreen 100');
INSERT INTO `Dictionary` VALUES (17,288,'yes','Foundry%GPASS%NetIron MLX-4');
INSERT INTO `Dictionary` VALUES (17,289,'yes','Foundry%GPASS%NetIron MLX-8');
INSERT INTO `Dictionary` VALUES (17,290,'yes','Foundry%GPASS%NetIron MLX-16');
INSERT INTO `Dictionary` VALUES (17,291,'yes','Foundry%GPASS%NetIron MLX-32');
INSERT INTO `Dictionary` VALUES (17,292,'yes','Foundry%GPASS%NetIron XMR 4000');
INSERT INTO `Dictionary` VALUES (17,293,'yes','Foundry%GPASS%NetIron XMR 8000');
INSERT INTO `Dictionary` VALUES (17,294,'yes','Foundry%GPASS%NetIron XMR 16000');
INSERT INTO `Dictionary` VALUES (17,295,'yes','Foundry%GPASS%NetIron XMR 32000');
INSERT INTO `Dictionary` VALUES (17,296,'yes','Foundry%GPASS%BigIron RX-4');
INSERT INTO `Dictionary` VALUES (17,297,'yes','Foundry%GPASS%BigIron RX-8');
INSERT INTO `Dictionary` VALUES (17,298,'yes','Foundry%GPASS%BigIron RX-16');
INSERT INTO `Dictionary` VALUES (17,299,'yes','Cisco%GPASS%1841');
INSERT INTO `Dictionary` VALUES (17,300,'yes','Cisco%GPASS%1812');
INSERT INTO `Dictionary` VALUES (17,301,'yes','Cisco%GPASS%1811');
INSERT INTO `Dictionary` VALUES (17,302,'yes','Cisco%GPASS%1803');
INSERT INTO `Dictionary` VALUES (17,303,'yes','Cisco%GPASS%1802');
INSERT INTO `Dictionary` VALUES (17,304,'yes','Cisco%GPASS%1801');
INSERT INTO `Dictionary` VALUES (17,305,'yes','Cisco%GPASS%7202');
INSERT INTO `Dictionary` VALUES (17,306,'yes','Cisco%GPASS%7204');
INSERT INTO `Dictionary` VALUES (17,307,'yes','Cisco%GPASS%7206');
INSERT INTO `Dictionary` VALUES (17,308,'yes','Cisco%GPASS%7604');
INSERT INTO `Dictionary` VALUES (17,309,'yes','Cisco%GPASS%OSR-7609');
INSERT INTO `Dictionary` VALUES (17,310,'yes','Foundry%GPASS%BigIron 8000');
INSERT INTO `Dictionary` VALUES (17,311,'yes','Foundry%GPASS%BigIron 15000');
INSERT INTO `Dictionary` VALUES (18,312,'yes','Sun StorEdge A1000');
INSERT INTO `Dictionary` VALUES (18,313,'yes','Dell/EMC AX150');
INSERT INTO `Dictionary` VALUES (18,314,'yes','EMC CLARiiON CX600');
INSERT INTO `Dictionary` VALUES (18,315,'yes','Sun StorEdge D240');
INSERT INTO `Dictionary` VALUES (18,316,'yes','EMC CLARiiON CX300');
INSERT INTO `Dictionary` VALUES (18,317,'yes','Sun StorageTek 6140');
INSERT INTO `Dictionary` VALUES (18,318,'yes','Sun StorageTek 3511');
INSERT INTO `Dictionary` VALUES (18,319,'yes','Sun StorageTek 3510');
INSERT INTO `Dictionary` VALUES (18,320,'yes','Sun StorageTek 3320');
INSERT INTO `Dictionary` VALUES (18,321,'yes','Sun StorageTek 3120');
INSERT INTO `Dictionary` VALUES (18,322,'yes','Dell PowerVault NX1950');
INSERT INTO `Dictionary` VALUES (18,323,'yes','Dell PowerVault 220S');
INSERT INTO `Dictionary` VALUES (18,324,'yes','Dell PowerVault MD3000');
INSERT INTO `Dictionary` VALUES (19,325,'yes','Dell PowerVault 136T');
INSERT INTO `Dictionary` VALUES (19,326,'yes','Sun StorageTek SL500');
INSERT INTO `Dictionary` VALUES (19,327,'yes','Sun StorageTek L1400');
INSERT INTO `Dictionary` VALUES (19,328,'yes','Sun StorageTek SL8500');
INSERT INTO `Dictionary` VALUES (19,329,'yes','Sun StorageTek C4');
INSERT INTO `Dictionary` VALUES (19,330,'yes','Dell PowerVault ML6000');
INSERT INTO `Dictionary` VALUES (19,331,'yes','Dell PowerVault 132T');
INSERT INTO `Dictionary` VALUES (19,332,'yes','Dell PowerVault TL4000');
INSERT INTO `Dictionary` VALUES (19,333,'yes','Dell PowerVault TL2000');
INSERT INTO `Dictionary` VALUES (19,334,'yes','Dell PowerVault 124T');
INSERT INTO `Dictionary` VALUES (19,335,'yes','Sun StorageTek C2');
INSERT INTO `Dictionary` VALUES (12,338,'yes','Dell PowerConnect%GPASS%2216');
INSERT INTO `Dictionary` VALUES (12,339,'yes','Dell PowerConnect%GPASS%2224');
INSERT INTO `Dictionary` VALUES (12,340,'yes','Dell PowerConnect%GPASS%2324');
INSERT INTO `Dictionary` VALUES (12,341,'yes','Dell PowerConnect%GPASS%2708');
INSERT INTO `Dictionary` VALUES (12,342,'yes','Dell PowerConnect%GPASS%2716');
INSERT INTO `Dictionary` VALUES (12,343,'yes','Dell PowerConnect%GPASS%2724');
INSERT INTO `Dictionary` VALUES (12,344,'yes','Dell PowerConnect%GPASS%2748');
INSERT INTO `Dictionary` VALUES (12,345,'yes','Dell PowerConnect%GPASS%3424');
INSERT INTO `Dictionary` VALUES (12,346,'yes','Dell PowerConnect%GPASS%3424P');
INSERT INTO `Dictionary` VALUES (12,347,'yes','Dell PowerConnect%GPASS%3448');
INSERT INTO `Dictionary` VALUES (12,348,'yes','Dell PowerConnect%GPASS%3448P');
INSERT INTO `Dictionary` VALUES (12,349,'yes','Dell PowerConnect%GPASS%5324');
INSERT INTO `Dictionary` VALUES (12,350,'yes','Dell PowerConnect%GPASS%6224');
INSERT INTO `Dictionary` VALUES (12,351,'yes','Dell PowerConnect%GPASS%6224P');
INSERT INTO `Dictionary` VALUES (12,352,'yes','Dell PowerConnect%GPASS%6224F');
INSERT INTO `Dictionary` VALUES (12,353,'yes','Dell PowerConnect%GPASS%6248');
INSERT INTO `Dictionary` VALUES (12,354,'yes','Dell PowerConnect%GPASS%6248P');
INSERT INTO `Dictionary` VALUES (11,355,'yes','Dell PowerEdge%GPASS%6850');
INSERT INTO `Dictionary` VALUES (11,356,'yes','Dell PowerEdge%GPASS%6950');
INSERT INTO `Dictionary` VALUES (11,357,'yes','Dell PowerEdge%GPASS%R900');
INSERT INTO `Dictionary` VALUES (11,358,'yes','Dell PowerEdge%GPASS%4400');
INSERT INTO `Dictionary` VALUES (11,359,'yes','Dell PowerEdge%GPASS%2650');
INSERT INTO `Dictionary` VALUES (11,360,'yes','Dell PowerEdge%GPASS%2550');
INSERT INTO `Dictionary` VALUES (11,361,'yes','Dell PowerEdge%GPASS%750');
INSERT INTO `Dictionary` VALUES (11,362,'yes','Dell PowerEdge%GPASS%2450');
INSERT INTO `Dictionary` VALUES (11,363,'yes','Dell PowerEdge%GPASS%850');
INSERT INTO `Dictionary` VALUES (11,364,'yes','Dell PowerEdge%GPASS%1850');
INSERT INTO `Dictionary` VALUES (11,365,'yes','Dell PowerEdge%GPASS%860');
INSERT INTO `Dictionary` VALUES (11,366,'yes','Dell PowerEdge%GPASS%2900');
INSERT INTO `Dictionary` VALUES (11,367,'yes','Dell PowerEdge%GPASS%2970');
INSERT INTO `Dictionary` VALUES (11,368,'yes','Dell PowerEdge%GPASS%SC1435');
INSERT INTO `Dictionary` VALUES (30,369,'yes','Cisco%GPASS%Catalyst 6509%L9,1H%');
INSERT INTO `Dictionary` VALUES (12,370,'yes','Cisco%GPASS%ME 6524GS-8S');
INSERT INTO `Dictionary` VALUES (12,371,'yes','Cisco%GPASS%ME 6524GT-8S');
INSERT INTO `Dictionary` VALUES (12,372,'yes','Cisco%GPASS%Catalyst 4503-E');
INSERT INTO `Dictionary` VALUES (12,373,'yes','Cisco%GPASS%Catalyst 4506-E');
INSERT INTO `Dictionary` VALUES (12,374,'yes','Cisco%GPASS%Catalyst 4507R-E');
INSERT INTO `Dictionary` VALUES (12,375,'yes','Cisco%GPASS%Catalyst 4510R-E');
INSERT INTO `Dictionary` VALUES (12,376,'yes','Cisco%GPASS%Catalyst 3750-24TE-M');
INSERT INTO `Dictionary` VALUES (12,377,'yes','[[Cisco%GPASS%Catalyst 4948-10GE | http://www.cisco.com/en/US/products/ps6230/index.html]]');
INSERT INTO `Dictionary` VALUES (12,378,'yes','Cisco%GPASS%ME 4924-10GE');
INSERT INTO `Dictionary` VALUES (12,379,'yes','Cisco%GPASS%Catalyst 2960-24-S');
INSERT INTO `Dictionary` VALUES (12,380,'yes','Cisco%GPASS%Catalyst 2950-24');
INSERT INTO `Dictionary` VALUES (12,381,'yes','Cisco%GPASS%Catalyst WS-C2950-12');
INSERT INTO `Dictionary` VALUES (12,382,'yes','Cisco%GPASS%Catalyst 2950C-24');
INSERT INTO `Dictionary` VALUES (12,383,'yes','Cisco%GPASS%Catalyst WS-C2950G-24-DC');
INSERT INTO `Dictionary` VALUES (12,384,'yes','Cisco%GPASS%Catalyst 2950SX-48');
INSERT INTO `Dictionary` VALUES (12,385,'yes','Cisco%GPASS%Catalyst WS-C2950SX-24');
INSERT INTO `Dictionary` VALUES (12,386,'yes','Cisco%GPASS%Catalyst 2950T-24');
INSERT INTO `Dictionary` VALUES (12,387,'yes','Cisco%GPASS%Catalyst 2950T-48');
INSERT INTO `Dictionary` VALUES (12,388,'yes','Cisco%GPASS%Catalyst 2950G-12');
INSERT INTO `Dictionary` VALUES (12,389,'yes','Cisco%GPASS%Catalyst 2950G-24');
INSERT INTO `Dictionary` VALUES (12,390,'yes','Cisco%GPASS%Catalyst 2950G-48');
INSERT INTO `Dictionary` VALUES (12,391,'yes','Cisco%GPASS%Catalyst 3508G XL');
INSERT INTO `Dictionary` VALUES (12,392,'yes','Cisco%GPASS%Catalyst 3512 XL');
INSERT INTO `Dictionary` VALUES (12,393,'yes','Cisco%GPASS%Catalyst WS-C3524-XL');
INSERT INTO `Dictionary` VALUES (12,394,'yes','Cisco%GPASS%Catalyst 3524 PWR XL');
INSERT INTO `Dictionary` VALUES (12,395,'yes','Cisco%GPASS%Catalyst 3548 XL');
INSERT INTO `Dictionary` VALUES (12,396,'yes','Cisco%GPASS%ME 2400-24TS-A');
INSERT INTO `Dictionary` VALUES (12,397,'yes','Cisco%GPASS%ME 2400-24TS-D');
INSERT INTO `Dictionary` VALUES (12,398,'yes','Cisco%GPASS%Catalyst 3550-12T');
INSERT INTO `Dictionary` VALUES (12,399,'yes','Cisco%GPASS%Catalyst 3550-12G');
INSERT INTO `Dictionary` VALUES (12,400,'yes','Cisco%GPASS%Catalyst 3550-24');
INSERT INTO `Dictionary` VALUES (12,401,'yes','Cisco%GPASS%Catalyst 3550-24 FX');
INSERT INTO `Dictionary` VALUES (12,402,'yes','Cisco%GPASS%Catalyst 3550-24 DC');
INSERT INTO `Dictionary` VALUES (12,403,'yes','Cisco%GPASS%Catalyst 3550-24 PWR');
INSERT INTO `Dictionary` VALUES (12,404,'yes','Cisco%GPASS%Catalyst 3550-48');
INSERT INTO `Dictionary` VALUES (12,405,'yes','Cisco%GPASS%ME 3400G-12CS-A');
INSERT INTO `Dictionary` VALUES (12,406,'yes','Cisco%GPASS%ME 3400G-12CS-D');
INSERT INTO `Dictionary` VALUES (12,407,'yes','Cisco%GPASS%ME 3400G-2CS-A');
INSERT INTO `Dictionary` VALUES (12,408,'yes','Cisco%GPASS%ME 3400-24TS-A');
INSERT INTO `Dictionary` VALUES (12,409,'yes','Cisco%GPASS%ME 3400-24TS-D');
INSERT INTO `Dictionary` VALUES (12,410,'yes','Cisco%GPASS%ME 3400-24FS-A');
INSERT INTO `Dictionary` VALUES (12,411,'yes','Foundry%GPASS%FastIron GS 624XGP');
INSERT INTO `Dictionary` VALUES (12,412,'yes','Foundry%GPASS%FastIron GS 624XGP-POE');
INSERT INTO `Dictionary` VALUES (12,413,'yes','Foundry%GPASS%FastIron LS 624');
INSERT INTO `Dictionary` VALUES (12,414,'yes','Foundry%GPASS%FastIron LS 648');
INSERT INTO `Dictionary` VALUES (12,415,'yes','Foundry%GPASS%NetIron M2404F');
INSERT INTO `Dictionary` VALUES (12,416,'yes','Foundry%GPASS%NetIron M2404C');
INSERT INTO `Dictionary` VALUES (17,417,'yes','Foundry%GPASS%BigIron RX-32');
INSERT INTO `Dictionary` VALUES (13,418,'yes','[[Debian%GSKIP%Debian 2.0 (hamm) | http://debian.org/releases/hamm/]]');
INSERT INTO `Dictionary` VALUES (13,419,'yes','[[Debian%GSKIP%Debian 2.1 (slink) | http://debian.org/releases/slink/]]');
INSERT INTO `Dictionary` VALUES (13,420,'yes','[[Debian%GSKIP%Debian 2.2 (potato) | http://debian.org/releases/potato/]]');
INSERT INTO `Dictionary` VALUES (13,421,'yes','[[Debian%GSKIP%Debian 4.0 (etch) | http://debian.org/releases/etch/]]');
INSERT INTO `Dictionary` VALUES (13,422,'yes','ALT_Linux%GSKIP%ALTLinux Server 4.0');
INSERT INTO `Dictionary` VALUES (13,423,'yes','ALT_Linux%GSKIP%ALTLinux Sisyphus');
INSERT INTO `Dictionary` VALUES (13,424,'yes','OpenSUSE%GSKIP%openSUSE 10.0');
INSERT INTO `Dictionary` VALUES (13,425,'yes','OpenSUSE%GSKIP%openSUSE 10.1');
INSERT INTO `Dictionary` VALUES (13,426,'yes','OpenSUSE%GSKIP%openSUSE 10.2');
INSERT INTO `Dictionary` VALUES (13,427,'yes','OpenSUSE%GSKIP%openSUSE 10.3');
INSERT INTO `Dictionary` VALUES (13,428,'yes','Ubuntu%GSKIP%Ubuntu 4.10');
INSERT INTO `Dictionary` VALUES (13,429,'yes','Ubuntu%GSKIP%Ubuntu 5.04');
INSERT INTO `Dictionary` VALUES (13,430,'yes','Ubuntu%GSKIP%Ubuntu 5.10');
INSERT INTO `Dictionary` VALUES (13,431,'yes','Ubuntu%GSKIP%Ubuntu 6.06 LTS');
INSERT INTO `Dictionary` VALUES (13,432,'yes','Ubuntu%GSKIP%Ubuntu 6.10');
INSERT INTO `Dictionary` VALUES (13,433,'yes','Ubuntu%GSKIP%Ubuntu 7.04');
INSERT INTO `Dictionary` VALUES (13,434,'yes','Ubuntu%GSKIP%Ubuntu 7.10');
INSERT INTO `Dictionary` VALUES (13,435,'yes','Ubuntu%GSKIP%Ubuntu 8.04 LTS');
INSERT INTO `Dictionary` VALUES (13,436,'yes','Red Hat Enterprise%GSKIP%RHEL V5');
INSERT INTO `Dictionary` VALUES (18,437,'yes','Dell PowerVault 210S');
INSERT INTO `Dictionary` VALUES (18,438,'yes','Dell PowerVault 221S');
INSERT INTO `Dictionary` VALUES (2,439,'yes','dry contact');
INSERT INTO `Dictionary` VALUES (2,440,'yes','unknown');
INSERT INTO `Dictionary` VALUES (13,441,'yes','[[CentOS%GSKIP%CentOS V2 | http://www.centos.org/]]');
INSERT INTO `Dictionary` VALUES (13,442,'yes','[[CentOS%GSKIP%CentOS V3 | http://www.centos.org/]]');
INSERT INTO `Dictionary` VALUES (13,443,'yes','[[CentOS%GSKIP%CentOS V4 | http://www.centos.org/]]');
INSERT INTO `Dictionary` VALUES (13,444,'yes','[[CentOS%GSKIP%CentOS V5 | http://www.centos.org/]]');
INSERT INTO `Dictionary` VALUES (1,445,'yes','KVM switch');
INSERT INTO `Dictionary` VALUES (2,446,'yes','KVM (console)');
INSERT INTO `Dictionary` VALUES (1,447,'yes','multiplexer');
INSERT INTO `Dictionary` VALUES (21,448,'yes','Avocent DSR1021');
INSERT INTO `Dictionary` VALUES (21,449,'yes','Avocent DSR1022');
INSERT INTO `Dictionary` VALUES (21,450,'yes','Avocent DSR1024');
INSERT INTO `Dictionary` VALUES (21,451,'yes','[[Avocent DSR1031 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentDSRAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,452,'yes','Avocent DSR1020');
INSERT INTO `Dictionary` VALUES (21,453,'yes','Avocent DSR2020');
INSERT INTO `Dictionary` VALUES (21,454,'yes','Avocent DSR4020');
INSERT INTO `Dictionary` VALUES (21,455,'yes','Avocent DSR8020');
INSERT INTO `Dictionary` VALUES (21,456,'yes','[[Avocent DSR1030 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentDSRAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,457,'yes','[[Avocent DSR2030 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentDSRAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,458,'yes','[[Avocent DSR2035 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentDSRAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,459,'yes','[[Avocent DSR4030 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentDSRAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,460,'yes','[[Avocent DSR8030 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentDSRAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,461,'yes','[[Avocent DSR8035 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentDSRAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,462,'yes','Avocent AutoView 1415');
INSERT INTO `Dictionary` VALUES (21,463,'yes','Avocent AutoView 1515');
INSERT INTO `Dictionary` VALUES (21,464,'yes','Avocent AutoView 2015');
INSERT INTO `Dictionary` VALUES (21,465,'yes','Avocent AutoView 2020');
INSERT INTO `Dictionary` VALUES (21,466,'yes','Avocent AutoView 2030');
INSERT INTO `Dictionary` VALUES (21,467,'yes','Avocent AutoView 3100');
INSERT INTO `Dictionary` VALUES (21,468,'yes','[[Avocent AutoView 3200 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentAutoViewAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,469,'yes','Avocent SwitchView 1000 4-port');
INSERT INTO `Dictionary` VALUES (21,470,'yes','Avocent SwitchView 1000 8-port');
INSERT INTO `Dictionary` VALUES (21,471,'yes','Avocent SwitchView 1000 16-port');
INSERT INTO `Dictionary` VALUES (9999,472,'yes','[[Cronyx%GPASS%FMUX/S-4E1 | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,473,'yes','[[Cronyx%GPASS%FMUX/S-4E1/ETS | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,474,'yes','[[Cronyx%GPASS%FMUX/S-4E1/M | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,475,'yes','[[Cronyx%GPASS%FMUX/S-8E1 | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,476,'yes','[[Cronyx%GPASS%FMUX/S-8E1/ETS | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,477,'yes','[[Cronyx%GPASS%FMUX/S-8E1/M | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,478,'yes','[[Cronyx%GPASS%FMUX/S-16E1 | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,479,'yes','[[Cronyx%GPASS%FMUX/S-16E1/ETS | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,480,'yes','[[Cronyx%GPASS%FMUX/S-16E1/M | http://www.cronyx.ru/hardware/fmux-ring.html]]');
INSERT INTO `Dictionary` VALUES (9999,481,'yes','[[Cronyx%GPASS%E1-XL/S | http://www.cronyx.ru/hardware/e1xl-s.html]]');
INSERT INTO `Dictionary` VALUES (9999,482,'yes','[[Cronyx%GPASS%E1-DXC/S | http://www.cronyx.ru/hardware/e1dxc-s.html]]');
INSERT INTO `Dictionary` VALUES (9999,483,'yes','[[Cronyx%GPASS%FMUX-4-E2 | http://www.cronyx.ru/hardware/fmux4-e2.html]]');
INSERT INTO `Dictionary` VALUES (9999,484,'yes','[[Cronyx%GPASS%FMUX-16-E3 | http://www.cronyx.ru/hardware/fmux16-e3.html]]');
INSERT INTO `Dictionary` VALUES (9999,485,'yes','[[Cronyx%GPASS%FMUX/SAT | http://www.cronyx.ru/hardware/fmux-sat.html]]');
INSERT INTO `Dictionary` VALUES (9999,486,'yes','[[Cronyx%GPASS%E1-XL/S-IP | http://www.cronyx.ru/hardware/e1xl-ip.html]]');
INSERT INTO `Dictionary` VALUES (17,487,'yes','[[RAD%GPASS%FCD-IPM | http://www.rad.com/Article/0,6583,36426-E1_T1_or_Fractional_E1_T1_Modular_Access_Device_with_Integrated_Router,00.html]]');
INSERT INTO `Dictionary` VALUES (9999,488,'yes','[[RAD%GPASS%FCD-E1M | http://www.rad.com/Article/0,6583,36723-E1_T1_Modular_Access_Multiplexer,00.html]]');
INSERT INTO `Dictionary` VALUES (9999,489,'yes','[[RAD%GPASS%FCD-T1M | http://www.rad.com/Article/0,6583,36723-E1_T1_Modular_Access_Multiplexer,00.html]]');
INSERT INTO `Dictionary` VALUES (9999,490,'yes','[[RAD%GPASS%FCD-155E | http://www.rad.com/Article/0,6583,36276-Ethernet_over_SDH_SONET_ADM,00.html]]');
INSERT INTO `Dictionary` VALUES (21,491,'yes','[[Aten CS78 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20070319151852001&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,492,'yes','[[Aten ACS1208A | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224111025006&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,493,'yes','[[Aten ACS1216A | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224111953008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,494,'yes','[[Aten CS1754 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050217161051008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,495,'yes','[[Aten CS1758 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224093143008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,496,'yes','[[Aten CS9134 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20070130133658002&pid=20050217172845005&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,497,'yes','[[Aten CS9138 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224094519006&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,498,'yes','[[Aten CS1708 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=2005022410563008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,499,'yes','[[Aten CS1716 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224110022008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,500,'yes','[[Aten CS1004 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224100546008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,501,'yes','[[Aten CS228 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224114323008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,502,'yes','[[Aten CS428 | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224114721008&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,503,'yes','[[Aten CS138A | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=20050224111458007&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,504,'yes','[[Aten CS88A | http://www.aten.com/products/productItem.php?pcid=20070130111936003&psid=20070130133658002&pid=2005022411042006&layerid=subClass2]]');
INSERT INTO `Dictionary` VALUES (21,505,'yes','[[Aten KM0832 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131626002&pid=20060628154826001&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (21,506,'yes','[[Aten KM0216 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411131626002&pid=20060417153950007&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (21,507,'yes','[[Aten KM0432 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411131626002&pid=2006041715359007&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (21,508,'yes','[[Aten KH1508 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411130954001&pid=20061101174038001&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (21,509,'yes','[[Aten KH1516 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411130954001&pid=20061101175320001&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (21,510,'yes','[[Aten KH0116 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411130954001&pid=20060411145734003&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (21,511,'yes','[[Aten KH98 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=2007012911116003&pid=20061221104352001&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,512,'yes','[[Aten KL1100 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=20071225113046001&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,513,'yes','[[Aten KL1508 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411131050002&pid=20070710020717009&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,514,'yes','[[Aten KL1516 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411131050002&pid=20070716232614001&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,515,'yes','[[Aten KL9108 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=20060811153413009&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,516,'yes','[[Aten KL9116 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411131050002&pid=2006081115384001&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,517,'yes','[[Aten KL3116 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=20060913162532009&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,518,'yes','[[Aten KL1116 | http://www.aten.com/products/productItem.php?pcid=2006041110563001&psid=20060411131050002&pid=20060420101520005&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,519,'yes','[[Aten CS1208DL | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=2005022413505007&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,520,'yes','[[Aten CS1216DL | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=2005022413505007&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,521,'yes','[[Aten CS1200L | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=20050224140854008&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,522,'yes','[[Aten CL1758 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=20051229164553003&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,523,'yes','[[Aten CL1208 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=2005072215482&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,524,'yes','[[Aten CL1216 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=2005072215482&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,525,'yes','[[Aten CL1200 | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=20050722165040002&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,526,'yes','[[Aten ACS1208AL | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=2005022413597003&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (23,527,'yes','[[Aten ACS1216AL | http://www.aten.com/products/productItem.php?pcid=2005010513171002&psid=20060411131050002&pid=2005022413597003&layerid=subClass1]]');
INSERT INTO `Dictionary` VALUES (9999,528,'yes','[[Tainet%GPASS%MUXpro 8216 | http://www.tainet.net/Product/muxpro820_8216.htm]]');
INSERT INTO `Dictionary` VALUES (9999,529,'yes','Tainet%GPASS%Mercury 3600+');
INSERT INTO `Dictionary` VALUES (9999,530,'yes','Tainet%GPASS%Mercury 3820');
INSERT INTO `Dictionary` VALUES (9999,531,'yes','Tainet%GPASS%Mercury 3630');
INSERT INTO `Dictionary` VALUES (9999,532,'yes','Tainet%GPASS%Mercury 3630E');
INSERT INTO `Dictionary` VALUES (9999,533,'yes','Tainet%GPASS%DSD-08A');
INSERT INTO `Dictionary` VALUES (11,534,'yes','HP ProLiant%GPASS%DL160');
INSERT INTO `Dictionary` VALUES (11,535,'yes','HP ProLiant%GPASS%DL180');
INSERT INTO `Dictionary` VALUES (11,536,'yes','HP ProLiant%GPASS%DL185');
INSERT INTO `Dictionary` VALUES (11,537,'yes','HP ProLiant%GPASS%DL365');
INSERT INTO `Dictionary` VALUES (11,538,'yes','HP ProLiant%GPASS%DL320s');
INSERT INTO `Dictionary` VALUES (11,539,'yes','HP ProLiant%GPASS%DL320p');
INSERT INTO `Dictionary` VALUES (11,540,'yes','HP ProLiant%GPASS%ML115');
INSERT INTO `Dictionary` VALUES (12,541,'yes','Force10%GPASS%S2410CP');
INSERT INTO `Dictionary` VALUES (12,542,'yes','Force10%GPASS%S50N');
INSERT INTO `Dictionary` VALUES (12,543,'yes','Force10%GPASS%S50V');
INSERT INTO `Dictionary` VALUES (12,544,'yes','Force10%GPASS%S25P');
INSERT INTO `Dictionary` VALUES (12,545,'yes','Force10%GPASS%C150');
INSERT INTO `Dictionary` VALUES (12,546,'yes','Force10%GPASS%C300');
INSERT INTO `Dictionary` VALUES (12,547,'yes','Force10%GPASS%E300');
INSERT INTO `Dictionary` VALUES (12,548,'yes','Force10%GPASS%E600');
INSERT INTO `Dictionary` VALUES (12,549,'yes','Force10%GPASS%E1200');
INSERT INTO `Dictionary` VALUES (12,550,'yes','NETGEAR%GPASS%JGS524F');
INSERT INTO `Dictionary` VALUES (12,551,'yes','NETGEAR%GPASS%JGS516');
INSERT INTO `Dictionary` VALUES (12,552,'yes','NETGEAR%GPASS%JFS524');
INSERT INTO `Dictionary` VALUES (12,553,'yes','NETGEAR%GPASS%JFS524F');
INSERT INTO `Dictionary` VALUES (12,554,'yes','NETGEAR%GPASS%JGS524');
INSERT INTO `Dictionary` VALUES (12,555,'yes','NETGEAR%GPASS%FS524');
INSERT INTO `Dictionary` VALUES (12,556,'yes','NETGEAR%GPASS%JFS516');
INSERT INTO `Dictionary` VALUES (12,557,'yes','NETGEAR%GPASS%GSM7224R');
INSERT INTO `Dictionary` VALUES (12,558,'yes','NETGEAR%GPASS%GSM7248');
INSERT INTO `Dictionary` VALUES (12,559,'yes','NETGEAR%GPASS%GSM7212');
INSERT INTO `Dictionary` VALUES (12,560,'yes','NETGEAR%GPASS%FSM726S');
INSERT INTO `Dictionary` VALUES (12,561,'yes','NETGEAR%GPASS%GSM7248R');
INSERT INTO `Dictionary` VALUES (12,562,'yes','NETGEAR%GPASS%GSM7224v1');
INSERT INTO `Dictionary` VALUES (12,563,'yes','NETGEAR%GPASS%FSM750S');
INSERT INTO `Dictionary` VALUES (12,564,'yes','NETGEAR%GPASS%FSM726');
INSERT INTO `Dictionary` VALUES (12,565,'yes','NETGEAR%GPASS%GS724TP');
INSERT INTO `Dictionary` VALUES (12,566,'yes','NETGEAR%GPASS%GS748TS');
INSERT INTO `Dictionary` VALUES (12,567,'yes','NETGEAR%GPASS%GS724T');
INSERT INTO `Dictionary` VALUES (12,568,'yes','NETGEAR%GPASS%FS728TP');
INSERT INTO `Dictionary` VALUES (12,569,'yes','NETGEAR%GPASS%FS752TS');
INSERT INTO `Dictionary` VALUES (12,570,'yes','NETGEAR%GPASS%FS728TS');
INSERT INTO `Dictionary` VALUES (12,571,'yes','NETGEAR%GPASS%FS726T');
INSERT INTO `Dictionary` VALUES (12,572,'yes','NETGEAR%GPASS%GS748TP');
INSERT INTO `Dictionary` VALUES (12,573,'yes','NETGEAR%GPASS%GS724TS');
INSERT INTO `Dictionary` VALUES (12,574,'yes','NETGEAR%GPASS%GS748T');
INSERT INTO `Dictionary` VALUES (12,575,'yes','NETGEAR%GPASS%GS716T');
INSERT INTO `Dictionary` VALUES (12,576,'yes','NETGEAR%GPASS%FS752TPS');
INSERT INTO `Dictionary` VALUES (12,577,'yes','NETGEAR%GPASS%FS750T2');
INSERT INTO `Dictionary` VALUES (12,578,'yes','NETGEAR%GPASS%FS726TP');
INSERT INTO `Dictionary` VALUES (12,579,'yes','NETGEAR%GPASS%FSM7328PS');
INSERT INTO `Dictionary` VALUES (12,580,'yes','NETGEAR%GPASS%GSM7352S');
INSERT INTO `Dictionary` VALUES (12,581,'yes','NETGEAR%GPASS%GSM7324');
INSERT INTO `Dictionary` VALUES (12,582,'yes','NETGEAR%GPASS%FSM7326P');
INSERT INTO `Dictionary` VALUES (12,583,'yes','NETGEAR%GPASS%FSM7352PS');
INSERT INTO `Dictionary` VALUES (12,584,'yes','NETGEAR%GPASS%GSM7328FS');
INSERT INTO `Dictionary` VALUES (12,585,'yes','NETGEAR%GPASS%GSM7328Sv1');
INSERT INTO `Dictionary` VALUES (12,586,'yes','NETGEAR%GPASS%GSM7312');
INSERT INTO `Dictionary` VALUES (12,587,'yes','NETGEAR%GPASS%FSM7328S');
INSERT INTO `Dictionary` VALUES (12,588,'yes','NETGEAR%GPASS%FSM7352S');
INSERT INTO `Dictionary` VALUES (12,589,'yes','[[D-Link%GPASS%DES-6500 | http://www.dlink.com/products/?sec=0&pid=341]]');
INSERT INTO `Dictionary` VALUES (12,590,'yes','[[D-Link%GPASS%DWS-3227 | http://www.dlink.com/products/?sec=0&pid=506]]');
INSERT INTO `Dictionary` VALUES (12,591,'yes','[[D-Link%GPASS%DWS-3227P | http://www.dlink.com/products/?sec=0&pid=507]]');
INSERT INTO `Dictionary` VALUES (12,592,'yes','[[D-Link%GPASS%DWS-3250 | http://www.dlink.com/products/?sec=0&pid=468]]');
INSERT INTO `Dictionary` VALUES (12,593,'yes','[[D-Link%GPASS%DWS-1008 | http://www.dlink.com/products/?sec=0&pid=434]]');
INSERT INTO `Dictionary` VALUES (12,594,'yes','[[D-Link%GPASS%DGS-3612G | http://www.dlink.com/products/?sec=0&pid=557]]');
INSERT INTO `Dictionary` VALUES (12,595,'yes','[[D-Link%GPASS%DGS-3627 | http://www.dlink.com/products/?sec=0&pid=639]]');
INSERT INTO `Dictionary` VALUES (12,596,'yes','[[D-Link%GPASS%DGS-3650 | http://www.dlink.com/products/?sec=0&pid=640]]');
INSERT INTO `Dictionary` VALUES (12,597,'yes','[[D-Link%GPASS%DGS-3324SR | http://www.dlink.com/products/?sec=0&pid=294]]');
INSERT INTO `Dictionary` VALUES (12,598,'yes','[[D-Link%GPASS%DGS-3324SRi | http://www.dlink.com/products/?sec=0&pid=309]]');
INSERT INTO `Dictionary` VALUES (12,599,'yes','[[D-Link%GPASS%DXS-3326GSR | http://www.dlink.com/products/?sec=0&pid=339]]');
INSERT INTO `Dictionary` VALUES (12,600,'yes','[[D-Link%GPASS%DXS-3350SR | http://www.dlink.com/products/?sec=0&pid=340]]');
INSERT INTO `Dictionary` VALUES (12,601,'yes','[[D-Link%GPASS%DES-3828 | http://www.dlink.com/products/?sec=0&pid=439]]');
INSERT INTO `Dictionary` VALUES (12,602,'yes','[[D-Link%GPASS%DES-3828P | http://www.dlink.com/products/?sec=0&pid=440]]');
INSERT INTO `Dictionary` VALUES (12,603,'yes','[[D-Link%GPASS%DGS-3100-24 | http://www.dlink.com/products/?sec=0&pid=635]]');
INSERT INTO `Dictionary` VALUES (12,604,'yes','[[D-Link%GPASS%DGS-3100-24P | http://www.dlink.com/products/?sec=0&pid=636]]');
INSERT INTO `Dictionary` VALUES (12,605,'yes','[[D-Link%GPASS%DGS-3100-48 | http://www.dlink.com/products/?sec=0&pid=637]]');
INSERT INTO `Dictionary` VALUES (12,606,'yes','[[D-Link%GPASS%DGS-3100-48P | http://www.dlink.com/products/?sec=0&pid=638]]');
INSERT INTO `Dictionary` VALUES (12,607,'yes','[[D-Link%GPASS%DXS-3227 | http://www.dlink.com/products/?sec=0&pid=483]]');
INSERT INTO `Dictionary` VALUES (12,608,'yes','[[D-Link%GPASS%DXS-3227P | http://www.dlink.com/products/?sec=0&pid=497]]');
INSERT INTO `Dictionary` VALUES (12,609,'yes','[[D-Link%GPASS%DXS-3250 | http://www.dlink.com/products/?sec=0&pid=443]]');
INSERT INTO `Dictionary` VALUES (12,610,'yes','[[D-Link%GPASS%DGS-3024 | http://www.dlink.com/products/?sec=0&pid=404]]');
INSERT INTO `Dictionary` VALUES (12,611,'yes','[[D-Link%GPASS%DGS-3224TGR | http://www.dlink.com/products/?sec=0&pid=269]]');
INSERT INTO `Dictionary` VALUES (12,612,'yes','[[D-Link%GPASS%DGS-3048 | http://www.dlink.com/products/?sec=0&pid=496]]');
INSERT INTO `Dictionary` VALUES (12,613,'yes','[[D-Link%GPASS%DES-3228PA | http://www.dlink.com/products/?sec=0&pid=644]]');
INSERT INTO `Dictionary` VALUES (12,614,'yes','[[D-Link%GPASS%DES-3028 | http://www.dlink.com/products/?sec=0&pid=630]]');
INSERT INTO `Dictionary` VALUES (12,615,'yes','[[D-Link%GPASS%DES-3028P | http://www.dlink.com/products/?sec=0&pid=631]]');
INSERT INTO `Dictionary` VALUES (12,616,'yes','[[D-Link%GPASS%DES-3052 | http://www.dlink.com/products/?sec=0&pid=632]]');
INSERT INTO `Dictionary` VALUES (12,617,'yes','[[D-Link%GPASS%DES-3052P | http://www.dlink.com/products/?sec=0&pid=633]]');
INSERT INTO `Dictionary` VALUES (12,618,'yes','[[D-Link%GPASS%DES-3010FA | http://www.dlink.com/products/?sec=0&pid=423]]');
INSERT INTO `Dictionary` VALUES (12,619,'yes','[[D-Link%GPASS%DES-3010GA | http://www.dlink.com/products/?sec=0&pid=424]]');
INSERT INTO `Dictionary` VALUES (12,620,'yes','[[D-Link%GPASS%DES-3010PA | http://www.dlink.com/products/?sec=0&pid=469]]');
INSERT INTO `Dictionary` VALUES (12,621,'yes','[[D-Link%GPASS%DES-3226L | http://www.dlink.com/products/?sec=0&pid=298]]');
INSERT INTO `Dictionary` VALUES (12,622,'yes','[[D-Link%GPASS%DES-3526 | http://www.dlink.com/products/?sec=0&pid=330]]');
INSERT INTO `Dictionary` VALUES (12,623,'yes','[[D-Link%GPASS%DES-3550 | http://www.dlink.com/products/?sec=0&pid=331]]');
INSERT INTO `Dictionary` VALUES (12,624,'yes','[[D-Link%GPASS%DGS-1216T | http://www.dlink.com/products/?sec=0&pid=324]]');
INSERT INTO `Dictionary` VALUES (12,625,'yes','[[D-Link%GPASS%DGS-1224T | http://www.dlink.com/products/?sec=0&pid=329]]');
INSERT INTO `Dictionary` VALUES (12,626,'yes','[[D-Link%GPASS%DGS-1248T | http://www.dlink.com/products/?sec=0&pid=367]]');
INSERT INTO `Dictionary` VALUES (12,627,'yes','[[D-Link%GPASS%DES-1316 | http://www.dlink.com/products/?sec=0&pid=353]]');
INSERT INTO `Dictionary` VALUES (12,628,'yes','[[D-Link%GPASS%DES-1228 | http://www.dlink.com/products/?sec=0&pid=540]]');
INSERT INTO `Dictionary` VALUES (12,629,'yes','[[D-Link%GPASS%DES-1228P | http://www.dlink.com/products/?sec=0&pid=541]]');
INSERT INTO `Dictionary` VALUES (12,630,'yes','[[D-Link%GPASS%DES-1252 | http://www.dlink.com/products/?sec=0&pid=555]]');
INSERT INTO `Dictionary` VALUES (12,631,'yes','[[D-Link%GPASS%DGS-1016D | http://www.dlink.com/products/?sec=0&pid=337]]');
INSERT INTO `Dictionary` VALUES (12,632,'yes','[[D-Link%GPASS%DGS-1024D | http://www.dlink.com/products/?sec=0&pid=338]]');
INSERT INTO `Dictionary` VALUES (12,633,'yes','[[D-Link%GPASS%DSS-24+ | http://www.dlink.com/products/?sec=0&pid=73]]');
INSERT INTO `Dictionary` VALUES (12,634,'yes','[[D-Link%GPASS%DES-1024D | http://www.dlink.com/products/?sec=0&pid=75]]');
INSERT INTO `Dictionary` VALUES (12,635,'yes','[[D-Link%GPASS%DES-1026G | http://www.dlink.com/products/?sec=0&pid=76]]');
INSERT INTO `Dictionary` VALUES (21,636,'yes','[[D-Link%GPASS%DKVM-16 | http://www.dlink.com/products/?sec=0&pid=228]]');
INSERT INTO `Dictionary` VALUES (21,637,'yes','[[D-Link%GPASS%DKVM-8E | http://www.dlink.com/products/?sec=0&pid=161]]');
INSERT INTO `Dictionary` VALUES (9999,638,'yes','Raisecom%GPASS%RC702');
INSERT INTO `Dictionary` VALUES (9999,639,'yes','Raisecom%GPASS%RC702-GE');
INSERT INTO `Dictionary` VALUES (9999,640,'yes','Raisecom%GPASS%ISCOM4300');
INSERT INTO `Dictionary` VALUES (9999,641,'yes','Raisecom%GPASS%RC953-FE4E1');
INSERT INTO `Dictionary` VALUES (9999,642,'yes','Raisecom%GPASS%RC953-FX4E1');
INSERT INTO `Dictionary` VALUES (9999,643,'yes','Raisecom%GPASS%RC953-FE8E1');
INSERT INTO `Dictionary` VALUES (9999,644,'yes','Raisecom%GPASS%RC953-FX8E1');
INSERT INTO `Dictionary` VALUES (9999,645,'yes','Raisecom%GPASS%RC953-8FE16E1');
INSERT INTO `Dictionary` VALUES (9999,646,'yes','Raisecom%GPASS%RC953E-3FE16E1');
INSERT INTO `Dictionary` VALUES (9999,647,'yes','Raisecom%GPASS%RC953-GESTM1');
INSERT INTO `Dictionary` VALUES (9999,648,'yes','Raisecom%GPASS%OPCOM3100-155');
INSERT INTO `Dictionary` VALUES (9999,649,'yes','Raisecom%GPASS%OPCOM3101-155');
INSERT INTO `Dictionary` VALUES (9999,650,'yes','Raisecom%GPASS%RC831-120');
INSERT INTO `Dictionary` VALUES (9999,651,'yes','Raisecom%GPASS%RC831-120-BL');
INSERT INTO `Dictionary` VALUES (9999,652,'yes','Raisecom%GPASS%RC831-240');
INSERT INTO `Dictionary` VALUES (9999,653,'yes','Raisecom%GPASS%RC831-240E');
INSERT INTO `Dictionary` VALUES (9999,654,'yes','Raisecom%GPASS%RCMS2801-480GE-BL');
INSERT INTO `Dictionary` VALUES (9999,655,'yes','Raisecom%GPASS%RCMS2801-120FE');
INSERT INTO `Dictionary` VALUES (9999,656,'yes','Raisecom%GPASS%RCMS2801-120FE-BL');
INSERT INTO `Dictionary` VALUES (9999,657,'yes','Raisecom%GPASS%RCMS2801-240FE');
INSERT INTO `Dictionary` VALUES (9999,658,'yes','Raisecom%GPASS%RCMS2801-240FE-BL');
INSERT INTO `Dictionary` VALUES (9999,659,'yes','Raisecom%GPASS%RCMS2801-240EFE');
INSERT INTO `Dictionary` VALUES (9999,660,'yes','Raisecom%GPASS%RCMS2811-120FE');
INSERT INTO `Dictionary` VALUES (9999,661,'yes','Raisecom%GPASS%RCMS2811-240FE');
INSERT INTO `Dictionary` VALUES (9999,662,'yes','Raisecom%GPASS%RCMS2811-240FE-BL');
INSERT INTO `Dictionary` VALUES (9999,663,'yes','Raisecom%GPASS%RCMS2811-480FE');
INSERT INTO `Dictionary` VALUES (9999,664,'yes','Raisecom%GPASS%RCMS2811-480FE-BL');
INSERT INTO `Dictionary` VALUES (9999,665,'yes','Raisecom%GPASS%RCMS2811-240EFE');
INSERT INTO `Dictionary` VALUES (9999,666,'yes','Raisecom%GPASS%RCMS2104-120');
INSERT INTO `Dictionary` VALUES (9999,667,'yes','Raisecom%GPASS%RCMS2304-120');
INSERT INTO `Dictionary` VALUES (9999,668,'yes','Raisecom%GPASS%RCMS2504-120');
INSERT INTO `Dictionary` VALUES (9999,669,'yes','Raisecom%GPASS%RCMS2104-240');
INSERT INTO `Dictionary` VALUES (9999,670,'yes','Raisecom%GPASS%RCMS2304-240');
INSERT INTO `Dictionary` VALUES (9999,671,'yes','Raisecom%GPASS%RCMS2504-240');
INSERT INTO `Dictionary` VALUES (9999,672,'yes','Raisecom%GPASS%RC801-120B');
INSERT INTO `Dictionary` VALUES (9999,673,'yes','Raisecom%GPASS%RC801-240B');
INSERT INTO `Dictionary` VALUES (9999,674,'yes','Raisecom%GPASS%RC801-480B');
INSERT INTO `Dictionary` VALUES (9999,675,'yes','Raisecom%GPASS%RC803-120B');
INSERT INTO `Dictionary` VALUES (9999,676,'yes','Raisecom%GPASS%RC803-240B');
INSERT INTO `Dictionary` VALUES (9999,677,'yes','Raisecom%GPASS%RC803-480B');
INSERT INTO `Dictionary` VALUES (9999,678,'yes','Raisecom%GPASS%RC805-120B');
INSERT INTO `Dictionary` VALUES (9999,679,'yes','Raisecom%GPASS%RC805-240B');
INSERT INTO `Dictionary` VALUES (9999,680,'yes','Raisecom%GPASS%RC805-480B');
INSERT INTO `Dictionary` VALUES (2,681,'yes','RS-232 (DB-9)');
INSERT INTO `Dictionary` VALUES (2,682,'yes','RS-232 (DB-25)');
INSERT INTO `Dictionary` VALUES (12,683,'yes','Force10%GPASS%S2410P');
INSERT INTO `Dictionary` VALUES (12,684,'yes','[[Extreme Networks%GPASS%Summit X150-24t | http://www.extremenetworks.com/products/summit-x150.aspx]]');
INSERT INTO `Dictionary` VALUES (12,685,'yes','[[Extreme Networks%GPASS%Summit X150-48t | http://www.extremenetworks.com/products/summit-x150.aspx]]');
INSERT INTO `Dictionary` VALUES (12,686,'yes','[[Extreme Networks%GPASS%Summit X150-24p | http://www.extremenetworks.com/products/summit-x150.aspx]]');
INSERT INTO `Dictionary` VALUES (12,687,'yes','[[Extreme Networks%GPASS%Summit X250e-24t | http://www.extremenetworks.com/products/summit-x250e.aspx]]');
INSERT INTO `Dictionary` VALUES (12,688,'yes','[[Extreme Networks%GPASS%Summit X250e-48t | http://www.extremenetworks.com/products/summit-x250e.aspx]]');
INSERT INTO `Dictionary` VALUES (12,689,'yes','[[Extreme Networks%GPASS%Summit X250e-24p | http://www.extremenetworks.com/products/summit-x250e.aspx]]');
INSERT INTO `Dictionary` VALUES (12,690,'yes','[[Extreme Networks%GPASS%Summit X250e-48p | http://www.extremenetworks.com/products/summit-x250e.aspx]]');
INSERT INTO `Dictionary` VALUES (12,691,'yes','[[Extreme Networks%GPASS%Summit X250e-24x | http://www.extremenetworks.com/products/summit-x250e.aspx]]');
INSERT INTO `Dictionary` VALUES (12,692,'yes','[[Extreme Networks%GPASS%Summit X450-24t | http://www.extremenetworks.com/products/summit-x450.aspx]]');
INSERT INTO `Dictionary` VALUES (12,693,'yes','[[Extreme Networks%GPASS%Summit X450-24x | http://www.extremenetworks.com/products/summit-x450.aspx]]');
INSERT INTO `Dictionary` VALUES (12,694,'yes','[[Extreme Networks%GPASS%Summit X450a-24t | http://www.extremenetworks.com/products/summit-x450a.aspx]]');
INSERT INTO `Dictionary` VALUES (12,695,'yes','[[Extreme Networks%GPASS%Summit X450a-48t | http://www.extremenetworks.com/products/summit-x450a.aspx]]');
INSERT INTO `Dictionary` VALUES (12,696,'yes','[[Extreme Networks%GPASS%Summit X450a-24x | http://www.extremenetworks.com/products/summit-x450a.aspx]]');
INSERT INTO `Dictionary` VALUES (12,697,'yes','[[Extreme Networks%GPASS%Summit X450e-24p | http://www.extremenetworks.com/products/summit-x450e.aspx]]');
INSERT INTO `Dictionary` VALUES (12,698,'yes','[[Extreme Networks%GPASS%Summit X450e-48p | http://www.extremenetworks.com/products/summit-x450e.aspx]]');
INSERT INTO `Dictionary` VALUES (12,699,'yes','[[Extreme Networks%GPASS%Summit 200-24fx | http://www.extremenetworks.com/products/summit-200.aspx]]');
INSERT INTO `Dictionary` VALUES (12,700,'yes','[[Extreme Networks%GPASS%Summit 200-24 | http://www.extremenetworks.com/products/summit-200.aspx]]');
INSERT INTO `Dictionary` VALUES (12,701,'yes','[[Extreme Networks%GPASS%Summit 200-48 | http://www.extremenetworks.com/products/summit-200.aspx]]');
INSERT INTO `Dictionary` VALUES (12,702,'yes','[[Extreme Networks%GPASS%Summit 300-24 | http://www.extremenetworks.com/products/summit-300.aspx]]');
INSERT INTO `Dictionary` VALUES (12,703,'yes','[[Extreme Networks%GPASS%Summit 300-48 | http://www.extremenetworks.com/products/summit-300.aspx]]');
INSERT INTO `Dictionary` VALUES (12,704,'yes','[[Extreme Networks%GPASS%Summit 400-24p | http://www.extremenetworks.com/products/summit-400-24p.aspx]]');
INSERT INTO `Dictionary` VALUES (12,705,'yes','[[Extreme Networks%GPASS%Summit 400-24t | http://www.extremenetworks.com/products/summit-400-24t.aspx]]');
INSERT INTO `Dictionary` VALUES (12,706,'yes','[[Extreme Networks%GPASS%Summit 400-48t | http://www.extremenetworks.com/products/summit-400-48t.aspx]]');
INSERT INTO `Dictionary` VALUES (12,707,'yes','[[Extreme Networks%GPASS%Summit48si | http://www.extremenetworks.com/products/summit-48si.aspx]]');
INSERT INTO `Dictionary` VALUES (12,708,'yes','[[Extreme Networks%GPASS%Alpine 3804 | http://www.extremenetworks.com/products/Alpine-3800.aspx]]');
INSERT INTO `Dictionary` VALUES (12,709,'yes','[[Extreme Networks%GPASS%Alpine 3808 | http://www.extremenetworks.com/products/Alpine-3800.aspx]]');
INSERT INTO `Dictionary` VALUES (12,710,'yes','[[Extreme Networks%GPASS%BlackDiamond 6808 | http://www.extremenetworks.com/products/blackdiamond-6800.aspx]]');
INSERT INTO `Dictionary` VALUES (12,711,'yes','[[Extreme Networks%GPASS%BlackDiamond 8806 | http://www.extremenetworks.com/products/blackdiamond-8800.aspx]]');
INSERT INTO `Dictionary` VALUES (12,712,'yes','[[Extreme Networks%GPASS%BlackDiamond 8810 | http://www.extremenetworks.com/products/blackdiamond-8800.aspx]]');
INSERT INTO `Dictionary` VALUES (12,713,'yes','[[Extreme Networks%GPASS%BlackDiamond 10808 | http://www.extremenetworks.com/products/blackdiamond-10808.aspx]]');
INSERT INTO `Dictionary` VALUES (12,714,'yes','[[Extreme Networks%GPASS%BlackDiamond 12802R | http://www.extremenetworks.com/products/blackdiamond-12800r.aspx]]');
INSERT INTO `Dictionary` VALUES (12,715,'yes','[[Extreme Networks%GPASS%BlackDiamond 12804R | http://www.extremenetworks.com/products/blackdiamond-12800r.aspx]]');
INSERT INTO `Dictionary` VALUES (12,716,'yes','[[Extreme Networks%GPASS%BlackDiamond 12804C | http://www.extremenetworks.com/products/blackdiamond-12804c.aspx]]');
INSERT INTO `Dictionary` VALUES (24,717,'yes','[[Cisco%GPASS%ASR 1002 | http://cisco.com/en/US/products/ps9436/index.html]]');
INSERT INTO `Dictionary` VALUES (24,718,'yes','[[Cisco%GPASS%ASR 1004 | http://cisco.com/en/US/products/ps9437/index.html]]');
INSERT INTO `Dictionary` VALUES (24,719,'yes','[[Cisco%GPASS%ASR 1006 | http://cisco.com/en/US/products/ps9438/index.html]]');
INSERT INTO `Dictionary` VALUES (13,720,'yes','[[OpenBSD%GSKIP%OpenBSD 3.3 | http://www.openbsd.org/33.html]]');
INSERT INTO `Dictionary` VALUES (13,721,'yes','[[OpenBSD%GSKIP%OpenBSD 3.4 | http://www.openbsd.org/34.html]]');
INSERT INTO `Dictionary` VALUES (13,722,'yes','[[OpenBSD%GSKIP%OpenBSD 3.5 | http://www.openbsd.org/35.html]]');
INSERT INTO `Dictionary` VALUES (13,723,'yes','[[OpenBSD%GSKIP%OpenBSD 3.6 | http://www.openbsd.org/36.html]]');
INSERT INTO `Dictionary` VALUES (13,724,'yes','[[OpenBSD%GSKIP%OpenBSD 3.7 | http://www.openbsd.org/37.html]]');
INSERT INTO `Dictionary` VALUES (13,725,'yes','[[OpenBSD%GSKIP%OpenBSD 3.8 | http://www.openbsd.org/38.html]]');
INSERT INTO `Dictionary` VALUES (13,726,'yes','[[OpenBSD%GSKIP%OpenBSD 3.9 | http://www.openbsd.org/39.html]]');
INSERT INTO `Dictionary` VALUES (13,727,'yes','[[OpenBSD%GSKIP%OpenBSD 4.0 | http://www.openbsd.org/40.html]]');
INSERT INTO `Dictionary` VALUES (13,728,'yes','[[OpenBSD%GSKIP%OpenBSD 4.1 | http://www.openbsd.org/41.html]]');
INSERT INTO `Dictionary` VALUES (13,729,'yes','[[OpenBSD%GSKIP%OpenBSD 4.2 | http://www.openbsd.org/42.html]]');
INSERT INTO `Dictionary` VALUES (13,730,'yes','[[OpenBSD%GSKIP%OpenBSD 4.3 | http://www.openbsd.org/43.html]]');
INSERT INTO `Dictionary` VALUES (13,732,'yes','[[FreeBSD%GSKIP%FreeBSD 7.0 | http://www.freebsd.org/releases/7.0R/announce.html]]');
INSERT INTO `Dictionary` VALUES (13,733,'yes','[[NetBSD%GSKIP%NetBSD 2.0 | http://netbsd.org/releases/formal-2.0/]]');
INSERT INTO `Dictionary` VALUES (13,734,'yes','[[NetBSD%GSKIP%NetBSD 2.1 | http://netbsd.org/releases/formal-2.0/NetBSD-2.1.html]]');
INSERT INTO `Dictionary` VALUES (13,735,'yes','[[NetBSD%GSKIP%NetBSD 3.0 | http://netbsd.org/releases/formal-3/]]');
INSERT INTO `Dictionary` VALUES (13,736,'yes','[[NetBSD%GSKIP%NetBSD 3.1 | http://netbsd.org/releases/formal-3/NetBSD-3.1.html]]');
INSERT INTO `Dictionary` VALUES (13,737,'yes','[[NetBSD%GSKIP%NetBSD 4.0 | http://netbsd.org/releases/formal-4/NetBSD-4.0.html]]');
INSERT INTO `Dictionary` VALUES (12,738,'yes','3Com%GPASS%Baseline 2016');
INSERT INTO `Dictionary` VALUES (12,739,'yes','3Com%GPASS%Baseline 2024');
INSERT INTO `Dictionary` VALUES (12,740,'yes','3Com%GPASS%Baseline 2126-G');
INSERT INTO `Dictionary` VALUES (12,741,'yes','3Com%GPASS%Baseline 2816');
INSERT INTO `Dictionary` VALUES (12,742,'yes','3Com%GPASS%Baseline 2824');
INSERT INTO `Dictionary` VALUES (12,743,'yes','3Com%GPASS%Baseline 2226 Plus');
INSERT INTO `Dictionary` VALUES (12,744,'yes','3Com%GPASS%Baseline 2426-PWR Plus');
INSERT INTO `Dictionary` VALUES (12,745,'yes','3Com%GPASS%Baseline 2250 Plus');
INSERT INTO `Dictionary` VALUES (12,746,'yes','3Com%GPASS%Baseline 2916-SFP Plus');
INSERT INTO `Dictionary` VALUES (12,747,'yes','3Com%GPASS%Baseline 2924-SFP Plus');
INSERT INTO `Dictionary` VALUES (12,748,'yes','3Com%GPASS%Baseline 2924-PWR Plus');
INSERT INTO `Dictionary` VALUES (12,749,'yes','3Com%GPASS%Baseline 2948-SFP Plus');
INSERT INTO `Dictionary` VALUES (12,750,'yes','3Com%GPASS%3870 24-port');
INSERT INTO `Dictionary` VALUES (12,751,'yes','3Com%GPASS%3870 48-port');
INSERT INTO `Dictionary` VALUES (12,752,'yes','3Com%GPASS%4200 26-port');
INSERT INTO `Dictionary` VALUES (12,753,'yes','3Com%GPASS%4200 28-port');
INSERT INTO `Dictionary` VALUES (12,754,'yes','3Com%GPASS%4200 50-port');
INSERT INTO `Dictionary` VALUES (12,755,'yes','3Com%GPASS%4200G 12-port');
INSERT INTO `Dictionary` VALUES (12,756,'yes','3Com%GPASS%4200G 24-port');
INSERT INTO `Dictionary` VALUES (12,757,'yes','3Com%GPASS%4200G PWR 24-port');
INSERT INTO `Dictionary` VALUES (12,758,'yes','3Com%GPASS%4200G 48-port');
INSERT INTO `Dictionary` VALUES (12,759,'yes','3Com%GPASS%4210 26-port');
INSERT INTO `Dictionary` VALUES (12,760,'yes','3Com%GPASS%4210 52-port');
INSERT INTO `Dictionary` VALUES (12,761,'yes','3Com%GPASS%4210 26-port PWR');
INSERT INTO `Dictionary` VALUES (12,762,'yes','3Com%GPASS%SS3 4400 48-port');
INSERT INTO `Dictionary` VALUES (12,763,'yes','3Com%GPASS%SS3 4400 24-port');
INSERT INTO `Dictionary` VALUES (12,764,'yes','3Com%GPASS%SS3 4400 PWR');
INSERT INTO `Dictionary` VALUES (12,765,'yes','3Com%GPASS%SS3 4400 SE 24-port');
INSERT INTO `Dictionary` VALUES (12,766,'yes','3Com%GPASS%4500 26-port');
INSERT INTO `Dictionary` VALUES (12,767,'yes','3Com%GPASS%4500 50-port');
INSERT INTO `Dictionary` VALUES (12,768,'yes','3Com%GPASS%4500 PWR 26-port');
INSERT INTO `Dictionary` VALUES (12,769,'yes','3Com%GPASS%4500 PWR 50-port');
INSERT INTO `Dictionary` VALUES (12,770,'yes','3Com%GPASS%4500G 24-port');
INSERT INTO `Dictionary` VALUES (12,771,'yes','3Com%GPASS%4500G 48-port');
INSERT INTO `Dictionary` VALUES (12,772,'yes','3Com%GPASS%4500G PWR 24-port');
INSERT INTO `Dictionary` VALUES (12,773,'yes','3Com%GPASS%4500G PWR 48-port');
INSERT INTO `Dictionary` VALUES (12,774,'yes','3Com%GPASS%5500-EI 28-port');
INSERT INTO `Dictionary` VALUES (12,775,'yes','3Com%GPASS%5500-EI 52-port');
INSERT INTO `Dictionary` VALUES (12,776,'yes','3Com%GPASS%5500-EI 28-port PWR');
INSERT INTO `Dictionary` VALUES (12,777,'yes','3Com%GPASS%5500-EI 52-port PWR');
INSERT INTO `Dictionary` VALUES (12,778,'yes','3Com%GPASS%5500-EI 28-port FX');
INSERT INTO `Dictionary` VALUES (12,779,'yes','3Com%GPASS%5500G-EI 24-port');
INSERT INTO `Dictionary` VALUES (12,780,'yes','3Com%GPASS%5500G-EI 48-port');
INSERT INTO `Dictionary` VALUES (12,781,'yes','3Com%GPASS%5500G-EI PWR 24-port');
INSERT INTO `Dictionary` VALUES (12,782,'yes','3Com%GPASS%5500G-EI 48-port PWR');
INSERT INTO `Dictionary` VALUES (12,783,'yes','3Com%GPASS%5500G-EI 24-port SFP');
INSERT INTO `Dictionary` VALUES (12,784,'yes','3Com%GPASS%7754');
INSERT INTO `Dictionary` VALUES (12,785,'yes','3Com%GPASS%7757');
INSERT INTO `Dictionary` VALUES (12,786,'yes','3Com%GPASS%7758');
INSERT INTO `Dictionary` VALUES (12,787,'yes','3Com%GPASS%8807');
INSERT INTO `Dictionary` VALUES (12,788,'yes','3Com%GPASS%8810');
INSERT INTO `Dictionary` VALUES (12,789,'yes','3Com%GPASS%8814');
INSERT INTO `Dictionary` VALUES (13,790,'yes','RH Fedora%GSKIP%Fedora 9');
INSERT INTO `Dictionary` VALUES (13,791,'yes','OpenSUSE%GSKIP%openSUSE 11.x');
INSERT INTO `Dictionary` VALUES (11,792,'yes','SGI%GPASS%Altix XE250');
INSERT INTO `Dictionary` VALUES (11,793,'yes','SGI%GPASS%Altix XE310');
INSERT INTO `Dictionary` VALUES (11,794,'yes','SGI%GPASS%Altix XE320');
INSERT INTO `Dictionary` VALUES (12,795,'yes','[[Cisco (blade)%GPASS%Catalyst 3032-DEL | http://www.cisco.com/en/US/products/ps8772/index.html]]');
INSERT INTO `Dictionary` VALUES (13,796,'yes','Ubuntu%GSKIP%Ubuntu 8.10');
INSERT INTO `Dictionary` VALUES (13,797,'yes','[[OpenBSD%GSKIP%OpenBSD 4.4 | http://www.openbsd.org/44.html]]');
INSERT INTO `Dictionary` VALUES (1,798,'yes','Network security');
INSERT INTO `Dictionary` VALUES (24,799,'yes','Cisco%GPASS%ASA 5505');
INSERT INTO `Dictionary` VALUES (24,800,'yes','Cisco%GPASS%ASA 5510');
INSERT INTO `Dictionary` VALUES (24,801,'yes','Cisco%GPASS%ASA 5520');
INSERT INTO `Dictionary` VALUES (24,802,'yes','Cisco%GPASS%ASA 5540');
INSERT INTO `Dictionary` VALUES (24,803,'yes','Cisco%GPASS%ASA 5550');
INSERT INTO `Dictionary` VALUES (24,804,'yes','Cisco%GPASS%ASA 5580-20');
INSERT INTO `Dictionary` VALUES (24,805,'yes','Cisco%GPASS%ASA 5580-40');
INSERT INTO `Dictionary` VALUES (24,806,'yes','[[Cisco%GPASS%IDS 4215 | http://www.cisco.com/en/US/products/hw/vpndevc/ps4077/ps5367/index.html]]');
INSERT INTO `Dictionary` VALUES (24,807,'yes','[[Cisco%GPASS%IDS 4240 | http://www.cisco.com/en/US/products/ps5768/index.html]]');
INSERT INTO `Dictionary` VALUES (24,808,'yes','[[Cisco%GPASS%IDS 4255 | http://www.cisco.com/en/US/products/ps5769/index.html]]');
INSERT INTO `Dictionary` VALUES (24,809,'yes','[[Cisco%GPASS%IDS 4260 | http://www.cisco.com/en/US/products/ps6751/index.html]]');
INSERT INTO `Dictionary` VALUES (24,810,'yes','[[Cisco%GPASS%IDS 4270 | http://www.cisco.com/en/US/products/ps9157/index.html]]');
INSERT INTO `Dictionary` VALUES (24,811,'yes','Foundry%GPASS%SecureIron 100');
INSERT INTO `Dictionary` VALUES (24,812,'yes','Foundry%GPASS%SecureIron 100C');
INSERT INTO `Dictionary` VALUES (24,813,'yes','Foundry%GPASS%SecureIron 300');
INSERT INTO `Dictionary` VALUES (24,814,'yes','Foundry%GPASS%SecureIron 300C');
INSERT INTO `Dictionary` VALUES (24,815,'yes','Foundry%GPASS%SecureIronLS 100-4802');
INSERT INTO `Dictionary` VALUES (24,816,'yes','Foundry%GPASS%SecureIronLS 300-32GC02');
INSERT INTO `Dictionary` VALUES (24,817,'yes','Foundry%GPASS%SecureIronLS 300-32GC10G');
INSERT INTO `Dictionary` VALUES (24,818,'yes','[[D-Link%GPASS%DFL-1600 | http://www.dlink.com/products/?sec=0&pid=454]]');
INSERT INTO `Dictionary` VALUES (24,819,'yes','[[D-Link%GPASS%DFL-M510 | http://www.dlink.com/products/?sec=0&pid=455]]');
INSERT INTO `Dictionary` VALUES (24,820,'yes','[[Extreme Networks%GPASS%Sentriant AG200 | http://www.extremenetworks.com/products/sentriant-ag200.aspx]]');
INSERT INTO `Dictionary` VALUES (24,821,'yes','[[Extreme Networks%GPASS%Sentriant NG300 | http://www.extremenetworks.com/products/sentriant-ng300.aspx]]');
INSERT INTO `Dictionary` VALUES (24,822,'yes','Force10%GPASS%P-Series');
INSERT INTO `Dictionary` VALUES (24,823,'yes','[[Juniper%GPASS%SSG 140 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/ssg_140/index.html]]');
INSERT INTO `Dictionary` VALUES (24,824,'yes','[[Juniper%GPASS%SSG 320 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/ssg_300_series/index.html]]');
INSERT INTO `Dictionary` VALUES (24,825,'yes','[[Juniper%GPASS%SSG 350 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/ssg_300_series/index.html]]');
INSERT INTO `Dictionary` VALUES (24,826,'yes','[[Juniper%GPASS%SSG 520 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/ssg_500_series/index.html]]');
INSERT INTO `Dictionary` VALUES (24,827,'yes','[[Juniper%GPASS%SSG 550 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/ssg_500_series/index.html]]');
INSERT INTO `Dictionary` VALUES (24,828,'yes','[[Juniper%GPASS%ISG 1000 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/isg_series_slash_gprs/index.html]]');
INSERT INTO `Dictionary` VALUES (24,829,'yes','[[Juniper%GPASS%ISG 2000 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/isg_series_slash_gprs/index.html]]');
INSERT INTO `Dictionary` VALUES (24,830,'yes','[[Juniper%GPASS%NetScreen 5200 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/isg_series_slash_gprs/index.html]]');
INSERT INTO `Dictionary` VALUES (24,831,'yes','[[Juniper%GPASS%NetScreen 5400 | http://www.juniper.net/products_and_services/firewall_slash_ipsec_vpn/isg_series_slash_gprs/index.html]]');
INSERT INTO `Dictionary` VALUES (24,832,'yes','[[Juniper%GPASS%SRX 5600 | http://www.juniper.net/products_and_services/srx_series/index.html]]');
INSERT INTO `Dictionary` VALUES (24,833,'yes','[[Juniper%GPASS%SRX 5800 | http://www.juniper.net/products_and_services/srx_series/index.html]]');
INSERT INTO `Dictionary` VALUES (24,834,'yes','[[SonicWall%GPASS%PRO 1260 | http://www.sonicwall.com/us/products/PRO_1260.html]]');
INSERT INTO `Dictionary` VALUES (24,835,'yes','[[SonicWall%GPASS%PRO 2040 | http://www.sonicwall.com/us/products/PRO_2040.html]]');
INSERT INTO `Dictionary` VALUES (24,836,'yes','[[SonicWall%GPASS%PRO 3060 | http://www.sonicwall.com/us/products/PRO_3060.html]]');
INSERT INTO `Dictionary` VALUES (24,837,'yes','[[SonicWall%GPASS%PRO 4060 | http://www.sonicwall.com/us/products/PRO_4060.html]]');
INSERT INTO `Dictionary` VALUES (24,838,'yes','[[SonicWall%GPASS%PRO 4100 | http://www.sonicwall.com/us/products/PRO_4100.html]]');
INSERT INTO `Dictionary` VALUES (24,839,'yes','[[SonicWall%GPASS%PRO 5060 | http://www.sonicwall.com/us/products/PRO_5060.html]]');
INSERT INTO `Dictionary` VALUES (24,840,'yes','[[SonicWall%GPASS%NSA 240 | http://www.sonicwall.com/us/products/NSA_240.html]]');
INSERT INTO `Dictionary` VALUES (24,841,'yes','[[SonicWall%GPASS%NSA 2400 | http://www.sonicwall.com/us/products/NSA_2400.html]]');
INSERT INTO `Dictionary` VALUES (24,842,'yes','[[SonicWall%GPASS%NSA 3500 | http://www.sonicwall.com/us/products/NSA_3500.html]]');
INSERT INTO `Dictionary` VALUES (24,843,'yes','[[SonicWall%GPASS%NSA 4500 | http://www.sonicwall.com/us/products/NSA_4500.html]]');
INSERT INTO `Dictionary` VALUES (24,844,'yes','[[SonicWall%GPASS%NSA 5000 | http://www.sonicwall.com/us/products/NSA_5000.html]]');
INSERT INTO `Dictionary` VALUES (24,845,'yes','[[SonicWall%GPASS%NSA E5500 | http://www.sonicwall.com/us/products/NSA_E5500.html]]');
INSERT INTO `Dictionary` VALUES (24,846,'yes','[[SonicWall%GPASS%NSA E6500 | http://www.sonicwall.com/us/products/NSA_E6500.html]]');
INSERT INTO `Dictionary` VALUES (24,847,'yes','[[SonicWall%GPASS%NSA E7500 | http://www.sonicwall.com/us/products/NSA_E7500.html]]');
INSERT INTO `Dictionary` VALUES (12,848,'yes','HP ProCurve%GPASS%1400-24G');
INSERT INTO `Dictionary` VALUES (12,849,'yes','HP ProCurve%GPASS%1700-24');
INSERT INTO `Dictionary` VALUES (12,850,'yes','HP ProCurve%GPASS%1800-24G');
INSERT INTO `Dictionary` VALUES (12,851,'yes','HP ProCurve%GPASS%2124');
INSERT INTO `Dictionary` VALUES (12,852,'yes','HP ProCurve%GPASS%2312');
INSERT INTO `Dictionary` VALUES (12,853,'yes','HP ProCurve%GPASS%2324');
INSERT INTO `Dictionary` VALUES (12,854,'yes','HP ProCurve%GPASS%2510-24');
INSERT INTO `Dictionary` VALUES (12,855,'yes','HP ProCurve%GPASS%2510-48');
INSERT INTO `Dictionary` VALUES (12,856,'yes','HP ProCurve%GPASS%2510G-24');
INSERT INTO `Dictionary` VALUES (12,857,'yes','HP ProCurve%GPASS%2510G-48');
INSERT INTO `Dictionary` VALUES (12,858,'yes','HP ProCurve%GPASS%2512');
INSERT INTO `Dictionary` VALUES (12,859,'yes','HP ProCurve%GPASS%2524');
INSERT INTO `Dictionary` VALUES (12,860,'yes','HP ProCurve%GPASS%E2610-24 J9085A');
INSERT INTO `Dictionary` VALUES (12,861,'yes','HP ProCurve%GPASS%E2610-24-PoE J9087A');
INSERT INTO `Dictionary` VALUES (12,862,'yes','HP ProCurve%GPASS%E2610-48 J9088A');
INSERT INTO `Dictionary` VALUES (12,863,'yes','HP ProCurve%GPASS%E2610-48-PoE J9089A');
INSERT INTO `Dictionary` VALUES (12,864,'yes','HP ProCurve%GPASS%2626');
INSERT INTO `Dictionary` VALUES (12,865,'yes','HP ProCurve%GPASS%2626-PWR');
INSERT INTO `Dictionary` VALUES (12,866,'yes','HP ProCurve%GPASS%2650');
INSERT INTO `Dictionary` VALUES (12,867,'yes','HP ProCurve%GPASS%2650-PWR');
INSERT INTO `Dictionary` VALUES (12,868,'yes','HP ProCurve%GPASS%2810-24G');
INSERT INTO `Dictionary` VALUES (12,869,'yes','HP ProCurve%GPASS%2810-48G J9022A');
INSERT INTO `Dictionary` VALUES (12,870,'yes','HP ProCurve%GPASS%2824');
INSERT INTO `Dictionary` VALUES (12,871,'yes','HP ProCurve%GPASS%2848');
INSERT INTO `Dictionary` VALUES (12,872,'yes','HP ProCurve%GPASS%2900-24G');
INSERT INTO `Dictionary` VALUES (12,873,'yes','HP ProCurve%GPASS%2900-48G');
INSERT INTO `Dictionary` VALUES (12,874,'yes','HP ProCurve%GPASS%3400cl-24G');
INSERT INTO `Dictionary` VALUES (12,875,'yes','HP ProCurve%GPASS%3400cl-48G');
INSERT INTO `Dictionary` VALUES (12,876,'yes','HP ProCurve%GPASS%3500yl-24G-PWR');
INSERT INTO `Dictionary` VALUES (12,877,'yes','HP ProCurve%GPASS%3500yl-48G-PWR');
INSERT INTO `Dictionary` VALUES (12,878,'yes','HP ProCurve%GPASS%4202vl-72');
INSERT INTO `Dictionary` VALUES (12,879,'yes','HP ProCurve%GPASS%4204vl');
INSERT INTO `Dictionary` VALUES (12,880,'yes','HP ProCurve%GPASS%4204vl-48GS');
INSERT INTO `Dictionary` VALUES (12,881,'yes','HP ProCurve%GPASS%4208vl');
INSERT INTO `Dictionary` VALUES (12,882,'yes','HP ProCurve%GPASS%4208vl-72GS');
INSERT INTO `Dictionary` VALUES (12,883,'yes','HP ProCurve%GPASS%4208vl-96');
INSERT INTO `Dictionary` VALUES (12,884,'yes','HP ProCurve%GPASS%5304xl');
INSERT INTO `Dictionary` VALUES (12,885,'yes','HP ProCurve%GPASS%5308xl');
INSERT INTO `Dictionary` VALUES (12,886,'yes','HP ProCurve%GPASS%5348xl');
INSERT INTO `Dictionary` VALUES (12,887,'yes','HP ProCurve%GPASS%5372xl');
INSERT INTO `Dictionary` VALUES (30,888,'yes','HP ProCurve%GPASS%5406zl J8697A%L4,2H%');
INSERT INTO `Dictionary` VALUES (30,889,'yes','HP ProCurve%GPASS%5406zl-48G J8699A%L4,2H%');
INSERT INTO `Dictionary` VALUES (30,890,'yes','HP ProCurve%GPASS%5412zl J8698A%L7,2H%');
INSERT INTO `Dictionary` VALUES (30,891,'yes','HP ProCurve%GPASS%5412zl-96G J8700A%L7,2H%');
INSERT INTO `Dictionary` VALUES (12,892,'yes','HP ProCurve%GPASS%6108');
INSERT INTO `Dictionary` VALUES (12,893,'yes','HP ProCurve%GPASS%6200yl-24G-mGBIC');
INSERT INTO `Dictionary` VALUES (12,894,'yes','HP ProCurve%GPASS%6400cl');
INSERT INTO `Dictionary` VALUES (12,895,'yes','HP ProCurve%GPASS%6410cl');
INSERT INTO `Dictionary` VALUES (12,896,'yes','HP ProCurve%GPASS%8108fl');
INSERT INTO `Dictionary` VALUES (12,897,'yes','HP ProCurve%GPASS%8116fl');
INSERT INTO `Dictionary` VALUES (12,898,'yes','HP ProCurve%GPASS%8212zl');
INSERT INTO `Dictionary` VALUES (12,899,'yes','[[Juniper%GPASS%EX 3200-24P | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,900,'yes','[[Juniper%GPASS%EX 3200-24T | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,901,'yes','[[Juniper%GPASS%EX 3200-48P | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,902,'yes','[[Juniper%GPASS%EX 3200-48T | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,903,'yes','[[Juniper%GPASS%EX 4200-24F | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,904,'yes','[[Juniper%GPASS%EX 4200-24P | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,905,'yes','[[Juniper%GPASS%EX 4200-24T | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,906,'yes','[[Juniper%GPASS%EX 4200-48P | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,907,'yes','[[Juniper%GPASS%EX 4200-48T | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,908,'yes','[[Juniper%GPASS%EX 8208 | http://www.juniper.net/products_and_services/ex_series/index.html]]');
INSERT INTO `Dictionary` VALUES (12,909,'yes','[[Juniper%GPASS%E120 BSR | http://www.juniper.net/products_and_services/e_series_broadband_service/index.html]]');
INSERT INTO `Dictionary` VALUES (17,910,'yes','[[Juniper%GPASS%E320 BSR | http://www.juniper.net/products_and_services/e_series_broadband_service/index.html]]');
INSERT INTO `Dictionary` VALUES (17,911,'yes','[[Juniper%GPASS%ERX-310 | http://www.juniper.net/products_and_services/e_series_broadband_service/index.html]]');
INSERT INTO `Dictionary` VALUES (17,912,'yes','[[Juniper%GPASS%ERX-705 | http://www.juniper.net/products_and_services/e_series_broadband_service/index.html]]');
INSERT INTO `Dictionary` VALUES (17,913,'yes','[[Juniper%GPASS%ERX-710 | http://www.juniper.net/products_and_services/e_series_broadband_service/index.html]]');
INSERT INTO `Dictionary` VALUES (17,914,'yes','[[Juniper%GPASS%ERX-1410 | http://www.juniper.net/products_and_services/e_series_broadband_service/index.html]]');
INSERT INTO `Dictionary` VALUES (17,915,'yes','[[Juniper%GPASS%ERX-1440 | http://www.juniper.net/products_and_services/e_series_broadband_service/index.html]]');
INSERT INTO `Dictionary` VALUES (17,916,'yes','[[Juniper%GPASS%J2320 | http://www.juniper.net/products_and_services/j_series_services_routers/index.html]]');
INSERT INTO `Dictionary` VALUES (17,917,'yes','[[Juniper%GPASS%J2350 | http://www.juniper.net/products_and_services/j_series_services_routers/index.html]]');
INSERT INTO `Dictionary` VALUES (17,918,'yes','[[Juniper%GPASS%J4350 | http://www.juniper.net/products_and_services/j_series_services_routers/index.html]]');
INSERT INTO `Dictionary` VALUES (17,919,'yes','[[Juniper%GPASS%J6350 | http://www.juniper.net/products_and_services/j_series_services_routers/index.html]]');
INSERT INTO `Dictionary` VALUES (17,920,'yes','[[Juniper%GPASS%M7i | http://www.juniper.net/products_and_services/m_series_routing_portfolio/index.html]]');
INSERT INTO `Dictionary` VALUES (17,921,'yes','[[Juniper%GPASS%M10i | http://www.juniper.net/products_and_services/m_series_routing_portfolio/index.html]]');
INSERT INTO `Dictionary` VALUES (17,922,'yes','[[Juniper%GPASS%M40e | http://www.juniper.net/products_and_services/m_series_routing_portfolio/index.html]]');
INSERT INTO `Dictionary` VALUES (17,923,'yes','[[Juniper%GPASS%M120 | http://www.juniper.net/products_and_services/m_series_routing_portfolio/index.html]]');
INSERT INTO `Dictionary` VALUES (17,924,'yes','[[Juniper%GPASS%M320 | http://www.juniper.net/products_and_services/m_series_routing_portfolio/index.html]]');
INSERT INTO `Dictionary` VALUES (17,925,'yes','[[Juniper%GPASS%MX240 | http://www.juniper.net/products_and_services/mx_series/index.html]]');
INSERT INTO `Dictionary` VALUES (17,926,'yes','[[Juniper%GPASS%MX480 | http://www.juniper.net/products_and_services/mx_series/index.html]]');
INSERT INTO `Dictionary` VALUES (17,927,'yes','[[Juniper%GPASS%MX960 | http://www.juniper.net/products_and_services/mx_series/index.html]]');
INSERT INTO `Dictionary` VALUES (17,928,'yes','[[Juniper%GPASS%T320 | http://www.juniper.net/products_and_services/t_series_core_platforms/index.html]]');
INSERT INTO `Dictionary` VALUES (17,929,'yes','[[Juniper%GPASS%T640 | http://www.juniper.net/products_and_services/t_series_core_platforms/index.html]]');
INSERT INTO `Dictionary` VALUES (17,930,'yes','[[Juniper%GPASS%T1600 | http://www.juniper.net/products_and_services/t_series_core_platforms/index.html]]');
INSERT INTO `Dictionary` VALUES (17,931,'yes','[[Juniper%GPASS%TX Matrix | http://www.juniper.net/products_and_services/t_series_core_platforms/index.html]]');
INSERT INTO `Dictionary` VALUES (13,932,'yes','RH Fedora%GSKIP%Fedora 10');
INSERT INTO `Dictionary` VALUES (13,933,'yes','OpenSUSE%GSKIP%openSUSE 11.1');
INSERT INTO `Dictionary` VALUES (12,934,'yes','[[F5%GPASS%BIG-IP WebAccelerator 4500 | http://www.f5.com/pdf/products/big-ip-webaccelerator-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (30,935,'yes','F5%GPASS%VIPRION 2400%L2,2H%');
INSERT INTO `Dictionary` VALUES (12,936,'yes','[[F5%GPASS%BIG-IP 1500 | http://www.f5.com/pdf/products/big-ip-platforms-2007-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,937,'yes','[[F5%GPASS%BIG-IP 1600 | http://www.f5.com/pdf/products/big-ip-platforms-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,938,'yes','[[F5%GPASS%BIG-IP 3400 | http://www.f5.com/pdf/products/big-ip-platforms-2007-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,939,'yes','[[F5%GPASS%BIG-IP 3600 | http://www.f5.com/pdf/products/big-ip-platforms-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,940,'yes','[[F5%GPASS%BIG-IP 6400 | http://www.f5.com/pdf/products/big-ip-platforms-2007-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,941,'yes','[[F5%GPASS%BIG-IP 6800 | http://www.f5.com/pdf/products/big-ip-platforms-2007-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,942,'yes','[[F5%GPASS%BIG-IP 6900 | http://www.f5.com/pdf/products/big-ip-platforms-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,943,'yes','[[F5%GPASS%BIG-IP 8400 | http://www.f5.com/pdf/products/big-ip-platforms-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,944,'yes','[[F5%GPASS%BIG-IP 8800 | http://www.f5.com/pdf/products/big-ip-platforms-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,945,'yes','[[F5%GPASS%ARX 500 | http://www.f5.com/pdf/products/arx-series-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,946,'yes','[[F5%GPASS%ARX 1000 | http://www.f5.com/pdf/products/arx-series-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,947,'yes','[[F5%GPASS%ARX 4000 | http://www.f5.com/pdf/products/arx-series-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (12,948,'yes','[[F5%GPASS%ARX 6000 | http://www.f5.com/pdf/products/arx-series-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (17,949,'yes','[[F5%GPASS%WANJet 300 | http://www.f5.com/pdf/products/wanjet-hardware-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (17,950,'yes','[[F5%GPASS%WANJet 500 | http://www.f5.com/pdf/products/wanjet-hardware-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (24,951,'yes','[[F5%GPASS%FirePass 1200 | http://www.f5.com/pdf/products/firepass-hardware-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (24,952,'yes','[[F5%GPASS%FirePass 4100 | http://www.f5.com/pdf/products/firepass-hardware-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (24,953,'yes','[[F5%GPASS%FirePass 4300 | http://www.f5.com/pdf/products/firepass-hardware-ds.pdf]]');
INSERT INTO `Dictionary` VALUES (13,954,'yes','[[Debian%GSKIP%Debian 5.0 (lenny) | http://debian.org/releases/lenny/]]');
INSERT INTO `Dictionary` VALUES (11,955,'yes','SGI%GPASS%Altix XE270');
INSERT INTO `Dictionary` VALUES (11,956,'yes','SGI%GPASS%Altix XE340');
INSERT INTO `Dictionary` VALUES (11,957,'yes','SGI%GPASS%Altix XE500');
INSERT INTO `Dictionary` VALUES (12,958,'yes','[[Cisco%GPASS%Nexus 2148T | http://cisco.com/en/US/products/ps10118/index.html]]');
INSERT INTO `Dictionary` VALUES (12,959,'yes','[[Cisco%GPASS%Nexus 5010 | http://cisco.com/en/US/products/ps9711/index.html]]');
INSERT INTO `Dictionary` VALUES (12,960,'yes','[[Cisco%GPASS%Nexus 5020 | http://cisco.com/en/US/products/ps9710/index.html]]');
INSERT INTO `Dictionary` VALUES (12,961,'yes','[[Cisco%GPASS%Nexus 7010 | http://cisco.com/en/US/products/ps9512/index.html]]');
INSERT INTO `Dictionary` VALUES (12,962,'yes','[[Cisco%GPASS%Nexus 7018 | http://cisco.com/en/US/products/ps10098/index.html]]');
INSERT INTO `Dictionary` VALUES (14,963,'yes','Cisco NX-OS 4.0');
INSERT INTO `Dictionary` VALUES (14,964,'yes','Cisco NX-OS 4.1');
INSERT INTO `Dictionary` VALUES (1,965,'yes','Wireless');
INSERT INTO `Dictionary` VALUES (25,966,'yes','Cisco%GPASS%2106');
INSERT INTO `Dictionary` VALUES (25,967,'yes','Cisco%GPASS%2112');
INSERT INTO `Dictionary` VALUES (25,968,'yes','Cisco%GPASS%2125');
INSERT INTO `Dictionary` VALUES (25,969,'yes','Cisco%GPASS%4402');
INSERT INTO `Dictionary` VALUES (25,970,'yes','Cisco%GPASS%4404');
INSERT INTO `Dictionary` VALUES (25,971,'yes','[[Cisco%GPASS%Aironet 1140 | http://cisco.com/en/US/products/ps10092/index.html]]');
INSERT INTO `Dictionary` VALUES (25,972,'yes','[[Cisco%GPASS%Aironet 1200 | http://cisco.com/en/US/products/hw/wireless/ps430/ps4076/index.html]]');
INSERT INTO `Dictionary` VALUES (25,973,'yes','[[Cisco%GPASS%Aironet 1230 AG | http://cisco.com/en/US/products/ps6132/index.html]]');
INSERT INTO `Dictionary` VALUES (25,974,'yes','[[Cisco%GPASS%Aironet 1240 AG | http://cisco.com/en/US/products/ps6521/index.html]]');
INSERT INTO `Dictionary` VALUES (25,975,'yes','[[Cisco%GPASS%Aironet 1250 | http://cisco.com/en/US/products/ps8382/index.html]]');
INSERT INTO `Dictionary` VALUES (25,976,'yes','[[Cisco%GPASS%Aironet 1520 | http://cisco.com/en/US/products/ps8368/index.html]]');
INSERT INTO `Dictionary` VALUES (25,977,'yes','Foundry%GPASS%AP150');
INSERT INTO `Dictionary` VALUES (25,978,'yes','Foundry%GPASS%OAP180');
INSERT INTO `Dictionary` VALUES (25,979,'yes','Foundry%GPASS%AP201');
INSERT INTO `Dictionary` VALUES (25,980,'yes','Foundry%GPASS%AP208');
INSERT INTO `Dictionary` VALUES (25,981,'yes','Foundry%GPASS%AP250');
INSERT INTO `Dictionary` VALUES (25,982,'yes','Foundry%GPASS%AP300');
INSERT INTO `Dictionary` VALUES (25,983,'yes','Foundry%GPASS%RS4000');
INSERT INTO `Dictionary` VALUES (25,984,'yes','Foundry%GPASS%MC500');
INSERT INTO `Dictionary` VALUES (25,985,'yes','Foundry%GPASS%MC1000');
INSERT INTO `Dictionary` VALUES (25,986,'yes','Foundry%GPASS%MC3000');
INSERT INTO `Dictionary` VALUES (25,987,'yes','Foundry%GPASS%MC4100');
INSERT INTO `Dictionary` VALUES (25,988,'yes','Foundry%GPASS%MC5000');
INSERT INTO `Dictionary` VALUES (11,989,'yes','Dell PowerEdge%GPASS%R410');
INSERT INTO `Dictionary` VALUES (11,990,'yes','Dell PowerEdge%GPASS%R610');
INSERT INTO `Dictionary` VALUES (11,991,'yes','Dell PowerEdge%GPASS%R710');
INSERT INTO `Dictionary` VALUES (11,992,'yes','Dell PowerEdge%GPASS%R805');
INSERT INTO `Dictionary` VALUES (11,993,'yes','Dell PowerEdge%GPASS%R905');
INSERT INTO `Dictionary` VALUES (31,994,'yes','Dell PowerEdge%GPASS%M1000e%L2,8V%');
INSERT INTO `Dictionary` VALUES (18,995,'yes','Dell PowerVault%GPASS%MD1000');
INSERT INTO `Dictionary` VALUES (18,996,'yes','Dell PowerVault%GPASS%MD1120');
INSERT INTO `Dictionary` VALUES (18,997,'yes','Dell EqualLogic PS5000');
INSERT INTO `Dictionary` VALUES (18,998,'yes','Dell EqualLogic PS6000');
INSERT INTO `Dictionary` VALUES (18,999,'yes','EMC CLARiiON CX4-120 SPE');
INSERT INTO `Dictionary` VALUES (18,1000,'yes','EMC CLARiiON CX4-240 SPE');
INSERT INTO `Dictionary` VALUES (18,1001,'yes','EMC CLARiiON CX4-480 SPE');
INSERT INTO `Dictionary` VALUES (18,1002,'yes','EMC CLARiiON CX4-960 SPE');
INSERT INTO `Dictionary` VALUES (18,1003,'yes','EMC CLARiiON CX4 DAE');
INSERT INTO `Dictionary` VALUES (26,1004,'yes','Brocade%GPASS%300');
INSERT INTO `Dictionary` VALUES (26,1005,'yes','Brocade%GPASS%4900');
INSERT INTO `Dictionary` VALUES (26,1006,'yes','Brocade%GPASS%5000');
INSERT INTO `Dictionary` VALUES (26,1007,'yes','Brocade%GPASS%5100');
INSERT INTO `Dictionary` VALUES (26,1008,'yes','Brocade%GPASS%5300');
INSERT INTO `Dictionary` VALUES (26,1009,'yes','[[Cisco%GPASS%MDS 9124 | http://www.cisco.com/en/US/products/ps7079/index.html]]');
INSERT INTO `Dictionary` VALUES (26,1010,'yes','[[Cisco%GPASS%MDS 9134 | http://www.cisco.com/en/US/products/ps8414/index.html]]');
INSERT INTO `Dictionary` VALUES (26,1011,'yes','QLogic%GPASS%1400');
INSERT INTO `Dictionary` VALUES (26,1012,'yes','QLogic%GPASS%3800');
INSERT INTO `Dictionary` VALUES (26,1013,'yes','QLogic%GPASS%5600Q');
INSERT INTO `Dictionary` VALUES (26,1014,'yes','QLogic%GPASS%5800V');
INSERT INTO `Dictionary` VALUES (26,1015,'yes','QLogic%GPASS%9000');
INSERT INTO `Dictionary` VALUES (17,1016,'yes','[[Cisco%GPASS%ASR 9006 | http://cisco.com/en/US/products/ps10075/index.html]]');
INSERT INTO `Dictionary` VALUES (17,1017,'yes','[[Cisco%GPASS%ASR 9010 | http://cisco.com/en/US/products/ps10076/index.html]]');
INSERT INTO `Dictionary` VALUES (12,1018,'yes','[[Cisco%GPASS%Catalyst 4900M | http://www.cisco.com/en/US/products/ps9310/index.html]]');
INSERT INTO `Dictionary` VALUES (12,1019,'yes','[[Cisco%GPASS%Catalyst 4928-10GE | http://www.cisco.com/en/US/products/ps9903/index.html]]');
INSERT INTO `Dictionary` VALUES (12,1022,'yes','[[Brocade%GPASS%FastIron CX 624S | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-cx-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1023,'yes','[[Brocade%GPASS%FastIron CX 624S-HPOE | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-cx-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1024,'yes','[[Brocade%GPASS%FastIron CX 648S | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-cx-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1025,'yes','[[Brocade%GPASS%FastIron CX 648S-HPOE | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-cx-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1026,'yes','[[Brocade%GPASS%FastIron WS 624 | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1027,'yes','[[Brocade%GPASS%FastIron WS 624-POE | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1028,'yes','[[Brocade%GPASS%FastIron WS 624G | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1029,'yes','[[Brocade%GPASS%FastIron WS 624G-POE | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1030,'yes','[[Brocade%GPASS%FastIron WS 648 | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1031,'yes','[[Brocade%GPASS%FastIron WS 648-POE | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1032,'yes','[[Brocade%GPASS%FastIron WS 648G | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1033,'yes','[[Brocade%GPASS%FastIron WS 648G-POE | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fastiron-ws-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1034,'yes','[[Brocade%GPASS%NetIron CES 2024C | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/service-provider/product-details/netiron-ces-2000-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1035,'yes','[[Brocade%GPASS%NetIron CES 2024F | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/service-provider/product-details/netiron-ces-2000-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1036,'yes','[[Brocade%GPASS%NetIron CES 2048C | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/service-provider/product-details/netiron-ces-2000-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1037,'yes','[[Brocade%GPASS%NetIron CES 2048F | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/service-provider/product-details/netiron-ces-2000-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1038,'yes','[[Brocade%GPASS%NetIron CES 2048CX | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/service-provider/product-details/netiron-ces-2000-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1039,'yes','[[Brocade%GPASS%NetIron CES 2048FX | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/service-provider/product-details/netiron-ces-2000-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1040,'yes','[[Brocade%GPASS%ServerIron ADX 1000 | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/application-switching/product-details/serveriron-adx-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1041,'yes','[[Brocade%GPASS%ServerIron ADX 4000 | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/application-switching/product-details/serveriron-adx-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1042,'yes','[[Brocade%GPASS%ServerIron ADX 8000 | http://www.brocade.com/products-solutions/products/ethernet-switches-routers/application-switching/product-details/serveriron-adx-series/overview.page]]');
INSERT INTO `Dictionary` VALUES (12,1043,'yes','[[Brocade%GPASS%ServerIron 4G-SSL-FIPS | http://www.brocade.com/sites/dotcom/products-solutions/products/ethernet-switches-routers/application-switching/product-details/serveriron-4g-application-switches/index.page]]');
INSERT INTO `Dictionary` VALUES (12,1044,'yes','[[Brocade%GPASS%TurboIron 24X | http://www.brocade.com/sites/dotcom/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/turboiron-24x-switch/index.page]]');
INSERT INTO `Dictionary` VALUES (13,1045,'yes','RH Fedora%GSKIP%Fedora 11');
INSERT INTO `Dictionary` VALUES (13,1046,'yes','[[NetBSD%GSKIP%NetBSD 5.0 | http://netbsd.org/releases/formal-5/NetBSD-5.0.html]]');
INSERT INTO `Dictionary` VALUES (13,1047,'yes','[[OpenBSD%GSKIP%OpenBSD 4.5 | http://www.openbsd.org/45.html]]');
INSERT INTO `Dictionary` VALUES (13,1048,'yes','[[Open Solaris%GSKIP%OpenSolaris 2008.05 | http://opensolaris.org/os/project/indiana/resources/relnotes/200805/x86/]]');
INSERT INTO `Dictionary` VALUES (13,1049,'yes','[[Open Solaris%GSKIP%OpenSolaris 2008.11 | http://opensolaris.org/os/project/indiana/resources/relnotes/200811/x86/]]');
INSERT INTO `Dictionary` VALUES (13,1050,'yes','[[Open Solaris%GSKIP%OpenSolaris 2009.06 | http://opensolaris.org/os/project/indiana/resources/relnotes/200906/x86/]]');
INSERT INTO `Dictionary` VALUES (13,1051,'yes','[[Gentoo%GSKIP%Gentoo 2006.0 | http://www.gentoo.org/proj/en/releng/release/2006.0/2006.0.xml]]');
INSERT INTO `Dictionary` VALUES (13,1052,'yes','[[Gentoo%GSKIP%Gentoo 2007.0 | http://www.gentoo.org/proj/en/releng/release/2007.0/2007.0-press-release.txt]]');
INSERT INTO `Dictionary` VALUES (13,1053,'yes','[[Gentoo%GSKIP%Gentoo 2008.0 | http://www.gentoo.org/proj/en/releng/release/2008.0/index.xml]]');
INSERT INTO `Dictionary` VALUES (13,1054,'yes','Ubuntu%GSKIP%Ubuntu 9.04');
INSERT INTO `Dictionary` VALUES (1,1055,'yes','FC switch');
INSERT INTO `Dictionary` VALUES (12,1056,'yes','Cisco%GPASS%Catalyst CBS3030-DEL');
INSERT INTO `Dictionary` VALUES (13,1057,'yes','[[FreeBSD%GSKIP%FreeBSD 7.1 | http://www.freebsd.org/releases/7.1R/relnotes.html]]');
INSERT INTO `Dictionary` VALUES (13,1058,'yes','[[FreeBSD%GSKIP%FreeBSD 7.2 | http://www.freebsd.org/releases/7.2R/relnotes.html]]');
INSERT INTO `Dictionary` VALUES (11,1059,'yes','Dell PowerEdge%GPASS%R200');
INSERT INTO `Dictionary` VALUES (11,1060,'yes','Dell PowerEdge%GPASS%R300');
INSERT INTO `Dictionary` VALUES (12,1061,'yes','Dell PowerConnect%GPASS%2808');
INSERT INTO `Dictionary` VALUES (12,1062,'yes','Dell PowerConnect%GPASS%2816');
INSERT INTO `Dictionary` VALUES (12,1063,'yes','Dell PowerConnect%GPASS%2824');
INSERT INTO `Dictionary` VALUES (12,1064,'yes','Dell PowerConnect%GPASS%2848');
INSERT INTO `Dictionary` VALUES (12,1065,'yes','Dell PowerConnect%GPASS%3524');
INSERT INTO `Dictionary` VALUES (12,1066,'yes','Dell PowerConnect%GPASS%3524P');
INSERT INTO `Dictionary` VALUES (12,1067,'yes','Dell PowerConnect%GPASS%3548');
INSERT INTO `Dictionary` VALUES (12,1068,'yes','Dell PowerConnect%GPASS%3548P');
INSERT INTO `Dictionary` VALUES (12,1069,'yes','Dell PowerConnect%GPASS%5424');
INSERT INTO `Dictionary` VALUES (12,1070,'yes','Dell PowerConnect%GPASS%5448');
INSERT INTO `Dictionary` VALUES (26,1071,'yes','Brocade%GPASS%Silkworm 2400');
INSERT INTO `Dictionary` VALUES (26,1072,'yes','Brocade%GPASS%Silkworm 2800');
INSERT INTO `Dictionary` VALUES (26,1073,'yes','Brocade%GPASS%Silkworm 3200');
INSERT INTO `Dictionary` VALUES (26,1074,'yes','Brocade%GPASS%Silkworm 3800');
INSERT INTO `Dictionary` VALUES (26,1075,'yes','Brocade%GPASS%Silkworm 3900');
INSERT INTO `Dictionary` VALUES (26,1076,'yes','Brocade%GPASS%Silkworm 4100');
INSERT INTO `Dictionary` VALUES (2,1077,'yes','empty SFP-1000');
INSERT INTO `Dictionary` VALUES (2,1078,'yes','empty GBIC');
INSERT INTO `Dictionary` VALUES (2,1079,'yes','empty XENPAK');
INSERT INTO `Dictionary` VALUES (2,1080,'yes','empty X2');
INSERT INTO `Dictionary` VALUES (2,1081,'yes','empty XPAK');
INSERT INTO `Dictionary` VALUES (2,1082,'yes','empty XFP');
INSERT INTO `Dictionary` VALUES (2,1084,'yes','empty SFP+');
INSERT INTO `Dictionary` VALUES (12,1085,'yes','Nortel%GPASS%BES50GE-12T PWR');
INSERT INTO `Dictionary` VALUES (12,1086,'yes','HP ProCurve%GPASS%4000M');
INSERT INTO `Dictionary` VALUES (2,1087,'yes','1000Base-T (Dell 1855)');
INSERT INTO `Dictionary` VALUES (18,1088,'yes','[[NetApp%GPASS%FAS2020 | http://www.netapp.com/us/products/storage-systems/fas2000/]]');
INSERT INTO `Dictionary` VALUES (18,1089,'yes','[[NetApp%GPASS%FAS2050 | http://www.netapp.com/us/products/storage-systems/fas2000/]]');
INSERT INTO `Dictionary` VALUES (18,1090,'yes','NetApp%GPASS%FAS3020');
INSERT INTO `Dictionary` VALUES (18,1091,'yes','NetApp%GPASS%FAS3040');
INSERT INTO `Dictionary` VALUES (18,1092,'yes','NetApp%GPASS%FAS3050');
INSERT INTO `Dictionary` VALUES (18,1093,'yes','NetApp%GPASS%FAS3070');
INSERT INTO `Dictionary` VALUES (18,1094,'yes','[[NetApp%GPASS%FAS3140 | http://www.netapp.com/us/products/storage-systems/fas3100/]]');
INSERT INTO `Dictionary` VALUES (18,1095,'yes','[[NetApp%GPASS%FAS3160 | http://www.netapp.com/us/products/storage-systems/fas3100/]]');
INSERT INTO `Dictionary` VALUES (18,1096,'yes','[[NetApp%GPASS%FAS3170 | http://www.netapp.com/us/products/storage-systems/fas3100/]]');
INSERT INTO `Dictionary` VALUES (18,1097,'yes','NetApp%GPASS%FAS6030');
INSERT INTO `Dictionary` VALUES (18,1098,'yes','[[NetApp%GPASS%FAS6040 | http://www.netapp.com/us/products/storage-systems/fas6000/]]');
INSERT INTO `Dictionary` VALUES (18,1099,'yes','NetApp%GPASS%FAS6070');
INSERT INTO `Dictionary` VALUES (18,1100,'yes','[[NetApp%GPASS%FAS6080 | http://www.netapp.com/us/products/storage-systems/fas6000/]]');
INSERT INTO `Dictionary` VALUES (18,1101,'yes','[[NetApp%GPASS%V3140 | http://www.netapp.com/us/products/storage-systems/v3100/]]');
INSERT INTO `Dictionary` VALUES (18,1102,'yes','[[NetApp%GPASS%V3160 | http://www.netapp.com/us/products/storage-systems/v3100/]]');
INSERT INTO `Dictionary` VALUES (18,1103,'yes','[[NetApp%GPASS%V3170 | http://www.netapp.com/us/products/storage-systems/v3100/]]');
INSERT INTO `Dictionary` VALUES (18,1104,'yes','[[NetApp%GPASS%V6030 | http://www.netapp.com/us/products/storage-systems/v6000/]]');
INSERT INTO `Dictionary` VALUES (18,1105,'yes','[[NetApp%GPASS%V6040 | http://www.netapp.com/us/products/storage-systems/v6000/]]');
INSERT INTO `Dictionary` VALUES (18,1106,'yes','[[NetApp%GPASS%V6070 | http://www.netapp.com/us/products/storage-systems/v6000/]]');
INSERT INTO `Dictionary` VALUES (18,1107,'yes','[[NetApp%GPASS%V6080 | http://www.netapp.com/us/products/storage-systems/v6000/]]');
INSERT INTO `Dictionary` VALUES (18,1108,'yes','NetApp%GPASS%DS14mk2 AT');
INSERT INTO `Dictionary` VALUES (18,1109,'yes','NetApp%GPASS%DS14mk2 FC');
INSERT INTO `Dictionary` VALUES (18,1110,'yes','NetApp%GPASS%DS14mk4 FC');
INSERT INTO `Dictionary` VALUES (27,1111,'yes','[[APC%GPASS%AP7152 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7152]]');
INSERT INTO `Dictionary` VALUES (27,1112,'yes','[[APC%GPASS%AP7155 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7155]]');
INSERT INTO `Dictionary` VALUES (27,1113,'yes','[[APC%GPASS%AP7175 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7175]]');
INSERT INTO `Dictionary` VALUES (27,1114,'yes','[[APC%GPASS%AP7526 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7526]]');
INSERT INTO `Dictionary` VALUES (27,1115,'yes','[[APC%GPASS%AP7551 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7551]]');
INSERT INTO `Dictionary` VALUES (27,1116,'yes','[[APC%GPASS%AP7552 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7552]]');
INSERT INTO `Dictionary` VALUES (27,1117,'yes','[[APC%GPASS%AP7553 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7553]]');
INSERT INTO `Dictionary` VALUES (27,1118,'yes','[[APC%GPASS%AP7554 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7554]]');
INSERT INTO `Dictionary` VALUES (27,1119,'yes','[[APC%GPASS%AP7555 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7555]]');
INSERT INTO `Dictionary` VALUES (27,1120,'yes','[[APC%GPASS%AP7557 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7557]]');
INSERT INTO `Dictionary` VALUES (27,1121,'yes','[[APC%GPASS%AP7585 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7585]]');
INSERT INTO `Dictionary` VALUES (27,1122,'yes','[[APC%GPASS%AP7586 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7586]]');
INSERT INTO `Dictionary` VALUES (27,1123,'yes','[[APC%GPASS%AP7611 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7611]]');
INSERT INTO `Dictionary` VALUES (27,1124,'yes','[[APC%GPASS%AP7631 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7631]]');
INSERT INTO `Dictionary` VALUES (27,1125,'yes','[[APC%GPASS%AP7820 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7820]]');
INSERT INTO `Dictionary` VALUES (27,1126,'yes','[[APC%GPASS%AP7821 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7821]]');
INSERT INTO `Dictionary` VALUES (27,1127,'yes','[[APC%GPASS%AP7822 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7822]]');
INSERT INTO `Dictionary` VALUES (27,1128,'yes','[[APC%GPASS%AP7850 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7850]]');
INSERT INTO `Dictionary` VALUES (27,1129,'yes','[[APC%GPASS%AP7851 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7851]]');
INSERT INTO `Dictionary` VALUES (27,1130,'yes','[[APC%GPASS%AP7852 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7852]]');
INSERT INTO `Dictionary` VALUES (27,1131,'yes','[[APC%GPASS%AP7853 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7853]]');
INSERT INTO `Dictionary` VALUES (27,1132,'yes','[[APC%GPASS%AP7854 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7854]]');
INSERT INTO `Dictionary` VALUES (27,1133,'yes','[[APC%GPASS%AP7855A | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7855A]]');
INSERT INTO `Dictionary` VALUES (27,1134,'yes','[[APC%GPASS%AP7856 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7856]]');
INSERT INTO `Dictionary` VALUES (27,1135,'yes','[[APC%GPASS%AP7856A | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7856A]]');
INSERT INTO `Dictionary` VALUES (27,1136,'yes','[[APC%GPASS%AP7857 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7857]]');
INSERT INTO `Dictionary` VALUES (27,1137,'yes','[[APC%GPASS%AP7920 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7920]]');
INSERT INTO `Dictionary` VALUES (27,1138,'yes','[[APC%GPASS%AP7921 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7921]]');
INSERT INTO `Dictionary` VALUES (27,1139,'yes','[[APC%GPASS%AP7922 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7922]]');
INSERT INTO `Dictionary` VALUES (27,1140,'yes','[[APC%GPASS%AP7950 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7950]]');
INSERT INTO `Dictionary` VALUES (27,1141,'yes','[[APC%GPASS%AP7951 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7951]]');
INSERT INTO `Dictionary` VALUES (27,1142,'yes','[[APC%GPASS%AP7952 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7952]]');
INSERT INTO `Dictionary` VALUES (27,1143,'yes','[[APC%GPASS%AP7953 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7953]]');
INSERT INTO `Dictionary` VALUES (27,1144,'yes','[[APC%GPASS%AP7954 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7954]]');
INSERT INTO `Dictionary` VALUES (27,1145,'yes','[[APC%GPASS%AP7957 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7957]]');
INSERT INTO `Dictionary` VALUES (27,1146,'yes','[[APC%GPASS%AP9559 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP9559]]');
INSERT INTO `Dictionary` VALUES (27,1147,'yes','[[APC%GPASS%AP9565 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP9565]]');
INSERT INTO `Dictionary` VALUES (27,1148,'yes','[[APC%GPASS%AP9568 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP9568]]');
INSERT INTO `Dictionary` VALUES (27,1149,'yes','[[APC%GPASS%AP9572 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP9572]]');
INSERT INTO `Dictionary` VALUES (30,1150,'yes','[[Cisco%GPASS%Catalyst 6509-V-E%L1,9V% | http://www.cisco.com/en/US/products/ps9306/index.html]]');
INSERT INTO `Dictionary` VALUES (27,1151,'yes','[[APC%GPASS%AP7902J | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7902J]]');
INSERT INTO `Dictionary` VALUES (27,1152,'yes','[[APC%GPASS%AP7930J | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7930J]]');
INSERT INTO `Dictionary` VALUES (27,1153,'yes','[[APC%GPASS%AP7932J | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7932J]]');
INSERT INTO `Dictionary` VALUES (27,1154,'yes','[[APC%GPASS%AP7900 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7900]]');
INSERT INTO `Dictionary` VALUES (27,1155,'yes','[[APC%GPASS%AP7901 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7901]]');
INSERT INTO `Dictionary` VALUES (27,1156,'yes','[[APC%GPASS%AP7902 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7902]]');
INSERT INTO `Dictionary` VALUES (27,1157,'yes','[[APC%GPASS%AP7930 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7930]]');
INSERT INTO `Dictionary` VALUES (27,1158,'yes','[[APC%GPASS%AP7931 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7931]]');
INSERT INTO `Dictionary` VALUES (27,1159,'yes','[[APC%GPASS%AP7932 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7932]]');
INSERT INTO `Dictionary` VALUES (27,1160,'yes','[[APC%GPASS%AP7911 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7911]]');
INSERT INTO `Dictionary` VALUES (27,1161,'yes','[[APC%GPASS%AP7940 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7940]]');
INSERT INTO `Dictionary` VALUES (27,1162,'yes','[[APC%GPASS%AP7941 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7941]]');
INSERT INTO `Dictionary` VALUES (27,1163,'yes','[[APC%GPASS%AP7960 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7960]]');
INSERT INTO `Dictionary` VALUES (27,1164,'yes','[[APC%GPASS%AP7961 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7961]]');
INSERT INTO `Dictionary` VALUES (27,1165,'yes','[[APC%GPASS%AP7968 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7968]]');
INSERT INTO `Dictionary` VALUES (27,1166,'yes','[[APC%GPASS%AP7990 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7990]]');
INSERT INTO `Dictionary` VALUES (27,1167,'yes','[[APC%GPASS%AP7991 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7991]]');
INSERT INTO `Dictionary` VALUES (27,1168,'yes','[[APC%GPASS%AP7998 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP7998]]');
INSERT INTO `Dictionary` VALUES (12,1169,'yes','Hitachi Cable%GPASS%Apresia3108FG2');
INSERT INTO `Dictionary` VALUES (12,1170,'yes','Hitachi Cable%GPASS%Apresia3124GT-HR2');
INSERT INTO `Dictionary` VALUES (12,1171,'yes','Hitachi Cable%GPASS%Apresia4348GT');
INSERT INTO `Dictionary` VALUES (12,1172,'yes','Hitachi Cable%GPASS%Apresia4348GT-PSR');
INSERT INTO `Dictionary` VALUES (12,1173,'yes','Hitachi Cable%GPASS%Apresia4328GT');
INSERT INTO `Dictionary` VALUES (12,1174,'yes','Hitachi Cable%GPASS%Apresia4224GT-PSR');
INSERT INTO `Dictionary` VALUES (12,1175,'yes','Hitachi Cable%GPASS%Apresia2248G2');
INSERT INTO `Dictionary` VALUES (12,1176,'yes','Hitachi Cable%GPASS%Apresia2124GT2');
INSERT INTO `Dictionary` VALUES (12,1177,'yes','Hitachi Cable%GPASS%Apresia2124GT-SS2');
INSERT INTO `Dictionary` VALUES (12,1178,'yes','Hitachi Cable%GPASS%Apresia2124-SS2');
INSERT INTO `Dictionary` VALUES (12,1179,'yes','Hitachi Cable%GPASS%Apresia13000-24GX-PSR');
INSERT INTO `Dictionary` VALUES (12,1180,'yes','Hitachi Cable%GPASS%Apresia13000-48X');
INSERT INTO `Dictionary` VALUES (12,1181,'yes','Hitachi Cable%GPASS%Apresia3424GT-SS');
INSERT INTO `Dictionary` VALUES (12,1182,'yes','Hitachi Cable%GPASS%Apresia3424GT-PoE');
INSERT INTO `Dictionary` VALUES (12,1183,'yes','Hitachi Cable%GPASS%Apresia3248G-PSR2');
INSERT INTO `Dictionary` VALUES (12,1184,'yes','Hitachi Cable%GPASS%Apresia3248G2');
INSERT INTO `Dictionary` VALUES (12,1185,'yes','Hitachi Cable%GPASS%Apresia3124GT-PSR2');
INSERT INTO `Dictionary` VALUES (12,1186,'yes','Hitachi Cable%GPASS%Apresia3124GT2');
INSERT INTO `Dictionary` VALUES (12,1187,'yes','Hitachi Cable%GPASS%Apresia18020');
INSERT INTO `Dictionary` VALUES (12,1188,'yes','Hitachi Cable%GPASS%Apresia18008');
INSERT INTO `Dictionary` VALUES (12,1189,'yes','Hitachi Cable%GPASS%Apresia18005');
INSERT INTO `Dictionary` VALUES (12,1190,'yes','Hitachi Cable%GPASS%Apresia8007');
INSERT INTO `Dictionary` VALUES (12,1191,'yes','Hitachi Cable%GPASS%Apresia8004');
INSERT INTO `Dictionary` VALUES (12,1192,'yes','Hitachi Cable%GPASS%Apresia6148G-PSR');
INSERT INTO `Dictionary` VALUES (12,1193,'yes','Hitachi Cable%GPASS%Apresia6148GT-PSR');
INSERT INTO `Dictionary` VALUES (12,1194,'yes','Hitachi Cable%GPASS%VXC-1024FE');
INSERT INTO `Dictionary` VALUES (2,1195,'yes','100Base-FX');
INSERT INTO `Dictionary` VALUES (2,1196,'yes','100Base-SX');
INSERT INTO `Dictionary` VALUES (2,1197,'yes','100Base-LX10');
INSERT INTO `Dictionary` VALUES (2,1198,'yes','100Base-BX10-D');
INSERT INTO `Dictionary` VALUES (2,1199,'yes','100Base-BX10-U');
INSERT INTO `Dictionary` VALUES (2,1200,'yes','100Base-EX');
INSERT INTO `Dictionary` VALUES (2,1201,'yes','100Base-ZX');
INSERT INTO `Dictionary` VALUES (2,1202,'yes','1000Base-SX');
INSERT INTO `Dictionary` VALUES (2,1203,'yes','1000Base-SX+');
INSERT INTO `Dictionary` VALUES (2,1204,'yes','1000Base-LX');
INSERT INTO `Dictionary` VALUES (2,1205,'yes','1000Base-LX/LH');
INSERT INTO `Dictionary` VALUES (2,1206,'yes','1000Base-BX10-D');
INSERT INTO `Dictionary` VALUES (2,1207,'yes','1000Base-BX10-U');
INSERT INTO `Dictionary` VALUES (2,1208,'yes','empty SFP-100');
INSERT INTO `Dictionary` VALUES (2,1209,'yes','1000Base-CWDM80-1470 (gray)');
INSERT INTO `Dictionary` VALUES (2,1210,'yes','1000Base-CWDM80-1490 (violet)');
INSERT INTO `Dictionary` VALUES (2,1211,'yes','1000Base-CWDM80-1510 (blue)');
INSERT INTO `Dictionary` VALUES (2,1212,'yes','1000Base-CWDM80-1530 (green)');
INSERT INTO `Dictionary` VALUES (2,1213,'yes','1000Base-CWDM80-1550 (yellow)');
INSERT INTO `Dictionary` VALUES (2,1214,'yes','1000Base-CWDM80-1570 (orange)');
INSERT INTO `Dictionary` VALUES (2,1215,'yes','1000Base-CWDM80-1590 (red)');
INSERT INTO `Dictionary` VALUES (2,1216,'yes','1000Base-CWDM80-1610 (brown)');
INSERT INTO `Dictionary` VALUES (2,1217,'yes','1000Base-DWDM80-61.42 (ITU 20)');
INSERT INTO `Dictionary` VALUES (2,1218,'yes','1000Base-DWDM80-60.61 (ITU 21)');
INSERT INTO `Dictionary` VALUES (2,1219,'yes','1000Base-DWDM80-59.79 (ITU 22)');
INSERT INTO `Dictionary` VALUES (2,1220,'yes','1000Base-DWDM80-58.98 (ITU 23)');
INSERT INTO `Dictionary` VALUES (2,1221,'yes','1000Base-DWDM80-58.17 (ITU 24)');
INSERT INTO `Dictionary` VALUES (2,1222,'yes','1000Base-DWDM80-57.36 (ITU 25)');
INSERT INTO `Dictionary` VALUES (2,1223,'yes','1000Base-DWDM80-56.55 (ITU 26)');
INSERT INTO `Dictionary` VALUES (2,1224,'yes','1000Base-DWDM80-55.75 (ITU 27)');
INSERT INTO `Dictionary` VALUES (2,1225,'yes','1000Base-DWDM80-54.94 (ITU 28)');
INSERT INTO `Dictionary` VALUES (2,1226,'yes','1000Base-DWDM80-54.13 (ITU 29)');
INSERT INTO `Dictionary` VALUES (2,1227,'yes','1000Base-DWDM80-53.33 (ITU 30)');
INSERT INTO `Dictionary` VALUES (2,1228,'yes','1000Base-DWDM80-52.52 (ITU 31)');
INSERT INTO `Dictionary` VALUES (2,1229,'yes','1000Base-DWDM80-51.72 (ITU 32)');
INSERT INTO `Dictionary` VALUES (2,1230,'yes','1000Base-DWDM80-50.92 (ITU 33)');
INSERT INTO `Dictionary` VALUES (2,1231,'yes','1000Base-DWDM80-50.12 (ITU 34)');
INSERT INTO `Dictionary` VALUES (2,1232,'yes','1000Base-DWDM80-49.32 (ITU 35)');
INSERT INTO `Dictionary` VALUES (2,1233,'yes','1000Base-DWDM80-48.51 (ITU 36)');
INSERT INTO `Dictionary` VALUES (2,1234,'yes','1000Base-DWDM80-47.72 (ITU 37)');
INSERT INTO `Dictionary` VALUES (2,1235,'yes','1000Base-DWDM80-46.92 (ITU 38)');
INSERT INTO `Dictionary` VALUES (2,1236,'yes','1000Base-DWDM80-46.12 (ITU 39)');
INSERT INTO `Dictionary` VALUES (2,1237,'yes','1000Base-DWDM80-45.32 (ITU 40)');
INSERT INTO `Dictionary` VALUES (2,1238,'yes','1000Base-DWDM80-44.53 (ITU 41)');
INSERT INTO `Dictionary` VALUES (2,1239,'yes','1000Base-DWDM80-43.73 (ITU 42)');
INSERT INTO `Dictionary` VALUES (2,1240,'yes','1000Base-DWDM80-42.94 (ITU 43)');
INSERT INTO `Dictionary` VALUES (2,1241,'yes','1000Base-DWDM80-42.14 (ITU 44)');
INSERT INTO `Dictionary` VALUES (2,1242,'yes','1000Base-DWDM80-41.35 (ITU 45)');
INSERT INTO `Dictionary` VALUES (2,1243,'yes','1000Base-DWDM80-40.56 (ITU 46)');
INSERT INTO `Dictionary` VALUES (2,1244,'yes','1000Base-DWDM80-39.77 (ITU 47)');
INSERT INTO `Dictionary` VALUES (2,1245,'yes','1000Base-DWDM80-38.98 (ITU 48)');
INSERT INTO `Dictionary` VALUES (2,1246,'yes','1000Base-DWDM80-38.19 (ITU 49)');
INSERT INTO `Dictionary` VALUES (2,1247,'yes','1000Base-DWDM80-37.40 (ITU 50)');
INSERT INTO `Dictionary` VALUES (2,1248,'yes','1000Base-DWDM80-36.61 (ITU 51)');
INSERT INTO `Dictionary` VALUES (2,1249,'yes','1000Base-DWDM80-35.82 (ITU 52)');
INSERT INTO `Dictionary` VALUES (2,1250,'yes','1000Base-DWDM80-35.04 (ITU 53)');
INSERT INTO `Dictionary` VALUES (2,1251,'yes','1000Base-DWDM80-34.25 (ITU 54)');
INSERT INTO `Dictionary` VALUES (2,1252,'yes','1000Base-DWDM80-33.47 (ITU 55)');
INSERT INTO `Dictionary` VALUES (2,1253,'yes','1000Base-DWDM80-32.68 (ITU 56)');
INSERT INTO `Dictionary` VALUES (2,1254,'yes','1000Base-DWDM80-31.90 (ITU 57)');
INSERT INTO `Dictionary` VALUES (2,1255,'yes','1000Base-DWDM80-31.12 (ITU 58)');
INSERT INTO `Dictionary` VALUES (2,1256,'yes','1000Base-DWDM80-30.33 (ITU 59)');
INSERT INTO `Dictionary` VALUES (2,1257,'yes','1000Base-DWDM80-29.55 (ITU 60)');
INSERT INTO `Dictionary` VALUES (2,1258,'yes','1000Base-DWDM80-28.77 (ITU 61)');
INSERT INTO `Dictionary` VALUES (2,1259,'yes','10GBase-ZR-DWDM80-61.42 (ITU 20)');
INSERT INTO `Dictionary` VALUES (2,1260,'yes','10GBase-ZR-DWDM80-60.61 (ITU 21)');
INSERT INTO `Dictionary` VALUES (2,1261,'yes','10GBase-ZR-DWDM80-59.79 (ITU 22)');
INSERT INTO `Dictionary` VALUES (2,1262,'yes','10GBase-ZR-DWDM80-58.98 (ITU 23)');
INSERT INTO `Dictionary` VALUES (2,1263,'yes','10GBase-ZR-DWDM80-58.17 (ITU 24)');
INSERT INTO `Dictionary` VALUES (2,1264,'yes','10GBase-ZR-DWDM80-57.36 (ITU 25)');
INSERT INTO `Dictionary` VALUES (2,1265,'yes','10GBase-ZR-DWDM80-56.55 (ITU 26)');
INSERT INTO `Dictionary` VALUES (2,1266,'yes','10GBase-ZR-DWDM80-55.75 (ITU 27)');
INSERT INTO `Dictionary` VALUES (2,1267,'yes','10GBase-ZR-DWDM80-54.94 (ITU 28)');
INSERT INTO `Dictionary` VALUES (2,1268,'yes','10GBase-ZR-DWDM80-54.13 (ITU 29)');
INSERT INTO `Dictionary` VALUES (2,1269,'yes','10GBase-ZR-DWDM80-53.33 (ITU 30)');
INSERT INTO `Dictionary` VALUES (2,1270,'yes','10GBase-ZR-DWDM80-52.52 (ITU 31)');
INSERT INTO `Dictionary` VALUES (2,1271,'yes','10GBase-ZR-DWDM80-51.72 (ITU 32)');
INSERT INTO `Dictionary` VALUES (2,1272,'yes','10GBase-ZR-DWDM80-50.92 (ITU 33)');
INSERT INTO `Dictionary` VALUES (2,1273,'yes','10GBase-ZR-DWDM80-50.12 (ITU 34)');
INSERT INTO `Dictionary` VALUES (2,1274,'yes','10GBase-ZR-DWDM80-49.32 (ITU 35)');
INSERT INTO `Dictionary` VALUES (2,1275,'yes','10GBase-ZR-DWDM80-48.51 (ITU 36)');
INSERT INTO `Dictionary` VALUES (2,1276,'yes','10GBase-ZR-DWDM80-47.72 (ITU 37)');
INSERT INTO `Dictionary` VALUES (2,1277,'yes','10GBase-ZR-DWDM80-46.92 (ITU 38)');
INSERT INTO `Dictionary` VALUES (2,1278,'yes','10GBase-ZR-DWDM80-46.12 (ITU 39)');
INSERT INTO `Dictionary` VALUES (2,1279,'yes','10GBase-ZR-DWDM80-45.32 (ITU 40)');
INSERT INTO `Dictionary` VALUES (2,1280,'yes','10GBase-ZR-DWDM80-44.53 (ITU 41)');
INSERT INTO `Dictionary` VALUES (2,1281,'yes','10GBase-ZR-DWDM80-43.73 (ITU 42)');
INSERT INTO `Dictionary` VALUES (2,1282,'yes','10GBase-ZR-DWDM80-42.94 (ITU 43)');
INSERT INTO `Dictionary` VALUES (2,1283,'yes','10GBase-ZR-DWDM80-42.14 (ITU 44)');
INSERT INTO `Dictionary` VALUES (2,1284,'yes','10GBase-ZR-DWDM80-41.35 (ITU 45)');
INSERT INTO `Dictionary` VALUES (2,1285,'yes','10GBase-ZR-DWDM80-40.56 (ITU 46)');
INSERT INTO `Dictionary` VALUES (2,1286,'yes','10GBase-ZR-DWDM80-39.77 (ITU 47)');
INSERT INTO `Dictionary` VALUES (2,1287,'yes','10GBase-ZR-DWDM80-38.98 (ITU 48)');
INSERT INTO `Dictionary` VALUES (2,1288,'yes','10GBase-ZR-DWDM80-38.19 (ITU 49)');
INSERT INTO `Dictionary` VALUES (2,1289,'yes','10GBase-ZR-DWDM80-37.40 (ITU 50)');
INSERT INTO `Dictionary` VALUES (2,1290,'yes','10GBase-ZR-DWDM80-36.61 (ITU 51)');
INSERT INTO `Dictionary` VALUES (2,1291,'yes','10GBase-ZR-DWDM80-35.82 (ITU 52)');
INSERT INTO `Dictionary` VALUES (2,1292,'yes','10GBase-ZR-DWDM80-35.04 (ITU 53)');
INSERT INTO `Dictionary` VALUES (2,1293,'yes','10GBase-ZR-DWDM80-34.25 (ITU 54)');
INSERT INTO `Dictionary` VALUES (2,1294,'yes','10GBase-ZR-DWDM80-33.47 (ITU 55)');
INSERT INTO `Dictionary` VALUES (2,1295,'yes','10GBase-ZR-DWDM80-32.68 (ITU 56)');
INSERT INTO `Dictionary` VALUES (2,1296,'yes','10GBase-ZR-DWDM80-31.90 (ITU 57)');
INSERT INTO `Dictionary` VALUES (2,1297,'yes','10GBase-ZR-DWDM80-31.12 (ITU 58)');
INSERT INTO `Dictionary` VALUES (2,1298,'yes','10GBase-ZR-DWDM80-30.33 (ITU 59)');
INSERT INTO `Dictionary` VALUES (2,1299,'yes','10GBase-ZR-DWDM80-29.55 (ITU 60)');
INSERT INTO `Dictionary` VALUES (2,1300,'yes','10GBase-ZR-DWDM80-28.77 (ITU 61)');
INSERT INTO `Dictionary` VALUES (25,1301,'yes','Cisco%GPASS%AIR-BR1410A');
INSERT INTO `Dictionary` VALUES (25,1302,'yes','Cisco%GPASS%AIR-BR1310G');
INSERT INTO `Dictionary` VALUES (25,1303,'yes','Cisco%GPASS%AIR-AP1252AG');
INSERT INTO `Dictionary` VALUES (25,1304,'yes','Cisco%GPASS%AIR-AP1252G');
INSERT INTO `Dictionary` VALUES (25,1305,'yes','Cisco%GPASS%AIR-AP1242AG');
INSERT INTO `Dictionary` VALUES (25,1306,'yes','Cisco%GPASS%AIR-AP1242G');
INSERT INTO `Dictionary` VALUES (25,1307,'yes','Cisco%GPASS%AIR-AP1231G');
INSERT INTO `Dictionary` VALUES (25,1308,'yes','Cisco%GPASS%AIR-AP1232AG');
INSERT INTO `Dictionary` VALUES (25,1309,'yes','Cisco%GPASS%AIR-AP1131AG');
INSERT INTO `Dictionary` VALUES (25,1310,'yes','Cisco%GPASS%AIR-AP1131G');
INSERT INTO `Dictionary` VALUES (25,1311,'yes','Cisco%GPASS%AIR-AP1121G');
INSERT INTO `Dictionary` VALUES (25,1312,'yes','Cisco%GPASS%AIR-AP521G');
INSERT INTO `Dictionary` VALUES (25,1313,'yes','Cisco%GPASS%AIR-WLC2106');
INSERT INTO `Dictionary` VALUES (25,1314,'yes','Cisco%GPASS%AIR-WLC526');
INSERT INTO `Dictionary` VALUES (25,1315,'yes','Cisco%GPASS%AIR-WLC4402');
INSERT INTO `Dictionary` VALUES (2,1316,'yes','1000Base-T (Dell M1000e)');
INSERT INTO `Dictionary` VALUES (13,1317,'yes','SUSE Enterprise%GSKIP%SLES11');
INSERT INTO `Dictionary` VALUES (13,1318,'yes','MicroSoft%GSKIP%Windows Server 2008');
INSERT INTO `Dictionary` VALUES (13,1319,'yes','SlackWare%GSKIP%Slackware 13.0');
INSERT INTO `Dictionary` VALUES (13,1320,'yes','Ubuntu%GSKIP%Ubuntu 9.10');
INSERT INTO `Dictionary` VALUES (12,1321,'yes','Huawei%GPASS%S5328C-EI-24S');
INSERT INTO `Dictionary` VALUES (2,1322,'yes','AC-out');
INSERT INTO `Dictionary` VALUES (1,1323,'yes','Voice/video');
INSERT INTO `Dictionary` VALUES (28,1324,'yes','Cisco%GPASS%MCS 7816');
INSERT INTO `Dictionary` VALUES (28,1325,'yes','Cisco%GPASS%MCS 7825');
INSERT INTO `Dictionary` VALUES (28,1326,'yes','Cisco%GPASS%MCS 7835');
INSERT INTO `Dictionary` VALUES (17,1327,'yes','[[ Cisco%GPASS%2901 | http://www.cisco.com/en/US/products/ps10539/index.html]]');
INSERT INTO `Dictionary` VALUES (17,1328,'yes','[[ Cisco%GPASS%2911 | http://www.cisco.com/en/US/products/ps10540/index.html]]');
INSERT INTO `Dictionary` VALUES (17,1329,'yes','[[ Cisco%GPASS%2921 | http://www.cisco.com/en/US/products/ps10543/index.html]]');
INSERT INTO `Dictionary` VALUES (17,1330,'yes','[[ Cisco%GPASS%2951 | http://www.cisco.com/en/US/products/ps10544/index.html]]');
INSERT INTO `Dictionary` VALUES (13,1331,'yes','ALT_Linux%GSKIP%ALTLinux 5');
INSERT INTO `Dictionary` VALUES (13,1332,'yes','[[RH Fedora%GSKIP%Fedora 12 | http://docs.fedoraproject.org/release-notes/f12/en-US/html/]]');
INSERT INTO `Dictionary` VALUES (13,1333,'yes','Gentoo%GSKIP%Gentoo 10.0');
INSERT INTO `Dictionary` VALUES (13,1334,'yes','Gentoo%GSKIP%Gentoo 10.1');
INSERT INTO `Dictionary` VALUES (12,1335,'yes','Huawei%GPASS%S5328C-EI');
INSERT INTO `Dictionary` VALUES (12,1336,'yes','Huawei%GPASS%S5328C-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1337,'yes','Huawei%GPASS%S5352C-EI');
INSERT INTO `Dictionary` VALUES (12,1338,'yes','Huawei%GPASS%S5352C-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1339,'yes','Huawei%GPASS%S5328C-SI');
INSERT INTO `Dictionary` VALUES (12,1340,'yes','Huawei%GPASS%S5328C-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1341,'yes','Huawei%GPASS%S5352C-SI');
INSERT INTO `Dictionary` VALUES (12,1342,'yes','Huawei%GPASS%S5352C-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1343,'yes','Huawei%GPASS%S5324TP-SI');
INSERT INTO `Dictionary` VALUES (12,1344,'yes','Huawei%GPASS%S5324TP-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1345,'yes','Huawei%GPASS%S5348TP-SI');
INSERT INTO `Dictionary` VALUES (12,1346,'yes','Huawei%GPASS%S5348TP-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1347,'yes','Cisco%GPASS%Catalyst 2960-24PC-L');
INSERT INTO `Dictionary` VALUES (12,1348,'yes','Cisco%GPASS%Catalyst 2350-48TD');
INSERT INTO `Dictionary` VALUES (12,1349,'yes','HP ProCurve%GPASS%E2910-48G J9147A');
INSERT INTO `Dictionary` VALUES (14,1350,'yes','ExtremeXOS 10');
INSERT INTO `Dictionary` VALUES (14,1351,'yes','ExtremeXOS 11');
INSERT INTO `Dictionary` VALUES (14,1352,'yes','ExtremeXOS 12');
INSERT INTO `Dictionary` VALUES (12,1353,'yes','[[Extreme Networks%GPASS%Summit X480-24x | http://extremenetworks.com/products/summit-X480.aspx]]');
INSERT INTO `Dictionary` VALUES (12,1354,'yes','[[Extreme Networks%GPASS%Summit X480-48t | http://extremenetworks.com/products/summit-X480.aspx]]');
INSERT INTO `Dictionary` VALUES (12,1355,'yes','[[Extreme Networks%GPASS%Summit X480-48x | http://extremenetworks.com/products/summit-X480.aspx]]');
INSERT INTO `Dictionary` VALUES (12,1356,'yes','[[Extreme Networks%GPASS%Summit X650 | http://extremenetworks.com/products/summit-x650.aspx]]');
INSERT INTO `Dictionary` VALUES (12,1357,'yes','Huawei%GPASS%S9303');
INSERT INTO `Dictionary` VALUES (12,1358,'yes','Huawei%GPASS%S9306');
INSERT INTO `Dictionary` VALUES (12,1359,'yes','Huawei%GPASS%S9312');
INSERT INTO `Dictionary` VALUES (14,1360,'yes','Huawei VRP 5.3');
INSERT INTO `Dictionary` VALUES (14,1361,'yes','Huawei VRP 5.5');
INSERT INTO `Dictionary` VALUES (12,1362,'yes','[[Brocade%GPASS%FCX 648 | http://www.brocade.com/sites/dotcom/products-solutions/products/ethernet-switches-routers/enterprise-mobility/product-details/fcx-series-data-center/index.page ]]');
INSERT INTO `Dictionary` VALUES (14,1363,'yes','IronWare 5');
INSERT INTO `Dictionary` VALUES (14,1364,'yes','IronWare 7');
INSERT INTO `Dictionary` VALUES (14,1365,'yes','Cisco NX-OS 4.2');
INSERT INTO `Dictionary` VALUES (14,1366,'yes','JunOS 9');
INSERT INTO `Dictionary` VALUES (14,1367,'yes','JunOS 10');
INSERT INTO `Dictionary` VALUES (17,1368,'yes','[[Juniper%GPASS%MX80 | http://www.juniper.net/products_and_services/mx_series/index.html]]');
INSERT INTO `Dictionary` VALUES (14,1369,'yes','Huawei VRP 5.7');
INSERT INTO `Dictionary` VALUES (12,1370,'yes','Cisco%GPASS%Catalyst 2960-48PST-L');
INSERT INTO `Dictionary` VALUES (12,1371,'yes','SMC%GPASS%8024L2');
INSERT INTO `Dictionary` VALUES (12,1372,'yes','SMC%GPASS%8124PL2');
INSERT INTO `Dictionary` VALUES (12,1373,'yes','SMC%GPASS%8126L2');
INSERT INTO `Dictionary` VALUES (12,1374,'yes','SMC%GPASS%8150L2');
INSERT INTO `Dictionary` VALUES (12,1375,'yes','SMC%GPASS%8612XL3');
INSERT INTO `Dictionary` VALUES (12,1376,'yes','SMC%GPASS%8708L2');
INSERT INTO `Dictionary` VALUES (12,1377,'yes','SMC%GPASS%8824M');
INSERT INTO `Dictionary` VALUES (12,1378,'yes','SMC%GPASS%8848M');
INSERT INTO `Dictionary` VALUES (12,1379,'yes','SMC%GPASS%8926EM');
INSERT INTO `Dictionary` VALUES (12,1380,'yes','SMC%GPASS%8950EM');
INSERT INTO `Dictionary` VALUES (11,1381,'yes','Dell PowerEdge%GPASS%R910');
INSERT INTO `Dictionary` VALUES (18,1382,'yes','Dell PowerVault%GPASS%MD1220');
INSERT INTO `Dictionary` VALUES (12,1383,'yes','Cisco%GPASS%Catalyst 2960S-48TD-L');
INSERT INTO `Dictionary` VALUES (12,1384,'yes','Cisco%GPASS%Catalyst 2960S-24TD-L');
INSERT INTO `Dictionary` VALUES (12,1385,'yes','Cisco%GPASS%Catalyst 2960S-48FPD-L');
INSERT INTO `Dictionary` VALUES (12,1386,'yes','Cisco%GPASS%Catalyst 2960S-48LPD-L');
INSERT INTO `Dictionary` VALUES (12,1387,'yes','Cisco%GPASS%Catalyst 2960S-24PD-L');
INSERT INTO `Dictionary` VALUES (12,1388,'yes','Cisco%GPASS%Catalyst 2960S-48TS-L');
INSERT INTO `Dictionary` VALUES (12,1389,'yes','Cisco%GPASS%Catalyst 2960S-24TS-L');
INSERT INTO `Dictionary` VALUES (12,1390,'yes','Cisco%GPASS%Catalyst 2960S-48TS-S');
INSERT INTO `Dictionary` VALUES (12,1391,'yes','Cisco%GPASS%Catalyst 2960S-24TS-S');
INSERT INTO `Dictionary` VALUES (12,1392,'yes','Cisco%GPASS%Catalyst 2960S-48FPS-L');
INSERT INTO `Dictionary` VALUES (12,1393,'yes','Cisco%GPASS%Catalyst 2960S-48LPS-L');
INSERT INTO `Dictionary` VALUES (12,1394,'yes','Cisco%GPASS%Catalyst 2960S-24PS-L');
INSERT INTO `Dictionary` VALUES (13,1395,'yes','[[Debian%GSKIP%Debian 6.0 (squeeze) | http://debian.org/releases/squeeze/]]');
INSERT INTO `Dictionary` VALUES (13,1396,'yes','Red Hat Enterprise%GSKIP%RHEL V6');
INSERT INTO `Dictionary` VALUES (1,1397,'yes','Power supply chassis');
INSERT INTO `Dictionary` VALUES (1,1398,'yes','Power supply');
INSERT INTO `Dictionary` VALUES (2,1399,'yes','DC');
INSERT INTO `Dictionary` VALUES (34,1400,'yes','Cisco%GPASS%RPS 2300');
INSERT INTO `Dictionary` VALUES (34,1401,'yes','D-Link%GPASS%DPS-800');
INSERT INTO `Dictionary` VALUES (34,1402,'yes','D-Link%GPASS%DPS-900');
INSERT INTO `Dictionary` VALUES (35,1403,'yes','Cisco%GPASS%RPS 675');
INSERT INTO `Dictionary` VALUES (35,1404,'yes','Cisco%GPASS%C3K-PWR-750WAC');
INSERT INTO `Dictionary` VALUES (35,1405,'yes','Cisco%GPASS%C3K-PWR-1150WAC');
INSERT INTO `Dictionary` VALUES (35,1406,'yes','D-Link%GPASS%DPS-200');
INSERT INTO `Dictionary` VALUES (35,1407,'yes','D-Link%GPASS%DPS-500');
INSERT INTO `Dictionary` VALUES (35,1408,'yes','D-Link%GPASS%DPS-510');
INSERT INTO `Dictionary` VALUES (35,1409,'yes','D-Link%GPASS%DPS-600');
INSERT INTO `Dictionary` VALUES (14,1410,'yes','Cisco NX-OS 5.0');
INSERT INTO `Dictionary` VALUES (14,1411,'yes','Cisco NX-OS 5.1');
INSERT INTO `Dictionary` VALUES (12,1412,'yes','[[Cisco%GPASS%Nexus 5548P | http://cisco.com/en/US/products/ps11215/index.html]]');
INSERT INTO `Dictionary` VALUES (12,1413,'yes','[[Cisco%GPASS%Nexus 2224TP | http://cisco.com/en/US/products/ps11045/index.html]]');
INSERT INTO `Dictionary` VALUES (12,1414,'yes','[[Cisco%GPASS%Nexus 2248TP | http://cisco.com/en/US/products/ps10783/index.html]]');
INSERT INTO `Dictionary` VALUES (12,1415,'yes','[[Cisco%GPASS%Nexus 2232PP | http://cisco.com/en/US/products/ps10784/index.html]]');
INSERT INTO `Dictionary` VALUES (13,1416,'yes','FreeBSD%GSKIP%FreeBSD 8.x');
INSERT INTO `Dictionary` VALUES (13,1417,'yes','[[SciLin%GSKIP%SL3.x | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (13,1418,'yes','[[SciLin%GSKIP%SL4.x | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (13,1419,'yes','[[SciLin%GSKIP%SL5.1 | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (13,1420,'yes','[[SciLin%GSKIP%SL5.2 | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (13,1421,'yes','[[SciLin%GSKIP%SL5.3 | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (13,1422,'yes','[[SciLin%GSKIP%SL5.4 | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (12,1423,'yes','HP ProCurve%GPASS%2600-8-PWR');
INSERT INTO `Dictionary` VALUES (2,1424,'yes','1000Base-CX');
INSERT INTO `Dictionary` VALUES (2,1425,'yes','10GBase-ER-DWDM40-61.42 (ITU 20)');
INSERT INTO `Dictionary` VALUES (2,1426,'yes','10GBase-ER-DWDM40-60.61 (ITU 21)');
INSERT INTO `Dictionary` VALUES (2,1427,'yes','10GBase-ER-DWDM40-59.79 (ITU 22)');
INSERT INTO `Dictionary` VALUES (2,1428,'yes','10GBase-ER-DWDM40-58.98 (ITU 23)');
INSERT INTO `Dictionary` VALUES (2,1429,'yes','10GBase-ER-DWDM40-58.17 (ITU 24)');
INSERT INTO `Dictionary` VALUES (2,1430,'yes','10GBase-ER-DWDM40-57.36 (ITU 25)');
INSERT INTO `Dictionary` VALUES (2,1431,'yes','10GBase-ER-DWDM40-56.55 (ITU 26)');
INSERT INTO `Dictionary` VALUES (2,1432,'yes','10GBase-ER-DWDM40-55.75 (ITU 27)');
INSERT INTO `Dictionary` VALUES (2,1433,'yes','10GBase-ER-DWDM40-54.94 (ITU 28)');
INSERT INTO `Dictionary` VALUES (2,1434,'yes','10GBase-ER-DWDM40-54.13 (ITU 29)');
INSERT INTO `Dictionary` VALUES (2,1435,'yes','10GBase-ER-DWDM40-53.33 (ITU 30)');
INSERT INTO `Dictionary` VALUES (2,1436,'yes','10GBase-ER-DWDM40-52.52 (ITU 31)');
INSERT INTO `Dictionary` VALUES (2,1437,'yes','10GBase-ER-DWDM40-51.72 (ITU 32)');
INSERT INTO `Dictionary` VALUES (2,1438,'yes','10GBase-ER-DWDM40-50.92 (ITU 33)');
INSERT INTO `Dictionary` VALUES (2,1439,'yes','10GBase-ER-DWDM40-50.12 (ITU 34)');
INSERT INTO `Dictionary` VALUES (2,1440,'yes','10GBase-ER-DWDM40-49.32 (ITU 35)');
INSERT INTO `Dictionary` VALUES (2,1441,'yes','10GBase-ER-DWDM40-48.51 (ITU 36)');
INSERT INTO `Dictionary` VALUES (2,1442,'yes','10GBase-ER-DWDM40-47.72 (ITU 37)');
INSERT INTO `Dictionary` VALUES (2,1443,'yes','10GBase-ER-DWDM40-46.92 (ITU 38)');
INSERT INTO `Dictionary` VALUES (2,1444,'yes','10GBase-ER-DWDM40-46.12 (ITU 39)');
INSERT INTO `Dictionary` VALUES (2,1445,'yes','10GBase-ER-DWDM40-45.32 (ITU 40)');
INSERT INTO `Dictionary` VALUES (2,1446,'yes','10GBase-ER-DWDM40-44.53 (ITU 41)');
INSERT INTO `Dictionary` VALUES (2,1447,'yes','10GBase-ER-DWDM40-43.73 (ITU 42)');
INSERT INTO `Dictionary` VALUES (2,1448,'yes','10GBase-ER-DWDM40-42.94 (ITU 43)');
INSERT INTO `Dictionary` VALUES (2,1449,'yes','10GBase-ER-DWDM40-42.14 (ITU 44)');
INSERT INTO `Dictionary` VALUES (2,1450,'yes','10GBase-ER-DWDM40-41.35 (ITU 45)');
INSERT INTO `Dictionary` VALUES (2,1451,'yes','10GBase-ER-DWDM40-40.56 (ITU 46)');
INSERT INTO `Dictionary` VALUES (2,1452,'yes','10GBase-ER-DWDM40-39.77 (ITU 47)');
INSERT INTO `Dictionary` VALUES (2,1453,'yes','10GBase-ER-DWDM40-38.98 (ITU 48)');
INSERT INTO `Dictionary` VALUES (2,1454,'yes','10GBase-ER-DWDM40-38.19 (ITU 49)');
INSERT INTO `Dictionary` VALUES (2,1455,'yes','10GBase-ER-DWDM40-37.40 (ITU 50)');
INSERT INTO `Dictionary` VALUES (2,1456,'yes','10GBase-ER-DWDM40-36.61 (ITU 51)');
INSERT INTO `Dictionary` VALUES (2,1457,'yes','10GBase-ER-DWDM40-35.82 (ITU 52)');
INSERT INTO `Dictionary` VALUES (2,1458,'yes','10GBase-ER-DWDM40-35.04 (ITU 53)');
INSERT INTO `Dictionary` VALUES (2,1459,'yes','10GBase-ER-DWDM40-34.25 (ITU 54)');
INSERT INTO `Dictionary` VALUES (2,1460,'yes','10GBase-ER-DWDM40-33.47 (ITU 55)');
INSERT INTO `Dictionary` VALUES (2,1461,'yes','10GBase-ER-DWDM40-32.68 (ITU 56)');
INSERT INTO `Dictionary` VALUES (2,1462,'yes','10GBase-ER-DWDM40-31.90 (ITU 57)');
INSERT INTO `Dictionary` VALUES (2,1463,'yes','10GBase-ER-DWDM40-31.12 (ITU 58)');
INSERT INTO `Dictionary` VALUES (2,1464,'yes','10GBase-ER-DWDM40-30.33 (ITU 59)');
INSERT INTO `Dictionary` VALUES (2,1465,'yes','10GBase-ER-DWDM40-29.55 (ITU 60)');
INSERT INTO `Dictionary` VALUES (2,1466,'yes','10GBase-ER-DWDM40-28.77 (ITU 61)');
INSERT INTO `Dictionary` VALUES (25,1467,'yes','Cisco%GPASS%AIR-AP1141N');
INSERT INTO `Dictionary` VALUES (25,1468,'yes','Cisco%GPASS%AIR-AP1262N');
INSERT INTO `Dictionary` VALUES (2,1469,'yes','virtual port');
INSERT INTO `Dictionary` VALUES (12,1470,'yes','[[Force10%GPASS%S55 | http://www.force10networks.com/products/s55.asp]]');
INSERT INTO `Dictionary` VALUES (12,1471,'yes','[[Force10%GPASS%S60 | http://www.force10networks.com/products/s60.asp]]');
INSERT INTO `Dictionary` VALUES (12,1472,'yes','[[Force10%GPASS%S4810 | http://www.force10networks.com/products/s4810.asp]]');
INSERT INTO `Dictionary` VALUES (31,1473,'yes','IBM%GPASS%BladeCenter S');
INSERT INTO `Dictionary` VALUES (31,1474,'yes','IBM%GPASS%BladeCenter H%L1,14V%');
INSERT INTO `Dictionary` VALUES (31,1475,'yes','IBM%GPASS%BladeCenter E%L1,14V%');
INSERT INTO `Dictionary` VALUES (31,1476,'yes','IBM%GPASS%BladeCenter T');
INSERT INTO `Dictionary` VALUES (31,1477,'yes','IBM%GPASS%BladeCenter HT');
INSERT INTO `Dictionary` VALUES (11,1478,'yes','IBM BladeCenter%GPASS%HS12');
INSERT INTO `Dictionary` VALUES (11,1479,'yes','IBM BladeCenter%GPASS%HS20');
INSERT INTO `Dictionary` VALUES (11,1480,'yes','IBM BladeCenter%GPASS%HS21');
INSERT INTO `Dictionary` VALUES (11,1481,'yes','IBM BladeCenter%GPASS%HS21 XM');
INSERT INTO `Dictionary` VALUES (11,1482,'yes','IBM BladeCenter%GPASS%HS22');
INSERT INTO `Dictionary` VALUES (11,1483,'yes','IBM BladeCenter%GPASS%HS22V');
INSERT INTO `Dictionary` VALUES (11,1484,'yes','IBM BladeCenter%GPASS%HX5');
INSERT INTO `Dictionary` VALUES (11,1485,'yes','IBM BladeCenter%GPASS%JS12');
INSERT INTO `Dictionary` VALUES (11,1486,'yes','IBM BladeCenter%GPASS%JS20');
INSERT INTO `Dictionary` VALUES (11,1487,'yes','IBM BladeCenter%GPASS%JS21');
INSERT INTO `Dictionary` VALUES (11,1488,'yes','IBM BladeCenter%GPASS%JS22');
INSERT INTO `Dictionary` VALUES (11,1489,'yes','IBM BladeCenter%GPASS%JS23');
INSERT INTO `Dictionary` VALUES (11,1490,'yes','IBM BladeCenter%GPASS%JS43');
INSERT INTO `Dictionary` VALUES (11,1491,'yes','IBM BladeCenter%GPASS%LS20');
INSERT INTO `Dictionary` VALUES (11,1492,'yes','IBM BladeCenter%GPASS%LS21');
INSERT INTO `Dictionary` VALUES (11,1493,'yes','IBM BladeCenter%GPASS%LS22');
INSERT INTO `Dictionary` VALUES (11,1494,'yes','IBM BladeCenter%GPASS%LS41');
INSERT INTO `Dictionary` VALUES (11,1495,'yes','IBM BladeCenter%GPASS%LS42');
INSERT INTO `Dictionary` VALUES (11,1496,'yes','IBM BladeCenter%GPASS%PS700');
INSERT INTO `Dictionary` VALUES (11,1497,'yes','IBM BladeCenter%GPASS%PS701');
INSERT INTO `Dictionary` VALUES (11,1498,'yes','IBM BladeCenter%GPASS%PS702');
INSERT INTO `Dictionary` VALUES (11,1499,'yes','IBM BladeCenter%GPASS%PS703');
INSERT INTO `Dictionary` VALUES (29,1500,'yes','No');
INSERT INTO `Dictionary` VALUES (29,1501,'yes','Yes');
INSERT INTO `Dictionary` VALUES (1,1502,'yes','Server chassis');
INSERT INTO `Dictionary` VALUES (1,1503,'yes','Network chassis');
INSERT INTO `Dictionary` VALUES (1,1504,'yes','VM');
INSERT INTO `Dictionary` VALUES (1,1505,'yes','VM Cluster');
INSERT INTO `Dictionary` VALUES (1,1506,'yes','VM Resource Pool');
INSERT INTO `Dictionary` VALUES (1,1507,'yes','VM Virtual Switch');
INSERT INTO `Dictionary` VALUES (13,1508,'yes','VMWare Hypervisor%GSKIP%VMware ESX 3.5');
INSERT INTO `Dictionary` VALUES (13,1509,'yes','VMWare Hypervisor%GSKIP%VMware ESXi 3.5');
INSERT INTO `Dictionary` VALUES (13,1510,'yes','VMWare Hypervisor%GSKIP%VMware ESX 4.0');
INSERT INTO `Dictionary` VALUES (13,1511,'yes','VMWare Hypervisor%GSKIP%VMware ESXi 4.0');
INSERT INTO `Dictionary` VALUES (13,1512,'yes','VMWare Hypervisor%GSKIP%VMware ESX 4.1');
INSERT INTO `Dictionary` VALUES (13,1513,'yes','VMWare Hypervisor%GSKIP%VMware ESXi 4.1');
INSERT INTO `Dictionary` VALUES (13,1514,'yes','Xen Hypervisor%GSKIP%XenServer 4.0');
INSERT INTO `Dictionary` VALUES (13,1515,'yes','Xen Hypervisor%GSKIP%XenServer 5.0');
INSERT INTO `Dictionary` VALUES (13,1516,'yes','Xen Hypervisor%GSKIP%XenServer 5.5');
INSERT INTO `Dictionary` VALUES (31,1517,'yes','Dell PowerEdge%GPASS%1855%L1,10V%');
INSERT INTO `Dictionary` VALUES (11,1518,'yes','Dell PowerEdge (blade)%GPASS%1955');
INSERT INTO `Dictionary` VALUES (11,1519,'yes','Dell PowerEdge (blade)%GPASS%M605');
INSERT INTO `Dictionary` VALUES (11,1520,'yes','Dell PowerEdge (blade)%GPASS%M610');
INSERT INTO `Dictionary` VALUES (11,1521,'yes','Dell PowerEdge (blade)%GPASS%M610x');
INSERT INTO `Dictionary` VALUES (11,1522,'yes','Dell PowerEdge (blade)%GPASS%M710%L2,1%');
INSERT INTO `Dictionary` VALUES (11,1523,'yes','Dell PowerEdge (blade)%GPASS%M805%L2,1%');
INSERT INTO `Dictionary` VALUES (11,1524,'yes','Dell PowerEdge (blade)%GPASS%M905%L2,1%');
INSERT INTO `Dictionary` VALUES (11,1525,'yes','Dell PowerEdge (blade)%GPASS%M910%L2,1%');
INSERT INTO `Dictionary` VALUES (26,1526,'yes','Brocade (blade)%GPASS%McDATA 3014');
INSERT INTO `Dictionary` VALUES (26,1527,'yes','Brocade (blade)%GPASS%McDATA 4314');
INSERT INTO `Dictionary` VALUES (26,1528,'yes','Brocade (blade)%GPASS%McDATA 4416');
INSERT INTO `Dictionary` VALUES (26,1529,'yes','Brocade (blade)%GPASS%M4424');
INSERT INTO `Dictionary` VALUES (26,1530,'yes','Brocade (blade)%GPASS%M5424');
INSERT INTO `Dictionary` VALUES (12,1531,'yes','Dell PowerConnect (blade)%GPASS%5316M');
INSERT INTO `Dictionary` VALUES (12,1532,'yes','Dell PowerConnect (blade)%GPASS%M6220');
INSERT INTO `Dictionary` VALUES (12,1533,'yes','Dell PowerConnect (blade)%GPASS%M8024');
INSERT INTO `Dictionary` VALUES (12,1534,'yes','Cisco (blade)%GPASS%Catalyst 3130G');
INSERT INTO `Dictionary` VALUES (12,1535,'yes','Cisco (blade)%GPASS%Catalyst 3130X');
INSERT INTO `Dictionary` VALUES (12,1536,'yes','Cisco (blade)%GPASS%WS-X6148-GE-TX');
INSERT INTO `Dictionary` VALUES (12,1537,'yes','Cisco (blade)%GPASS%WS-X6148A-GE-45AF');
INSERT INTO `Dictionary` VALUES (12,1538,'yes','Cisco (blade)%GPASS%WS-X6148A-GE-TX');
INSERT INTO `Dictionary` VALUES (12,1539,'yes','Cisco (blade)%GPASS%WS-X6408A-GBIC');
INSERT INTO `Dictionary` VALUES (12,1540,'yes','Cisco (blade)%GPASS%WS-X6416-GBIC');
INSERT INTO `Dictionary` VALUES (12,1541,'yes','Cisco (blade)%GPASS%WS-X6516A-GBIC');
INSERT INTO `Dictionary` VALUES (12,1542,'yes','Cisco (blade)%GPASS%WS-X6548-GE-TX');
INSERT INTO `Dictionary` VALUES (12,1543,'yes','Cisco (blade)%GPASS%WS-X6548-GE-45AF');
INSERT INTO `Dictionary` VALUES (12,1544,'yes','Cisco (blade)%GPASS%WS-X6704-10GE');
INSERT INTO `Dictionary` VALUES (12,1545,'yes','Cisco (blade)%GPASS%WS-X6708-10G-3C');
INSERT INTO `Dictionary` VALUES (12,1546,'yes','Cisco (blade)%GPASS%WS-X6708-10G-3CXL');
INSERT INTO `Dictionary` VALUES (12,1547,'yes','Cisco (blade)%GPASS%WS-X6716-10GT-3C');
INSERT INTO `Dictionary` VALUES (12,1548,'yes','Cisco (blade)%GPASS%WS-X6716-10GT-3CXL');
INSERT INTO `Dictionary` VALUES (12,1549,'yes','Cisco (blade)%GPASS%WS-X6724-SFP');
INSERT INTO `Dictionary` VALUES (12,1550,'yes','Cisco (blade)%GPASS%WS-X6748-GE-TX');
INSERT INTO `Dictionary` VALUES (12,1551,'yes','Cisco (blade)%GPASS%WS-X6748-SFP');
INSERT INTO `Dictionary` VALUES (12,1552,'yes','Cisco (blade)%GPASS%WS-SUP720-3B');
INSERT INTO `Dictionary` VALUES (12,1553,'yes','Cisco (blade)%GPASS%WS-XSUP720-3BXL');
INSERT INTO `Dictionary` VALUES (12,1554,'yes','Cisco (blade)%GPASS%WS-SUP32-GE-3B');
INSERT INTO `Dictionary` VALUES (12,1555,'yes','Cisco (blade)%GPASS%WS-SUP32-10GE-3B');
INSERT INTO `Dictionary` VALUES (32,1556,'yes','VMware%GPASS%Standard vSwitch');
INSERT INTO `Dictionary` VALUES (32,1557,'yes','VMware%GPASS%Distributed vSwitch');
INSERT INTO `Dictionary` VALUES (32,1558,'yes','Cisco%GPASS%Nexus 1000V');
INSERT INTO `Dictionary` VALUES (33,1559,'yes','NS-OS 4.0');
INSERT INTO `Dictionary` VALUES (1,1560,'yes','Rack');
INSERT INTO `Dictionary` VALUES (1,1561,'yes','Row');
INSERT INTO `Dictionary` VALUES (1,1562,'yes','Location');
INSERT INTO `Dictionary` VALUES (11,1563,'yes','IBM BladeCenter%GPASS%PS704');
INSERT INTO `Dictionary` VALUES (11,1564,'yes','IBM BladeCenter%GPASS%QS21');
INSERT INTO `Dictionary` VALUES (11,1565,'yes','IBM BladeCenter%GPASS%QS22');
INSERT INTO `Dictionary` VALUES (12,1566,'yes','SMC%GPASS%SMC6110L2');
INSERT INTO `Dictionary` VALUES (12,1567,'yes','SMC%GPASS%SMC6128L2');
INSERT INTO `Dictionary` VALUES (12,1568,'yes','SMC%GPASS%SMC6128PL2');
INSERT INTO `Dictionary` VALUES (12,1569,'yes','SMC%GPASS%SMC6152L2');
INSERT INTO `Dictionary` VALUES (12,1570,'yes','HP ProCurve%GPASS%E2610-24/12-PoE J9086A');
INSERT INTO `Dictionary` VALUES (12,1571,'yes','HP ProCurve%GPASS%E2910-24G');
INSERT INTO `Dictionary` VALUES (12,1572,'yes','Cisco%GPASS%Catalyst 2960-48TT-L');
INSERT INTO `Dictionary` VALUES (12,1573,'yes','Cisco%GPASS%Catalyst 2960-48TT-S');
INSERT INTO `Dictionary` VALUES (12,1574,'yes','Cisco%GPASS%Catalyst 3560E-48TD');
INSERT INTO `Dictionary` VALUES (12,1575,'yes','Cisco%GPASS%Catalyst 3560E-24TD');
INSERT INTO `Dictionary` VALUES (12,1576,'yes','Cisco%GPASS%Catalyst 3560X-24T');
INSERT INTO `Dictionary` VALUES (12,1577,'yes','Cisco%GPASS%Catalyst 3560X-48T');
INSERT INTO `Dictionary` VALUES (12,1578,'yes','Cisco%GPASS%Catalyst 3560X-24P');
INSERT INTO `Dictionary` VALUES (12,1579,'yes','Cisco%GPASS%Catalyst 3560X-48P');
INSERT INTO `Dictionary` VALUES (12,1580,'yes','Cisco%GPASS%Catalyst 3560X-48PF');
INSERT INTO `Dictionary` VALUES (12,1581,'yes','Cisco%GPASS%Catalyst 3750X-24T');
INSERT INTO `Dictionary` VALUES (12,1582,'yes','Cisco%GPASS%Catalyst 3750X-48T');
INSERT INTO `Dictionary` VALUES (12,1583,'yes','Cisco%GPASS%Catalyst 3750X-24P');
INSERT INTO `Dictionary` VALUES (12,1584,'yes','Cisco%GPASS%Catalyst 3750X-48P');
INSERT INTO `Dictionary` VALUES (12,1585,'yes','Cisco%GPASS%Catalyst 3750X-48PF');
INSERT INTO `Dictionary` VALUES (12,1586,'yes','Cisco%GPASS%Catalyst 3750X-12S');
INSERT INTO `Dictionary` VALUES (12,1587,'yes','Cisco%GPASS%Catalyst 3750X-24S');
INSERT INTO `Dictionary` VALUES (2,1588,'yes','empty QSFP+');
INSERT INTO `Dictionary` VALUES (25,1589,'yes','Cisco%GPASS%AIR-AP1261N');
INSERT INTO `Dictionary` VALUES (12,1590,'yes','Cisco%GPASS%Catalyst 2960-48TC-L');
INSERT INTO `Dictionary` VALUES (14,1591,'yes','Force10 SFTOS 2');
INSERT INTO `Dictionary` VALUES (14,1592,'yes','Force10 FTOS 6');
INSERT INTO `Dictionary` VALUES (14,1593,'yes','Force10 FTOS 7');
INSERT INTO `Dictionary` VALUES (14,1594,'yes','Force10 FTOS 8');
INSERT INTO `Dictionary` VALUES (13,1595,'yes','[[RH Fedora%GSKIP%Fedora 13 | http://docs.fedoraproject.org/release-notes/f13/en-US/html/]]');
INSERT INTO `Dictionary` VALUES (13,1596,'yes','[[RH Fedora%GSKIP%Fedora 14 | http://docs.fedoraproject.org/release-notes/f14/en-US/html/]]');
INSERT INTO `Dictionary` VALUES (16,1597,'yes','JunOS 10');
INSERT INTO `Dictionary` VALUES (16,1598,'yes','JunOS 11');
INSERT INTO `Dictionary` VALUES (16,1599,'yes','JunOS 12');
INSERT INTO `Dictionary` VALUES (12,1600,'yes','HP ProCurve%GPASS%E2910-48G-PoE+ J9148A');
INSERT INTO `Dictionary` VALUES (12,1601,'yes','NETGEAR%GPASS%GSM7328Sv2');
INSERT INTO `Dictionary` VALUES (12,1602,'yes','NETGEAR%GPASS%GSM7224v2');
INSERT INTO `Dictionary` VALUES (2,1603,'yes','1000Base-T (HP c-Class)');
INSERT INTO `Dictionary` VALUES (2,1604,'yes','100Base-TX (HP c-Class)');
INSERT INTO `Dictionary` VALUES (12,1605,'yes','HP GbE2c w/SFP');
INSERT INTO `Dictionary` VALUES (12,1606,'yes','Cisco%GPASS%Catalyst 2360-48TD');
INSERT INTO `Dictionary` VALUES (12,1607,'yes','Cisco%GPASS%Catalyst 3560E-12D');
INSERT INTO `Dictionary` VALUES (13,1608,'yes','VMWare Hypervisor%GSKIP%VMware ESXi 5.0');
INSERT INTO `Dictionary` VALUES (17,1609,'yes','Fortinet%GPASS%Fortigate 310B');
INSERT INTO `Dictionary` VALUES (12,1610,'yes','Arista%GPASS%7124S');
INSERT INTO `Dictionary` VALUES (12,1611,'yes','Dell PowerConnect%GPASS%3348');
INSERT INTO `Dictionary` VALUES (12,1612,'yes','Cisco%GPASS%SF 300-48');
INSERT INTO `Dictionary` VALUES (12,1613,'yes','Huawei%GPASS%S2309TP-EI');
INSERT INTO `Dictionary` VALUES (12,1614,'yes','Huawei%GPASS%S2309TP-SI');
INSERT INTO `Dictionary` VALUES (12,1615,'yes','Huawei%GPASS%S2318TP-EI');
INSERT INTO `Dictionary` VALUES (12,1616,'yes','Huawei%GPASS%S2318TP-SI');
INSERT INTO `Dictionary` VALUES (12,1617,'yes','Huawei%GPASS%S2326TP-EI');
INSERT INTO `Dictionary` VALUES (12,1618,'yes','Huawei%GPASS%S2326TP-SI');
INSERT INTO `Dictionary` VALUES (12,1619,'yes','Huawei%GPASS%S2352P-EI');
INSERT INTO `Dictionary` VALUES (12,1620,'yes','Huawei%GPASS%S2309TP-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1621,'yes','Huawei%GPASS%S2326TP-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1622,'yes','Dell PowerConnect%GPASS%5224');
INSERT INTO `Dictionary` VALUES (12,1623,'yes','Dell PowerConnect%GPASS%6024F');
INSERT INTO `Dictionary` VALUES (12,1624,'yes','Linksys%GPASS%SRW2048');
INSERT INTO `Dictionary` VALUES (30,1625,'yes','HP ProCurve%GPASS%5406zl-48G PoE+ J9447A%L4,2H%');
INSERT INTO `Dictionary` VALUES (30,1626,'yes','HP ProCurve%GPASS%5412zl-96G PoE+ J9448A%L7,2H%');
INSERT INTO `Dictionary` VALUES (12,1627,'yes','HP ProCurve (blade)%GPASS%5400zl Management Module J8726A');
INSERT INTO `Dictionary` VALUES (12,1628,'yes','HP ProCurve (blade)%GPASS%5400zl 24 1Gb-PoE J8702A');
INSERT INTO `Dictionary` VALUES (12,1629,'yes','HP ProCurve (blade)%GPASS%5400zl 20 1Gb + 4 Mini-GBIC J8705A');
INSERT INTO `Dictionary` VALUES (12,1630,'yes','HP ProCurve (blade)%GPASS%5400zl 24 Mini-GBIC J8706A');
INSERT INTO `Dictionary` VALUES (12,1631,'yes','HP ProCurve (blade)%GPASS%5400zl 4 10GbE X2 J8707A');
INSERT INTO `Dictionary` VALUES (12,1632,'yes','HP ProCurve (blade)%GPASS%5400zl 4 10GbE CX4 J8708A');
INSERT INTO `Dictionary` VALUES (12,1633,'yes','HP ProCurve (blade)%GPASS%5400zl 24 1Gb-PoE+ J9307A');
INSERT INTO `Dictionary` VALUES (12,1634,'yes','HP ProCurve (blade)%GPASS%5400zl 20 1Gb-PoE+ + 4 Mini-GBIC J9308A');
INSERT INTO `Dictionary` VALUES (12,1635,'yes','HP ProCurve (blade)%GPASS%5400zl 4 10GbE SFP+ J9309A');
INSERT INTO `Dictionary` VALUES (12,1636,'yes','HP ProCurve (blade)%GPASS%5400zl 24 100Mb PoE+ J9478A');
INSERT INTO `Dictionary` VALUES (12,1637,'yes','HP ProCurve%GPASS%6600-24G J9263A');
INSERT INTO `Dictionary` VALUES (12,1638,'yes','HP ProCurve%GPASS%6600-24G-4XG J9264A');
INSERT INTO `Dictionary` VALUES (12,1639,'yes','HP ProCurve%GPASS%6600-24XG J9265A');
INSERT INTO `Dictionary` VALUES (12,1640,'yes','HP ProCurve%GPASS%6600-48G J9451A');
INSERT INTO `Dictionary` VALUES (12,1641,'yes','HP ProCurve%GPASS%6600-48G-4XG J9452A');
INSERT INTO `Dictionary` VALUES (2,1642,'yes','10GBase-T');
INSERT INTO `Dictionary` VALUES (14,1643,'yes','Cisco NX-OS 6.0');
INSERT INTO `Dictionary` VALUES (1,1644,'yes','serial console server');
INSERT INTO `Dictionary` VALUES (36,1645,'yes','[[Moxa%GPASS%NPort 6150 | http://www.moxa.com/product/NPort_6150.htm]]');
INSERT INTO `Dictionary` VALUES (36,1646,'yes','[[Moxa%GPASS%NPort 6610-8 | http://www.moxa.com/product/NPort_6650.htm]]');
INSERT INTO `Dictionary` VALUES (36,1647,'yes','[[Moxa%GPASS%NPort 6610-16 | http://www.moxa.com/product/NPort_6650.htm]]');
INSERT INTO `Dictionary` VALUES (36,1648,'yes','[[Moxa%GPASS%NPort 6610-32 | http://www.moxa.com/product/NPort_6650.htm]]');
INSERT INTO `Dictionary` VALUES (36,1649,'yes','[[Moxa%GPASS%NPort 6650-8 | http://www.moxa.com/product/NPort_6650.htm]]');
INSERT INTO `Dictionary` VALUES (36,1650,'yes','[[Moxa%GPASS%NPort 6650-16 | http://www.moxa.com/product/NPort_6650.htm]]');
INSERT INTO `Dictionary` VALUES (36,1651,'yes','[[Moxa%GPASS%NPort 6650-32 | http://www.moxa.com/product/NPort_6650.htm]]');
INSERT INTO `Dictionary` VALUES (36,1652,'yes','[[Moxa%GPASS%CN2510-8 | http://www.moxa.com/product/CN2510.htm]]');
INSERT INTO `Dictionary` VALUES (36,1653,'yes','[[Moxa%GPASS%CN2510-16 | http://www.moxa.com/product/CN2510.htm]]');
INSERT INTO `Dictionary` VALUES (36,1654,'yes','[[Moxa%GPASS%CN2610-8 | http://www.moxa.com/product/CN2610.htm]]');
INSERT INTO `Dictionary` VALUES (36,1655,'yes','[[Moxa%GPASS%CN2610-16 | http://www.moxa.com/product/CN2610.htm]]');
INSERT INTO `Dictionary` VALUES (36,1656,'yes','[[Moxa%GPASS%CN2650-8 | http://www.moxa.com/product/CN2610.htm]]');
INSERT INTO `Dictionary` VALUES (36,1657,'yes','[[Moxa%GPASS%CN2650-16 | http://www.moxa.com/product/CN2610.htm]]');
INSERT INTO `Dictionary` VALUES (36,1658,'yes','[[Moxa%GPASS%NPort 6250 | http://www.moxa.com/product/NPort_6250.htm]]');
INSERT INTO `Dictionary` VALUES (36,1659,'yes','[[Moxa%GPASS%NPort 6450 | http://www.moxa.com/product/NPort_6450.htm]]');
INSERT INTO `Dictionary` VALUES (36,1660,'yes','[[Moxa%GPASS%NPort S8458 | http://www.moxa.com/product/NPort_S8458_Series.htm]]');
INSERT INTO `Dictionary` VALUES (2,1661,'yes','40GBase-KR4');
INSERT INTO `Dictionary` VALUES (2,1663,'yes','40GBase-SR4');
INSERT INTO `Dictionary` VALUES (2,1664,'yes','40GBase-LR4');
INSERT INTO `Dictionary` VALUES (13,1665,'yes','[[SciLin%GSKIP%SL6.x | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (13,1666,'yes','[[SciLin%GSKIP%SL5.x | https://www.scientificlinux.org/]]');
INSERT INTO `Dictionary` VALUES (13,1667,'yes','[[CentOS%GSKIP%CentOS V6 | http://www.centos.org/]]');
INSERT INTO `Dictionary` VALUES (2,1668,'yes','empty CFP');
INSERT INTO `Dictionary` VALUES (2,1669,'yes','100GBase-SR10');
INSERT INTO `Dictionary` VALUES (2,1670,'yes','100GBase-LR4');
INSERT INTO `Dictionary` VALUES (2,1671,'yes','100GBase-ER4');
INSERT INTO `Dictionary` VALUES (11,1672,'yes','Dell PowerEdge%GPASS%R510');
INSERT INTO `Dictionary` VALUES (37,1673,'yes','Cisco Aironet IOS 12.3');
INSERT INTO `Dictionary` VALUES (37,1674,'yes','Cisco Aironet IOS 12.4');
INSERT INTO `Dictionary` VALUES (14,1675,'yes','Arista EOS 4');
INSERT INTO `Dictionary` VALUES (11,1676,'yes','Dell PowerEdge C%GPASS%C1100');
INSERT INTO `Dictionary` VALUES (11,1677,'yes','Dell PowerEdge C%GPASS%C2100');
INSERT INTO `Dictionary` VALUES (11,1678,'yes','Dell PowerEdge C%GPASS%C5125');
INSERT INTO `Dictionary` VALUES (11,1679,'yes','Dell PowerEdge C%GPASS%C5220');
INSERT INTO `Dictionary` VALUES (11,1680,'yes','Dell PowerEdge C%GPASS%C6100');
INSERT INTO `Dictionary` VALUES (11,1681,'yes','Dell PowerEdge C%GPASS%C6105');
INSERT INTO `Dictionary` VALUES (11,1682,'yes','Dell PowerEdge C%GPASS%C6145');
INSERT INTO `Dictionary` VALUES (11,1683,'yes','Dell PowerEdge C%GPASS%C6220');
INSERT INTO `Dictionary` VALUES (31,1684,'yes','Dell PowerEdge C%GPASS%C410x%L1,10V%');
INSERT INTO `Dictionary` VALUES (31,1685,'yes','Dell PowerEdge C%GPASS%C5000%L1,12V%');
INSERT INTO `Dictionary` VALUES (11,1686,'yes','Dell PowerEdge%GPASS%R210');
INSERT INTO `Dictionary` VALUES (11,1687,'yes','Dell PowerEdge%GPASS%R310');
INSERT INTO `Dictionary` VALUES (11,1688,'yes','Dell PowerEdge%GPASS%R415');
INSERT INTO `Dictionary` VALUES (11,1689,'yes','Dell PowerEdge%GPASS%R515');
INSERT INTO `Dictionary` VALUES (11,1690,'yes','Dell PowerEdge%GPASS%R620');
INSERT INTO `Dictionary` VALUES (11,1691,'yes','Dell PowerEdge%GPASS%R715');
INSERT INTO `Dictionary` VALUES (11,1692,'yes','Dell PowerEdge%GPASS%R720');
INSERT INTO `Dictionary` VALUES (11,1693,'yes','Dell PowerEdge%GPASS%R720xd');
INSERT INTO `Dictionary` VALUES (11,1694,'yes','Dell PowerEdge%GPASS%R810');
INSERT INTO `Dictionary` VALUES (11,1695,'yes','Dell PowerEdge%GPASS%R815');
INSERT INTO `Dictionary` VALUES (11,1696,'yes','Dell PowerEdge (blade)%GPASS%M620');
INSERT INTO `Dictionary` VALUES (11,1697,'yes','Dell PowerEdge (blade)%GPASS%M710HD');
INSERT INTO `Dictionary` VALUES (11,1698,'yes','Dell PowerEdge (blade)%GPASS%M915%L2,1%');
INSERT INTO `Dictionary` VALUES (12,1699,'yes','Dell PowerConnect (blade)%GPASS%M6348');
INSERT INTO `Dictionary` VALUES (12,1700,'yes','Dell PowerConnect (blade)%GPASS%M8428');
INSERT INTO `Dictionary` VALUES (13,1701,'yes','[[RH Fedora%GSKIP%Fedora 16 | http://docs.fedoraproject.org/en-US/Fedora/16/html/Release_Notes/]]');
INSERT INTO `Dictionary` VALUES (12,1702,'yes','Dell PowerConnect%GPASS%8024');
INSERT INTO `Dictionary` VALUES (12,1703,'yes','Dell PowerConnect%GPASS%8024F');
INSERT INTO `Dictionary` VALUES (13,1704,'yes','Ubuntu%GSKIP%Ubuntu 10.04 LTS');
INSERT INTO `Dictionary` VALUES (13,1705,'yes','Ubuntu%GSKIP%Ubuntu 10.10');
INSERT INTO `Dictionary` VALUES (13,1706,'yes','Ubuntu%GSKIP%Ubuntu 11.04');
INSERT INTO `Dictionary` VALUES (13,1707,'yes','Ubuntu%GSKIP%Ubuntu 11.10');
INSERT INTO `Dictionary` VALUES (13,1708,'yes','Ubuntu%GSKIP%Ubuntu 12.04 LTS');
INSERT INTO `Dictionary` VALUES (13,1709,'yes','[[Debian%GSKIP%Debian 7.0 (wheezy) | http://debian.org/releases/wheezy/]]');
INSERT INTO `Dictionary` VALUES (12,1710,'yes','Cisco%GPASS%Catalyst 2960-24TC-L');
INSERT INTO `Dictionary` VALUES (12,1711,'yes','HP ProCurve%GPASS%2520-24-PoE J9138A');
INSERT INTO `Dictionary` VALUES (11,1712,'yes','Dell PowerEdge%GPASS%R210 II');
INSERT INTO `Dictionary` VALUES (13,1713,'yes','[[OpenBSD%GSKIP%OpenBSD 4.6 | http://www.openbsd.org/46.html]]');
INSERT INTO `Dictionary` VALUES (13,1714,'yes','[[OpenBSD%GSKIP%OpenBSD 4.7 | http://www.openbsd.org/47.html]]');
INSERT INTO `Dictionary` VALUES (13,1715,'yes','[[OpenBSD%GSKIP%OpenBSD 4.8 | http://www.openbsd.org/48.html]]');
INSERT INTO `Dictionary` VALUES (13,1716,'yes','[[OpenBSD%GSKIP%OpenBSD 4.9 | http://www.openbsd.org/49.html]]');
INSERT INTO `Dictionary` VALUES (13,1717,'yes','[[OpenBSD%GSKIP%OpenBSD 5.0 | http://www.openbsd.org/50.html]]');
INSERT INTO `Dictionary` VALUES (13,1718,'yes','[[OpenBSD%GSKIP%OpenBSD 5.1 | http://www.openbsd.org/51.html]]');
INSERT INTO `Dictionary` VALUES (12,1719,'yes','Cisco%GPASS%Catalyst 2924M-XL');
INSERT INTO `Dictionary` VALUES (12,1720,'yes','Allied Telesis%GPASS%AT9924T');
INSERT INTO `Dictionary` VALUES (12,1721,'yes','Cisco%GPASS%Catalyst 3560E-12SD');
INSERT INTO `Dictionary` VALUES (12,1722,'yes','Arista%GPASS%7148S');
INSERT INTO `Dictionary` VALUES (12,1723,'yes','Arista%GPASS%7124SX');
INSERT INTO `Dictionary` VALUES (12,1724,'yes','Arista%GPASS%7148SX');
INSERT INTO `Dictionary` VALUES (12,1725,'yes','Arista%GPASS%7124FX');
INSERT INTO `Dictionary` VALUES (12,1726,'yes','Arista%GPASS%7048T-A');
INSERT INTO `Dictionary` VALUES (12,1727,'yes','Arista%GPASS%7050T-64');
INSERT INTO `Dictionary` VALUES (12,1728,'yes','Arista%GPASS%7050T-52');
INSERT INTO `Dictionary` VALUES (12,1729,'yes','Arista%GPASS%7050Q-16');
INSERT INTO `Dictionary` VALUES (12,1730,'yes','Arista%GPASS%7050S-64');
INSERT INTO `Dictionary` VALUES (12,1731,'yes','Arista%GPASS%7050S-52');
INSERT INTO `Dictionary` VALUES (13,1732,'yes','[[RH Fedora%GSKIP%Fedora 17 | http://docs.fedoraproject.org/en-US/Fedora/17/html/Release_Notes/]]');
INSERT INTO `Dictionary` VALUES (13,1733,'yes','OpenSUSE%GSKIP%openSUSE 12.x');
INSERT INTO `Dictionary` VALUES (13,1734,'yes','FreeBSD%GSKIP%FreeBSD 9.x');
INSERT INTO `Dictionary` VALUES (31,1735,'yes','Cisco%GPASS%UCS 5108 Blade Chassis%L4,2H%');
INSERT INTO `Dictionary` VALUES (11,1736,'yes','Cisco%GPASS%UCS B200 M1');
INSERT INTO `Dictionary` VALUES (11,1737,'yes','Cisco%GPASS%UCS B200 M2');
INSERT INTO `Dictionary` VALUES (11,1738,'yes','Cisco%GPASS%UCS B200 M3');
INSERT INTO `Dictionary` VALUES (11,1739,'yes','Cisco%GPASS%UCS B230 M1');
INSERT INTO `Dictionary` VALUES (11,1740,'yes','Cisco%GPASS%UCS B230 M2');
INSERT INTO `Dictionary` VALUES (11,1741,'yes','Cisco%GPASS%UCS B250 M1');
INSERT INTO `Dictionary` VALUES (11,1742,'yes','Cisco%GPASS%UCS B250 M2');
INSERT INTO `Dictionary` VALUES (11,1743,'yes','Cisco%GPASS%UCS B440 M2');
INSERT INTO `Dictionary` VALUES (11,1744,'yes','Cisco%GPASS%UCS B420 M3');
INSERT INTO `Dictionary` VALUES (11,1745,'yes','Cisco%GPASS%UCS B22  M3');
INSERT INTO `Dictionary` VALUES (11,1746,'yes','Cisco%GPASS%UCS C200 M2');
INSERT INTO `Dictionary` VALUES (11,1747,'yes','Cisco%GPASS%UCS C210 M2');
INSERT INTO `Dictionary` VALUES (11,1748,'yes','Cisco%GPASS%UCS C250 M2');
INSERT INTO `Dictionary` VALUES (11,1749,'yes','Cisco%GPASS%UCS C260 M2');
INSERT INTO `Dictionary` VALUES (11,1750,'yes','Cisco%GPASS%UCS C460 M2');
INSERT INTO `Dictionary` VALUES (11,1751,'yes','Cisco%GPASS%UCS C22  M3');
INSERT INTO `Dictionary` VALUES (11,1752,'yes','Cisco%GPASS%UCS C220 M3');
INSERT INTO `Dictionary` VALUES (11,1753,'yes','Cisco%GPASS%UCS C24  M3');
INSERT INTO `Dictionary` VALUES (11,1754,'yes','Cisco%GPASS%UCS C240 M3');
INSERT INTO `Dictionary` VALUES (12,1755,'yes','Cisco%GPASS%UCS 6120 Fabric Interconnect');
INSERT INTO `Dictionary` VALUES (12,1756,'yes','Cisco%GPASS%UCS 6140 Fabric Interconnect');
INSERT INTO `Dictionary` VALUES (12,1757,'yes','Cisco%GPASS%UCS 6248 Fabric Interconnect');
INSERT INTO `Dictionary` VALUES (12,1758,'yes','Cisco%GPASS%UCS 6296 Fabric Interconnect');
INSERT INTO `Dictionary` VALUES (16,1759,'yes','Cisco IOS XR 4.2');
INSERT INTO `Dictionary` VALUES (12,1760,'yes','Huawei%GPASS%S3700-28TP-EI-24S');
INSERT INTO `Dictionary` VALUES (12,1761,'yes','Huawei%GPASS%S3700-28TP-EI-MC-AC');
INSERT INTO `Dictionary` VALUES (12,1762,'yes','Huawei%GPASS%S3700-28TP-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1763,'yes','Huawei%GPASS%S5700-28C-HI');
INSERT INTO `Dictionary` VALUES (12,1764,'yes','Huawei%GPASS%S5700-28C-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1765,'yes','Huawei%GPASS%S5700-48TP-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1766,'yes','Huawei%GPASS%S5710-52C-EI');
INSERT INTO `Dictionary` VALUES (12,1767,'yes','Huawei%GPASS%S5700-52P-PWR-LI');
INSERT INTO `Dictionary` VALUES (12,1768,'yes','Huawei%GPASS%S5700S-28P-LI');
INSERT INTO `Dictionary` VALUES (12,1769,'yes','Huawei%GPASS%CE5850-48T4S2Q-EI');
INSERT INTO `Dictionary` VALUES (12,1770,'yes','Huawei%GPASS%S6700-24-EI');
INSERT INTO `Dictionary` VALUES (12,1771,'yes','Huawei%GPASS%S6700-48-EI');
INSERT INTO `Dictionary` VALUES (12,1772,'yes','Huawei%GPASS%CE6850-48S4Q-EI');
INSERT INTO `Dictionary` VALUES (12,1773,'yes','Huawei%GPASS%CE6850-48T4Q-EI');
INSERT INTO `Dictionary` VALUES (12,1774,'yes','Huawei%GPASS%S7703');
INSERT INTO `Dictionary` VALUES (12,1775,'yes','Huawei%GPASS%S7706');
INSERT INTO `Dictionary` VALUES (12,1776,'yes','Huawei%GPASS%S7712');
INSERT INTO `Dictionary` VALUES (12,1777,'yes','Huawei%GPASS%S9703');
INSERT INTO `Dictionary` VALUES (12,1778,'yes','Huawei%GPASS%S9706');
INSERT INTO `Dictionary` VALUES (12,1779,'yes','Huawei%GPASS%S9712');
INSERT INTO `Dictionary` VALUES (12,1780,'yes','Huawei%GPASS%CE12804');
INSERT INTO `Dictionary` VALUES (12,1781,'yes','Huawei%GPASS%CE12808');
INSERT INTO `Dictionary` VALUES (12,1782,'yes','Huawei%GPASS%CE12812');
INSERT INTO `Dictionary` VALUES (12,1783,'yes','Cisco%GPASS%SG 300-52');
INSERT INTO `Dictionary` VALUES (12,1784,'yes','Cisco%GPASS%SF 300-24P');
INSERT INTO `Dictionary` VALUES (12,1785,'yes','Cisco%GPASS%SG 300-10');
INSERT INTO `Dictionary` VALUES (14,1786,'yes','Marvell ROS 1.1');
INSERT INTO `Dictionary` VALUES (1,1787,'yes','Management interface');
INSERT INTO `Dictionary` VALUES (38,1788,'yes','Cisco%GPASS%UCS Domain');
INSERT INTO `Dictionary` VALUES (38,1789,'yes','Generic%GPASS%Switch stack');
INSERT INTO `Dictionary` VALUES (38,1790,'yes','VMware%GPASS%vSphere instance');
INSERT INTO `Dictionary` VALUES (12,1791,'yes','Dell PowerConnect%GPASS%5524');
INSERT INTO `Dictionary` VALUES (12,1792,'yes','Dell PowerConnect%GPASS%5548');
INSERT INTO `Dictionary` VALUES (12,1793,'yes','TPLink%GPASS%TL-SG5426');
INSERT INTO `Dictionary` VALUES (12,1794,'yes','NETGEAR%GPASS%GSM7352Sv2');
INSERT INTO `Dictionary` VALUES (25,1795,'yes','Motorola%GPASS%RFS 4000');
INSERT INTO `Dictionary` VALUES (12,1796,'yes','Cisco%GPASS%Catalyst 2948G-L3');
INSERT INTO `Dictionary` VALUES (12,1797,'yes','D-Link%GPASS%DGS-1210-10P');
INSERT INTO `Dictionary` VALUES (12,1798,'yes','D-Link%GPASS%DGS-1210-16');
INSERT INTO `Dictionary` VALUES (12,1799,'yes','D-Link%GPASS%DGS-1210-24');
INSERT INTO `Dictionary` VALUES (12,1800,'yes','D-Link%GPASS%DGS-1210-48');
INSERT INTO `Dictionary` VALUES (12,1801,'yes','Extreme Networks%GPASS%Summit X670-48x');
INSERT INTO `Dictionary` VALUES (12,1802,'yes','Extreme Networks%GPASS%Summit X670V-48x');
INSERT INTO `Dictionary` VALUES (12,1803,'yes','Extreme Networks%GPASS%Summit X670V-48t');
INSERT INTO `Dictionary` VALUES (12,1804,'yes','Cisco%GPASS%Catalyst 3560V2-24TS');
INSERT INTO `Dictionary` VALUES (12,1805,'yes','Cisco%GPASS%Catalyst 3560V2-48TS');
INSERT INTO `Dictionary` VALUES (12,1806,'yes','Cisco%GPASS%Catalyst 3560V2-24PS');
INSERT INTO `Dictionary` VALUES (12,1807,'yes','Cisco%GPASS%Catalyst 3560V2-48PS');
INSERT INTO `Dictionary` VALUES (12,1808,'yes','Cisco%GPASS%Catalyst 3560V2-24TS-SD');
INSERT INTO `Dictionary` VALUES (14,1809,'yes','Cisco NX-OS 5.2');
INSERT INTO `Dictionary` VALUES (12,1810,'yes','NEC%GPASS%PF5240');
INSERT INTO `Dictionary` VALUES (12,1811,'yes','NEC%GPASS%PF5820');
INSERT INTO `Dictionary` VALUES (13,1812,'yes','MicroSoft%GSKIP%Windows Server 2008 R2');
INSERT INTO `Dictionary` VALUES (13,1813,'yes','Ubuntu%GSKIP%Ubuntu 12.10');
INSERT INTO `Dictionary` VALUES (11,1814,'yes','[[Fujitsu%GSKIP%PRIMERGY RX100 S7 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx100/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1815,'yes','[[Fujitsu%GSKIP%PRIMERGY RX200 S7 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx200/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1816,'yes','[[Fujitsu%GSKIP%PRIMERGY RX300 S6 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx300/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1817,'yes','[[Fujitsu%GSKIP%PRIMERGY RX300 S7 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx300/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1818,'yes','[[Fujitsu%GSKIP%PRIMERGY RX350 S7 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx350/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1819,'yes','[[Fujitsu%GSKIP%PRIMERGY RX500 S7 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx500/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1820,'yes','[[Fujitsu%GSKIP%PRIMERGY RX600 S6 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx600/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1821,'yes','[[Fujitsu%GSKIP%PRIMERGY RX900 S2 | http://www.fujitsu.com/fts/products/computing/servers/primergy/rack/rx900/index.html]]');
INSERT INTO `Dictionary` VALUES (31,1822,'yes','[[Fujitsu%GSKIP%PRIMERGY BX400 S1%L1,9V% | http://www.fujitsu.com/fts/products/computing/servers/primergy/blades/bx400/index.html]]');
INSERT INTO `Dictionary` VALUES (31,1823,'yes','[[Fujitsu%GSKIP%PRIMERGY BX900 S2%L2,9V% | http://www.fujitsu.com/fts/products/computing/servers/primergy/blades/bx900s2/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1824,'yes','[[IBM xSeries%GPASS%x3690 X5 | http://www-03.ibm.com/systems/x/hardware/enterprise/x3690x5/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1825,'yes','[[IBM xSeries%GPASS%x3850 X5 | http://www-03.ibm.com/systems/x/hardware/enterprise/x3850x5/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1826,'yes','[[IBM xSeries%GPASS%x3950 X5 | http://www-03.ibm.com/systems/x/hardware/enterprise/x3850x5/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1827,'yes','[[IBM xSeries%GPASS%x3750 M4 | http://www-03.ibm.com/systems/x/hardware/rack/x3750m4/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1828,'yes','[[IBM xSeries%GPASS%x3650 M4 | http://www-03.ibm.com/systems/x/hardware/rack/x3650m4/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1829,'yes','[[IBM xSeries%GPASS%x3630 M4 | http://www-03.ibm.com/systems/x/hardware/rack/x3630m4/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1830,'yes','[[IBM xSeries%GPASS%x3550 M4 | http://www-03.ibm.com/systems/x/hardware/rack/x3550m4/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1831,'yes','[[IBM xSeries%GPASS%x3530 M4 | http://www-03.ibm.com/systems/x/hardware/rack/x3530m4/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1832,'yes','[[IBM xSeries%GPASS%x3250 M4 | http://www-03.ibm.com/systems/x/hardware/rack/x3250m4/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1833,'yes','[[IBM xSeries%GPASS%x3755 M3 | http://www-03.ibm.com/systems/x/hardware/rack/x3755m3/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1834,'yes','[[IBM xSeries%GPASS%x3650 M3 | http://www-03.ibm.com/systems/x/hardware/rack/x3650m3/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1835,'yes','[[IBM xSeries%GPASS%x3630 M3 | http://www-03.ibm.com/systems/x/hardware/rack/x3630m3/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1836,'yes','[[IBM xSeries%GPASS%x3620 M3 | http://www-03.ibm.com/systems/x/hardware/rack/x3620m3/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1837,'yes','[[IBM xSeries%GPASS%x3550 M3 | http://www-03.ibm.com/systems/x/hardware/rack/x3550m3/index.html]]');
INSERT INTO `Dictionary` VALUES (11,1838,'yes','[[IBM xSeries%GPASS%x3250 M3 | http://www-03.ibm.com/systems/x/hardware/rack/x3250m3/index.html]]');
INSERT INTO `Dictionary` VALUES (21,1839,'yes','[[Avocent%GPASS%AutoView 3008 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentAutoViewAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (21,1840,'yes','[[Avocent%GPASS%AutoView 3016 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/DigitalKVMAppliances/Pages/AvocentAutoViewAppliances.aspx]]');
INSERT INTO `Dictionary` VALUES (23,1841,'yes','[[Avocent%GPASS%ECS19PWRUSB | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/LCDConsoleTrays/Pages/AvocentLCDConsoleTray.aspx]]');
INSERT INTO `Dictionary` VALUES (23,1842,'yes','[[Avocent%GPASS%ECS19UWRUSB | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/LCDConsoleTrays/Pages/AvocentLCDConsoleTray.aspx]]');
INSERT INTO `Dictionary` VALUES (23,1843,'yes','[[Avocent%GPASS%ECS17KMMP | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/LCDConsoleTrays/Pages/AvocentLCDConsoleTray.aspx]]');
INSERT INTO `Dictionary` VALUES (23,1844,'yes','[[Avocent%GPASS%ECS17KMM | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/LCDConsoleTrays/Pages/AvocentLCDConsoleTray.aspx]]');
INSERT INTO `Dictionary` VALUES (23,1845,'yes','[[Avocent%GPASS%AP17KMMP | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/LCDConsoleTrays/Pages/AvocentLCDConsoleTray.aspx]]');
INSERT INTO `Dictionary` VALUES (23,1846,'yes','[[Avocent%GPASS%LCD17 | http://www.emersonnetworkpower.com/en-US/Products/InfrastructureManagement/LCDConsoleTrays/Pages/AvocentLCDConsoleTray.aspx]]');
INSERT INTO `Dictionary` VALUES (21,1847,'yes','[[APC%GPASS%AP5201 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP5201]]');
INSERT INTO `Dictionary` VALUES (21,1848,'yes','[[APC%GPASS%AP5202 | http://www.apc.com/products/resource/include/techspec_index.cfm?base_sku=AP5202]]');
INSERT INTO `Dictionary` VALUES (35,1849,'yes','[[Cisco%GPASS%RPS 2300 | http://www.cisco.com/en/US/products/ps7130/index.html]]');
INSERT INTO `Dictionary` VALUES (19,1850,'yes','[[NEC%GPASS%LL009F | http://www.nec.com/en/global/prod/tapestorage/index_009.html]]');
INSERT INTO `Dictionary` VALUES (19,1851,'yes','[[NEC%GPASS%T30A | http://www.nec.com/en/global/prod/tapestorage/index_t30a.html]]');
INSERT INTO `Dictionary` VALUES (19,1852,'yes','[[NEC%GPASS%T60A | http://www.nec.com/en/global/prod/tapestorage/index_t60a.html]]');
INSERT INTO `Dictionary` VALUES (18,1853,'yes','[[Infortrend%GPASS%ES F16F-R4031 | http://www.infortrend.com/global/products/models/ES%20F16F-R4031]]');
INSERT INTO `Dictionary` VALUES (18,1854,'yes','[[Infortrend%GPASS%ES F16F-R4840 | http://www.infortrend.com/global/products/models/ES%20F16F-R4840]]');
INSERT INTO `Dictionary` VALUES (18,1855,'yes','[[Infortrend%GPASS%ES F16F-S4031 | http://www.infortrend.com/global/products/models/ES%20F16F-S4031]]');
INSERT INTO `Dictionary` VALUES (18,1856,'yes','[[Infortrend%GPASS%ES F16F-S4840 | http://www.infortrend.com/global/products/models/ES%20F16F-S4840]]');
INSERT INTO `Dictionary` VALUES (18,1857,'yes','[[Infortrend%GPASS%ES S24F-G1440 | http://www.infortrend.com/global/products/models/ES%20S24F-G1440]]');
INSERT INTO `Dictionary` VALUES (18,1858,'yes','[[Infortrend%GPASS%ES S24F-G1840 | http://www.infortrend.com/global/products/models/ES%20S24F-G1840]]');
INSERT INTO `Dictionary` VALUES (18,1859,'yes','[[Infortrend%GPASS%ES S24F-R1440 | http://www.infortrend.com/global/products/models/ES%20S24F-R1440]]');
INSERT INTO `Dictionary` VALUES (18,1860,'yes','[[Infortrend%GPASS%ES S24F-R1840 | http://www.infortrend.com/global/products/models/ES%20S24F-R1840]]');
INSERT INTO `Dictionary` VALUES (18,1861,'yes','[[Infortrend%GPASS%ES S16F-G1840 | http://www.infortrend.com/global/products/models/ES%20S16F-G1840]]');
INSERT INTO `Dictionary` VALUES (18,1862,'yes','[[Infortrend%GPASS%ES S16F-R1840 | http://www.infortrend.com/global/products/models/ES%20S16F-R1840]]');
INSERT INTO `Dictionary` VALUES (18,1863,'yes','[[Infortrend%GPASS%ES S12F-G1842 | http://www.infortrend.com/global/products/models/ES%20S12F-G1842]]');
INSERT INTO `Dictionary` VALUES (18,1864,'yes','[[Infortrend%GPASS%ES S12F-R1840 | http://www.infortrend.com/global/products/models/ES%20S12F-R1840]]');
INSERT INTO `Dictionary` VALUES (18,1865,'yes','[[Infortrend%GPASS%ES A08F-G2422 | http://www.infortrend.com/global/products/models/ES%20A08F-G2422]]');
INSERT INTO `Dictionary` VALUES (18,1866,'yes','[[Infortrend%GPASS%ES A12F-G2422 | http://www.infortrend.com/global/products/models/ES%20A12F-G2422]]');
INSERT INTO `Dictionary` VALUES (18,1867,'yes','[[Infortrend%GPASS%ES A12E-G2121 | http://www.infortrend.com/global/products/models/ES%20A12E-G2121]]');
INSERT INTO `Dictionary` VALUES (18,1868,'yes','[[Infortrend%GPASS%ES A08S-C2133 | http://www.infortrend.com/global/products/models/ES%20A08S-C2133]]');
INSERT INTO `Dictionary` VALUES (18,1869,'yes','[[Infortrend%GPASS%ES A08S-C2134 | http://www.infortrend.com/global/products/models/ES%20A08S-C2134]]');
INSERT INTO `Dictionary` VALUES (18,1870,'yes','[[Infortrend%GPASS%ES A16S-G2130 | http://www.infortrend.com/global/products/models/ES%20A16S-G2130]]');
INSERT INTO `Dictionary` VALUES (18,1871,'yes','[[Infortrend%GPASS%ES A08S-G2130 | http://www.infortrend.com/global/products/models/ES%20A08S-G2130]]');
INSERT INTO `Dictionary` VALUES (18,1872,'yes','[[Infortrend%GPASS%ES A12S-G2130 | http://www.infortrend.com/global/products/models/ES%20A12S-G2130]]');
INSERT INTO `Dictionary` VALUES (18,1873,'yes','[[Infortrend%GPASS%ES S16U-G1440 | http://www.infortrend.com/global/products/models/ES%20S16U-G1440]]');
INSERT INTO `Dictionary` VALUES (18,1874,'yes','[[Infortrend%GPASS%ES S12U-G1440 | http://www.infortrend.com/global/products/models/ES%20S12U-G1440]]');
INSERT INTO `Dictionary` VALUES (18,1875,'yes','[[Infortrend%GPASS%ES A24U-G2421 | http://www.infortrend.com/global/products/models/ES%20A24U-G2421]]');
INSERT INTO `Dictionary` VALUES (18,1876,'yes','[[Infortrend%GPASS%ES A08U-G2421 | http://www.infortrend.com/global/products/models/ES%20A08U-G2421]]');
INSERT INTO `Dictionary` VALUES (18,1877,'yes','[[Infortrend%GPASS%ES A12U-G2421 | http://www.infortrend.com/global/products/models/ES%20A12U-G2421]]');
INSERT INTO `Dictionary` VALUES (18,1878,'yes','[[Infortrend%GPASS%ES A04U-G2421 | http://www.infortrend.com/global/products/models/ES%20A04U-G2421]]');
INSERT INTO `Dictionary` VALUES (18,1879,'yes','[[Infortrend%GPASS%ES F16F-J4000-R | http://www.infortrend.com/global/products/models/ES%20F16F-J4000-R]]');
INSERT INTO `Dictionary` VALUES (18,1880,'yes','[[Infortrend%GPASS%ES B12S-J1000-R | http://www.infortrend.com/global/products/models/ES%20B12S-J1000-R]]');
INSERT INTO `Dictionary` VALUES (18,1881,'yes','[[Infortrend%GPASS%ES B12S-J1000-S | http://www.infortrend.com/global/products/models/ES%20B12S-J1000-S]]');
INSERT INTO `Dictionary` VALUES (18,1882,'yes','[[Infortrend%GPASS%ES A16F-J2430-G | http://www.infortrend.com/global/products/models/ES%20A16F-J2430-G]]');
INSERT INTO `Dictionary` VALUES (18,1883,'yes','[[Infortrend%GPASS%ES S16S-J1000-R1 | http://www.infortrend.com/global/products/models/ES%20S16S-J1000-R1]]');
INSERT INTO `Dictionary` VALUES (18,1884,'yes','[[Infortrend%GPASS%ES S16S-J1000-S1 | http://www.infortrend.com/global/products/models/ES%20S16S-J1000-S1]]');
INSERT INTO `Dictionary` VALUES (18,1885,'yes','[[Infortrend%GPASS%ES S12S-J1000-G | http://www.infortrend.com/global/products/models/ES%20S12S-J1000-G]]');
INSERT INTO `Dictionary` VALUES (18,1886,'yes','[[Infortrend%GPASS%ES S12S-J1002-R | http://www.infortrend.com/global/products/models/ES%20S12S-J1002-R]]');
INSERT INTO `Dictionary` VALUES (12,1887,'yes','[[IBM%GPASS%RackSwitch G8052 | http://www-03.ibm.com/systems/networking/switches/rack/g8052/]]');
INSERT INTO `Dictionary` VALUES (12,1888,'yes','[[IBM%GPASS%RackSwitch G8264 | http://www-03.ibm.com/systems/networking/switches/rack/g8264/]]');
INSERT INTO `Dictionary` VALUES (12,1889,'yes','[[Brocade%GPASS%ICX-6610-48-PE | http://www.brocade.com/products/all/switches/product-details/icx-6610-switch/index.page]]');
INSERT INTO `Dictionary` VALUES (12,1890,'yes','[[Brocade%GPASS%ICX-6650-48-E-ADV | http://www.brocade.com/products/all/switches/product-details/icx-6650-switch/index.page]]');
INSERT INTO `Dictionary` VALUES (12,1891,'yes','[[Fortinet%GPASS%Fortigate 3140B| http://www.fortinet.com/products/fortigate/3140B.html]]');
INSERT INTO `Dictionary` VALUES (12,1892,'yes','[[Fortinet%GPASS%Fortigate 300C | http://www.fortinet.com/products/fortigate/300C.html]]');
INSERT INTO `Dictionary` VALUES (12,1893,'yes','[[Fortinet%GPASS%Fortigate 800C | http://www.fortinet.com/products/fortigate/800C.html]]');
INSERT INTO `Dictionary` VALUES (12,1894,'yes','Cisco%GPASS%Catalyst 2960-24TC-S');
INSERT INTO `Dictionary` VALUES (12,1895,'yes','Cisco%GPASS%Catalyst 2960-24LT-L');
INSERT INTO `Dictionary` VALUES (12,1896,'yes','Cisco%GPASS%Catalyst 2960-48PST-S');
INSERT INTO `Dictionary` VALUES (12,1897,'yes','Cisco%GPASS%Catalyst 2960-24PC-S');
INSERT INTO `Dictionary` VALUES (12,1898,'yes','Cisco%GPASS%Catalyst 2960-24LC-S');
INSERT INTO `Dictionary` VALUES (12,1899,'yes','Cisco%GPASS%Catalyst 2960-8TC-S');
INSERT INTO `Dictionary` VALUES (12,1900,'yes','Cisco%GPASS%Catalyst 2960PD-8TT-L');
INSERT INTO `Dictionary` VALUES (14,1901,'yes','Cisco IOS 15.0');
INSERT INTO `Dictionary` VALUES (12,1902,'yes','Cisco%GPASS%Catalyst 2960S-F48FPS-L');
INSERT INTO `Dictionary` VALUES (12,1903,'yes','Cisco%GPASS%Catalyst 2960S-F48LPS-L');
INSERT INTO `Dictionary` VALUES (12,1904,'yes','Cisco%GPASS%Catalyst 2960S-F24PS-L');
INSERT INTO `Dictionary` VALUES (12,1905,'yes','Cisco%GPASS%Catalyst 2960S-F48TS-L');
INSERT INTO `Dictionary` VALUES (12,1906,'yes','Cisco%GPASS%Catalyst 2960S-F24TS-L');
INSERT INTO `Dictionary` VALUES (12,1907,'yes','Cisco%GPASS%Catalyst 2960S-F48TS-S');
INSERT INTO `Dictionary` VALUES (12,1908,'yes','Cisco%GPASS%Catalyst 2960S-F24TS-S');
INSERT INTO `Dictionary` VALUES (12,1909,'yes','Huawei%GPASS%S2700-18TP-EI');
INSERT INTO `Dictionary` VALUES (12,1910,'yes','Huawei%GPASS%S2700-18TP-SI');
INSERT INTO `Dictionary` VALUES (12,1911,'yes','Huawei%GPASS%S2700-26TP-EI');
INSERT INTO `Dictionary` VALUES (12,1912,'yes','Huawei%GPASS%S2700-26TP-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1913,'yes','Huawei%GPASS%S2700-26TP-SI');
INSERT INTO `Dictionary` VALUES (12,1914,'yes','Huawei%GPASS%S2700-52P-EI');
INSERT INTO `Dictionary` VALUES (12,1915,'yes','Huawei%GPASS%S2700-52P-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1916,'yes','Huawei%GPASS%S2700-9TP-EI');
INSERT INTO `Dictionary` VALUES (12,1917,'yes','Huawei%GPASS%S2700-9TP-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1918,'yes','Huawei%GPASS%S2700-9TP-SI');
INSERT INTO `Dictionary` VALUES (12,1919,'yes','Huawei%GPASS%S2710-52P-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1920,'yes','Huawei%GPASS%S2710-52P-SI');
INSERT INTO `Dictionary` VALUES (12,1921,'yes','Huawei%GPASS%S3700-26C-HI');
INSERT INTO `Dictionary` VALUES (12,1922,'yes','Huawei%GPASS%S3700-28TP-EI');
INSERT INTO `Dictionary` VALUES (12,1923,'yes','Huawei%GPASS%S3700-28TP-EI-MC');
INSERT INTO `Dictionary` VALUES (12,1924,'yes','Huawei%GPASS%S3700-28TP-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1925,'yes','Huawei%GPASS%S3700-28TP-SI');
INSERT INTO `Dictionary` VALUES (12,1926,'yes','Huawei%GPASS%S3700-52P-EI');
INSERT INTO `Dictionary` VALUES (12,1927,'yes','Huawei%GPASS%S3700-52P-EI-24S');
INSERT INTO `Dictionary` VALUES (12,1928,'yes','Huawei%GPASS%S3700-52P-EI-48S');
INSERT INTO `Dictionary` VALUES (12,1929,'yes','Huawei%GPASS%S3700-52P-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1930,'yes','Huawei%GPASS%S3700-52P-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1931,'yes','Huawei%GPASS%S3700-52P-SI');
INSERT INTO `Dictionary` VALUES (12,1932,'yes','Huawei%GPASS%S5700-10P-LI');
INSERT INTO `Dictionary` VALUES (12,1933,'yes','Huawei%GPASS%S5700-10P-PWR-LI');
INSERT INTO `Dictionary` VALUES (12,1934,'yes','Huawei%GPASS%S5700-24TP-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1935,'yes','Huawei%GPASS%S5700-24TP-SI');
INSERT INTO `Dictionary` VALUES (12,1936,'yes','Huawei%GPASS%S5700-26X-SI-12S');
INSERT INTO `Dictionary` VALUES (12,1937,'yes','Huawei%GPASS%S5700-28C-EI');
INSERT INTO `Dictionary` VALUES (12,1938,'yes','Huawei%GPASS%S5700-28C-EI-24S');
INSERT INTO `Dictionary` VALUES (12,1939,'yes','Huawei%GPASS%S5700-28C-HI-24S');
INSERT INTO `Dictionary` VALUES (12,1940,'yes','Huawei%GPASS%S5700-28C-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1941,'yes','Huawei%GPASS%S5700-28C-SI');
INSERT INTO `Dictionary` VALUES (12,1942,'yes','Huawei%GPASS%S5700-28P-LI');
INSERT INTO `Dictionary` VALUES (12,1943,'yes','Huawei%GPASS%S5700-28P-PWR-LI');
INSERT INTO `Dictionary` VALUES (12,1944,'yes','Huawei%GPASS%S5700-28X-LI');
INSERT INTO `Dictionary` VALUES (12,1945,'yes','Huawei%GPASS%S5700-28X-PWR-LI');
INSERT INTO `Dictionary` VALUES (12,1946,'yes','Huawei%GPASS%S5700-48TP-SI');
INSERT INTO `Dictionary` VALUES (12,1947,'yes','Huawei%GPASS%S5700-52C-EI');
INSERT INTO `Dictionary` VALUES (12,1948,'yes','Huawei%GPASS%S5700-52C-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1949,'yes','Huawei%GPASS%S5700-52C-PWR-SI');
INSERT INTO `Dictionary` VALUES (12,1950,'yes','Huawei%GPASS%S5700-52C-SI');
INSERT INTO `Dictionary` VALUES (12,1951,'yes','Huawei%GPASS%S5700-52P-LI');
INSERT INTO `Dictionary` VALUES (12,1952,'yes','Huawei%GPASS%S5700-52X-LI');
INSERT INTO `Dictionary` VALUES (12,1953,'yes','Huawei%GPASS%S5700-52X-PWR-LI');
INSERT INTO `Dictionary` VALUES (12,1954,'yes','Huawei%GPASS%S5700S-52P-LI');
INSERT INTO `Dictionary` VALUES (12,1955,'yes','Huawei%GPASS%S5710-28C-EI');
INSERT INTO `Dictionary` VALUES (12,1956,'yes','Huawei%GPASS%S5710-28C-LI');
INSERT INTO `Dictionary` VALUES (12,1957,'yes','Huawei%GPASS%S5710-28C-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1958,'yes','Huawei%GPASS%S5710-28C-PWR-LI');
INSERT INTO `Dictionary` VALUES (12,1959,'yes','Huawei%GPASS%S5710-52C-LI');
INSERT INTO `Dictionary` VALUES (12,1960,'yes','Huawei%GPASS%S5710-52C-PWR-EI');
INSERT INTO `Dictionary` VALUES (12,1961,'yes','Huawei%GPASS%S5710-52C-PWR-LI');
INSERT INTO `Dictionary` VALUES (12,1962,'yes','Force10%GPASS%S4820T');
INSERT INTO `Dictionary` VALUES (16,1963,'yes','Cisco IOS 15.1');
INSERT INTO `Dictionary` VALUES (25,1964,'yes','Cisco%GPASS%AIR-AP1041N');
INSERT INTO `Dictionary` VALUES (25,1965,'yes','Cisco%GPASS%AIR-AP1042N');
INSERT INTO `Dictionary` VALUES (12,1966,'yes','Linksys%GPASS%SRW224G4');
INSERT INTO `Dictionary` VALUES (12,1967,'yes','HP ProCurve%GPASS%2520-8-PoE J9137A');
INSERT INTO `Dictionary` VALUES (30,1968,'yes','Enterasys%GPASS%K6%L3,2H%');
INSERT INTO `Dictionary` VALUES (30,1969,'yes','Enterasys%GPASS%K10%L5,2H%');
INSERT INTO `Dictionary` VALUES (12,1970,'yes','Enterasys%GPASS%SSA130');
INSERT INTO `Dictionary` VALUES (12,1971,'yes','Enterasys%GPASS%SSA150');
INSERT INTO `Dictionary` VALUES (12,1972,'yes','Enterasys%GPASS%SSA180');
INSERT INTO `Dictionary` VALUES (30,1973,'yes','Enterasys%GPASS%S1%L1,1H%');
INSERT INTO `Dictionary` VALUES (30,1974,'yes','Enterasys%GPASS%S3%L3,1H%');
INSERT INTO `Dictionary` VALUES (30,1975,'yes','Enterasys%GPASS%S4%L4,1H%');
INSERT INTO `Dictionary` VALUES (30,1976,'yes','Enterasys%GPASS%S6%L6,1H%');
INSERT INTO `Dictionary` VALUES (30,1977,'yes','Enterasys%GPASS%S8%L8,1H%');
INSERT INTO `Dictionary` VALUES (12,1978,'yes','Enterasys%GPASS%A4H124-24');
INSERT INTO `Dictionary` VALUES (12,1979,'yes','Enterasys%GPASS%A4H124-24P');
INSERT INTO `Dictionary` VALUES (12,1980,'yes','Enterasys%GPASS%A4H124-24FX');
INSERT INTO `Dictionary` VALUES (12,1981,'yes','Enterasys%GPASS%A4H124-48');
INSERT INTO `Dictionary` VALUES (12,1982,'yes','Enterasys%GPASS%A4H124-48P');
INSERT INTO `Dictionary` VALUES (12,1983,'yes','Enterasys%GPASS%A4H254-8F8T');
INSERT INTO `Dictionary` VALUES (12,1984,'yes','Enterasys%GPASS%B5G124-24');
INSERT INTO `Dictionary` VALUES (12,1985,'yes','Enterasys%GPASS%B5G124-24P2');
INSERT INTO `Dictionary` VALUES (12,1986,'yes','Enterasys%GPASS%B5G124-48');
INSERT INTO `Dictionary` VALUES (12,1987,'yes','Enterasys%GPASS%B5G124-48P2');
INSERT INTO `Dictionary` VALUES (12,1988,'yes','Enterasys%GPASS%B5K125-24');
INSERT INTO `Dictionary` VALUES (12,1989,'yes','Enterasys%GPASS%B5K125-24P2');
INSERT INTO `Dictionary` VALUES (12,1990,'yes','Enterasys%GPASS%B5K125-48');
INSERT INTO `Dictionary` VALUES (12,1991,'yes','Enterasys%GPASS%B5K125-48P2');
INSERT INTO `Dictionary` VALUES (12,1992,'yes','Enterasys%GPASS%C5G124-24');
INSERT INTO `Dictionary` VALUES (12,1993,'yes','Enterasys%GPASS%C5G124-24P2');
INSERT INTO `Dictionary` VALUES (12,1994,'yes','Enterasys%GPASS%C5G124-48');
INSERT INTO `Dictionary` VALUES (12,1995,'yes','Enterasys%GPASS%C5G124-48P2');
INSERT INTO `Dictionary` VALUES (12,1996,'yes','Enterasys%GPASS%C5K125-24');
INSERT INTO `Dictionary` VALUES (12,1997,'yes','Enterasys%GPASS%C5K125-24P2');
INSERT INTO `Dictionary` VALUES (12,1998,'yes','Enterasys%GPASS%C5K125-48');
INSERT INTO `Dictionary` VALUES (12,1999,'yes','Enterasys%GPASS%C5K125-48P2');
INSERT INTO `Dictionary` VALUES (12,2000,'yes','Enterasys%GPASS%C5K175-24');
INSERT INTO `Dictionary` VALUES (12,2001,'yes','Enterasys%GPASS%7124-24');
INSERT INTO `Dictionary` VALUES (12,2002,'yes','Enterasys%GPASS%7124-24T');
INSERT INTO `Dictionary` VALUES (12,2003,'yes','Enterasys%GPASS%7124-48');
INSERT INTO `Dictionary` VALUES (12,2004,'yes','Enterasys%GPASS%7124-48T');
INSERT INTO `Dictionary` VALUES (12,2005,'yes','Enterasys%GPASS%08G20G2-08');
INSERT INTO `Dictionary` VALUES (12,2006,'yes','Enterasys%GPASS%08G20G2-08P');
INSERT INTO `Dictionary` VALUES (12,2007,'yes','Enterasys%GPASS%08G20G4-24');
INSERT INTO `Dictionary` VALUES (12,2008,'yes','Enterasys%GPASS%08G20G4-24P');
INSERT INTO `Dictionary` VALUES (12,2009,'yes','Enterasys%GPASS%08G20G4-48');
INSERT INTO `Dictionary` VALUES (12,2010,'yes','Enterasys%GPASS%08G20G4-48P');
INSERT INTO `Dictionary` VALUES (12,2011,'yes','Enterasys%GPASS%08H20G4-24');
INSERT INTO `Dictionary` VALUES (12,2012,'yes','Enterasys%GPASS%08H20G4-24P');
INSERT INTO `Dictionary` VALUES (12,2013,'yes','Enterasys%GPASS%08H20G4-48');
INSERT INTO `Dictionary` VALUES (12,2014,'yes','Enterasys%GPASS%08H20G4-48P');
INSERT INTO `Dictionary` VALUES (12,2015,'yes','Enterasys%GPASS%D2G124-12');
INSERT INTO `Dictionary` VALUES (12,2016,'yes','Enterasys%GPASS%D2G124-12P');
INSERT INTO `Dictionary` VALUES (12,2017,'yes','Enterasys%GPASS%G3G124-24');
INSERT INTO `Dictionary` VALUES (12,2018,'yes','Enterasys%GPASS%G3G124-24P');
INSERT INTO `Dictionary` VALUES (12,2019,'yes','Enterasys%GPASS%G3G170-24');
INSERT INTO `Dictionary` VALUES (30,2020,'yes','Enterasys%GPASS%N1%L1,1H%');
INSERT INTO `Dictionary` VALUES (30,2021,'yes','Enterasys%GPASS%N3%L3,1H%');
INSERT INTO `Dictionary` VALUES (30,2022,'yes','Enterasys%GPASS%N5%L5,1H%');
INSERT INTO `Dictionary` VALUES (30,2023,'yes','Enterasys%GPASS%N7%L1,7V%');
INSERT INTO `Dictionary` VALUES (25,2024,'yes','Cisco%GPASS%877');
INSERT INTO `Dictionary` VALUES (25,2025,'yes','Cisco%GPASS%878');
INSERT INTO `Dictionary` VALUES (12,2026,'yes','[[Cisco%GPASS%Catalyst 4948E | http://www.cisco.com/en/US/products/ps10947/index.html]]');
INSERT INTO `Dictionary` VALUES (14,2027,'yes','Huawei VRP 8.5');
INSERT INTO `Dictionary` VALUES (14,2028,'yes','Cisco NX-OS 6.1');
INSERT INTO `Dictionary` VALUES (12,2029,'yes','[[Cisco%GPASS%Catalyst C2960CG-8TC-L | http://www.cisco.com/en/US/products/ps6406/index.html]]');
INSERT INTO `Dictionary` VALUES (12,2030,'yes','Cisco%GPASS%ME-3400EG-2CS-A');
INSERT INTO `Dictionary` VALUES (11,2031,'yes','Dell PowerEdge (blade)%GPASS%M520');
INSERT INTO `Dictionary` VALUES (11,2032,'yes','Dell PowerEdge (blade)%GPASS%M820%L2,1%');
INSERT INTO `Dictionary` VALUES (30,2033,'yes','F5%GPASS%VIPRION 4480%L4,1H%');
INSERT INTO `Dictionary` VALUES (30,2034,'yes','F5%GPASS%VIPRION 4800%L1,8V%');
INSERT INTO `Dictionary` VALUES (12,2035,'yes','F5 (blade)%GPASS%VIPRION 2100');
INSERT INTO `Dictionary` VALUES (12,2036,'yes','F5 (blade)%GPASS%VIPRION 4200');
INSERT INTO `Dictionary` VALUES (12,2037,'yes','F5 (blade)%GPASS%VIPRION 4300');
INSERT INTO `Dictionary` VALUES (12,2038,'yes','[[Cisco%GPASS%Catalyst WS-CBS3012-IBM/-I | http://www.cisco.com/en/US/products/ps8766/index.html]]');
INSERT INTO `Dictionary` VALUES (17,2039,'yes','MikroTik%GPASS%RB1000U');
INSERT INTO `Dictionary` VALUES (17,2040,'yes','MikroTik%GPASS%RB1100');
INSERT INTO `Dictionary` VALUES (17,2041,'yes','MikroTik%GPASS%RB1100Hx2');
INSERT INTO `Dictionary` VALUES (17,2042,'yes','MikroTik%GPASS%RB1100AH');
INSERT INTO `Dictionary` VALUES (17,2043,'yes','MikroTik%GPASS%RB1100AHx2');
INSERT INTO `Dictionary` VALUES (17,2044,'yes','MikroTik%GPASS%RB1200');
INSERT INTO `Dictionary` VALUES (17,2045,'yes','MikroTik%GPASS%RB2011L-RM');
INSERT INTO `Dictionary` VALUES (17,2046,'yes','MikroTik%GPASS%RB2011iL-RM');
INSERT INTO `Dictionary` VALUES (17,2047,'yes','MikroTik%GPASS%RB2011UAS-RM');
INSERT INTO `Dictionary` VALUES (17,2048,'yes','MikroTik%GPASS%CCR1016-12G');
INSERT INTO `Dictionary` VALUES (17,2049,'yes','MikroTik%GPASS%CCR1036-8G-2S+');
INSERT INTO `Dictionary` VALUES (17,2050,'yes','MikroTik%GPASS%CCR1036-12G-4S');
INSERT INTO `Dictionary` VALUES (12,2051,'yes','Arista%GPASS%7150S-24');
INSERT INTO `Dictionary` VALUES (12,2052,'yes','Arista%GPASS%7150S-52');
INSERT INTO `Dictionary` VALUES (12,2053,'yes','Arista%GPASS%7150S-64');
INSERT INTO `Dictionary` VALUES (12,2054,'yes','Arista%GPASS%7100S');
INSERT INTO `Dictionary` VALUES (12,2055,'yes','Arista%GPASS%7050T-36');
INSERT INTO `Dictionary` VALUES (12,2056,'yes','Arista%GPASS%7050QX-32');
INSERT INTO `Dictionary` VALUES (12,2057,'yes','Arista%GPASS%7050SX-128');
INSERT INTO `Dictionary` VALUES (12,2058,'yes','[[TPLink%GPASS%TL-SL5428E | http://www.tp-link.com/en/products/details/?model=TL-SL5428E]]');
INSERT INTO `Dictionary` VALUES (12,2059,'yes','Cisco%GPASS%Catalyst 3560CG-8PC-S');
INSERT INTO `Dictionary` VALUES (13,2060,'yes','[[RH Fedora%GSKIP%Fedora 18 | http://docs.fedoraproject.org/en-US/Fedora/18/html/Release_Notes/]]');
INSERT INTO `Dictionary` VALUES (13,2061,'yes','[[RH Fedora%GSKIP%Fedora 19 | http://docs.fedoraproject.org/en-US/Fedora/19/html/Release_Notes/]]');
INSERT INTO `Dictionary` VALUES (12,2062,'yes','MikroTik%GPASS%CRS125-24G-1S-RM');
INSERT INTO `Dictionary` VALUES (13,2063,'yes','MicroSoft%GSKIP%Windows Server 2012');
INSERT INTO `Dictionary` VALUES (13,2064,'yes','MicroSoft%GSKIP%Windows Server 2012 R2');
INSERT INTO `Dictionary` VALUES (12,2065,'yes','Extreme Networks%GPASS%Summit X770');
INSERT INTO `Dictionary` VALUES (12,2066,'yes','Arista%GPASS%7504');
INSERT INTO `Dictionary` VALUES (12,2067,'yes','Arista%GPASS%7508');
INSERT INTO `Dictionary` VALUES (12,2068,'yes','Arista%GPASS%7304');
INSERT INTO `Dictionary` VALUES (12,2069,'yes','Arista%GPASS%7308');
INSERT INTO `Dictionary` VALUES (12,2070,'yes','Arista%GPASS%7316');
INSERT INTO `Dictionary` VALUES (12,2071,'yes','Arista%GPASS%7250QX-64');
INSERT INTO `Dictionary` VALUES (12,2072,'yes','Mellanox%GPASS%SX1012');
INSERT INTO `Dictionary` VALUES (12,2073,'yes','Mellanox%GPASS%SX1016');
INSERT INTO `Dictionary` VALUES (12,2074,'yes','Mellanox%GPASS%SX1024');
INSERT INTO `Dictionary` VALUES (12,2075,'yes','Mellanox%GPASS%SX1035');
INSERT INTO `Dictionary` VALUES (12,2076,'yes','Mellanox%GPASS%SX1036');
INSERT INTO `Dictionary` VALUES (12,2077,'yes','NEC%GPASS%PF5248');
INSERT INTO `Dictionary` VALUES (12,2078,'yes','Pica8%GPASS%P-3290');
INSERT INTO `Dictionary` VALUES (12,2079,'yes','Pica8%GPASS%P-3295');
INSERT INTO `Dictionary` VALUES (14,2080,'yes','Huawei VRP 5.11');
INSERT INTO `Dictionary` VALUES (14,2081,'yes','Huawei VRP 5.12');
INSERT INTO `Dictionary` VALUES (14,2082,'yes','Cisco IOS 15.1');
INSERT INTO `Dictionary` VALUES (17,2083,'yes','[[Cisco%GPASS%ASR 9001 | http://cisco.com/en/US/products/ps12074/index.html]]');
INSERT INTO `Dictionary` VALUES (17,2084,'yes','[[Cisco%GPASS%ASR 9922 | http://cisco.com/en/US/products/ps11755/index.html]]');
INSERT INTO `Dictionary` VALUES (12,2085,'yes','[[Cisco%GPASS%Nexus 6001 | http://www.cisco.com/en/US/products/ps12869/index.html]]');
INSERT INTO `Dictionary` VALUES (12,2086,'yes','[[Cisco%GPASS%Nexus 6004 | http://www.cisco.com/en/US/products/ps12807/index.html]]');
INSERT INTO `Dictionary` VALUES (12,2087,'yes','Huawei%GPASS%CE5810-48T4S-EI');
INSERT INTO `Dictionary` VALUES (12,2088,'yes','Huawei%GPASS%CE5810-24T4S-EI');
INSERT INTO `Dictionary` VALUES (12,2089,'yes','Pica8%GPASS%P-3780');
INSERT INTO `Dictionary` VALUES (12,2090,'yes','Pica8%GPASS%P-3922');
INSERT INTO `Dictionary` VALUES (12,2091,'yes','Cisco%GPASS%SF302-08MP');
INSERT INTO `Dictionary` VALUES (12,2092,'yes','3Com%GPASS%4510G 24-port');
INSERT INTO `Dictionary` VALUES (12,2093,'yes','Linksys%GPASS%SRW248G4');
INSERT INTO `Dictionary` VALUES (12,2094,'yes','Cisco%GPASS%Catalyst 1924');
INSERT INTO `Dictionary` VALUES (12,2095,'yes','Allied Telesis%GPASS%AT-GS950/24');
INSERT INTO `Dictionary` VALUES (12,2096,'yes','HP ProCurve%GPASS%2910-24G-PoE J9146A');
INSERT INTO `Dictionary` VALUES (12,2097,'yes','Dell PowerConnect%GPASS%8132');
INSERT INTO `Dictionary` VALUES (12,2098,'yes','Dell PowerConnect%GPASS%8132F');
INSERT INTO `Dictionary` VALUES (12,2099,'yes','Dell PowerConnect%GPASS%8164');
INSERT INTO `Dictionary` VALUES (12,2100,'yes','Dell PowerConnect%GPASS%8164F');
INSERT INTO `Dictionary` VALUES (13,2101,'yes','[[OpenBSD%GSKIP%OpenBSD 5.2 | http://www.openbsd.org/52.html]]');
INSERT INTO `Dictionary` VALUES (13,2102,'yes','[[OpenBSD%GSKIP%OpenBSD 5.3 | http://www.openbsd.org/53.html]]');
INSERT INTO `Dictionary` VALUES (13,2103,'yes','[[OpenBSD%GSKIP%OpenBSD 5.4 | http://www.openbsd.org/54.html]]');
INSERT INTO `Dictionary` VALUES (13,2104,'yes','FreeBSD%GSKIP%FreeBSD 10.x');
INSERT INTO `Dictionary` VALUES (13,2105,'yes','VMWare Hypervisor%GSKIP%VMware ESXi 5.1');
INSERT INTO `Dictionary` VALUES (13,2106,'yes','VMWare Hypervisor%GSKIP%VMware ESXi 5.5');
INSERT INTO `Dictionary` VALUES (13,2107,'yes','Ubuntu%GSKIP%Ubuntu 13.04');
INSERT INTO `Dictionary` VALUES (13,2108,'yes','Ubuntu%GSKIP%Ubuntu 13.10');
INSERT INTO `Dictionary` VALUES (13,2109,'yes','Ubuntu%GSKIP%Ubuntu 14.04 LTS');
INSERT INTO `Dictionary` VALUES (12,2110,'yes','Cisco%GPASS%Catalyst 2960X-48FPD-L');
INSERT INTO `Dictionary` VALUES (12,2111,'yes','Cisco%GPASS%Catalyst 2960X-48LPD-L');
INSERT INTO `Dictionary` VALUES (12,2112,'yes','Cisco%GPASS%Catalyst 2960X-24PD-L');
INSERT INTO `Dictionary` VALUES (12,2113,'yes','Cisco%GPASS%Catalyst 2960X-48TD-L');
INSERT INTO `Dictionary` VALUES (12,2114,'yes','Cisco%GPASS%Catalyst 2960X-24TD-L');
INSERT INTO `Dictionary` VALUES (12,2115,'yes','Cisco%GPASS%Catalyst 2960X-48FPS-L');
INSERT INTO `Dictionary` VALUES (12,2116,'yes','Cisco%GPASS%Catalyst 2960X-48LPS-L');
INSERT INTO `Dictionary` VALUES (12,2117,'yes','Cisco%GPASS%Catalyst 2960X-24PS-L');
INSERT INTO `Dictionary` VALUES (12,2118,'yes','Cisco%GPASS%Catalyst 2960X-24PSQ-L');
INSERT INTO `Dictionary` VALUES (12,2119,'yes','Cisco%GPASS%Catalyst 2960X-48TS-L');
INSERT INTO `Dictionary` VALUES (12,2120,'yes','Cisco%GPASS%Catalyst 2960X-24TS-L');
INSERT INTO `Dictionary` VALUES (12,2121,'yes','Cisco%GPASS%Catalyst 2960X-48TS-LL');
INSERT INTO `Dictionary` VALUES (12,2122,'yes','Cisco%GPASS%Catalyst 2960X-24TS-LL');
INSERT INTO `Dictionary` VALUES (12,2123,'yes','Cisco%GPASS%Catalyst 2960XR-48FPD-I');
INSERT INTO `Dictionary` VALUES (12,2124,'yes','Cisco%GPASS%Catalyst 2960XR-48LPD-I');
INSERT INTO `Dictionary` VALUES (12,2125,'yes','Cisco%GPASS%Catalyst 2960XR-24PD-I');
INSERT INTO `Dictionary` VALUES (12,2126,'yes','Cisco%GPASS%Catalyst 2960XR-48TD-I');
INSERT INTO `Dictionary` VALUES (12,2127,'yes','Cisco%GPASS%Catalyst 2960XR-24TD-I');
INSERT INTO `Dictionary` VALUES (12,2128,'yes','Cisco%GPASS%Catalyst 2960XR-48FPS-I');
INSERT INTO `Dictionary` VALUES (12,2129,'yes','Cisco%GPASS%Catalyst 2960XR-48LPS-I');
INSERT INTO `Dictionary` VALUES (12,2130,'yes','Cisco%GPASS%Catalyst 2960XR-24PS-I');
INSERT INTO `Dictionary` VALUES (12,2131,'yes','Cisco%GPASS%Catalyst 2960XR-48TS-I');
INSERT INTO `Dictionary` VALUES (12,2132,'yes','Cisco%GPASS%Catalyst 2960XR-24TS-I');
INSERT INTO `Dictionary` VALUES (12,2133,'yes','Cisco%GPASS%Catalyst 2960-Plus 48PST-L');
INSERT INTO `Dictionary` VALUES (12,2134,'yes','Cisco%GPASS%Catalyst 2960-Plus 24PC-L');
INSERT INTO `Dictionary` VALUES (12,2135,'yes','Cisco%GPASS%Catalyst 2960-Plus 24LC-L');
INSERT INTO `Dictionary` VALUES (12,2136,'yes','Cisco%GPASS%Catalyst 2960-Plus 48TC-L');
INSERT INTO `Dictionary` VALUES (12,2137,'yes','Cisco%GPASS%Catalyst 2960-Plus 24TC-L');
INSERT INTO `Dictionary` VALUES (12,2138,'yes','Cisco%GPASS%Catalyst 2960-Plus 48PST-S');
INSERT INTO `Dictionary` VALUES (12,2139,'yes','Cisco%GPASS%Catalyst 2960-Plus 24PC-S');
INSERT INTO `Dictionary` VALUES (12,2140,'yes','Cisco%GPASS%Catalyst 2960-Plus 24LC-S');
INSERT INTO `Dictionary` VALUES (12,2141,'yes','Cisco%GPASS%Catalyst 2960-Plus 24TC-S');
INSERT INTO `Dictionary` VALUES (14,2142,'yes','Cisco IOS 15.2');
INSERT INTO `Dictionary` VALUES (13,2143,'yes','Red Hat Enterprise%GSKIP%RHEL V7');
INSERT INTO `Dictionary` VALUES (38,2144,'yes','Cisco%GPASS%Wireless Controller');
INSERT INTO `Dictionary` VALUES (13,49999,'yes','[[RH Fedora%GSKIP%Fedora 15 | http://docs.fedoraproject.org/release-notes/f15/en-US/html/]]');
/*!40000 ALTER TABLE `Dictionary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EntityLink`
--

DROP TABLE IF EXISTS `EntityLink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EntityLink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_entity_type` enum('location','object','rack','row') COLLATE utf8_unicode_ci NOT NULL,
  `parent_entity_id` int(10) unsigned NOT NULL,
  `child_entity_type` enum('location','object','rack','row') COLLATE utf8_unicode_ci NOT NULL,
  `child_entity_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `EntityLink-unique` (`parent_entity_type`,`parent_entity_id`,`child_entity_type`,`child_entity_id`),
  KEY `EntityLink-compound` (`parent_entity_type`,`child_entity_type`,`child_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EntityLink`
--
-- ORDER BY:  `id`

LOCK TABLES `EntityLink` WRITE;
/*!40000 ALTER TABLE `EntityLink` DISABLE KEYS */;
/*!40000 ALTER TABLE `EntityLink` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE TRIGGER `EntityLink-before-insert` BEFORE INSERT ON `EntityLink` FOR EACH ROW
EntityLinkTrigger:BEGIN
  DECLARE parent_objtype, child_objtype, count INTEGER;

  # forbid linking an entity to itself
  IF NEW.parent_entity_type = NEW.child_entity_type AND NEW.parent_entity_id = NEW.child_entity_id THEN
    SET NEW.parent_entity_id = NULL;
    LEAVE EntityLinkTrigger;
  END IF;

  # in some scenarios, only one parent is allowed
  CASE CONCAT(NEW.parent_entity_type, '.', NEW.child_entity_type)
    WHEN 'location.location' THEN
      SELECT COUNT(*) INTO count FROM EntityLink WHERE parent_entity_type = 'location' AND child_entity_type = 'location' AND child_entity_id = NEW.child_entity_id;
    WHEN 'location.row' THEN
      SELECT COUNT(*) INTO count FROM EntityLink WHERE parent_entity_type = 'location' AND child_entity_type = 'row' AND child_entity_id = NEW.child_entity_id;
    WHEN 'row.rack' THEN
      SELECT COUNT(*) INTO count FROM EntityLink WHERE parent_entity_type = 'row' AND child_entity_type = 'rack' AND child_entity_id = NEW.child_entity_id;
    ELSE
      # some other scenario, assume it is valid
      SET count = 0;
  END CASE; 
  IF count > 0 THEN
    SET NEW.parent_entity_id = NULL;
    LEAVE EntityLinkTrigger;
  END IF;

  IF NEW.parent_entity_type = 'object' AND NEW.child_entity_type = 'object' THEN
    # lock objects to prevent concurrent link establishment
    SELECT objtype_id INTO parent_objtype FROM Object WHERE id = NEW.parent_entity_id FOR UPDATE;
    SELECT objtype_id INTO child_objtype FROM Object WHERE id = NEW.child_entity_id FOR UPDATE;

    # only permit the link if object types are compatibile
    SELECT COUNT(*) INTO count FROM ObjectParentCompat WHERE parent_objtype_id = parent_objtype AND child_objtype_id = child_objtype;
    IF count = 0 THEN
      SET NEW.parent_entity_id = NULL;
    END IF;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE TRIGGER `EntityLink-before-update` BEFORE UPDATE ON `EntityLink` FOR EACH ROW
EntityLinkTrigger:BEGIN
  DECLARE parent_objtype, child_objtype, count INTEGER;

  # forbid linking an entity to itself
  IF NEW.parent_entity_type = NEW.child_entity_type AND NEW.parent_entity_id = NEW.child_entity_id THEN
    SET NEW.parent_entity_id = NULL;
    LEAVE EntityLinkTrigger;
  END IF;

  # in some scenarios, only one parent is allowed
  CASE CONCAT(NEW.parent_entity_type, '.', NEW.child_entity_type)
    WHEN 'location.location' THEN
      SELECT COUNT(*) INTO count FROM EntityLink WHERE parent_entity_type = 'location' AND child_entity_type = 'location' AND child_entity_id = NEW.child_entity_id AND id != NEW.id;
    WHEN 'location.row' THEN
      SELECT COUNT(*) INTO count FROM EntityLink WHERE parent_entity_type = 'location' AND child_entity_type = 'row' AND child_entity_id = NEW.child_entity_id AND id != NEW.id;
    WHEN 'row.rack' THEN
      SELECT COUNT(*) INTO count FROM EntityLink WHERE parent_entity_type = 'row' AND child_entity_type = 'rack' AND child_entity_id = NEW.child_entity_id AND id != NEW.id;
    ELSE
      # some other scenario, assume it is valid
      SET count = 0;
  END CASE; 
  IF count > 0 THEN
    SET NEW.parent_entity_id = NULL;
    LEAVE EntityLinkTrigger;
  END IF;

  IF NEW.parent_entity_type = 'object' AND NEW.child_entity_type = 'object' THEN
    # lock objects to prevent concurrent link establishment
    SELECT objtype_id INTO parent_objtype FROM Object WHERE id = NEW.parent_entity_id FOR UPDATE;
    SELECT objtype_id INTO child_objtype FROM Object WHERE id = NEW.child_entity_id FOR UPDATE;

    # only permit the link if object types are compatibile
    SELECT COUNT(*) INTO count FROM ObjectParentCompat WHERE parent_objtype_id = parent_objtype AND child_objtype_id = child_objtype;
    IF count = 0 THEN
      SET NEW.parent_entity_id = NULL;
    END IF;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `File`
--

DROP TABLE IF EXISTS `File`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `File` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `ctime` datetime NOT NULL,
  `mtime` datetime NOT NULL,
  `atime` datetime NOT NULL,
  `thumbnail` longblob,
  `contents` longblob NOT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `File`
--
-- ORDER BY:  `id`

LOCK TABLES `File` WRITE;
/*!40000 ALTER TABLE `File` DISABLE KEYS */;
/*!40000 ALTER TABLE `File` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FileLink`
--

DROP TABLE IF EXISTS `FileLink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FileLink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned NOT NULL,
  `entity_type` enum('ipv4net','ipv4rspool','ipv4vs','ipvs','ipv6net','location','object','rack','row','user') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'object',
  `entity_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `FileLink-unique` (`file_id`,`entity_type`,`entity_id`),
  KEY `FileLink-file_id` (`file_id`),
  CONSTRAINT `FileLink-File_fkey` FOREIGN KEY (`file_id`) REFERENCES `File` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FileLink`
--
-- ORDER BY:  `id`

LOCK TABLES `FileLink` WRITE;
/*!40000 ALTER TABLE `FileLink` DISABLE KEYS */;
/*!40000 ALTER TABLE `FileLink` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4Address`
--

DROP TABLE IF EXISTS `IPv4Address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4Address` (
  `ip` int(10) unsigned NOT NULL DEFAULT '0',
  `name` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comment` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reserved` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4Address`
--
-- ORDER BY:  `ip`

LOCK TABLES `IPv4Address` WRITE;
/*!40000 ALTER TABLE `IPv4Address` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4Address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4Allocation`
--

DROP TABLE IF EXISTS `IPv4Allocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4Allocation` (
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` int(10) unsigned NOT NULL DEFAULT '0',
  `name` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('regular','shared','virtual','router','point2point') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'regular',
  PRIMARY KEY (`object_id`,`ip`),
  KEY `ip` (`ip`),
  CONSTRAINT `IPv4Allocation-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4Allocation`
--
-- ORDER BY:  `object_id`,`ip`

LOCK TABLES `IPv4Allocation` WRITE;
/*!40000 ALTER TABLE `IPv4Allocation` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4Allocation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4LB`
--

DROP TABLE IF EXISTS `IPv4LB`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4LB` (
  `object_id` int(10) unsigned DEFAULT NULL,
  `rspool_id` int(10) unsigned DEFAULT NULL,
  `vs_id` int(10) unsigned DEFAULT NULL,
  `prio` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `LB-VS` (`object_id`,`vs_id`),
  KEY `IPv4LB-FK-rspool_id` (`rspool_id`),
  KEY `IPv4LB-FK-vs_id` (`vs_id`),
  CONSTRAINT `IPv4LB-FK-vs_id` FOREIGN KEY (`vs_id`) REFERENCES `IPv4VS` (`id`),
  CONSTRAINT `IPv4LB-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`),
  CONSTRAINT `IPv4LB-FK-rspool_id` FOREIGN KEY (`rspool_id`) REFERENCES `IPv4RSPool` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4LB`
--
-- ORDER BY:  `object_id`,`vs_id`

LOCK TABLES `IPv4LB` WRITE;
/*!40000 ALTER TABLE `IPv4LB` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4LB` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4Log`
--

DROP TABLE IF EXISTS `IPv4Log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4Log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `user` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip-date` (`ip`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4Log`
--
-- ORDER BY:  `id`

LOCK TABLES `IPv4Log` WRITE;
/*!40000 ALTER TABLE `IPv4Log` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4Log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4NAT`
--

DROP TABLE IF EXISTS `IPv4NAT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4NAT` (
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `proto` enum('TCP','UDP','ALL') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'TCP',
  `localip` int(10) unsigned NOT NULL DEFAULT '0',
  `localport` smallint(5) unsigned NOT NULL DEFAULT '0',
  `remoteip` int(10) unsigned NOT NULL DEFAULT '0',
  `remoteport` smallint(5) unsigned NOT NULL DEFAULT '0',
  `description` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`object_id`,`proto`,`localip`,`localport`,`remoteip`,`remoteport`),
  KEY `localip` (`localip`),
  KEY `remoteip` (`remoteip`),
  KEY `object_id` (`object_id`),
  CONSTRAINT `IPv4NAT-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4NAT`
--
-- ORDER BY:  `object_id`,`proto`,`localip`,`localport`,`remoteip`,`remoteport`

LOCK TABLES `IPv4NAT` WRITE;
/*!40000 ALTER TABLE `IPv4NAT` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4NAT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4Network`
--

DROP TABLE IF EXISTS `IPv4Network`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4Network` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` int(10) unsigned NOT NULL DEFAULT '0',
  `mask` int(10) unsigned NOT NULL DEFAULT '0',
  `name` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `base-len` (`ip`,`mask`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4Network`
--
-- ORDER BY:  `id`

LOCK TABLES `IPv4Network` WRITE;
/*!40000 ALTER TABLE `IPv4Network` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4Network` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4RS`
--

DROP TABLE IF EXISTS `IPv4RS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4RS` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inservice` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `rsip` varbinary(16) NOT NULL,
  `rsport` smallint(5) unsigned DEFAULT NULL,
  `rspool_id` int(10) unsigned DEFAULT NULL,
  `rsconfig` text COLLATE utf8_unicode_ci,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pool-endpoint` (`rspool_id`,`rsip`,`rsport`),
  KEY `rsip` (`rsip`),
  CONSTRAINT `IPv4RS-FK` FOREIGN KEY (`rspool_id`) REFERENCES `IPv4RSPool` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4RS`
--
-- ORDER BY:  `id`

LOCK TABLES `IPv4RS` WRITE;
/*!40000 ALTER TABLE `IPv4RS` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4RS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4RSPool`
--

DROP TABLE IF EXISTS `IPv4RSPool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4RSPool` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4RSPool`
--
-- ORDER BY:  `id`

LOCK TABLES `IPv4RSPool` WRITE;
/*!40000 ALTER TABLE `IPv4RSPool` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4RSPool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv4VS`
--

DROP TABLE IF EXISTS `IPv4VS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv4VS` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vip` varbinary(16) NOT NULL,
  `vport` smallint(5) unsigned DEFAULT NULL,
  `proto` enum('TCP','UDP','MARK') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'TCP',
  `name` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `vip` (`vip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv4VS`
--
-- ORDER BY:  `id`

LOCK TABLES `IPv4VS` WRITE;
/*!40000 ALTER TABLE `IPv4VS` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv4VS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv6Address`
--

DROP TABLE IF EXISTS `IPv6Address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv6Address` (
  `ip` binary(16) NOT NULL,
  `name` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comment` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reserved` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv6Address`
--
-- ORDER BY:  `ip`

LOCK TABLES `IPv6Address` WRITE;
/*!40000 ALTER TABLE `IPv6Address` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv6Address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv6Allocation`
--

DROP TABLE IF EXISTS `IPv6Allocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv6Allocation` (
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` binary(16) NOT NULL,
  `name` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('regular','shared','virtual','router','point2point') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'regular',
  PRIMARY KEY (`object_id`,`ip`),
  KEY `ip` (`ip`),
  CONSTRAINT `IPv6Allocation-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv6Allocation`
--
-- ORDER BY:  `object_id`,`ip`

LOCK TABLES `IPv6Allocation` WRITE;
/*!40000 ALTER TABLE `IPv6Allocation` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv6Allocation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv6Log`
--

DROP TABLE IF EXISTS `IPv6Log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv6Log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` binary(16) NOT NULL,
  `date` datetime NOT NULL,
  `user` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip-date` (`ip`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv6Log`
--
-- ORDER BY:  `id`

LOCK TABLES `IPv6Log` WRITE;
/*!40000 ALTER TABLE `IPv6Log` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv6Log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IPv6Network`
--

DROP TABLE IF EXISTS `IPv6Network`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IPv6Network` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` binary(16) NOT NULL,
  `mask` int(10) unsigned NOT NULL,
  `last_ip` binary(16) NOT NULL,
  `name` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`,`mask`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IPv6Network`
--
-- ORDER BY:  `id`

LOCK TABLES `IPv6Network` WRITE;
/*!40000 ALTER TABLE `IPv6Network` DISABLE KEYS */;
/*!40000 ALTER TABLE `IPv6Network` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LDAPCache`
--

DROP TABLE IF EXISTS `LDAPCache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LDAPCache` (
  `presented_username` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `successful_hash` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `first_success` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_retry` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `displayed_name` char(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `memberof` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `presented_username` (`presented_username`),
  KEY `scanidx` (`presented_username`,`successful_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LDAPCache`
--
-- ORDER BY:  `presented_username`

LOCK TABLES `LDAPCache` WRITE;
/*!40000 ALTER TABLE `LDAPCache` DISABLE KEYS */;
/*!40000 ALTER TABLE `LDAPCache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Link`
--

DROP TABLE IF EXISTS `Link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Link` (
  `porta` int(10) unsigned NOT NULL DEFAULT '0',
  `portb` int(10) unsigned NOT NULL DEFAULT '0',
  `cable` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`porta`,`portb`),
  UNIQUE KEY `porta` (`porta`),
  UNIQUE KEY `portb` (`portb`),
  CONSTRAINT `Link-FK-a` FOREIGN KEY (`porta`) REFERENCES `Port` (`id`) ON DELETE CASCADE,
  CONSTRAINT `Link-FK-b` FOREIGN KEY (`portb`) REFERENCES `Port` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Link`
--
-- ORDER BY:  `porta`,`portb`

LOCK TABLES `Link` WRITE;
/*!40000 ALTER TABLE `Link` DISABLE KEYS */;
/*!40000 ALTER TABLE `Link` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE TRIGGER `Link-before-insert` BEFORE INSERT ON `Link` FOR EACH ROW LinkTrigger:BEGIN
  DECLARE tmp, porta_type, portb_type, count INTEGER;

  IF NEW.porta = NEW.portb THEN
    # forbid connecting a port to itself
    SET NEW.porta = NULL;
    LEAVE LinkTrigger;
  ELSEIF NEW.porta > NEW.portb THEN
    # force porta < portb
    SET tmp = NEW.porta;
    SET NEW.porta = NEW.portb;
    SET NEW.portb = tmp;
  END IF; 

  # lock ports to prevent concurrent link establishment
  SELECT type INTO porta_type FROM Port WHERE id = NEW.porta FOR UPDATE;
  SELECT type INTO portb_type FROM Port WHERE id = NEW.portb FOR UPDATE;

  # only permit the link if ports are compatibile
  SELECT COUNT(*) INTO count FROM PortCompat WHERE (type1 = porta_type AND type2 = portb_type) OR (type1 = portb_type AND type2 = porta_type);
  IF count = 0 THEN
    SET NEW.porta = NULL;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE TRIGGER `Link-before-update` BEFORE UPDATE ON `Link` FOR EACH ROW LinkTrigger:BEGIN
  DECLARE tmp, porta_type, portb_type, count INTEGER;

  IF NEW.porta = NEW.portb THEN
    # forbid connecting a port to itself
    SET NEW.porta = NULL;
    LEAVE LinkTrigger;
  ELSEIF NEW.porta > NEW.portb THEN
    # force porta < portb
    SET tmp = NEW.porta;
    SET NEW.porta = NEW.portb;
    SET NEW.portb = tmp;
  END IF; 

  # lock ports to prevent concurrent link establishment
  SELECT type INTO porta_type FROM Port WHERE id = NEW.porta FOR UPDATE;
  SELECT type INTO portb_type FROM Port WHERE id = NEW.portb FOR UPDATE;

  # only permit the link if ports are compatibile
  SELECT COUNT(*) INTO count FROM PortCompat WHERE (type1 = porta_type AND type2 = portb_type) OR (type1 = portb_type AND type2 = porta_type);
  IF count = 0 THEN
    SET NEW.porta = NULL;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary table structure for view `Location`
--

DROP TABLE IF EXISTS `Location`;
/*!50001 DROP VIEW IF EXISTS `Location`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Location` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `has_problems` tinyint NOT NULL,
  `comment` tinyint NOT NULL,
  `parent_id` tinyint NOT NULL,
  `parent_name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Molecule`
--

DROP TABLE IF EXISTS `Molecule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Molecule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Molecule`
--
-- ORDER BY:  `id`

LOCK TABLES `Molecule` WRITE;
/*!40000 ALTER TABLE `Molecule` DISABLE KEYS */;
/*!40000 ALTER TABLE `Molecule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MountOperation`
--

DROP TABLE IF EXISTS `MountOperation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MountOperation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_name` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `old_molecule_id` int(10) unsigned DEFAULT NULL,
  `new_molecule_id` int(10) unsigned DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `MountOperation-FK-old_molecule_id` (`old_molecule_id`),
  KEY `MountOperation-FK-new_molecule_id` (`new_molecule_id`),
  CONSTRAINT `MountOperation-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE,
  CONSTRAINT `MountOperation-FK-old_molecule_id` FOREIGN KEY (`old_molecule_id`) REFERENCES `Molecule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `MountOperation-FK-new_molecule_id` FOREIGN KEY (`new_molecule_id`) REFERENCES `Molecule` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MountOperation`
--
-- ORDER BY:  `id`

LOCK TABLES `MountOperation` WRITE;
/*!40000 ALTER TABLE `MountOperation` DISABLE KEYS */;
/*!40000 ALTER TABLE `MountOperation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MuninGraph`
--

DROP TABLE IF EXISTS `MuninGraph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MuninGraph` (
  `object_id` int(10) unsigned NOT NULL,
  `server_id` int(10) unsigned NOT NULL,
  `graph` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `caption` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`object_id`,`server_id`,`graph`),
  KEY `server_id` (`server_id`),
  KEY `graph` (`graph`),
  CONSTRAINT `MuninGraph-FK-server_id` FOREIGN KEY (`server_id`) REFERENCES `MuninServer` (`id`),
  CONSTRAINT `MuninGraph-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MuninGraph`
--
-- ORDER BY:  `object_id`,`server_id`,`graph`

LOCK TABLES `MuninGraph` WRITE;
/*!40000 ALTER TABLE `MuninGraph` DISABLE KEYS */;
/*!40000 ALTER TABLE `MuninGraph` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MuninServer`
--

DROP TABLE IF EXISTS `MuninServer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MuninServer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `base_url` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MuninServer`
--
-- ORDER BY:  `id`

LOCK TABLES `MuninServer` WRITE;
/*!40000 ALTER TABLE `MuninServer` DISABLE KEYS */;
/*!40000 ALTER TABLE `MuninServer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Object`
--

DROP TABLE IF EXISTS `Object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Object` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `objtype_id` int(10) unsigned NOT NULL DEFAULT '1',
  `asset_no` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_problems` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_no` (`asset_no`),
  KEY `id-tid` (`id`,`objtype_id`),
  KEY `type_id` (`objtype_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Object`
--
-- ORDER BY:  `id`

LOCK TABLES `Object` WRITE;
/*!40000 ALTER TABLE `Object` DISABLE KEYS */;
/*!40000 ALTER TABLE `Object` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ObjectHistory`
--

DROP TABLE IF EXISTS `ObjectHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ObjectHistory` (
  `id` int(10) unsigned DEFAULT NULL,
  `name` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `objtype_id` int(10) unsigned DEFAULT NULL,
  `asset_no` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_problems` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `comment` text COLLATE utf8_unicode_ci,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_name` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `id` (`id`),
  CONSTRAINT `ObjectHistory-FK-object_id` FOREIGN KEY (`id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ObjectHistory`
--

LOCK TABLES `ObjectHistory` WRITE;
/*!40000 ALTER TABLE `ObjectHistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `ObjectHistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ObjectLog`
--

DROP TABLE IF EXISTS `ObjectLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ObjectLog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` int(10) unsigned NOT NULL,
  `user` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `date` (`date`),
  CONSTRAINT `ObjectLog-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ObjectLog`
--
-- ORDER BY:  `id`

LOCK TABLES `ObjectLog` WRITE;
/*!40000 ALTER TABLE `ObjectLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `ObjectLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ObjectParentCompat`
--

DROP TABLE IF EXISTS `ObjectParentCompat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ObjectParentCompat` (
  `parent_objtype_id` int(10) unsigned NOT NULL,
  `child_objtype_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `parent_child` (`parent_objtype_id`,`child_objtype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ObjectParentCompat`
--
-- ORDER BY:  `parent_objtype_id`,`child_objtype_id`

LOCK TABLES `ObjectParentCompat` WRITE;
/*!40000 ALTER TABLE `ObjectParentCompat` DISABLE KEYS */;
INSERT INTO `ObjectParentCompat` VALUES (3,13);
INSERT INTO `ObjectParentCompat` VALUES (4,1504);
INSERT INTO `ObjectParentCompat` VALUES (4,1507);
INSERT INTO `ObjectParentCompat` VALUES (1397,1398);
INSERT INTO `ObjectParentCompat` VALUES (1502,4);
INSERT INTO `ObjectParentCompat` VALUES (1503,8);
INSERT INTO `ObjectParentCompat` VALUES (1505,4);
INSERT INTO `ObjectParentCompat` VALUES (1505,1504);
INSERT INTO `ObjectParentCompat` VALUES (1505,1506);
INSERT INTO `ObjectParentCompat` VALUES (1505,1507);
INSERT INTO `ObjectParentCompat` VALUES (1506,4);
INSERT INTO `ObjectParentCompat` VALUES (1506,1504);
/*!40000 ALTER TABLE `ObjectParentCompat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Port`
--

DROP TABLE IF EXISTS `Port`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Port` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `iif_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  `l2address` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reservation_comment` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object_iif_oif_name` (`object_id`,`iif_id`,`type`,`name`),
  KEY `type` (`type`),
  KEY `comment` (`reservation_comment`),
  KEY `l2address` (`l2address`),
  KEY `Port-FK-iif-oif` (`iif_id`,`type`),
  CONSTRAINT `Port-FK-iif-oif` FOREIGN KEY (`iif_id`, `type`) REFERENCES `PortInterfaceCompat` (`iif_id`, `oif_id`),
  CONSTRAINT `Port-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Port`
--
-- ORDER BY:  `id`

LOCK TABLES `Port` WRITE;
/*!40000 ALTER TABLE `Port` DISABLE KEYS */;
/*!40000 ALTER TABLE `Port` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PortAllowedVLAN`
--

DROP TABLE IF EXISTS `PortAllowedVLAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PortAllowedVLAN` (
  `object_id` int(10) unsigned NOT NULL,
  `port_name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`port_name`,`vlan_id`),
  KEY `vlan_id` (`vlan_id`),
  CONSTRAINT `PortAllowedVLAN-FK-object-port` FOREIGN KEY (`object_id`, `port_name`) REFERENCES `PortVLANMode` (`object_id`, `port_name`) ON DELETE CASCADE,
  CONSTRAINT `PortAllowedVLAN-FK-vlan_id` FOREIGN KEY (`vlan_id`) REFERENCES `VLANValidID` (`vlan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PortAllowedVLAN`
--
-- ORDER BY:  `object_id`,`port_name`,`vlan_id`

LOCK TABLES `PortAllowedVLAN` WRITE;
/*!40000 ALTER TABLE `PortAllowedVLAN` DISABLE KEYS */;
/*!40000 ALTER TABLE `PortAllowedVLAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PortCompat`
--

DROP TABLE IF EXISTS `PortCompat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PortCompat` (
  `type1` int(10) unsigned NOT NULL DEFAULT '0',
  `type2` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `type1_2` (`type1`,`type2`),
  KEY `type2` (`type2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PortCompat`
--
-- ORDER BY:  `type1`,`type2`

LOCK TABLES `PortCompat` WRITE;
/*!40000 ALTER TABLE `PortCompat` DISABLE KEYS */;
INSERT INTO `PortCompat` VALUES (16,1322);
INSERT INTO `PortCompat` VALUES (17,17);
INSERT INTO `PortCompat` VALUES (18,18);
INSERT INTO `PortCompat` VALUES (18,19);
INSERT INTO `PortCompat` VALUES (18,24);
INSERT INTO `PortCompat` VALUES (19,18);
INSERT INTO `PortCompat` VALUES (19,19);
INSERT INTO `PortCompat` VALUES (19,24);
INSERT INTO `PortCompat` VALUES (24,18);
INSERT INTO `PortCompat` VALUES (24,19);
INSERT INTO `PortCompat` VALUES (24,24);
INSERT INTO `PortCompat` VALUES (29,29);
INSERT INTO `PortCompat` VALUES (29,681);
INSERT INTO `PortCompat` VALUES (29,682);
INSERT INTO `PortCompat` VALUES (30,30);
INSERT INTO `PortCompat` VALUES (32,32);
INSERT INTO `PortCompat` VALUES (33,446);
INSERT INTO `PortCompat` VALUES (34,34);
INSERT INTO `PortCompat` VALUES (35,35);
INSERT INTO `PortCompat` VALUES (36,36);
INSERT INTO `PortCompat` VALUES (37,37);
INSERT INTO `PortCompat` VALUES (38,38);
INSERT INTO `PortCompat` VALUES (39,39);
INSERT INTO `PortCompat` VALUES (40,40);
INSERT INTO `PortCompat` VALUES (41,41);
INSERT INTO `PortCompat` VALUES (439,439);
INSERT INTO `PortCompat` VALUES (446,33);
INSERT INTO `PortCompat` VALUES (681,29);
INSERT INTO `PortCompat` VALUES (681,681);
INSERT INTO `PortCompat` VALUES (681,682);
INSERT INTO `PortCompat` VALUES (682,29);
INSERT INTO `PortCompat` VALUES (682,681);
INSERT INTO `PortCompat` VALUES (682,682);
INSERT INTO `PortCompat` VALUES (1077,1077);
INSERT INTO `PortCompat` VALUES (1084,1084);
INSERT INTO `PortCompat` VALUES (1087,1087);
INSERT INTO `PortCompat` VALUES (1195,1195);
INSERT INTO `PortCompat` VALUES (1196,1196);
INSERT INTO `PortCompat` VALUES (1197,1197);
INSERT INTO `PortCompat` VALUES (1198,1199);
INSERT INTO `PortCompat` VALUES (1199,1198);
INSERT INTO `PortCompat` VALUES (1200,1200);
INSERT INTO `PortCompat` VALUES (1201,1201);
INSERT INTO `PortCompat` VALUES (1202,1202);
INSERT INTO `PortCompat` VALUES (1203,1203);
INSERT INTO `PortCompat` VALUES (1204,1204);
INSERT INTO `PortCompat` VALUES (1205,1205);
INSERT INTO `PortCompat` VALUES (1206,1207);
INSERT INTO `PortCompat` VALUES (1207,1206);
INSERT INTO `PortCompat` VALUES (1209,1209);
INSERT INTO `PortCompat` VALUES (1210,1210);
INSERT INTO `PortCompat` VALUES (1211,1211);
INSERT INTO `PortCompat` VALUES (1212,1212);
INSERT INTO `PortCompat` VALUES (1213,1213);
INSERT INTO `PortCompat` VALUES (1214,1214);
INSERT INTO `PortCompat` VALUES (1215,1215);
INSERT INTO `PortCompat` VALUES (1216,1216);
INSERT INTO `PortCompat` VALUES (1217,1217);
INSERT INTO `PortCompat` VALUES (1218,1218);
INSERT INTO `PortCompat` VALUES (1219,1219);
INSERT INTO `PortCompat` VALUES (1220,1220);
INSERT INTO `PortCompat` VALUES (1221,1221);
INSERT INTO `PortCompat` VALUES (1222,1222);
INSERT INTO `PortCompat` VALUES (1223,1223);
INSERT INTO `PortCompat` VALUES (1224,1224);
INSERT INTO `PortCompat` VALUES (1225,1225);
INSERT INTO `PortCompat` VALUES (1226,1226);
INSERT INTO `PortCompat` VALUES (1227,1227);
INSERT INTO `PortCompat` VALUES (1228,1228);
INSERT INTO `PortCompat` VALUES (1229,1229);
INSERT INTO `PortCompat` VALUES (1230,1230);
INSERT INTO `PortCompat` VALUES (1231,1231);
INSERT INTO `PortCompat` VALUES (1232,1232);
INSERT INTO `PortCompat` VALUES (1233,1233);
INSERT INTO `PortCompat` VALUES (1234,1234);
INSERT INTO `PortCompat` VALUES (1235,1235);
INSERT INTO `PortCompat` VALUES (1236,1236);
INSERT INTO `PortCompat` VALUES (1237,1237);
INSERT INTO `PortCompat` VALUES (1238,1238);
INSERT INTO `PortCompat` VALUES (1239,1239);
INSERT INTO `PortCompat` VALUES (1240,1240);
INSERT INTO `PortCompat` VALUES (1241,1241);
INSERT INTO `PortCompat` VALUES (1242,1242);
INSERT INTO `PortCompat` VALUES (1243,1243);
INSERT INTO `PortCompat` VALUES (1244,1244);
INSERT INTO `PortCompat` VALUES (1245,1245);
INSERT INTO `PortCompat` VALUES (1246,1246);
INSERT INTO `PortCompat` VALUES (1247,1247);
INSERT INTO `PortCompat` VALUES (1248,1248);
INSERT INTO `PortCompat` VALUES (1249,1249);
INSERT INTO `PortCompat` VALUES (1250,1250);
INSERT INTO `PortCompat` VALUES (1251,1251);
INSERT INTO `PortCompat` VALUES (1252,1252);
INSERT INTO `PortCompat` VALUES (1253,1253);
INSERT INTO `PortCompat` VALUES (1254,1254);
INSERT INTO `PortCompat` VALUES (1255,1255);
INSERT INTO `PortCompat` VALUES (1256,1256);
INSERT INTO `PortCompat` VALUES (1257,1257);
INSERT INTO `PortCompat` VALUES (1258,1258);
INSERT INTO `PortCompat` VALUES (1259,1259);
INSERT INTO `PortCompat` VALUES (1260,1260);
INSERT INTO `PortCompat` VALUES (1261,1261);
INSERT INTO `PortCompat` VALUES (1262,1262);
INSERT INTO `PortCompat` VALUES (1263,1263);
INSERT INTO `PortCompat` VALUES (1264,1264);
INSERT INTO `PortCompat` VALUES (1265,1265);
INSERT INTO `PortCompat` VALUES (1266,1266);
INSERT INTO `PortCompat` VALUES (1267,1267);
INSERT INTO `PortCompat` VALUES (1268,1268);
INSERT INTO `PortCompat` VALUES (1269,1269);
INSERT INTO `PortCompat` VALUES (1270,1270);
INSERT INTO `PortCompat` VALUES (1271,1271);
INSERT INTO `PortCompat` VALUES (1272,1272);
INSERT INTO `PortCompat` VALUES (1273,1273);
INSERT INTO `PortCompat` VALUES (1274,1274);
INSERT INTO `PortCompat` VALUES (1275,1275);
INSERT INTO `PortCompat` VALUES (1276,1276);
INSERT INTO `PortCompat` VALUES (1277,1277);
INSERT INTO `PortCompat` VALUES (1278,1278);
INSERT INTO `PortCompat` VALUES (1279,1279);
INSERT INTO `PortCompat` VALUES (1280,1280);
INSERT INTO `PortCompat` VALUES (1281,1281);
INSERT INTO `PortCompat` VALUES (1282,1282);
INSERT INTO `PortCompat` VALUES (1283,1283);
INSERT INTO `PortCompat` VALUES (1284,1284);
INSERT INTO `PortCompat` VALUES (1285,1285);
INSERT INTO `PortCompat` VALUES (1286,1286);
INSERT INTO `PortCompat` VALUES (1287,1287);
INSERT INTO `PortCompat` VALUES (1288,1288);
INSERT INTO `PortCompat` VALUES (1289,1289);
INSERT INTO `PortCompat` VALUES (1290,1290);
INSERT INTO `PortCompat` VALUES (1291,1291);
INSERT INTO `PortCompat` VALUES (1292,1292);
INSERT INTO `PortCompat` VALUES (1293,1293);
INSERT INTO `PortCompat` VALUES (1294,1294);
INSERT INTO `PortCompat` VALUES (1295,1295);
INSERT INTO `PortCompat` VALUES (1296,1296);
INSERT INTO `PortCompat` VALUES (1297,1297);
INSERT INTO `PortCompat` VALUES (1298,1298);
INSERT INTO `PortCompat` VALUES (1299,1299);
INSERT INTO `PortCompat` VALUES (1300,1300);
INSERT INTO `PortCompat` VALUES (1316,1316);
INSERT INTO `PortCompat` VALUES (1322,16);
INSERT INTO `PortCompat` VALUES (1399,1399);
INSERT INTO `PortCompat` VALUES (1424,1424);
INSERT INTO `PortCompat` VALUES (1425,1425);
INSERT INTO `PortCompat` VALUES (1426,1426);
INSERT INTO `PortCompat` VALUES (1427,1427);
INSERT INTO `PortCompat` VALUES (1428,1428);
INSERT INTO `PortCompat` VALUES (1429,1429);
INSERT INTO `PortCompat` VALUES (1430,1430);
INSERT INTO `PortCompat` VALUES (1431,1431);
INSERT INTO `PortCompat` VALUES (1432,1432);
INSERT INTO `PortCompat` VALUES (1433,1433);
INSERT INTO `PortCompat` VALUES (1434,1434);
INSERT INTO `PortCompat` VALUES (1435,1435);
INSERT INTO `PortCompat` VALUES (1436,1436);
INSERT INTO `PortCompat` VALUES (1437,1437);
INSERT INTO `PortCompat` VALUES (1438,1438);
INSERT INTO `PortCompat` VALUES (1439,1439);
INSERT INTO `PortCompat` VALUES (1440,1440);
INSERT INTO `PortCompat` VALUES (1441,1441);
INSERT INTO `PortCompat` VALUES (1442,1442);
INSERT INTO `PortCompat` VALUES (1443,1443);
INSERT INTO `PortCompat` VALUES (1444,1444);
INSERT INTO `PortCompat` VALUES (1445,1445);
INSERT INTO `PortCompat` VALUES (1446,1446);
INSERT INTO `PortCompat` VALUES (1447,1447);
INSERT INTO `PortCompat` VALUES (1448,1448);
INSERT INTO `PortCompat` VALUES (1449,1449);
INSERT INTO `PortCompat` VALUES (1450,1450);
INSERT INTO `PortCompat` VALUES (1451,1451);
INSERT INTO `PortCompat` VALUES (1452,1452);
INSERT INTO `PortCompat` VALUES (1453,1453);
INSERT INTO `PortCompat` VALUES (1454,1454);
INSERT INTO `PortCompat` VALUES (1455,1455);
INSERT INTO `PortCompat` VALUES (1456,1456);
INSERT INTO `PortCompat` VALUES (1457,1457);
INSERT INTO `PortCompat` VALUES (1458,1458);
INSERT INTO `PortCompat` VALUES (1459,1459);
INSERT INTO `PortCompat` VALUES (1460,1460);
INSERT INTO `PortCompat` VALUES (1461,1461);
INSERT INTO `PortCompat` VALUES (1462,1462);
INSERT INTO `PortCompat` VALUES (1463,1463);
INSERT INTO `PortCompat` VALUES (1464,1464);
INSERT INTO `PortCompat` VALUES (1465,1465);
INSERT INTO `PortCompat` VALUES (1466,1466);
INSERT INTO `PortCompat` VALUES (1469,1469);
INSERT INTO `PortCompat` VALUES (1588,1588);
INSERT INTO `PortCompat` VALUES (1603,1603);
INSERT INTO `PortCompat` VALUES (1642,1642);
INSERT INTO `PortCompat` VALUES (1661,1661);
INSERT INTO `PortCompat` VALUES (1663,1663);
INSERT INTO `PortCompat` VALUES (1664,1664);
INSERT INTO `PortCompat` VALUES (1668,1668);
INSERT INTO `PortCompat` VALUES (1669,1669);
INSERT INTO `PortCompat` VALUES (1670,1670);
INSERT INTO `PortCompat` VALUES (1671,1671);
/*!40000 ALTER TABLE `PortCompat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PortInnerInterface`
--

DROP TABLE IF EXISTS `PortInnerInterface`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PortInnerInterface` (
  `id` int(10) unsigned NOT NULL,
  `iif_name` char(16) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iif_name` (`iif_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PortInnerInterface`
--
-- ORDER BY:  `id`

LOCK TABLES `PortInnerInterface` WRITE;
/*!40000 ALTER TABLE `PortInnerInterface` DISABLE KEYS */;
INSERT INTO `PortInnerInterface` VALUES (1,'hardwired');
INSERT INTO `PortInnerInterface` VALUES (2,'SFP-100');
INSERT INTO `PortInnerInterface` VALUES (3,'GBIC');
INSERT INTO `PortInnerInterface` VALUES (4,'SFP-1000');
INSERT INTO `PortInnerInterface` VALUES (5,'XENPAK');
INSERT INTO `PortInnerInterface` VALUES (6,'X2');
INSERT INTO `PortInnerInterface` VALUES (7,'XPAK');
INSERT INTO `PortInnerInterface` VALUES (8,'XFP');
INSERT INTO `PortInnerInterface` VALUES (9,'SFP+');
INSERT INTO `PortInnerInterface` VALUES (10,'QSFP+');
INSERT INTO `PortInnerInterface` VALUES (11,'CFP');
/*!40000 ALTER TABLE `PortInnerInterface` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PortInterfaceCompat`
--

DROP TABLE IF EXISTS `PortInterfaceCompat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PortInterfaceCompat` (
  `iif_id` int(10) unsigned NOT NULL,
  `oif_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `pair` (`iif_id`,`oif_id`),
  CONSTRAINT `PortInterfaceCompat-FK-iif_id` FOREIGN KEY (`iif_id`) REFERENCES `PortInnerInterface` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PortInterfaceCompat`
--
-- ORDER BY:  `iif_id`,`oif_id`

LOCK TABLES `PortInterfaceCompat` WRITE;
/*!40000 ALTER TABLE `PortInterfaceCompat` DISABLE KEYS */;
INSERT INTO `PortInterfaceCompat` VALUES (1,16);
INSERT INTO `PortInterfaceCompat` VALUES (1,19);
INSERT INTO `PortInterfaceCompat` VALUES (1,24);
INSERT INTO `PortInterfaceCompat` VALUES (1,29);
INSERT INTO `PortInterfaceCompat` VALUES (1,31);
INSERT INTO `PortInterfaceCompat` VALUES (1,33);
INSERT INTO `PortInterfaceCompat` VALUES (1,446);
INSERT INTO `PortInterfaceCompat` VALUES (1,681);
INSERT INTO `PortInterfaceCompat` VALUES (1,682);
INSERT INTO `PortInterfaceCompat` VALUES (1,1322);
INSERT INTO `PortInterfaceCompat` VALUES (1,1399);
INSERT INTO `PortInterfaceCompat` VALUES (1,1469);
INSERT INTO `PortInterfaceCompat` VALUES (2,1195);
INSERT INTO `PortInterfaceCompat` VALUES (2,1196);
INSERT INTO `PortInterfaceCompat` VALUES (2,1197);
INSERT INTO `PortInterfaceCompat` VALUES (2,1198);
INSERT INTO `PortInterfaceCompat` VALUES (2,1199);
INSERT INTO `PortInterfaceCompat` VALUES (2,1200);
INSERT INTO `PortInterfaceCompat` VALUES (2,1201);
INSERT INTO `PortInterfaceCompat` VALUES (2,1208);
INSERT INTO `PortInterfaceCompat` VALUES (3,24);
INSERT INTO `PortInterfaceCompat` VALUES (3,34);
INSERT INTO `PortInterfaceCompat` VALUES (3,1078);
INSERT INTO `PortInterfaceCompat` VALUES (3,1202);
INSERT INTO `PortInterfaceCompat` VALUES (3,1203);
INSERT INTO `PortInterfaceCompat` VALUES (3,1204);
INSERT INTO `PortInterfaceCompat` VALUES (3,1205);
INSERT INTO `PortInterfaceCompat` VALUES (3,1206);
INSERT INTO `PortInterfaceCompat` VALUES (3,1207);
INSERT INTO `PortInterfaceCompat` VALUES (4,24);
INSERT INTO `PortInterfaceCompat` VALUES (4,34);
INSERT INTO `PortInterfaceCompat` VALUES (4,1077);
INSERT INTO `PortInterfaceCompat` VALUES (4,1202);
INSERT INTO `PortInterfaceCompat` VALUES (4,1203);
INSERT INTO `PortInterfaceCompat` VALUES (4,1204);
INSERT INTO `PortInterfaceCompat` VALUES (4,1205);
INSERT INTO `PortInterfaceCompat` VALUES (4,1206);
INSERT INTO `PortInterfaceCompat` VALUES (4,1207);
INSERT INTO `PortInterfaceCompat` VALUES (5,30);
INSERT INTO `PortInterfaceCompat` VALUES (5,35);
INSERT INTO `PortInterfaceCompat` VALUES (5,36);
INSERT INTO `PortInterfaceCompat` VALUES (5,37);
INSERT INTO `PortInterfaceCompat` VALUES (5,38);
INSERT INTO `PortInterfaceCompat` VALUES (5,39);
INSERT INTO `PortInterfaceCompat` VALUES (5,40);
INSERT INTO `PortInterfaceCompat` VALUES (5,1079);
INSERT INTO `PortInterfaceCompat` VALUES (6,30);
INSERT INTO `PortInterfaceCompat` VALUES (6,35);
INSERT INTO `PortInterfaceCompat` VALUES (6,36);
INSERT INTO `PortInterfaceCompat` VALUES (6,37);
INSERT INTO `PortInterfaceCompat` VALUES (6,38);
INSERT INTO `PortInterfaceCompat` VALUES (6,39);
INSERT INTO `PortInterfaceCompat` VALUES (6,40);
INSERT INTO `PortInterfaceCompat` VALUES (6,1080);
INSERT INTO `PortInterfaceCompat` VALUES (7,30);
INSERT INTO `PortInterfaceCompat` VALUES (7,35);
INSERT INTO `PortInterfaceCompat` VALUES (7,36);
INSERT INTO `PortInterfaceCompat` VALUES (7,37);
INSERT INTO `PortInterfaceCompat` VALUES (7,38);
INSERT INTO `PortInterfaceCompat` VALUES (7,39);
INSERT INTO `PortInterfaceCompat` VALUES (7,40);
INSERT INTO `PortInterfaceCompat` VALUES (7,1081);
INSERT INTO `PortInterfaceCompat` VALUES (8,30);
INSERT INTO `PortInterfaceCompat` VALUES (8,35);
INSERT INTO `PortInterfaceCompat` VALUES (8,36);
INSERT INTO `PortInterfaceCompat` VALUES (8,37);
INSERT INTO `PortInterfaceCompat` VALUES (8,38);
INSERT INTO `PortInterfaceCompat` VALUES (8,39);
INSERT INTO `PortInterfaceCompat` VALUES (8,40);
INSERT INTO `PortInterfaceCompat` VALUES (8,1082);
INSERT INTO `PortInterfaceCompat` VALUES (9,30);
INSERT INTO `PortInterfaceCompat` VALUES (9,35);
INSERT INTO `PortInterfaceCompat` VALUES (9,36);
INSERT INTO `PortInterfaceCompat` VALUES (9,37);
INSERT INTO `PortInterfaceCompat` VALUES (9,38);
INSERT INTO `PortInterfaceCompat` VALUES (9,39);
INSERT INTO `PortInterfaceCompat` VALUES (9,40);
INSERT INTO `PortInterfaceCompat` VALUES (9,1084);
INSERT INTO `PortInterfaceCompat` VALUES (10,1588);
INSERT INTO `PortInterfaceCompat` VALUES (10,1663);
INSERT INTO `PortInterfaceCompat` VALUES (10,1664);
INSERT INTO `PortInterfaceCompat` VALUES (11,1668);
INSERT INTO `PortInterfaceCompat` VALUES (11,1669);
INSERT INTO `PortInterfaceCompat` VALUES (11,1670);
INSERT INTO `PortInterfaceCompat` VALUES (11,1671);
/*!40000 ALTER TABLE `PortInterfaceCompat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PortLog`
--

DROP TABLE IF EXISTS `PortLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PortLog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `port_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `user` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `port_id-date` (`port_id`,`date`),
  CONSTRAINT `PortLog_ibfk_1` FOREIGN KEY (`port_id`) REFERENCES `Port` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PortLog`
--
-- ORDER BY:  `id`

LOCK TABLES `PortLog` WRITE;
/*!40000 ALTER TABLE `PortLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `PortLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PortNativeVLAN`
--

DROP TABLE IF EXISTS `PortNativeVLAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PortNativeVLAN` (
  `object_id` int(10) unsigned NOT NULL,
  `port_name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`port_name`,`vlan_id`),
  UNIQUE KEY `port_id` (`object_id`,`port_name`),
  CONSTRAINT `PortNativeVLAN-FK-compound` FOREIGN KEY (`object_id`, `port_name`, `vlan_id`) REFERENCES `PortAllowedVLAN` (`object_id`, `port_name`, `vlan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PortNativeVLAN`
--
-- ORDER BY:  `object_id`,`port_name`,`vlan_id`

LOCK TABLES `PortNativeVLAN` WRITE;
/*!40000 ALTER TABLE `PortNativeVLAN` DISABLE KEYS */;
/*!40000 ALTER TABLE `PortNativeVLAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PortVLANMode`
--

DROP TABLE IF EXISTS `PortVLANMode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PortVLANMode` (
  `object_id` int(10) unsigned NOT NULL,
  `port_name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `vlan_mode` enum('access','trunk') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'access',
  PRIMARY KEY (`object_id`,`port_name`),
  CONSTRAINT `PortVLANMode-FK-object-port` FOREIGN KEY (`object_id`, `port_name`) REFERENCES `CachedPVM` (`object_id`, `port_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PortVLANMode`
--
-- ORDER BY:  `object_id`,`port_name`

LOCK TABLES `PortVLANMode` WRITE;
/*!40000 ALTER TABLE `PortVLANMode` DISABLE KEYS */;
/*!40000 ALTER TABLE `PortVLANMode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `Rack`
--

DROP TABLE IF EXISTS `Rack`;
/*!50001 DROP VIEW IF EXISTS `Rack`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Rack` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `asset_no` tinyint NOT NULL,
  `has_problems` tinyint NOT NULL,
  `comment` tinyint NOT NULL,
  `height` tinyint NOT NULL,
  `sort_order` tinyint NOT NULL,
  `thumb_data` tinyint NOT NULL,
  `row_id` tinyint NOT NULL,
  `row_name` tinyint NOT NULL,
  `location_id` tinyint NOT NULL,
  `location_name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `RackObject`
--

DROP TABLE IF EXISTS `RackObject`;
/*!50001 DROP VIEW IF EXISTS `RackObject`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `RackObject` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `label` tinyint NOT NULL,
  `objtype_id` tinyint NOT NULL,
  `asset_no` tinyint NOT NULL,
  `has_problems` tinyint NOT NULL,
  `comment` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `RackSpace`
--

DROP TABLE IF EXISTS `RackSpace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RackSpace` (
  `rack_id` int(10) unsigned NOT NULL DEFAULT '0',
  `unit_no` int(10) unsigned NOT NULL DEFAULT '0',
  `atom` enum('front','interior','rear') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'interior',
  `state` enum('A','U','T') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A',
  `object_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rack_id`,`unit_no`,`atom`),
  KEY `RackSpace_object_id` (`object_id`),
  CONSTRAINT `RackSpace-FK-rack_id` FOREIGN KEY (`rack_id`) REFERENCES `Object` (`id`),
  CONSTRAINT `RackSpace-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RackSpace`
--
-- ORDER BY:  `rack_id`,`unit_no`,`atom`

LOCK TABLES `RackSpace` WRITE;
/*!40000 ALTER TABLE `RackSpace` DISABLE KEYS */;
/*!40000 ALTER TABLE `RackSpace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RackThumbnail`
--

DROP TABLE IF EXISTS `RackThumbnail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RackThumbnail` (
  `rack_id` int(10) unsigned NOT NULL,
  `thumb_data` blob,
  UNIQUE KEY `rack_id` (`rack_id`),
  CONSTRAINT `RackThumbnail-FK-rack_id` FOREIGN KEY (`rack_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RackThumbnail`
--
-- ORDER BY:  `rack_id`

LOCK TABLES `RackThumbnail` WRITE;
/*!40000 ALTER TABLE `RackThumbnail` DISABLE KEYS */;
/*!40000 ALTER TABLE `RackThumbnail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `Row`
--

DROP TABLE IF EXISTS `Row`;
/*!50001 DROP VIEW IF EXISTS `Row`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Row` (
 `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `location_id` tinyint NOT NULL,
  `location_name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Script`
--

DROP TABLE IF EXISTS `Script`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Script` (
  `script_name` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `script_text` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`script_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Script`
--
-- ORDER BY:  `script_name`

LOCK TABLES `Script` WRITE;
/*!40000 ALTER TABLE `Script` DISABLE KEYS */;
INSERT INTO `Script` VALUES ('RackCode','# Keep admin password immutable by means of special (and also immutable) RackCode.\ndeny {$op_updateUser} or {$op_saveRackCode}\nallow {$userid_1}\n');
/*!40000 ALTER TABLE `Script` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TagStorage`
--

DROP TABLE IF EXISTS `TagStorage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TagStorage` (
  `entity_realm` enum('file','ipv4net','ipv4rspool','ipv4vs','ipvs','ipv6net','location','object','rack','user','vst') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'object',
  `entity_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tag_is_assignable` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `user` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  UNIQUE KEY `entity_tag` (`entity_realm`,`entity_id`,`tag_id`),
  KEY `entity_id` (`entity_id`),
  KEY `TagStorage-FK-tag_id` (`tag_id`),
  KEY `tag_id-tag_is_assignable` (`tag_id`,`tag_is_assignable`),
  CONSTRAINT `TagStorage-FK-TagTree` FOREIGN KEY (`tag_id`, `tag_is_assignable`) REFERENCES `TagTree` (`id`, `is_assignable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TagStorage`
--
-- ORDER BY:  `entity_realm`,`entity_id`,`tag_id`

LOCK TABLES `TagStorage` WRITE;
/*!40000 ALTER TABLE `TagStorage` DISABLE KEYS */;
/*!40000 ALTER TABLE `TagStorage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TagTree`
--

DROP TABLE IF EXISTS `TagTree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TagTree` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `is_assignable` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `tag` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`),
  KEY `TagTree-K-parent_id` (`parent_id`),
  KEY `id-is_assignable` (`id`,`is_assignable`),
  CONSTRAINT `TagTree-K-parent_id` FOREIGN KEY (`parent_id`) REFERENCES `TagTree` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TagTree`
--
-- ORDER BY:  `id`

LOCK TABLES `TagTree` WRITE;
/*!40000 ALTER TABLE `TagTree` DISABLE KEYS */;
/*!40000 ALTER TABLE `TagTree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserAccount`
--

DROP TABLE IF EXISTS `UserAccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UserAccount` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` char(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_password_hash` char(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_realname` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UserAccount`
--
-- ORDER BY:  `user_id`

LOCK TABLES `UserAccount` WRITE;
/*!40000 ALTER TABLE `UserAccount` DISABLE KEYS */;
INSERT INTO `UserAccount` VALUES (1,'admin','d033e22ae348aeb5660fc2140aec35850c4da997','RackTables Administrator');
/*!40000 ALTER TABLE `UserAccount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserConfig`
--

DROP TABLE IF EXISTS `UserConfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UserConfig` (
  `varname` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `varvalue` text COLLATE utf8_unicode_ci NOT NULL,
  `user` char(64) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `user_varname` (`user`,`varname`),
  KEY `varname` (`varname`),
  CONSTRAINT `UserConfig-FK-varname` FOREIGN KEY (`varname`) REFERENCES `Config` (`varname`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UserConfig`
--
-- ORDER BY:  `user`,`varname`

LOCK TABLES `UserConfig` WRITE;
/*!40000 ALTER TABLE `UserConfig` DISABLE KEYS */;
/*!40000 ALTER TABLE `UserConfig` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANDescription`
--

DROP TABLE IF EXISTS `VLANDescription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANDescription` (
  `domain_id` int(10) unsigned NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vlan_type` enum('ondemand','compulsory','alien') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ondemand',
  `vlan_descr` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`domain_id`,`vlan_id`),
  KEY `vlan_id` (`vlan_id`),
  CONSTRAINT `VLANDescription-FK-domain_id` FOREIGN KEY (`domain_id`) REFERENCES `VLANDomain` (`id`) ON DELETE CASCADE,
  CONSTRAINT `VLANDescription-FK-vlan_id` FOREIGN KEY (`vlan_id`) REFERENCES `VLANValidID` (`vlan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANDescription`
--
-- ORDER BY:  `domain_id`,`vlan_id`

LOCK TABLES `VLANDescription` WRITE;
/*!40000 ALTER TABLE `VLANDescription` DISABLE KEYS */;
/*!40000 ALTER TABLE `VLANDescription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANDomain`
--

DROP TABLE IF EXISTS `VLANDomain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANDomain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANDomain`
--
-- ORDER BY:  `id`

LOCK TABLES `VLANDomain` WRITE;
/*!40000 ALTER TABLE `VLANDomain` DISABLE KEYS */;
/*!40000 ALTER TABLE `VLANDomain` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANIPv4`
--

DROP TABLE IF EXISTS `VLANIPv4`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANIPv4` (
  `domain_id` int(10) unsigned NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL,
  `ipv4net_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `network-domain-vlan` (`ipv4net_id`,`domain_id`,`vlan_id`),
  KEY `VLANIPv4-FK-compound` (`domain_id`,`vlan_id`),
  CONSTRAINT `VLANIPv4-FK-compound` FOREIGN KEY (`domain_id`, `vlan_id`) REFERENCES `VLANDescription` (`domain_id`, `vlan_id`) ON DELETE CASCADE,
  CONSTRAINT `VLANIPv4-FK-ipv4net_id` FOREIGN KEY (`ipv4net_id`) REFERENCES `IPv4Network` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANIPv4`
--
-- ORDER BY:  `ipv4net_id`,`domain_id`,`vlan_id`

LOCK TABLES `VLANIPv4` WRITE;
/*!40000 ALTER TABLE `VLANIPv4` DISABLE KEYS */;
/*!40000 ALTER TABLE `VLANIPv4` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANIPv6`
--

DROP TABLE IF EXISTS `VLANIPv6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANIPv6` (
  `domain_id` int(10) unsigned NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL,
  `ipv6net_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `network-domain-vlan` (`ipv6net_id`,`domain_id`,`vlan_id`),
  KEY `VLANIPv6-FK-compound` (`domain_id`,`vlan_id`),
  CONSTRAINT `VLANIPv6-FK-compound` FOREIGN KEY (`domain_id`, `vlan_id`) REFERENCES `VLANDescription` (`domain_id`, `vlan_id`) ON DELETE CASCADE,
  CONSTRAINT `VLANIPv6-FK-ipv6net_id` FOREIGN KEY (`ipv6net_id`) REFERENCES `IPv6Network` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANIPv6`
--
-- ORDER BY:  `ipv6net_id`,`domain_id`,`vlan_id`

LOCK TABLES `VLANIPv6` WRITE;
/*!40000 ALTER TABLE `VLANIPv6` DISABLE KEYS */;
/*!40000 ALTER TABLE `VLANIPv6` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANSTRule`
--

DROP TABLE IF EXISTS `VLANSTRule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANSTRule` (
  `vst_id` int(10) unsigned NOT NULL,
  `rule_no` int(10) unsigned NOT NULL,
  `port_pcre` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `port_role` enum('access','trunk','anymode','uplink','downlink','none') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  `wrt_vlans` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `vst-rule` (`vst_id`,`rule_no`),
  CONSTRAINT `VLANSTRule-FK-vst_id` FOREIGN KEY (`vst_id`) REFERENCES `VLANSwitchTemplate` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANSTRule`
--
-- ORDER BY:  `vst_id`,`rule_no`

LOCK TABLES `VLANSTRule` WRITE;
/*!40000 ALTER TABLE `VLANSTRule` DISABLE KEYS */;
/*!40000 ALTER TABLE `VLANSTRule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANSwitch`
--

DROP TABLE IF EXISTS `VLANSwitch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANSwitch` (
  `object_id` int(10) unsigned NOT NULL,
  `domain_id` int(10) unsigned NOT NULL,
  `template_id` int(10) unsigned NOT NULL,
  `mutex_rev` int(10) unsigned NOT NULL DEFAULT '0',
  `out_of_sync` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `last_errno` int(10) unsigned NOT NULL DEFAULT '0',
  `last_change` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_push_started` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_push_finished` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_error_ts` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `object_id` (`object_id`),
  KEY `domain_id` (`domain_id`),
  KEY `template_id` (`template_id`),
  KEY `out_of_sync` (`out_of_sync`),
  KEY `last_errno` (`last_errno`),
  CONSTRAINT `VLANSwitch-FK-domain_id` FOREIGN KEY (`domain_id`) REFERENCES `VLANDomain` (`id`),
  CONSTRAINT `VLANSwitch-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`),
  CONSTRAINT `VLANSwitch-FK-template_id` FOREIGN KEY (`template_id`) REFERENCES `VLANSwitchTemplate` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANSwitch`
--
-- ORDER BY:  `object_id`

LOCK TABLES `VLANSwitch` WRITE;
/*!40000 ALTER TABLE `VLANSwitch` DISABLE KEYS */;
/*!40000 ALTER TABLE `VLANSwitch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANSwitchTemplate`
--

DROP TABLE IF EXISTS `VLANSwitchTemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANSwitchTemplate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mutex_rev` int(10) NOT NULL,
  `description` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `saved_by` char(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANSwitchTemplate`
--
-- ORDER BY:  `id`

LOCK TABLES `VLANSwitchTemplate` WRITE;
/*!40000 ALTER TABLE `VLANSwitchTemplate` DISABLE KEYS */;
/*!40000 ALTER TABLE `VLANSwitchTemplate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VLANValidID`
--

DROP TABLE IF EXISTS `VLANValidID`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VLANValidID` (
  `vlan_id` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`vlan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VLANValidID`
--
-- ORDER BY:  `vlan_id`

LOCK TABLES `VLANValidID` WRITE;
/*!40000 ALTER TABLE `VLANValidID` DISABLE KEYS */;
INSERT INTO `VLANValidID` VALUES (1);
INSERT INTO `VLANValidID` VALUES (2);
INSERT INTO `VLANValidID` VALUES (3);
INSERT INTO `VLANValidID` VALUES (4);
INSERT INTO `VLANValidID` VALUES (5);
INSERT INTO `VLANValidID` VALUES (6);
INSERT INTO `VLANValidID` VALUES (7);
INSERT INTO `VLANValidID` VALUES (8);
INSERT INTO `VLANValidID` VALUES (9);
INSERT INTO `VLANValidID` VALUES (10);
INSERT INTO `VLANValidID` VALUES (11);
INSERT INTO `VLANValidID` VALUES (12);
INSERT INTO `VLANValidID` VALUES (13);
INSERT INTO `VLANValidID` VALUES (14);
INSERT INTO `VLANValidID` VALUES (15);
INSERT INTO `VLANValidID` VALUES (16);
INSERT INTO `VLANValidID` VALUES (17);
INSERT INTO `VLANValidID` VALUES (18);
INSERT INTO `VLANValidID` VALUES (19);
INSERT INTO `VLANValidID` VALUES (20);
INSERT INTO `VLANValidID` VALUES (21);
INSERT INTO `VLANValidID` VALUES (22);
INSERT INTO `VLANValidID` VALUES (23);
INSERT INTO `VLANValidID` VALUES (24);
INSERT INTO `VLANValidID` VALUES (25);
INSERT INTO `VLANValidID` VALUES (26);
INSERT INTO `VLANValidID` VALUES (27);
INSERT INTO `VLANValidID` VALUES (28);
INSERT INTO `VLANValidID` VALUES (29);
INSERT INTO `VLANValidID` VALUES (30);
INSERT INTO `VLANValidID` VALUES (31);
INSERT INTO `VLANValidID` VALUES (32);
INSERT INTO `VLANValidID` VALUES (33);
INSERT INTO `VLANValidID` VALUES (34);
INSERT INTO `VLANValidID` VALUES (35);
INSERT INTO `VLANValidID` VALUES (36);
INSERT INTO `VLANValidID` VALUES (37);
INSERT INTO `VLANValidID` VALUES (38);
INSERT INTO `VLANValidID` VALUES (39);
INSERT INTO `VLANValidID` VALUES (40);
INSERT INTO `VLANValidID` VALUES (41);
INSERT INTO `VLANValidID` VALUES (42);
INSERT INTO `VLANValidID` VALUES (43);
INSERT INTO `VLANValidID` VALUES (44);
INSERT INTO `VLANValidID` VALUES (45);
INSERT INTO `VLANValidID` VALUES (46);
INSERT INTO `VLANValidID` VALUES (47);
INSERT INTO `VLANValidID` VALUES (48);
INSERT INTO `VLANValidID` VALUES (49);
INSERT INTO `VLANValidID` VALUES (50);
INSERT INTO `VLANValidID` VALUES (51);
INSERT INTO `VLANValidID` VALUES (52);
INSERT INTO `VLANValidID` VALUES (53);
INSERT INTO `VLANValidID` VALUES (54);
INSERT INTO `VLANValidID` VALUES (55);
INSERT INTO `VLANValidID` VALUES (56);
INSERT INTO `VLANValidID` VALUES (57);
INSERT INTO `VLANValidID` VALUES (58);
INSERT INTO `VLANValidID` VALUES (59);
INSERT INTO `VLANValidID` VALUES (60);
INSERT INTO `VLANValidID` VALUES (61);
INSERT INTO `VLANValidID` VALUES (62);
INSERT INTO `VLANValidID` VALUES (63);
INSERT INTO `VLANValidID` VALUES (64);
INSERT INTO `VLANValidID` VALUES (65);
INSERT INTO `VLANValidID` VALUES (66);
INSERT INTO `VLANValidID` VALUES (67);
INSERT INTO `VLANValidID` VALUES (68);
INSERT INTO `VLANValidID` VALUES (69);
INSERT INTO `VLANValidID` VALUES (70);
INSERT INTO `VLANValidID` VALUES (71);
INSERT INTO `VLANValidID` VALUES (72);
INSERT INTO `VLANValidID` VALUES (73);
INSERT INTO `VLANValidID` VALUES (74);
INSERT INTO `VLANValidID` VALUES (75);
INSERT INTO `VLANValidID` VALUES (76);
INSERT INTO `VLANValidID` VALUES (77);
INSERT INTO `VLANValidID` VALUES (78);
INSERT INTO `VLANValidID` VALUES (79);
INSERT INTO `VLANValidID` VALUES (80);
INSERT INTO `VLANValidID` VALUES (81);
INSERT INTO `VLANValidID` VALUES (82);
INSERT INTO `VLANValidID` VALUES (83);
INSERT INTO `VLANValidID` VALUES (84);
INSERT INTO `VLANValidID` VALUES (85);
INSERT INTO `VLANValidID` VALUES (86);
INSERT INTO `VLANValidID` VALUES (87);
INSERT INTO `VLANValidID` VALUES (88);
INSERT INTO `VLANValidID` VALUES (89);
INSERT INTO `VLANValidID` VALUES (90);
INSERT INTO `VLANValidID` VALUES (91);
INSERT INTO `VLANValidID` VALUES (92);
INSERT INTO `VLANValidID` VALUES (93);
INSERT INTO `VLANValidID` VALUES (94);
INSERT INTO `VLANValidID` VALUES (95);
INSERT INTO `VLANValidID` VALUES (96);
INSERT INTO `VLANValidID` VALUES (97);
INSERT INTO `VLANValidID` VALUES (98);
INSERT INTO `VLANValidID` VALUES (99);
INSERT INTO `VLANValidID` VALUES (100);
INSERT INTO `VLANValidID` VALUES (101);
INSERT INTO `VLANValidID` VALUES (102);
INSERT INTO `VLANValidID` VALUES (103);
INSERT INTO `VLANValidID` VALUES (104);
INSERT INTO `VLANValidID` VALUES (105);
INSERT INTO `VLANValidID` VALUES (106);
INSERT INTO `VLANValidID` VALUES (107);
INSERT INTO `VLANValidID` VALUES (108);
INSERT INTO `VLANValidID` VALUES (109);
INSERT INTO `VLANValidID` VALUES (110);
INSERT INTO `VLANValidID` VALUES (111);
INSERT INTO `VLANValidID` VALUES (112);
INSERT INTO `VLANValidID` VALUES (113);
INSERT INTO `VLANValidID` VALUES (114);
INSERT INTO `VLANValidID` VALUES (115);
INSERT INTO `VLANValidID` VALUES (116);
INSERT INTO `VLANValidID` VALUES (117);
INSERT INTO `VLANValidID` VALUES (118);
INSERT INTO `VLANValidID` VALUES (119);
INSERT INTO `VLANValidID` VALUES (120);
INSERT INTO `VLANValidID` VALUES (121);
INSERT INTO `VLANValidID` VALUES (122);
INSERT INTO `VLANValidID` VALUES (123);
INSERT INTO `VLANValidID` VALUES (124);
INSERT INTO `VLANValidID` VALUES (125);
INSERT INTO `VLANValidID` VALUES (126);
INSERT INTO `VLANValidID` VALUES (127);
INSERT INTO `VLANValidID` VALUES (128);
INSERT INTO `VLANValidID` VALUES (129);
INSERT INTO `VLANValidID` VALUES (130);
INSERT INTO `VLANValidID` VALUES (131);
INSERT INTO `VLANValidID` VALUES (132);
INSERT INTO `VLANValidID` VALUES (133);
INSERT INTO `VLANValidID` VALUES (134);
INSERT INTO `VLANValidID` VALUES (135);
INSERT INTO `VLANValidID` VALUES (136);
INSERT INTO `VLANValidID` VALUES (137);
INSERT INTO `VLANValidID` VALUES (138);
INSERT INTO `VLANValidID` VALUES (139);
INSERT INTO `VLANValidID` VALUES (140);
INSERT INTO `VLANValidID` VALUES (141);
INSERT INTO `VLANValidID` VALUES (142);
INSERT INTO `VLANValidID` VALUES (143);
INSERT INTO `VLANValidID` VALUES (144);
INSERT INTO `VLANValidID` VALUES (145);
INSERT INTO `VLANValidID` VALUES (146);
INSERT INTO `VLANValidID` VALUES (147);
INSERT INTO `VLANValidID` VALUES (148);
INSERT INTO `VLANValidID` VALUES (149);
INSERT INTO `VLANValidID` VALUES (150);
INSERT INTO `VLANValidID` VALUES (151);
INSERT INTO `VLANValidID` VALUES (152);
INSERT INTO `VLANValidID` VALUES (153);
INSERT INTO `VLANValidID` VALUES (154);
INSERT INTO `VLANValidID` VALUES (155);
INSERT INTO `VLANValidID` VALUES (156);
INSERT INTO `VLANValidID` VALUES (157);
INSERT INTO `VLANValidID` VALUES (158);
INSERT INTO `VLANValidID` VALUES (159);
INSERT INTO `VLANValidID` VALUES (160);
INSERT INTO `VLANValidID` VALUES (161);
INSERT INTO `VLANValidID` VALUES (162);
INSERT INTO `VLANValidID` VALUES (163);
INSERT INTO `VLANValidID` VALUES (164);
INSERT INTO `VLANValidID` VALUES (165);
INSERT INTO `VLANValidID` VALUES (166);
INSERT INTO `VLANValidID` VALUES (167);
INSERT INTO `VLANValidID` VALUES (168);
INSERT INTO `VLANValidID` VALUES (169);
INSERT INTO `VLANValidID` VALUES (170);
INSERT INTO `VLANValidID` VALUES (171);
INSERT INTO `VLANValidID` VALUES (172);
INSERT INTO `VLANValidID` VALUES (173);
INSERT INTO `VLANValidID` VALUES (174);
INSERT INTO `VLANValidID` VALUES (175);
INSERT INTO `VLANValidID` VALUES (176);
INSERT INTO `VLANValidID` VALUES (177);
INSERT INTO `VLANValidID` VALUES (178);
INSERT INTO `VLANValidID` VALUES (179);
INSERT INTO `VLANValidID` VALUES (180);
INSERT INTO `VLANValidID` VALUES (181);
INSERT INTO `VLANValidID` VALUES (182);
INSERT INTO `VLANValidID` VALUES (183);
INSERT INTO `VLANValidID` VALUES (184);
INSERT INTO `VLANValidID` VALUES (185);
INSERT INTO `VLANValidID` VALUES (186);
INSERT INTO `VLANValidID` VALUES (187);
INSERT INTO `VLANValidID` VALUES (188);
INSERT INTO `VLANValidID` VALUES (189);
INSERT INTO `VLANValidID` VALUES (190);
INSERT INTO `VLANValidID` VALUES (191);
INSERT INTO `VLANValidID` VALUES (192);
INSERT INTO `VLANValidID` VALUES (193);
INSERT INTO `VLANValidID` VALUES (194);
INSERT INTO `VLANValidID` VALUES (195);
INSERT INTO `VLANValidID` VALUES (196);
INSERT INTO `VLANValidID` VALUES (197);
INSERT INTO `VLANValidID` VALUES (198);
INSERT INTO `VLANValidID` VALUES (199);
INSERT INTO `VLANValidID` VALUES (200);
INSERT INTO `VLANValidID` VALUES (201);
INSERT INTO `VLANValidID` VALUES (202);
INSERT INTO `VLANValidID` VALUES (203);
INSERT INTO `VLANValidID` VALUES (204);
INSERT INTO `VLANValidID` VALUES (205);
INSERT INTO `VLANValidID` VALUES (206);
INSERT INTO `VLANValidID` VALUES (207);
INSERT INTO `VLANValidID` VALUES (208);
INSERT INTO `VLANValidID` VALUES (209);
INSERT INTO `VLANValidID` VALUES (210);
INSERT INTO `VLANValidID` VALUES (211);
INSERT INTO `VLANValidID` VALUES (212);
INSERT INTO `VLANValidID` VALUES (213);
INSERT INTO `VLANValidID` VALUES (214);
INSERT INTO `VLANValidID` VALUES (215);
INSERT INTO `VLANValidID` VALUES (216);
INSERT INTO `VLANValidID` VALUES (217);
INSERT INTO `VLANValidID` VALUES (218);
INSERT INTO `VLANValidID` VALUES (219);
INSERT INTO `VLANValidID` VALUES (220);
INSERT INTO `VLANValidID` VALUES (221);
INSERT INTO `VLANValidID` VALUES (222);
INSERT INTO `VLANValidID` VALUES (223);
INSERT INTO `VLANValidID` VALUES (224);
INSERT INTO `VLANValidID` VALUES (225);
INSERT INTO `VLANValidID` VALUES (226);
INSERT INTO `VLANValidID` VALUES (227);
INSERT INTO `VLANValidID` VALUES (228);
INSERT INTO `VLANValidID` VALUES (229);
INSERT INTO `VLANValidID` VALUES (230);
INSERT INTO `VLANValidID` VALUES (231);
INSERT INTO `VLANValidID` VALUES (232);
INSERT INTO `VLANValidID` VALUES (233);
INSERT INTO `VLANValidID` VALUES (234);
INSERT INTO `VLANValidID` VALUES (235);
INSERT INTO `VLANValidID` VALUES (236);
INSERT INTO `VLANValidID` VALUES (237);
INSERT INTO `VLANValidID` VALUES (238);
INSERT INTO `VLANValidID` VALUES (239);
INSERT INTO `VLANValidID` VALUES (240);
INSERT INTO `VLANValidID` VALUES (241);
INSERT INTO `VLANValidID` VALUES (242);
INSERT INTO `VLANValidID` VALUES (243);
INSERT INTO `VLANValidID` VALUES (244);
INSERT INTO `VLANValidID` VALUES (245);
INSERT INTO `VLANValidID` VALUES (246);
INSERT INTO `VLANValidID` VALUES (247);
INSERT INTO `VLANValidID` VALUES (248);
INSERT INTO `VLANValidID` VALUES (249);
INSERT INTO `VLANValidID` VALUES (250);
INSERT INTO `VLANValidID` VALUES (251);
INSERT INTO `VLANValidID` VALUES (252);
INSERT INTO `VLANValidID` VALUES (253);
INSERT INTO `VLANValidID` VALUES (254);
INSERT INTO `VLANValidID` VALUES (255);
INSERT INTO `VLANValidID` VALUES (256);
INSERT INTO `VLANValidID` VALUES (257);
INSERT INTO `VLANValidID` VALUES (258);
INSERT INTO `VLANValidID` VALUES (259);
INSERT INTO `VLANValidID` VALUES (260);
INSERT INTO `VLANValidID` VALUES (261);
INSERT INTO `VLANValidID` VALUES (262);
INSERT INTO `VLANValidID` VALUES (263);
INSERT INTO `VLANValidID` VALUES (264);
INSERT INTO `VLANValidID` VALUES (265);
INSERT INTO `VLANValidID` VALUES (266);
INSERT INTO `VLANValidID` VALUES (267);
INSERT INTO `VLANValidID` VALUES (268);
INSERT INTO `VLANValidID` VALUES (269);
INSERT INTO `VLANValidID` VALUES (270);
INSERT INTO `VLANValidID` VALUES (271);
INSERT INTO `VLANValidID` VALUES (272);
INSERT INTO `VLANValidID` VALUES (273);
INSERT INTO `VLANValidID` VALUES (274);
INSERT INTO `VLANValidID` VALUES (275);
INSERT INTO `VLANValidID` VALUES (276);
INSERT INTO `VLANValidID` VALUES (277);
INSERT INTO `VLANValidID` VALUES (278);
INSERT INTO `VLANValidID` VALUES (279);
INSERT INTO `VLANValidID` VALUES (280);
INSERT INTO `VLANValidID` VALUES (281);
INSERT INTO `VLANValidID` VALUES (282);
INSERT INTO `VLANValidID` VALUES (283);
INSERT INTO `VLANValidID` VALUES (284);
INSERT INTO `VLANValidID` VALUES (285);
INSERT INTO `VLANValidID` VALUES (286);
INSERT INTO `VLANValidID` VALUES (287);
INSERT INTO `VLANValidID` VALUES (288);
INSERT INTO `VLANValidID` VALUES (289);
INSERT INTO `VLANValidID` VALUES (290);
INSERT INTO `VLANValidID` VALUES (291);
INSERT INTO `VLANValidID` VALUES (292);
INSERT INTO `VLANValidID` VALUES (293);
INSERT INTO `VLANValidID` VALUES (294);
INSERT INTO `VLANValidID` VALUES (295);
INSERT INTO `VLANValidID` VALUES (296);
INSERT INTO `VLANValidID` VALUES (297);
INSERT INTO `VLANValidID` VALUES (298);
INSERT INTO `VLANValidID` VALUES (299);
INSERT INTO `VLANValidID` VALUES (300);
INSERT INTO `VLANValidID` VALUES (301);
INSERT INTO `VLANValidID` VALUES (302);
INSERT INTO `VLANValidID` VALUES (303);
INSERT INTO `VLANValidID` VALUES (304);
INSERT INTO `VLANValidID` VALUES (305);
INSERT INTO `VLANValidID` VALUES (306);
INSERT INTO `VLANValidID` VALUES (307);
INSERT INTO `VLANValidID` VALUES (308);
INSERT INTO `VLANValidID` VALUES (309);
INSERT INTO `VLANValidID` VALUES (310);
INSERT INTO `VLANValidID` VALUES (311);
INSERT INTO `VLANValidID` VALUES (312);
INSERT INTO `VLANValidID` VALUES (313);
INSERT INTO `VLANValidID` VALUES (314);
INSERT INTO `VLANValidID` VALUES (315);
INSERT INTO `VLANValidID` VALUES (316);
INSERT INTO `VLANValidID` VALUES (317);
INSERT INTO `VLANValidID` VALUES (318);
INSERT INTO `VLANValidID` VALUES (319);
INSERT INTO `VLANValidID` VALUES (320);
INSERT INTO `VLANValidID` VALUES (321);
INSERT INTO `VLANValidID` VALUES (322);
INSERT INTO `VLANValidID` VALUES (323);
INSERT INTO `VLANValidID` VALUES (324);
INSERT INTO `VLANValidID` VALUES (325);
INSERT INTO `VLANValidID` VALUES (326);
INSERT INTO `VLANValidID` VALUES (327);
INSERT INTO `VLANValidID` VALUES (328);
INSERT INTO `VLANValidID` VALUES (329);
INSERT INTO `VLANValidID` VALUES (330);
INSERT INTO `VLANValidID` VALUES (331);
INSERT INTO `VLANValidID` VALUES (332);
INSERT INTO `VLANValidID` VALUES (333);
INSERT INTO `VLANValidID` VALUES (334);
INSERT INTO `VLANValidID` VALUES (335);
INSERT INTO `VLANValidID` VALUES (336);
INSERT INTO `VLANValidID` VALUES (337);
INSERT INTO `VLANValidID` VALUES (338);
INSERT INTO `VLANValidID` VALUES (339);
INSERT INTO `VLANValidID` VALUES (340);
INSERT INTO `VLANValidID` VALUES (341);
INSERT INTO `VLANValidID` VALUES (342);
INSERT INTO `VLANValidID` VALUES (343);
INSERT INTO `VLANValidID` VALUES (344);
INSERT INTO `VLANValidID` VALUES (345);
INSERT INTO `VLANValidID` VALUES (346);
INSERT INTO `VLANValidID` VALUES (347);
INSERT INTO `VLANValidID` VALUES (348);
INSERT INTO `VLANValidID` VALUES (349);
INSERT INTO `VLANValidID` VALUES (350);
INSERT INTO `VLANValidID` VALUES (351);
INSERT INTO `VLANValidID` VALUES (352);
INSERT INTO `VLANValidID` VALUES (353);
INSERT INTO `VLANValidID` VALUES (354);
INSERT INTO `VLANValidID` VALUES (355);
INSERT INTO `VLANValidID` VALUES (356);
INSERT INTO `VLANValidID` VALUES (357);
INSERT INTO `VLANValidID` VALUES (358);
INSERT INTO `VLANValidID` VALUES (359);
INSERT INTO `VLANValidID` VALUES (360);
INSERT INTO `VLANValidID` VALUES (361);
INSERT INTO `VLANValidID` VALUES (362);
INSERT INTO `VLANValidID` VALUES (363);
INSERT INTO `VLANValidID` VALUES (364);
INSERT INTO `VLANValidID` VALUES (365);
INSERT INTO `VLANValidID` VALUES (366);
INSERT INTO `VLANValidID` VALUES (367);
INSERT INTO `VLANValidID` VALUES (368);
INSERT INTO `VLANValidID` VALUES (369);
INSERT INTO `VLANValidID` VALUES (370);
INSERT INTO `VLANValidID` VALUES (371);
INSERT INTO `VLANValidID` VALUES (372);
INSERT INTO `VLANValidID` VALUES (373);
INSERT INTO `VLANValidID` VALUES (374);
INSERT INTO `VLANValidID` VALUES (375);
INSERT INTO `VLANValidID` VALUES (376);
INSERT INTO `VLANValidID` VALUES (377);
INSERT INTO `VLANValidID` VALUES (378);
INSERT INTO `VLANValidID` VALUES (379);
INSERT INTO `VLANValidID` VALUES (380);
INSERT INTO `VLANValidID` VALUES (381);
INSERT INTO `VLANValidID` VALUES (382);
INSERT INTO `VLANValidID` VALUES (383);
INSERT INTO `VLANValidID` VALUES (384);
INSERT INTO `VLANValidID` VALUES (385);
INSERT INTO `VLANValidID` VALUES (386);
INSERT INTO `VLANValidID` VALUES (387);
INSERT INTO `VLANValidID` VALUES (388);
INSERT INTO `VLANValidID` VALUES (389);
INSERT INTO `VLANValidID` VALUES (390);
INSERT INTO `VLANValidID` VALUES (391);
INSERT INTO `VLANValidID` VALUES (392);
INSERT INTO `VLANValidID` VALUES (393);
INSERT INTO `VLANValidID` VALUES (394);
INSERT INTO `VLANValidID` VALUES (395);
INSERT INTO `VLANValidID` VALUES (396);
INSERT INTO `VLANValidID` VALUES (397);
INSERT INTO `VLANValidID` VALUES (398);
INSERT INTO `VLANValidID` VALUES (399);
INSERT INTO `VLANValidID` VALUES (400);
INSERT INTO `VLANValidID` VALUES (401);
INSERT INTO `VLANValidID` VALUES (402);
INSERT INTO `VLANValidID` VALUES (403);
INSERT INTO `VLANValidID` VALUES (404);
INSERT INTO `VLANValidID` VALUES (405);
INSERT INTO `VLANValidID` VALUES (406);
INSERT INTO `VLANValidID` VALUES (407);
INSERT INTO `VLANValidID` VALUES (408);
INSERT INTO `VLANValidID` VALUES (409);
INSERT INTO `VLANValidID` VALUES (410);
INSERT INTO `VLANValidID` VALUES (411);
INSERT INTO `VLANValidID` VALUES (412);
INSERT INTO `VLANValidID` VALUES (413);
INSERT INTO `VLANValidID` VALUES (414);
INSERT INTO `VLANValidID` VALUES (415);
INSERT INTO `VLANValidID` VALUES (416);
INSERT INTO `VLANValidID` VALUES (417);
INSERT INTO `VLANValidID` VALUES (418);
INSERT INTO `VLANValidID` VALUES (419);
INSERT INTO `VLANValidID` VALUES (420);
INSERT INTO `VLANValidID` VALUES (421);
INSERT INTO `VLANValidID` VALUES (422);
INSERT INTO `VLANValidID` VALUES (423);
INSERT INTO `VLANValidID` VALUES (424);
INSERT INTO `VLANValidID` VALUES (425);
INSERT INTO `VLANValidID` VALUES (426);
INSERT INTO `VLANValidID` VALUES (427);
INSERT INTO `VLANValidID` VALUES (428);
INSERT INTO `VLANValidID` VALUES (429);
INSERT INTO `VLANValidID` VALUES (430);
INSERT INTO `VLANValidID` VALUES (431);
INSERT INTO `VLANValidID` VALUES (432);
INSERT INTO `VLANValidID` VALUES (433);
INSERT INTO `VLANValidID` VALUES (434);
INSERT INTO `VLANValidID` VALUES (435);
INSERT INTO `VLANValidID` VALUES (436);
INSERT INTO `VLANValidID` VALUES (437);
INSERT INTO `VLANValidID` VALUES (438);
INSERT INTO `VLANValidID` VALUES (439);
INSERT INTO `VLANValidID` VALUES (440);
INSERT INTO `VLANValidID` VALUES (441);
INSERT INTO `VLANValidID` VALUES (442);
INSERT INTO `VLANValidID` VALUES (443);
INSERT INTO `VLANValidID` VALUES (444);
INSERT INTO `VLANValidID` VALUES (445);
INSERT INTO `VLANValidID` VALUES (446);
INSERT INTO `VLANValidID` VALUES (447);
INSERT INTO `VLANValidID` VALUES (448);
INSERT INTO `VLANValidID` VALUES (449);
INSERT INTO `VLANValidID` VALUES (450);
INSERT INTO `VLANValidID` VALUES (451);
INSERT INTO `VLANValidID` VALUES (452);
INSERT INTO `VLANValidID` VALUES (453);
INSERT INTO `VLANValidID` VALUES (454);
INSERT INTO `VLANValidID` VALUES (455);
INSERT INTO `VLANValidID` VALUES (456);
INSERT INTO `VLANValidID` VALUES (457);
INSERT INTO `VLANValidID` VALUES (458);
INSERT INTO `VLANValidID` VALUES (459);
INSERT INTO `VLANValidID` VALUES (460);
INSERT INTO `VLANValidID` VALUES (461);
INSERT INTO `VLANValidID` VALUES (462);
INSERT INTO `VLANValidID` VALUES (463);
INSERT INTO `VLANValidID` VALUES (464);
INSERT INTO `VLANValidID` VALUES (465);
INSERT INTO `VLANValidID` VALUES (466);
INSERT INTO `VLANValidID` VALUES (467);
INSERT INTO `VLANValidID` VALUES (468);
INSERT INTO `VLANValidID` VALUES (469);
INSERT INTO `VLANValidID` VALUES (470);
INSERT INTO `VLANValidID` VALUES (471);
INSERT INTO `VLANValidID` VALUES (472);
INSERT INTO `VLANValidID` VALUES (473);
INSERT INTO `VLANValidID` VALUES (474);
INSERT INTO `VLANValidID` VALUES (475);
INSERT INTO `VLANValidID` VALUES (476);
INSERT INTO `VLANValidID` VALUES (477);
INSERT INTO `VLANValidID` VALUES (478);
INSERT INTO `VLANValidID` VALUES (479);
INSERT INTO `VLANValidID` VALUES (480);
INSERT INTO `VLANValidID` VALUES (481);
INSERT INTO `VLANValidID` VALUES (482);
INSERT INTO `VLANValidID` VALUES (483);
INSERT INTO `VLANValidID` VALUES (484);
INSERT INTO `VLANValidID` VALUES (485);
INSERT INTO `VLANValidID` VALUES (486);
INSERT INTO `VLANValidID` VALUES (487);
INSERT INTO `VLANValidID` VALUES (488);
INSERT INTO `VLANValidID` VALUES (489);
INSERT INTO `VLANValidID` VALUES (490);
INSERT INTO `VLANValidID` VALUES (491);
INSERT INTO `VLANValidID` VALUES (492);
INSERT INTO `VLANValidID` VALUES (493);
INSERT INTO `VLANValidID` VALUES (494);
INSERT INTO `VLANValidID` VALUES (495);
INSERT INTO `VLANValidID` VALUES (496);
INSERT INTO `VLANValidID` VALUES (497);
INSERT INTO `VLANValidID` VALUES (498);
INSERT INTO `VLANValidID` VALUES (499);
INSERT INTO `VLANValidID` VALUES (500);
INSERT INTO `VLANValidID` VALUES (501);
INSERT INTO `VLANValidID` VALUES (502);
INSERT INTO `VLANValidID` VALUES (503);
INSERT INTO `VLANValidID` VALUES (504);
INSERT INTO `VLANValidID` VALUES (505);
INSERT INTO `VLANValidID` VALUES (506);
INSERT INTO `VLANValidID` VALUES (507);
INSERT INTO `VLANValidID` VALUES (508);
INSERT INTO `VLANValidID` VALUES (509);
INSERT INTO `VLANValidID` VALUES (510);
INSERT INTO `VLANValidID` VALUES (511);
INSERT INTO `VLANValidID` VALUES (512);
INSERT INTO `VLANValidID` VALUES (513);
INSERT INTO `VLANValidID` VALUES (514);
INSERT INTO `VLANValidID` VALUES (515);
INSERT INTO `VLANValidID` VALUES (516);
INSERT INTO `VLANValidID` VALUES (517);
INSERT INTO `VLANValidID` VALUES (518);
INSERT INTO `VLANValidID` VALUES (519);
INSERT INTO `VLANValidID` VALUES (520);
INSERT INTO `VLANValidID` VALUES (521);
INSERT INTO `VLANValidID` VALUES (522);
INSERT INTO `VLANValidID` VALUES (523);
INSERT INTO `VLANValidID` VALUES (524);
INSERT INTO `VLANValidID` VALUES (525);
INSERT INTO `VLANValidID` VALUES (526);
INSERT INTO `VLANValidID` VALUES (527);
INSERT INTO `VLANValidID` VALUES (528);
INSERT INTO `VLANValidID` VALUES (529);
INSERT INTO `VLANValidID` VALUES (530);
INSERT INTO `VLANValidID` VALUES (531);
INSERT INTO `VLANValidID` VALUES (532);
INSERT INTO `VLANValidID` VALUES (533);
INSERT INTO `VLANValidID` VALUES (534);
INSERT INTO `VLANValidID` VALUES (535);
INSERT INTO `VLANValidID` VALUES (536);
INSERT INTO `VLANValidID` VALUES (537);
INSERT INTO `VLANValidID` VALUES (538);
INSERT INTO `VLANValidID` VALUES (539);
INSERT INTO `VLANValidID` VALUES (540);
INSERT INTO `VLANValidID` VALUES (541);
INSERT INTO `VLANValidID` VALUES (542);
INSERT INTO `VLANValidID` VALUES (543);
INSERT INTO `VLANValidID` VALUES (544);
INSERT INTO `VLANValidID` VALUES (545);
INSERT INTO `VLANValidID` VALUES (546);
INSERT INTO `VLANValidID` VALUES (547);
INSERT INTO `VLANValidID` VALUES (548);
INSERT INTO `VLANValidID` VALUES (549);
INSERT INTO `VLANValidID` VALUES (550);
INSERT INTO `VLANValidID` VALUES (551);
INSERT INTO `VLANValidID` VALUES (552);
INSERT INTO `VLANValidID` VALUES (553);
INSERT INTO `VLANValidID` VALUES (554);
INSERT INTO `VLANValidID` VALUES (555);
INSERT INTO `VLANValidID` VALUES (556);
INSERT INTO `VLANValidID` VALUES (557);
INSERT INTO `VLANValidID` VALUES (558);
INSERT INTO `VLANValidID` VALUES (559);
INSERT INTO `VLANValidID` VALUES (560);
INSERT INTO `VLANValidID` VALUES (561);
INSERT INTO `VLANValidID` VALUES (562);
INSERT INTO `VLANValidID` VALUES (563);
INSERT INTO `VLANValidID` VALUES (564);
INSERT INTO `VLANValidID` VALUES (565);
INSERT INTO `VLANValidID` VALUES (566);
INSERT INTO `VLANValidID` VALUES (567);
INSERT INTO `VLANValidID` VALUES (568);
INSERT INTO `VLANValidID` VALUES (569);
INSERT INTO `VLANValidID` VALUES (570);
INSERT INTO `VLANValidID` VALUES (571);
INSERT INTO `VLANValidID` VALUES (572);
INSERT INTO `VLANValidID` VALUES (573);
INSERT INTO `VLANValidID` VALUES (574);
INSERT INTO `VLANValidID` VALUES (575);
INSERT INTO `VLANValidID` VALUES (576);
INSERT INTO `VLANValidID` VALUES (577);
INSERT INTO `VLANValidID` VALUES (578);
INSERT INTO `VLANValidID` VALUES (579);
INSERT INTO `VLANValidID` VALUES (580);
INSERT INTO `VLANValidID` VALUES (581);
INSERT INTO `VLANValidID` VALUES (582);
INSERT INTO `VLANValidID` VALUES (583);
INSERT INTO `VLANValidID` VALUES (584);
INSERT INTO `VLANValidID` VALUES (585);
INSERT INTO `VLANValidID` VALUES (586);
INSERT INTO `VLANValidID` VALUES (587);
INSERT INTO `VLANValidID` VALUES (588);
INSERT INTO `VLANValidID` VALUES (589);
INSERT INTO `VLANValidID` VALUES (590);
INSERT INTO `VLANValidID` VALUES (591);
INSERT INTO `VLANValidID` VALUES (592);
INSERT INTO `VLANValidID` VALUES (593);
INSERT INTO `VLANValidID` VALUES (594);
INSERT INTO `VLANValidID` VALUES (595);
INSERT INTO `VLANValidID` VALUES (596);
INSERT INTO `VLANValidID` VALUES (597);
INSERT INTO `VLANValidID` VALUES (598);
INSERT INTO `VLANValidID` VALUES (599);
INSERT INTO `VLANValidID` VALUES (600);
INSERT INTO `VLANValidID` VALUES (601);
INSERT INTO `VLANValidID` VALUES (602);
INSERT INTO `VLANValidID` VALUES (603);
INSERT INTO `VLANValidID` VALUES (604);
INSERT INTO `VLANValidID` VALUES (605);
INSERT INTO `VLANValidID` VALUES (606);
INSERT INTO `VLANValidID` VALUES (607);
INSERT INTO `VLANValidID` VALUES (608);
INSERT INTO `VLANValidID` VALUES (609);
INSERT INTO `VLANValidID` VALUES (610);
INSERT INTO `VLANValidID` VALUES (611);
INSERT INTO `VLANValidID` VALUES (612);
INSERT INTO `VLANValidID` VALUES (613);
INSERT INTO `VLANValidID` VALUES (614);
INSERT INTO `VLANValidID` VALUES (615);
INSERT INTO `VLANValidID` VALUES (616);
INSERT INTO `VLANValidID` VALUES (617);
INSERT INTO `VLANValidID` VALUES (618);
INSERT INTO `VLANValidID` VALUES (619);
INSERT INTO `VLANValidID` VALUES (620);
INSERT INTO `VLANValidID` VALUES (621);
INSERT INTO `VLANValidID` VALUES (622);
INSERT INTO `VLANValidID` VALUES (623);
INSERT INTO `VLANValidID` VALUES (624);
INSERT INTO `VLANValidID` VALUES (625);
INSERT INTO `VLANValidID` VALUES (626);
INSERT INTO `VLANValidID` VALUES (627);
INSERT INTO `VLANValidID` VALUES (628);
INSERT INTO `VLANValidID` VALUES (629);
INSERT INTO `VLANValidID` VALUES (630);
INSERT INTO `VLANValidID` VALUES (631);
INSERT INTO `VLANValidID` VALUES (632);
INSERT INTO `VLANValidID` VALUES (633);
INSERT INTO `VLANValidID` VALUES (634);
INSERT INTO `VLANValidID` VALUES (635);
INSERT INTO `VLANValidID` VALUES (636);
INSERT INTO `VLANValidID` VALUES (637);
INSERT INTO `VLANValidID` VALUES (638);
INSERT INTO `VLANValidID` VALUES (639);
INSERT INTO `VLANValidID` VALUES (640);
INSERT INTO `VLANValidID` VALUES (641);
INSERT INTO `VLANValidID` VALUES (642);
INSERT INTO `VLANValidID` VALUES (643);
INSERT INTO `VLANValidID` VALUES (644);
INSERT INTO `VLANValidID` VALUES (645);
INSERT INTO `VLANValidID` VALUES (646);
INSERT INTO `VLANValidID` VALUES (647);
INSERT INTO `VLANValidID` VALUES (648);
INSERT INTO `VLANValidID` VALUES (649);
INSERT INTO `VLANValidID` VALUES (650);
INSERT INTO `VLANValidID` VALUES (651);
INSERT INTO `VLANValidID` VALUES (652);
INSERT INTO `VLANValidID` VALUES (653);
INSERT INTO `VLANValidID` VALUES (654);
INSERT INTO `VLANValidID` VALUES (655);
INSERT INTO `VLANValidID` VALUES (656);
INSERT INTO `VLANValidID` VALUES (657);
INSERT INTO `VLANValidID` VALUES (658);
INSERT INTO `VLANValidID` VALUES (659);
INSERT INTO `VLANValidID` VALUES (660);
INSERT INTO `VLANValidID` VALUES (661);
INSERT INTO `VLANValidID` VALUES (662);
INSERT INTO `VLANValidID` VALUES (663);
INSERT INTO `VLANValidID` VALUES (664);
INSERT INTO `VLANValidID` VALUES (665);
INSERT INTO `VLANValidID` VALUES (666);
INSERT INTO `VLANValidID` VALUES (667);
INSERT INTO `VLANValidID` VALUES (668);
INSERT INTO `VLANValidID` VALUES (669);
INSERT INTO `VLANValidID` VALUES (670);
INSERT INTO `VLANValidID` VALUES (671);
INSERT INTO `VLANValidID` VALUES (672);
INSERT INTO `VLANValidID` VALUES (673);
INSERT INTO `VLANValidID` VALUES (674);
INSERT INTO `VLANValidID` VALUES (675);
INSERT INTO `VLANValidID` VALUES (676);
INSERT INTO `VLANValidID` VALUES (677);
INSERT INTO `VLANValidID` VALUES (678);
INSERT INTO `VLANValidID` VALUES (679);
INSERT INTO `VLANValidID` VALUES (680);
INSERT INTO `VLANValidID` VALUES (681);
INSERT INTO `VLANValidID` VALUES (682);
INSERT INTO `VLANValidID` VALUES (683);
INSERT INTO `VLANValidID` VALUES (684);
INSERT INTO `VLANValidID` VALUES (685);
INSERT INTO `VLANValidID` VALUES (686);
INSERT INTO `VLANValidID` VALUES (687);
INSERT INTO `VLANValidID` VALUES (688);
INSERT INTO `VLANValidID` VALUES (689);
INSERT INTO `VLANValidID` VALUES (690);
INSERT INTO `VLANValidID` VALUES (691);
INSERT INTO `VLANValidID` VALUES (692);
INSERT INTO `VLANValidID` VALUES (693);
INSERT INTO `VLANValidID` VALUES (694);
INSERT INTO `VLANValidID` VALUES (695);
INSERT INTO `VLANValidID` VALUES (696);
INSERT INTO `VLANValidID` VALUES (697);
INSERT INTO `VLANValidID` VALUES (698);
INSERT INTO `VLANValidID` VALUES (699);
INSERT INTO `VLANValidID` VALUES (700);
INSERT INTO `VLANValidID` VALUES (701);
INSERT INTO `VLANValidID` VALUES (702);
INSERT INTO `VLANValidID` VALUES (703);
INSERT INTO `VLANValidID` VALUES (704);
INSERT INTO `VLANValidID` VALUES (705);
INSERT INTO `VLANValidID` VALUES (706);
INSERT INTO `VLANValidID` VALUES (707);
INSERT INTO `VLANValidID` VALUES (708);
INSERT INTO `VLANValidID` VALUES (709);
INSERT INTO `VLANValidID` VALUES (710);
INSERT INTO `VLANValidID` VALUES (711);
INSERT INTO `VLANValidID` VALUES (712);
INSERT INTO `VLANValidID` VALUES (713);
INSERT INTO `VLANValidID` VALUES (714);
INSERT INTO `VLANValidID` VALUES (715);
INSERT INTO `VLANValidID` VALUES (716);
INSERT INTO `VLANValidID` VALUES (717);
INSERT INTO `VLANValidID` VALUES (718);
INSERT INTO `VLANValidID` VALUES (719);
INSERT INTO `VLANValidID` VALUES (720);
INSERT INTO `VLANValidID` VALUES (721);
INSERT INTO `VLANValidID` VALUES (722);
INSERT INTO `VLANValidID` VALUES (723);
INSERT INTO `VLANValidID` VALUES (724);
INSERT INTO `VLANValidID` VALUES (725);
INSERT INTO `VLANValidID` VALUES (726);
INSERT INTO `VLANValidID` VALUES (727);
INSERT INTO `VLANValidID` VALUES (728);
INSERT INTO `VLANValidID` VALUES (729);
INSERT INTO `VLANValidID` VALUES (730);
INSERT INTO `VLANValidID` VALUES (731);
INSERT INTO `VLANValidID` VALUES (732);
INSERT INTO `VLANValidID` VALUES (733);
INSERT INTO `VLANValidID` VALUES (734);
INSERT INTO `VLANValidID` VALUES (735);
INSERT INTO `VLANValidID` VALUES (736);
INSERT INTO `VLANValidID` VALUES (737);
INSERT INTO `VLANValidID` VALUES (738);
INSERT INTO `VLANValidID` VALUES (739);
INSERT INTO `VLANValidID` VALUES (740);
INSERT INTO `VLANValidID` VALUES (741);
INSERT INTO `VLANValidID` VALUES (742);
INSERT INTO `VLANValidID` VALUES (743);
INSERT INTO `VLANValidID` VALUES (744);
INSERT INTO `VLANValidID` VALUES (745);
INSERT INTO `VLANValidID` VALUES (746);
INSERT INTO `VLANValidID` VALUES (747);
INSERT INTO `VLANValidID` VALUES (748);
INSERT INTO `VLANValidID` VALUES (749);
INSERT INTO `VLANValidID` VALUES (750);
INSERT INTO `VLANValidID` VALUES (751);
INSERT INTO `VLANValidID` VALUES (752);
INSERT INTO `VLANValidID` VALUES (753);
INSERT INTO `VLANValidID` VALUES (754);
INSERT INTO `VLANValidID` VALUES (755);
INSERT INTO `VLANValidID` VALUES (756);
INSERT INTO `VLANValidID` VALUES (757);
INSERT INTO `VLANValidID` VALUES (758);
INSERT INTO `VLANValidID` VALUES (759);
INSERT INTO `VLANValidID` VALUES (760);
INSERT INTO `VLANValidID` VALUES (761);
INSERT INTO `VLANValidID` VALUES (762);
INSERT INTO `VLANValidID` VALUES (763);
INSERT INTO `VLANValidID` VALUES (764);
INSERT INTO `VLANValidID` VALUES (765);
INSERT INTO `VLANValidID` VALUES (766);
INSERT INTO `VLANValidID` VALUES (767);
INSERT INTO `VLANValidID` VALUES (768);
INSERT INTO `VLANValidID` VALUES (769);
INSERT INTO `VLANValidID` VALUES (770);
INSERT INTO `VLANValidID` VALUES (771);
INSERT INTO `VLANValidID` VALUES (772);
INSERT INTO `VLANValidID` VALUES (773);
INSERT INTO `VLANValidID` VALUES (774);
INSERT INTO `VLANValidID` VALUES (775);
INSERT INTO `VLANValidID` VALUES (776);
INSERT INTO `VLANValidID` VALUES (777);
INSERT INTO `VLANValidID` VALUES (778);
INSERT INTO `VLANValidID` VALUES (779);
INSERT INTO `VLANValidID` VALUES (780);
INSERT INTO `VLANValidID` VALUES (781);
INSERT INTO `VLANValidID` VALUES (782);
INSERT INTO `VLANValidID` VALUES (783);
INSERT INTO `VLANValidID` VALUES (784);
INSERT INTO `VLANValidID` VALUES (785);
INSERT INTO `VLANValidID` VALUES (786);
INSERT INTO `VLANValidID` VALUES (787);
INSERT INTO `VLANValidID` VALUES (788);
INSERT INTO `VLANValidID` VALUES (789);
INSERT INTO `VLANValidID` VALUES (790);
INSERT INTO `VLANValidID` VALUES (791);
INSERT INTO `VLANValidID` VALUES (792);
INSERT INTO `VLANValidID` VALUES (793);
INSERT INTO `VLANValidID` VALUES (794);
INSERT INTO `VLANValidID` VALUES (795);
INSERT INTO `VLANValidID` VALUES (796);
INSERT INTO `VLANValidID` VALUES (797);
INSERT INTO `VLANValidID` VALUES (798);
INSERT INTO `VLANValidID` VALUES (799);
INSERT INTO `VLANValidID` VALUES (800);
INSERT INTO `VLANValidID` VALUES (801);
INSERT INTO `VLANValidID` VALUES (802);
INSERT INTO `VLANValidID` VALUES (803);
INSERT INTO `VLANValidID` VALUES (804);
INSERT INTO `VLANValidID` VALUES (805);
INSERT INTO `VLANValidID` VALUES (806);
INSERT INTO `VLANValidID` VALUES (807);
INSERT INTO `VLANValidID` VALUES (808);
INSERT INTO `VLANValidID` VALUES (809);
INSERT INTO `VLANValidID` VALUES (810);
INSERT INTO `VLANValidID` VALUES (811);
INSERT INTO `VLANValidID` VALUES (812);
INSERT INTO `VLANValidID` VALUES (813);
INSERT INTO `VLANValidID` VALUES (814);
INSERT INTO `VLANValidID` VALUES (815);
INSERT INTO `VLANValidID` VALUES (816);
INSERT INTO `VLANValidID` VALUES (817);
INSERT INTO `VLANValidID` VALUES (818);
INSERT INTO `VLANValidID` VALUES (819);
INSERT INTO `VLANValidID` VALUES (820);
INSERT INTO `VLANValidID` VALUES (821);
INSERT INTO `VLANValidID` VALUES (822);
INSERT INTO `VLANValidID` VALUES (823);
INSERT INTO `VLANValidID` VALUES (824);
INSERT INTO `VLANValidID` VALUES (825);
INSERT INTO `VLANValidID` VALUES (826);
INSERT INTO `VLANValidID` VALUES (827);
INSERT INTO `VLANValidID` VALUES (828);
INSERT INTO `VLANValidID` VALUES (829);
INSERT INTO `VLANValidID` VALUES (830);
INSERT INTO `VLANValidID` VALUES (831);
INSERT INTO `VLANValidID` VALUES (832);
INSERT INTO `VLANValidID` VALUES (833);
INSERT INTO `VLANValidID` VALUES (834);
INSERT INTO `VLANValidID` VALUES (835);
INSERT INTO `VLANValidID` VALUES (836);
INSERT INTO `VLANValidID` VALUES (837);
INSERT INTO `VLANValidID` VALUES (838);
INSERT INTO `VLANValidID` VALUES (839);
INSERT INTO `VLANValidID` VALUES (840);
INSERT INTO `VLANValidID` VALUES (841);
INSERT INTO `VLANValidID` VALUES (842);
INSERT INTO `VLANValidID` VALUES (843);
INSERT INTO `VLANValidID` VALUES (844);
INSERT INTO `VLANValidID` VALUES (845);
INSERT INTO `VLANValidID` VALUES (846);
INSERT INTO `VLANValidID` VALUES (847);
INSERT INTO `VLANValidID` VALUES (848);
INSERT INTO `VLANValidID` VALUES (849);
INSERT INTO `VLANValidID` VALUES (850);
INSERT INTO `VLANValidID` VALUES (851);
INSERT INTO `VLANValidID` VALUES (852);
INSERT INTO `VLANValidID` VALUES (853);
INSERT INTO `VLANValidID` VALUES (854);
INSERT INTO `VLANValidID` VALUES (855);
INSERT INTO `VLANValidID` VALUES (856);
INSERT INTO `VLANValidID` VALUES (857);
INSERT INTO `VLANValidID` VALUES (858);
INSERT INTO `VLANValidID` VALUES (859);
INSERT INTO `VLANValidID` VALUES (860);
INSERT INTO `VLANValidID` VALUES (861);
INSERT INTO `VLANValidID` VALUES (862);
INSERT INTO `VLANValidID` VALUES (863);
INSERT INTO `VLANValidID` VALUES (864);
INSERT INTO `VLANValidID` VALUES (865);
INSERT INTO `VLANValidID` VALUES (866);
INSERT INTO `VLANValidID` VALUES (867);
INSERT INTO `VLANValidID` VALUES (868);
INSERT INTO `VLANValidID` VALUES (869);
INSERT INTO `VLANValidID` VALUES (870);
INSERT INTO `VLANValidID` VALUES (871);
INSERT INTO `VLANValidID` VALUES (872);
INSERT INTO `VLANValidID` VALUES (873);
INSERT INTO `VLANValidID` VALUES (874);
INSERT INTO `VLANValidID` VALUES (875);
INSERT INTO `VLANValidID` VALUES (876);
INSERT INTO `VLANValidID` VALUES (877);
INSERT INTO `VLANValidID` VALUES (878);
INSERT INTO `VLANValidID` VALUES (879);
INSERT INTO `VLANValidID` VALUES (880);
INSERT INTO `VLANValidID` VALUES (881);
INSERT INTO `VLANValidID` VALUES (882);
INSERT INTO `VLANValidID` VALUES (883);
INSERT INTO `VLANValidID` VALUES (884);
INSERT INTO `VLANValidID` VALUES (885);
INSERT INTO `VLANValidID` VALUES (886);
INSERT INTO `VLANValidID` VALUES (887);
INSERT INTO `VLANValidID` VALUES (888);
INSERT INTO `VLANValidID` VALUES (889);
INSERT INTO `VLANValidID` VALUES (890);
INSERT INTO `VLANValidID` VALUES (891);
INSERT INTO `VLANValidID` VALUES (892);
INSERT INTO `VLANValidID` VALUES (893);
INSERT INTO `VLANValidID` VALUES (894);
INSERT INTO `VLANValidID` VALUES (895);
INSERT INTO `VLANValidID` VALUES (896);
INSERT INTO `VLANValidID` VALUES (897);
INSERT INTO `VLANValidID` VALUES (898);
INSERT INTO `VLANValidID` VALUES (899);
INSERT INTO `VLANValidID` VALUES (900);
INSERT INTO `VLANValidID` VALUES (901);
INSERT INTO `VLANValidID` VALUES (902);
INSERT INTO `VLANValidID` VALUES (903);
INSERT INTO `VLANValidID` VALUES (904);
INSERT INTO `VLANValidID` VALUES (905);
INSERT INTO `VLANValidID` VALUES (906);
INSERT INTO `VLANValidID` VALUES (907);
INSERT INTO `VLANValidID` VALUES (908);
INSERT INTO `VLANValidID` VALUES (909);
INSERT INTO `VLANValidID` VALUES (910);
INSERT INTO `VLANValidID` VALUES (911);
INSERT INTO `VLANValidID` VALUES (912);
INSERT INTO `VLANValidID` VALUES (913);
INSERT INTO `VLANValidID` VALUES (914);
INSERT INTO `VLANValidID` VALUES (915);
INSERT INTO `VLANValidID` VALUES (916);
INSERT INTO `VLANValidID` VALUES (917);
INSERT INTO `VLANValidID` VALUES (918);
INSERT INTO `VLANValidID` VALUES (919);
INSERT INTO `VLANValidID` VALUES (920);
INSERT INTO `VLANValidID` VALUES (921);
INSERT INTO `VLANValidID` VALUES (922);
INSERT INTO `VLANValidID` VALUES (923);
INSERT INTO `VLANValidID` VALUES (924);
INSERT INTO `VLANValidID` VALUES (925);
INSERT INTO `VLANValidID` VALUES (926);
INSERT INTO `VLANValidID` VALUES (927);
INSERT INTO `VLANValidID` VALUES (928);
INSERT INTO `VLANValidID` VALUES (929);
INSERT INTO `VLANValidID` VALUES (930);
INSERT INTO `VLANValidID` VALUES (931);
INSERT INTO `VLANValidID` VALUES (932);
INSERT INTO `VLANValidID` VALUES (933);
INSERT INTO `VLANValidID` VALUES (934);
INSERT INTO `VLANValidID` VALUES (935);
INSERT INTO `VLANValidID` VALUES (936);
INSERT INTO `VLANValidID` VALUES (937);
INSERT INTO `VLANValidID` VALUES (938);
INSERT INTO `VLANValidID` VALUES (939);
INSERT INTO `VLANValidID` VALUES (940);
INSERT INTO `VLANValidID` VALUES (941);
INSERT INTO `VLANValidID` VALUES (942);
INSERT INTO `VLANValidID` VALUES (943);
INSERT INTO `VLANValidID` VALUES (944);
INSERT INTO `VLANValidID` VALUES (945);
INSERT INTO `VLANValidID` VALUES (946);
INSERT INTO `VLANValidID` VALUES (947);
INSERT INTO `VLANValidID` VALUES (948);
INSERT INTO `VLANValidID` VALUES (949);
INSERT INTO `VLANValidID` VALUES (950);
INSERT INTO `VLANValidID` VALUES (951);
INSERT INTO `VLANValidID` VALUES (952);
INSERT INTO `VLANValidID` VALUES (953);
INSERT INTO `VLANValidID` VALUES (954);
INSERT INTO `VLANValidID` VALUES (955);
INSERT INTO `VLANValidID` VALUES (956);
INSERT INTO `VLANValidID` VALUES (957);
INSERT INTO `VLANValidID` VALUES (958);
INSERT INTO `VLANValidID` VALUES (959);
INSERT INTO `VLANValidID` VALUES (960);
INSERT INTO `VLANValidID` VALUES (961);
INSERT INTO `VLANValidID` VALUES (962);
INSERT INTO `VLANValidID` VALUES (963);
INSERT INTO `VLANValidID` VALUES (964);
INSERT INTO `VLANValidID` VALUES (965);
INSERT INTO `VLANValidID` VALUES (966);
INSERT INTO `VLANValidID` VALUES (967);
INSERT INTO `VLANValidID` VALUES (968);
INSERT INTO `VLANValidID` VALUES (969);
INSERT INTO `VLANValidID` VALUES (970);
INSERT INTO `VLANValidID` VALUES (971);
INSERT INTO `VLANValidID` VALUES (972);
INSERT INTO `VLANValidID` VALUES (973);
INSERT INTO `VLANValidID` VALUES (974);
INSERT INTO `VLANValidID` VALUES (975);
INSERT INTO `VLANValidID` VALUES (976);
INSERT INTO `VLANValidID` VALUES (977);
INSERT INTO `VLANValidID` VALUES (978);
INSERT INTO `VLANValidID` VALUES (979);
INSERT INTO `VLANValidID` VALUES (980);
INSERT INTO `VLANValidID` VALUES (981);
INSERT INTO `VLANValidID` VALUES (982);
INSERT INTO `VLANValidID` VALUES (983);
INSERT INTO `VLANValidID` VALUES (984);
INSERT INTO `VLANValidID` VALUES (985);
INSERT INTO `VLANValidID` VALUES (986);
INSERT INTO `VLANValidID` VALUES (987);
INSERT INTO `VLANValidID` VALUES (988);
INSERT INTO `VLANValidID` VALUES (989);
INSERT INTO `VLANValidID` VALUES (990);
INSERT INTO `VLANValidID` VALUES (991);
INSERT INTO `VLANValidID` VALUES (992);
INSERT INTO `VLANValidID` VALUES (993);
INSERT INTO `VLANValidID` VALUES (994);
INSERT INTO `VLANValidID` VALUES (995);
INSERT INTO `VLANValidID` VALUES (996);
INSERT INTO `VLANValidID` VALUES (997);
INSERT INTO `VLANValidID` VALUES (998);
INSERT INTO `VLANValidID` VALUES (999);
INSERT INTO `VLANValidID` VALUES (1000);
INSERT INTO `VLANValidID` VALUES (1001);
INSERT INTO `VLANValidID` VALUES (1002);
INSERT INTO `VLANValidID` VALUES (1003);
INSERT INTO `VLANValidID` VALUES (1004);
INSERT INTO `VLANValidID` VALUES (1005);
INSERT INTO `VLANValidID` VALUES (1006);
INSERT INTO `VLANValidID` VALUES (1007);
INSERT INTO `VLANValidID` VALUES (1008);
INSERT INTO `VLANValidID` VALUES (1009);
INSERT INTO `VLANValidID` VALUES (1010);
INSERT INTO `VLANValidID` VALUES (1011);
INSERT INTO `VLANValidID` VALUES (1012);
INSERT INTO `VLANValidID` VALUES (1013);
INSERT INTO `VLANValidID` VALUES (1014);
INSERT INTO `VLANValidID` VALUES (1015);
INSERT INTO `VLANValidID` VALUES (1016);
INSERT INTO `VLANValidID` VALUES (1017);
INSERT INTO `VLANValidID` VALUES (1018);
INSERT INTO `VLANValidID` VALUES (1019);
INSERT INTO `VLANValidID` VALUES (1020);
INSERT INTO `VLANValidID` VALUES (1021);
INSERT INTO `VLANValidID` VALUES (1022);
INSERT INTO `VLANValidID` VALUES (1023);
INSERT INTO `VLANValidID` VALUES (1024);
INSERT INTO `VLANValidID` VALUES (1025);
INSERT INTO `VLANValidID` VALUES (1026);
INSERT INTO `VLANValidID` VALUES (1027);
INSERT INTO `VLANValidID` VALUES (1028);
INSERT INTO `VLANValidID` VALUES (1029);
INSERT INTO `VLANValidID` VALUES (1030);
INSERT INTO `VLANValidID` VALUES (1031);
INSERT INTO `VLANValidID` VALUES (1032);
INSERT INTO `VLANValidID` VALUES (1033);
INSERT INTO `VLANValidID` VALUES (1034);
INSERT INTO `VLANValidID` VALUES (1035);
INSERT INTO `VLANValidID` VALUES (1036);
INSERT INTO `VLANValidID` VALUES (1037);
INSERT INTO `VLANValidID` VALUES (1038);
INSERT INTO `VLANValidID` VALUES (1039);
INSERT INTO `VLANValidID` VALUES (1040);
INSERT INTO `VLANValidID` VALUES (1041);
INSERT INTO `VLANValidID` VALUES (1042);
INSERT INTO `VLANValidID` VALUES (1043);
INSERT INTO `VLANValidID` VALUES (1044);
INSERT INTO `VLANValidID` VALUES (1045);
INSERT INTO `VLANValidID` VALUES (1046);
INSERT INTO `VLANValidID` VALUES (1047);
INSERT INTO `VLANValidID` VALUES (1048);
INSERT INTO `VLANValidID` VALUES (1049);
INSERT INTO `VLANValidID` VALUES (1050);
INSERT INTO `VLANValidID` VALUES (1051);
INSERT INTO `VLANValidID` VALUES (1052);
INSERT INTO `VLANValidID` VALUES (1053);
INSERT INTO `VLANValidID` VALUES (1054);
INSERT INTO `VLANValidID` VALUES (1055);
INSERT INTO `VLANValidID` VALUES (1056);
INSERT INTO `VLANValidID` VALUES (1057);
INSERT INTO `VLANValidID` VALUES (1058);
INSERT INTO `VLANValidID` VALUES (1059);
INSERT INTO `VLANValidID` VALUES (1060);
INSERT INTO `VLANValidID` VALUES (1061);
INSERT INTO `VLANValidID` VALUES (1062);
INSERT INTO `VLANValidID` VALUES (1063);
INSERT INTO `VLANValidID` VALUES (1064);
INSERT INTO `VLANValidID` VALUES (1065);
INSERT INTO `VLANValidID` VALUES (1066);
INSERT INTO `VLANValidID` VALUES (1067);
INSERT INTO `VLANValidID` VALUES (1068);
INSERT INTO `VLANValidID` VALUES (1069);
INSERT INTO `VLANValidID` VALUES (1070);
INSERT INTO `VLANValidID` VALUES (1071);
INSERT INTO `VLANValidID` VALUES (1072);
INSERT INTO `VLANValidID` VALUES (1073);
INSERT INTO `VLANValidID` VALUES (1074);
INSERT INTO `VLANValidID` VALUES (1075);
INSERT INTO `VLANValidID` VALUES (1076);
INSERT INTO `VLANValidID` VALUES (1077);
INSERT INTO `VLANValidID` VALUES (1078);
INSERT INTO `VLANValidID` VALUES (1079);
INSERT INTO `VLANValidID` VALUES (1080);
INSERT INTO `VLANValidID` VALUES (1081);
INSERT INTO `VLANValidID` VALUES (1082);
INSERT INTO `VLANValidID` VALUES (1083);
INSERT INTO `VLANValidID` VALUES (1084);
INSERT INTO `VLANValidID` VALUES (1085);
INSERT INTO `VLANValidID` VALUES (1086);
INSERT INTO `VLANValidID` VALUES (1087);
INSERT INTO `VLANValidID` VALUES (1088);
INSERT INTO `VLANValidID` VALUES (1089);
INSERT INTO `VLANValidID` VALUES (1090);
INSERT INTO `VLANValidID` VALUES (1091);
INSERT INTO `VLANValidID` VALUES (1092);
INSERT INTO `VLANValidID` VALUES (1093);
INSERT INTO `VLANValidID` VALUES (1094);
INSERT INTO `VLANValidID` VALUES (1095);
INSERT INTO `VLANValidID` VALUES (1096);
INSERT INTO `VLANValidID` VALUES (1097);
INSERT INTO `VLANValidID` VALUES (1098);
INSERT INTO `VLANValidID` VALUES (1099);
INSERT INTO `VLANValidID` VALUES (1100);
INSERT INTO `VLANValidID` VALUES (1101);
INSERT INTO `VLANValidID` VALUES (1102);
INSERT INTO `VLANValidID` VALUES (1103);
INSERT INTO `VLANValidID` VALUES (1104);
INSERT INTO `VLANValidID` VALUES (1105);
INSERT INTO `VLANValidID` VALUES (1106);
INSERT INTO `VLANValidID` VALUES (1107);
INSERT INTO `VLANValidID` VALUES (1108);
INSERT INTO `VLANValidID` VALUES (1109);
INSERT INTO `VLANValidID` VALUES (1110);
INSERT INTO `VLANValidID` VALUES (1111);
INSERT INTO `VLANValidID` VALUES (1112);
INSERT INTO `VLANValidID` VALUES (1113);
INSERT INTO `VLANValidID` VALUES (1114);
INSERT INTO `VLANValidID` VALUES (1115);
INSERT INTO `VLANValidID` VALUES (1116);
INSERT INTO `VLANValidID` VALUES (1117);
INSERT INTO `VLANValidID` VALUES (1118);
INSERT INTO `VLANValidID` VALUES (1119);
INSERT INTO `VLANValidID` VALUES (1120);
INSERT INTO `VLANValidID` VALUES (1121);
INSERT INTO `VLANValidID` VALUES (1122);
INSERT INTO `VLANValidID` VALUES (1123);
INSERT INTO `VLANValidID` VALUES (1124);
INSERT INTO `VLANValidID` VALUES (1125);
INSERT INTO `VLANValidID` VALUES (1126);
INSERT INTO `VLANValidID` VALUES (1127);
INSERT INTO `VLANValidID` VALUES (1128);
INSERT INTO `VLANValidID` VALUES (1129);
INSERT INTO `VLANValidID` VALUES (1130);
INSERT INTO `VLANValidID` VALUES (1131);
INSERT INTO `VLANValidID` VALUES (1132);
INSERT INTO `VLANValidID` VALUES (1133);
INSERT INTO `VLANValidID` VALUES (1134);
INSERT INTO `VLANValidID` VALUES (1135);
INSERT INTO `VLANValidID` VALUES (1136);
INSERT INTO `VLANValidID` VALUES (1137);
INSERT INTO `VLANValidID` VALUES (1138);
INSERT INTO `VLANValidID` VALUES (1139);
INSERT INTO `VLANValidID` VALUES (1140);
INSERT INTO `VLANValidID` VALUES (1141);
INSERT INTO `VLANValidID` VALUES (1142);
INSERT INTO `VLANValidID` VALUES (1143);
INSERT INTO `VLANValidID` VALUES (1144);
INSERT INTO `VLANValidID` VALUES (1145);
INSERT INTO `VLANValidID` VALUES (1146);
INSERT INTO `VLANValidID` VALUES (1147);
INSERT INTO `VLANValidID` VALUES (1148);
INSERT INTO `VLANValidID` VALUES (1149);
INSERT INTO `VLANValidID` VALUES (1150);
INSERT INTO `VLANValidID` VALUES (1151);
INSERT INTO `VLANValidID` VALUES (1152);
INSERT INTO `VLANValidID` VALUES (1153);
INSERT INTO `VLANValidID` VALUES (1154);
INSERT INTO `VLANValidID` VALUES (1155);
INSERT INTO `VLANValidID` VALUES (1156);
INSERT INTO `VLANValidID` VALUES (1157);
INSERT INTO `VLANValidID` VALUES (1158);
INSERT INTO `VLANValidID` VALUES (1159);
INSERT INTO `VLANValidID` VALUES (1160);
INSERT INTO `VLANValidID` VALUES (1161);
INSERT INTO `VLANValidID` VALUES (1162);
INSERT INTO `VLANValidID` VALUES (1163);
INSERT INTO `VLANValidID` VALUES (1164);
INSERT INTO `VLANValidID` VALUES (1165);
INSERT INTO `VLANValidID` VALUES (1166);
INSERT INTO `VLANValidID` VALUES (1167);
INSERT INTO `VLANValidID` VALUES (1168);
INSERT INTO `VLANValidID` VALUES (1169);
INSERT INTO `VLANValidID` VALUES (1170);
INSERT INTO `VLANValidID` VALUES (1171);
INSERT INTO `VLANValidID` VALUES (1172);
INSERT INTO `VLANValidID` VALUES (1173);
INSERT INTO `VLANValidID` VALUES (1174);
INSERT INTO `VLANValidID` VALUES (1175);
INSERT INTO `VLANValidID` VALUES (1176);
INSERT INTO `VLANValidID` VALUES (1177);
INSERT INTO `VLANValidID` VALUES (1178);
INSERT INTO `VLANValidID` VALUES (1179);
INSERT INTO `VLANValidID` VALUES (1180);
INSERT INTO `VLANValidID` VALUES (1181);
INSERT INTO `VLANValidID` VALUES (1182);
INSERT INTO `VLANValidID` VALUES (1183);
INSERT INTO `VLANValidID` VALUES (1184);
INSERT INTO `VLANValidID` VALUES (1185);
INSERT INTO `VLANValidID` VALUES (1186);
INSERT INTO `VLANValidID` VALUES (1187);
INSERT INTO `VLANValidID` VALUES (1188);
INSERT INTO `VLANValidID` VALUES (1189);
INSERT INTO `VLANValidID` VALUES (1190);
INSERT INTO `VLANValidID` VALUES (1191);
INSERT INTO `VLANValidID` VALUES (1192);
INSERT INTO `VLANValidID` VALUES (1193);
INSERT INTO `VLANValidID` VALUES (1194);
INSERT INTO `VLANValidID` VALUES (1195);
INSERT INTO `VLANValidID` VALUES (1196);
INSERT INTO `VLANValidID` VALUES (1197);
INSERT INTO `VLANValidID` VALUES (1198);
INSERT INTO `VLANValidID` VALUES (1199);
INSERT INTO `VLANValidID` VALUES (1200);
INSERT INTO `VLANValidID` VALUES (1201);
INSERT INTO `VLANValidID` VALUES (1202);
INSERT INTO `VLANValidID` VALUES (1203);
INSERT INTO `VLANValidID` VALUES (1204);
INSERT INTO `VLANValidID` VALUES (1205);
INSERT INTO `VLANValidID` VALUES (1206);
INSERT INTO `VLANValidID` VALUES (1207);
INSERT INTO `VLANValidID` VALUES (1208);
INSERT INTO `VLANValidID` VALUES (1209);
INSERT INTO `VLANValidID` VALUES (1210);
INSERT INTO `VLANValidID` VALUES (1211);
INSERT INTO `VLANValidID` VALUES (1212);
INSERT INTO `VLANValidID` VALUES (1213);
INSERT INTO `VLANValidID` VALUES (1214);
INSERT INTO `VLANValidID` VALUES (1215);
INSERT INTO `VLANValidID` VALUES (1216);
INSERT INTO `VLANValidID` VALUES (1217);
INSERT INTO `VLANValidID` VALUES (1218);
INSERT INTO `VLANValidID` VALUES (1219);
INSERT INTO `VLANValidID` VALUES (1220);
INSERT INTO `VLANValidID` VALUES (1221);
INSERT INTO `VLANValidID` VALUES (1222);
INSERT INTO `VLANValidID` VALUES (1223);
INSERT INTO `VLANValidID` VALUES (1224);
INSERT INTO `VLANValidID` VALUES (1225);
INSERT INTO `VLANValidID` VALUES (1226);
INSERT INTO `VLANValidID` VALUES (1227);
INSERT INTO `VLANValidID` VALUES (1228);
INSERT INTO `VLANValidID` VALUES (1229);
INSERT INTO `VLANValidID` VALUES (1230);
INSERT INTO `VLANValidID` VALUES (1231);
INSERT INTO `VLANValidID` VALUES (1232);
INSERT INTO `VLANValidID` VALUES (1233);
INSERT INTO `VLANValidID` VALUES (1234);
INSERT INTO `VLANValidID` VALUES (1235);
INSERT INTO `VLANValidID` VALUES (1236);
INSERT INTO `VLANValidID` VALUES (1237);
INSERT INTO `VLANValidID` VALUES (1238);
INSERT INTO `VLANValidID` VALUES (1239);
INSERT INTO `VLANValidID` VALUES (1240);
INSERT INTO `VLANValidID` VALUES (1241);
INSERT INTO `VLANValidID` VALUES (1242);
INSERT INTO `VLANValidID` VALUES (1243);
INSERT INTO `VLANValidID` VALUES (1244);
INSERT INTO `VLANValidID` VALUES (1245);
INSERT INTO `VLANValidID` VALUES (1246);
INSERT INTO `VLANValidID` VALUES (1247);
INSERT INTO `VLANValidID` VALUES (1248);
INSERT INTO `VLANValidID` VALUES (1249);
INSERT INTO `VLANValidID` VALUES (1250);
INSERT INTO `VLANValidID` VALUES (1251);
INSERT INTO `VLANValidID` VALUES (1252);
INSERT INTO `VLANValidID` VALUES (1253);
INSERT INTO `VLANValidID` VALUES (1254);
INSERT INTO `VLANValidID` VALUES (1255);
INSERT INTO `VLANValidID` VALUES (1256);
INSERT INTO `VLANValidID` VALUES (1257);
INSERT INTO `VLANValidID` VALUES (1258);
INSERT INTO `VLANValidID` VALUES (1259);
INSERT INTO `VLANValidID` VALUES (1260);
INSERT INTO `VLANValidID` VALUES (1261);
INSERT INTO `VLANValidID` VALUES (1262);
INSERT INTO `VLANValidID` VALUES (1263);
INSERT INTO `VLANValidID` VALUES (1264);
INSERT INTO `VLANValidID` VALUES (1265);
INSERT INTO `VLANValidID` VALUES (1266);
INSERT INTO `VLANValidID` VALUES (1267);
INSERT INTO `VLANValidID` VALUES (1268);
INSERT INTO `VLANValidID` VALUES (1269);
INSERT INTO `VLANValidID` VALUES (1270);
INSERT INTO `VLANValidID` VALUES (1271);
INSERT INTO `VLANValidID` VALUES (1272);
INSERT INTO `VLANValidID` VALUES (1273);
INSERT INTO `VLANValidID` VALUES (1274);
INSERT INTO `VLANValidID` VALUES (1275);
INSERT INTO `VLANValidID` VALUES (1276);
INSERT INTO `VLANValidID` VALUES (1277);
INSERT INTO `VLANValidID` VALUES (1278);
INSERT INTO `VLANValidID` VALUES (1279);
INSERT INTO `VLANValidID` VALUES (1280);
INSERT INTO `VLANValidID` VALUES (1281);
INSERT INTO `VLANValidID` VALUES (1282);
INSERT INTO `VLANValidID` VALUES (1283);
INSERT INTO `VLANValidID` VALUES (1284);
INSERT INTO `VLANValidID` VALUES (1285);
INSERT INTO `VLANValidID` VALUES (1286);
INSERT INTO `VLANValidID` VALUES (1287);
INSERT INTO `VLANValidID` VALUES (1288);
INSERT INTO `VLANValidID` VALUES (1289);
INSERT INTO `VLANValidID` VALUES (1290);
INSERT INTO `VLANValidID` VALUES (1291);
INSERT INTO `VLANValidID` VALUES (1292);
INSERT INTO `VLANValidID` VALUES (1293);
INSERT INTO `VLANValidID` VALUES (1294);
INSERT INTO `VLANValidID` VALUES (1295);
INSERT INTO `VLANValidID` VALUES (1296);
INSERT INTO `VLANValidID` VALUES (1297);
INSERT INTO `VLANValidID` VALUES (1298);
INSERT INTO `VLANValidID` VALUES (1299);
INSERT INTO `VLANValidID` VALUES (1300);
INSERT INTO `VLANValidID` VALUES (1301);
INSERT INTO `VLANValidID` VALUES (1302);
INSERT INTO `VLANValidID` VALUES (1303);
INSERT INTO `VLANValidID` VALUES (1304);
INSERT INTO `VLANValidID` VALUES (1305);
INSERT INTO `VLANValidID` VALUES (1306);
INSERT INTO `VLANValidID` VALUES (1307);
INSERT INTO `VLANValidID` VALUES (1308);
INSERT INTO `VLANValidID` VALUES (1309);
INSERT INTO `VLANValidID` VALUES (1310);
INSERT INTO `VLANValidID` VALUES (1311);
INSERT INTO `VLANValidID` VALUES (1312);
INSERT INTO `VLANValidID` VALUES (1313);
INSERT INTO `VLANValidID` VALUES (1314);
INSERT INTO `VLANValidID` VALUES (1315);
INSERT INTO `VLANValidID` VALUES (1316);
INSERT INTO `VLANValidID` VALUES (1317);
INSERT INTO `VLANValidID` VALUES (1318);
INSERT INTO `VLANValidID` VALUES (1319);
INSERT INTO `VLANValidID` VALUES (1320);
INSERT INTO `VLANValidID` VALUES (1321);
INSERT INTO `VLANValidID` VALUES (1322);
INSERT INTO `VLANValidID` VALUES (1323);
INSERT INTO `VLANValidID` VALUES (1324);
INSERT INTO `VLANValidID` VALUES (1325);
INSERT INTO `VLANValidID` VALUES (1326);
INSERT INTO `VLANValidID` VALUES (1327);
INSERT INTO `VLANValidID` VALUES (1328);
INSERT INTO `VLANValidID` VALUES (1329);
INSERT INTO `VLANValidID` VALUES (1330);
INSERT INTO `VLANValidID` VALUES (1331);
INSERT INTO `VLANValidID` VALUES (1332);
INSERT INTO `VLANValidID` VALUES (1333);
INSERT INTO `VLANValidID` VALUES (1334);
INSERT INTO `VLANValidID` VALUES (1335);
INSERT INTO `VLANValidID` VALUES (1336);
INSERT INTO `VLANValidID` VALUES (1337);
INSERT INTO `VLANValidID` VALUES (1338);
INSERT INTO `VLANValidID` VALUES (1339);
INSERT INTO `VLANValidID` VALUES (1340);
INSERT INTO `VLANValidID` VALUES (1341);
INSERT INTO `VLANValidID` VALUES (1342);
INSERT INTO `VLANValidID` VALUES (1343);
INSERT INTO `VLANValidID` VALUES (1344);
INSERT INTO `VLANValidID` VALUES (1345);
INSERT INTO `VLANValidID` VALUES (1346);
INSERT INTO `VLANValidID` VALUES (1347);
INSERT INTO `VLANValidID` VALUES (1348);
INSERT INTO `VLANValidID` VALUES (1349);
INSERT INTO `VLANValidID` VALUES (1350);
INSERT INTO `VLANValidID` VALUES (1351);
INSERT INTO `VLANValidID` VALUES (1352);
INSERT INTO `VLANValidID` VALUES (1353);
INSERT INTO `VLANValidID` VALUES (1354);
INSERT INTO `VLANValidID` VALUES (1355);
INSERT INTO `VLANValidID` VALUES (1356);
INSERT INTO `VLANValidID` VALUES (1357);
INSERT INTO `VLANValidID` VALUES (1358);
INSERT INTO `VLANValidID` VALUES (1359);
INSERT INTO `VLANValidID` VALUES (1360);
INSERT INTO `VLANValidID` VALUES (1361);
INSERT INTO `VLANValidID` VALUES (1362);
INSERT INTO `VLANValidID` VALUES (1363);
INSERT INTO `VLANValidID` VALUES (1364);
INSERT INTO `VLANValidID` VALUES (1365);
INSERT INTO `VLANValidID` VALUES (1366);
INSERT INTO `VLANValidID` VALUES (1367);
INSERT INTO `VLANValidID` VALUES (1368);
INSERT INTO `VLANValidID` VALUES (1369);
INSERT INTO `VLANValidID` VALUES (1370);
INSERT INTO `VLANValidID` VALUES (1371);
INSERT INTO `VLANValidID` VALUES (1372);
INSERT INTO `VLANValidID` VALUES (1373);
INSERT INTO `VLANValidID` VALUES (1374);
INSERT INTO `VLANValidID` VALUES (1375);
INSERT INTO `VLANValidID` VALUES (1376);
INSERT INTO `VLANValidID` VALUES (1377);
INSERT INTO `VLANValidID` VALUES (1378);
INSERT INTO `VLANValidID` VALUES (1379);
INSERT INTO `VLANValidID` VALUES (1380);
INSERT INTO `VLANValidID` VALUES (1381);
INSERT INTO `VLANValidID` VALUES (1382);
INSERT INTO `VLANValidID` VALUES (1383);
INSERT INTO `VLANValidID` VALUES (1384);
INSERT INTO `VLANValidID` VALUES (1385);
INSERT INTO `VLANValidID` VALUES (1386);
INSERT INTO `VLANValidID` VALUES (1387);
INSERT INTO `VLANValidID` VALUES (1388);
INSERT INTO `VLANValidID` VALUES (1389);
INSERT INTO `VLANValidID` VALUES (1390);
INSERT INTO `VLANValidID` VALUES (1391);
INSERT INTO `VLANValidID` VALUES (1392);
INSERT INTO `VLANValidID` VALUES (1393);
INSERT INTO `VLANValidID` VALUES (1394);
INSERT INTO `VLANValidID` VALUES (1395);
INSERT INTO `VLANValidID` VALUES (1396);
INSERT INTO `VLANValidID` VALUES (1397);
INSERT INTO `VLANValidID` VALUES (1398);
INSERT INTO `VLANValidID` VALUES (1399);
INSERT INTO `VLANValidID` VALUES (1400);
INSERT INTO `VLANValidID` VALUES (1401);
INSERT INTO `VLANValidID` VALUES (1402);
INSERT INTO `VLANValidID` VALUES (1403);
INSERT INTO `VLANValidID` VALUES (1404);
INSERT INTO `VLANValidID` VALUES (1405);
INSERT INTO `VLANValidID` VALUES (1406);
INSERT INTO `VLANValidID` VALUES (1407);
INSERT INTO `VLANValidID` VALUES (1408);
INSERT INTO `VLANValidID` VALUES (1409);
INSERT INTO `VLANValidID` VALUES (1410);
INSERT INTO `VLANValidID` VALUES (1411);
INSERT INTO `VLANValidID` VALUES (1412);
INSERT INTO `VLANValidID` VALUES (1413);
INSERT INTO `VLANValidID` VALUES (1414);
INSERT INTO `VLANValidID` VALUES (1415);
INSERT INTO `VLANValidID` VALUES (1416);
INSERT INTO `VLANValidID` VALUES (1417);
INSERT INTO `VLANValidID` VALUES (1418);
INSERT INTO `VLANValidID` VALUES (1419);
INSERT INTO `VLANValidID` VALUES (1420);
INSERT INTO `VLANValidID` VALUES (1421);
INSERT INTO `VLANValidID` VALUES (1422);
INSERT INTO `VLANValidID` VALUES (1423);
INSERT INTO `VLANValidID` VALUES (1424);
INSERT INTO `VLANValidID` VALUES (1425);
INSERT INTO `VLANValidID` VALUES (1426);
INSERT INTO `VLANValidID` VALUES (1427);
INSERT INTO `VLANValidID` VALUES (1428);
INSERT INTO `VLANValidID` VALUES (1429);
INSERT INTO `VLANValidID` VALUES (1430);
INSERT INTO `VLANValidID` VALUES (1431);
INSERT INTO `VLANValidID` VALUES (1432);
INSERT INTO `VLANValidID` VALUES (1433);
INSERT INTO `VLANValidID` VALUES (1434);
INSERT INTO `VLANValidID` VALUES (1435);
INSERT INTO `VLANValidID` VALUES (1436);
INSERT INTO `VLANValidID` VALUES (1437);
INSERT INTO `VLANValidID` VALUES (1438);
INSERT INTO `VLANValidID` VALUES (1439);
INSERT INTO `VLANValidID` VALUES (1440);
INSERT INTO `VLANValidID` VALUES (1441);
INSERT INTO `VLANValidID` VALUES (1442);
INSERT INTO `VLANValidID` VALUES (1443);
INSERT INTO `VLANValidID` VALUES (1444);
INSERT INTO `VLANValidID` VALUES (1445);
INSERT INTO `VLANValidID` VALUES (1446);
INSERT INTO `VLANValidID` VALUES (1447);
INSERT INTO `VLANValidID` VALUES (1448);
INSERT INTO `VLANValidID` VALUES (1449);
INSERT INTO `VLANValidID` VALUES (1450);
INSERT INTO `VLANValidID` VALUES (1451);
INSERT INTO `VLANValidID` VALUES (1452);
INSERT INTO `VLANValidID` VALUES (1453);
INSERT INTO `VLANValidID` VALUES (1454);
INSERT INTO `VLANValidID` VALUES (1455);
INSERT INTO `VLANValidID` VALUES (1456);
INSERT INTO `VLANValidID` VALUES (1457);
INSERT INTO `VLANValidID` VALUES (1458);
INSERT INTO `VLANValidID` VALUES (1459);
INSERT INTO `VLANValidID` VALUES (1460);
INSERT INTO `VLANValidID` VALUES (1461);
INSERT INTO `VLANValidID` VALUES (1462);
INSERT INTO `VLANValidID` VALUES (1463);
INSERT INTO `VLANValidID` VALUES (1464);
INSERT INTO `VLANValidID` VALUES (1465);
INSERT INTO `VLANValidID` VALUES (1466);
INSERT INTO `VLANValidID` VALUES (1467);
INSERT INTO `VLANValidID` VALUES (1468);
INSERT INTO `VLANValidID` VALUES (1469);
INSERT INTO `VLANValidID` VALUES (1470);
INSERT INTO `VLANValidID` VALUES (1471);
INSERT INTO `VLANValidID` VALUES (1472);
INSERT INTO `VLANValidID` VALUES (1473);
INSERT INTO `VLANValidID` VALUES (1474);
INSERT INTO `VLANValidID` VALUES (1475);
INSERT INTO `VLANValidID` VALUES (1476);
INSERT INTO `VLANValidID` VALUES (1477);
INSERT INTO `VLANValidID` VALUES (1478);
INSERT INTO `VLANValidID` VALUES (1479);
INSERT INTO `VLANValidID` VALUES (1480);
INSERT INTO `VLANValidID` VALUES (1481);
INSERT INTO `VLANValidID` VALUES (1482);
INSERT INTO `VLANValidID` VALUES (1483);
INSERT INTO `VLANValidID` VALUES (1484);
INSERT INTO `VLANValidID` VALUES (1485);
INSERT INTO `VLANValidID` VALUES (1486);
INSERT INTO `VLANValidID` VALUES (1487);
INSERT INTO `VLANValidID` VALUES (1488);
INSERT INTO `VLANValidID` VALUES (1489);
INSERT INTO `VLANValidID` VALUES (1490);
INSERT INTO `VLANValidID` VALUES (1491);
INSERT INTO `VLANValidID` VALUES (1492);
INSERT INTO `VLANValidID` VALUES (1493);
INSERT INTO `VLANValidID` VALUES (1494);
INSERT INTO `VLANValidID` VALUES (1495);
INSERT INTO `VLANValidID` VALUES (1496);
INSERT INTO `VLANValidID` VALUES (1497);
INSERT INTO `VLANValidID` VALUES (1498);
INSERT INTO `VLANValidID` VALUES (1499);
INSERT INTO `VLANValidID` VALUES (1500);
INSERT INTO `VLANValidID` VALUES (1501);
INSERT INTO `VLANValidID` VALUES (1502);
INSERT INTO `VLANValidID` VALUES (1503);
INSERT INTO `VLANValidID` VALUES (1504);
INSERT INTO `VLANValidID` VALUES (1505);
INSERT INTO `VLANValidID` VALUES (1506);
INSERT INTO `VLANValidID` VALUES (1507);
INSERT INTO `VLANValidID` VALUES (1508);
INSERT INTO `VLANValidID` VALUES (1509);
INSERT INTO `VLANValidID` VALUES (1510);
INSERT INTO `VLANValidID` VALUES (1511);
INSERT INTO `VLANValidID` VALUES (1512);
INSERT INTO `VLANValidID` VALUES (1513);
INSERT INTO `VLANValidID` VALUES (1514);
INSERT INTO `VLANValidID` VALUES (1515);
INSERT INTO `VLANValidID` VALUES (1516);
INSERT INTO `VLANValidID` VALUES (1517);
INSERT INTO `VLANValidID` VALUES (1518);
INSERT INTO `VLANValidID` VALUES (1519);
INSERT INTO `VLANValidID` VALUES (1520);
INSERT INTO `VLANValidID` VALUES (1521);
INSERT INTO `VLANValidID` VALUES (1522);
INSERT INTO `VLANValidID` VALUES (1523);
INSERT INTO `VLANValidID` VALUES (1524);
INSERT INTO `VLANValidID` VALUES (1525);
INSERT INTO `VLANValidID` VALUES (1526);
INSERT INTO `VLANValidID` VALUES (1527);
INSERT INTO `VLANValidID` VALUES (1528);
INSERT INTO `VLANValidID` VALUES (1529);
INSERT INTO `VLANValidID` VALUES (1530);
INSERT INTO `VLANValidID` VALUES (1531);
INSERT INTO `VLANValidID` VALUES (1532);
INSERT INTO `VLANValidID` VALUES (1533);
INSERT INTO `VLANValidID` VALUES (1534);
INSERT INTO `VLANValidID` VALUES (1535);
INSERT INTO `VLANValidID` VALUES (1536);
INSERT INTO `VLANValidID` VALUES (1537);
INSERT INTO `VLANValidID` VALUES (1538);
INSERT INTO `VLANValidID` VALUES (1539);
INSERT INTO `VLANValidID` VALUES (1540);
INSERT INTO `VLANValidID` VALUES (1541);
INSERT INTO `VLANValidID` VALUES (1542);
INSERT INTO `VLANValidID` VALUES (1543);
INSERT INTO `VLANValidID` VALUES (1544);
INSERT INTO `VLANValidID` VALUES (1545);
INSERT INTO `VLANValidID` VALUES (1546);
INSERT INTO `VLANValidID` VALUES (1547);
INSERT INTO `VLANValidID` VALUES (1548);
INSERT INTO `VLANValidID` VALUES (1549);
INSERT INTO `VLANValidID` VALUES (1550);
INSERT INTO `VLANValidID` VALUES (1551);
INSERT INTO `VLANValidID` VALUES (1552);
INSERT INTO `VLANValidID` VALUES (1553);
INSERT INTO `VLANValidID` VALUES (1554);
INSERT INTO `VLANValidID` VALUES (1555);
INSERT INTO `VLANValidID` VALUES (1556);
INSERT INTO `VLANValidID` VALUES (1557);
INSERT INTO `VLANValidID` VALUES (1558);
INSERT INTO `VLANValidID` VALUES (1559);
INSERT INTO `VLANValidID` VALUES (1560);
INSERT INTO `VLANValidID` VALUES (1561);
INSERT INTO `VLANValidID` VALUES (1562);
INSERT INTO `VLANValidID` VALUES (1563);
INSERT INTO `VLANValidID` VALUES (1564);
INSERT INTO `VLANValidID` VALUES (1565);
INSERT INTO `VLANValidID` VALUES (1566);
INSERT INTO `VLANValidID` VALUES (1567);
INSERT INTO `VLANValidID` VALUES (1568);
INSERT INTO `VLANValidID` VALUES (1569);
INSERT INTO `VLANValidID` VALUES (1570);
INSERT INTO `VLANValidID` VALUES (1571);
INSERT INTO `VLANValidID` VALUES (1572);
INSERT INTO `VLANValidID` VALUES (1573);
INSERT INTO `VLANValidID` VALUES (1574);
INSERT INTO `VLANValidID` VALUES (1575);
INSERT INTO `VLANValidID` VALUES (1576);
INSERT INTO `VLANValidID` VALUES (1577);
INSERT INTO `VLANValidID` VALUES (1578);
INSERT INTO `VLANValidID` VALUES (1579);
INSERT INTO `VLANValidID` VALUES (1580);
INSERT INTO `VLANValidID` VALUES (1581);
INSERT INTO `VLANValidID` VALUES (1582);
INSERT INTO `VLANValidID` VALUES (1583);
INSERT INTO `VLANValidID` VALUES (1584);
INSERT INTO `VLANValidID` VALUES (1585);
INSERT INTO `VLANValidID` VALUES (1586);
INSERT INTO `VLANValidID` VALUES (1587);
INSERT INTO `VLANValidID` VALUES (1588);
INSERT INTO `VLANValidID` VALUES (1589);
INSERT INTO `VLANValidID` VALUES (1590);
INSERT INTO `VLANValidID` VALUES (1591);
INSERT INTO `VLANValidID` VALUES (1592);
INSERT INTO `VLANValidID` VALUES (1593);
INSERT INTO `VLANValidID` VALUES (1594);
INSERT INTO `VLANValidID` VALUES (1595);
INSERT INTO `VLANValidID` VALUES (1596);
INSERT INTO `VLANValidID` VALUES (1597);
INSERT INTO `VLANValidID` VALUES (1598);
INSERT INTO `VLANValidID` VALUES (1599);
INSERT INTO `VLANValidID` VALUES (1600);
INSERT INTO `VLANValidID` VALUES (1601);
INSERT INTO `VLANValidID` VALUES (1602);
INSERT INTO `VLANValidID` VALUES (1603);
INSERT INTO `VLANValidID` VALUES (1604);
INSERT INTO `VLANValidID` VALUES (1605);
INSERT INTO `VLANValidID` VALUES (1606);
INSERT INTO `VLANValidID` VALUES (1607);
INSERT INTO `VLANValidID` VALUES (1608);
INSERT INTO `VLANValidID` VALUES (1609);
INSERT INTO `VLANValidID` VALUES (1610);
INSERT INTO `VLANValidID` VALUES (1611);
INSERT INTO `VLANValidID` VALUES (1612);
INSERT INTO `VLANValidID` VALUES (1613);
INSERT INTO `VLANValidID` VALUES (1614);
INSERT INTO `VLANValidID` VALUES (1615);
INSERT INTO `VLANValidID` VALUES (1616);
INSERT INTO `VLANValidID` VALUES (1617);
INSERT INTO `VLANValidID` VALUES (1618);
INSERT INTO `VLANValidID` VALUES (1619);
INSERT INTO `VLANValidID` VALUES (1620);
INSERT INTO `VLANValidID` VALUES (1621);
INSERT INTO `VLANValidID` VALUES (1622);
INSERT INTO `VLANValidID` VALUES (1623);
INSERT INTO `VLANValidID` VALUES (1624);
INSERT INTO `VLANValidID` VALUES (1625);
INSERT INTO `VLANValidID` VALUES (1626);
INSERT INTO `VLANValidID` VALUES (1627);
INSERT INTO `VLANValidID` VALUES (1628);
INSERT INTO `VLANValidID` VALUES (1629);
INSERT INTO `VLANValidID` VALUES (1630);
INSERT INTO `VLANValidID` VALUES (1631);
INSERT INTO `VLANValidID` VALUES (1632);
INSERT INTO `VLANValidID` VALUES (1633);
INSERT INTO `VLANValidID` VALUES (1634);
INSERT INTO `VLANValidID` VALUES (1635);
INSERT INTO `VLANValidID` VALUES (1636);
INSERT INTO `VLANValidID` VALUES (1637);
INSERT INTO `VLANValidID` VALUES (1638);
INSERT INTO `VLANValidID` VALUES (1639);
INSERT INTO `VLANValidID` VALUES (1640);
INSERT INTO `VLANValidID` VALUES (1641);
INSERT INTO `VLANValidID` VALUES (1642);
INSERT INTO `VLANValidID` VALUES (1643);
INSERT INTO `VLANValidID` VALUES (1644);
INSERT INTO `VLANValidID` VALUES (1645);
INSERT INTO `VLANValidID` VALUES (1646);
INSERT INTO `VLANValidID` VALUES (1647);
INSERT INTO `VLANValidID` VALUES (1648);
INSERT INTO `VLANValidID` VALUES (1649);
INSERT INTO `VLANValidID` VALUES (1650);
INSERT INTO `VLANValidID` VALUES (1651);
INSERT INTO `VLANValidID` VALUES (1652);
INSERT INTO `VLANValidID` VALUES (1653);
INSERT INTO `VLANValidID` VALUES (1654);
INSERT INTO `VLANValidID` VALUES (1655);
INSERT INTO `VLANValidID` VALUES (1656);
INSERT INTO `VLANValidID` VALUES (1657);
INSERT INTO `VLANValidID` VALUES (1658);
INSERT INTO `VLANValidID` VALUES (1659);
INSERT INTO `VLANValidID` VALUES (1660);
INSERT INTO `VLANValidID` VALUES (1661);
INSERT INTO `VLANValidID` VALUES (1662);
INSERT INTO `VLANValidID` VALUES (1663);
INSERT INTO `VLANValidID` VALUES (1664);
INSERT INTO `VLANValidID` VALUES (1665);
INSERT INTO `VLANValidID` VALUES (1666);
INSERT INTO `VLANValidID` VALUES (1667);
INSERT INTO `VLANValidID` VALUES (1668);
INSERT INTO `VLANValidID` VALUES (1669);
INSERT INTO `VLANValidID` VALUES (1670);
INSERT INTO `VLANValidID` VALUES (1671);
INSERT INTO `VLANValidID` VALUES (1672);
INSERT INTO `VLANValidID` VALUES (1673);
INSERT INTO `VLANValidID` VALUES (1674);
INSERT INTO `VLANValidID` VALUES (1675);
INSERT INTO `VLANValidID` VALUES (1676);
INSERT INTO `VLANValidID` VALUES (1677);
INSERT INTO `VLANValidID` VALUES (1678);
INSERT INTO `VLANValidID` VALUES (1679);
INSERT INTO `VLANValidID` VALUES (1680);
INSERT INTO `VLANValidID` VALUES (1681);
INSERT INTO `VLANValidID` VALUES (1682);
INSERT INTO `VLANValidID` VALUES (1683);
INSERT INTO `VLANValidID` VALUES (1684);
INSERT INTO `VLANValidID` VALUES (1685);
INSERT INTO `VLANValidID` VALUES (1686);
INSERT INTO `VLANValidID` VALUES (1687);
INSERT INTO `VLANValidID` VALUES (1688);
INSERT INTO `VLANValidID` VALUES (1689);
INSERT INTO `VLANValidID` VALUES (1690);
INSERT INTO `VLANValidID` VALUES (1691);
INSERT INTO `VLANValidID` VALUES (1692);
INSERT INTO `VLANValidID` VALUES (1693);
INSERT INTO `VLANValidID` VALUES (1694);
INSERT INTO `VLANValidID` VALUES (1695);
INSERT INTO `VLANValidID` VALUES (1696);
INSERT INTO `VLANValidID` VALUES (1697);
INSERT INTO `VLANValidID` VALUES (1698);
INSERT INTO `VLANValidID` VALUES (1699);
INSERT INTO `VLANValidID` VALUES (1700);
INSERT INTO `VLANValidID` VALUES (1701);
INSERT INTO `VLANValidID` VALUES (1702);
INSERT INTO `VLANValidID` VALUES (1703);
INSERT INTO `VLANValidID` VALUES (1704);
INSERT INTO `VLANValidID` VALUES (1705);
INSERT INTO `VLANValidID` VALUES (1706);
INSERT INTO `VLANValidID` VALUES (1707);
INSERT INTO `VLANValidID` VALUES (1708);
INSERT INTO `VLANValidID` VALUES (1709);
INSERT INTO `VLANValidID` VALUES (1710);
INSERT INTO `VLANValidID` VALUES (1711);
INSERT INTO `VLANValidID` VALUES (1712);
INSERT INTO `VLANValidID` VALUES (1713);
INSERT INTO `VLANValidID` VALUES (1714);
INSERT INTO `VLANValidID` VALUES (1715);
INSERT INTO `VLANValidID` VALUES (1716);
INSERT INTO `VLANValidID` VALUES (1717);
INSERT INTO `VLANValidID` VALUES (1718);
INSERT INTO `VLANValidID` VALUES (1719);
INSERT INTO `VLANValidID` VALUES (1720);
INSERT INTO `VLANValidID` VALUES (1721);
INSERT INTO `VLANValidID` VALUES (1722);
INSERT INTO `VLANValidID` VALUES (1723);
INSERT INTO `VLANValidID` VALUES (1724);
INSERT INTO `VLANValidID` VALUES (1725);
INSERT INTO `VLANValidID` VALUES (1726);
INSERT INTO `VLANValidID` VALUES (1727);
INSERT INTO `VLANValidID` VALUES (1728);
INSERT INTO `VLANValidID` VALUES (1729);
INSERT INTO `VLANValidID` VALUES (1730);
INSERT INTO `VLANValidID` VALUES (1731);
INSERT INTO `VLANValidID` VALUES (1732);
INSERT INTO `VLANValidID` VALUES (1733);
INSERT INTO `VLANValidID` VALUES (1734);
INSERT INTO `VLANValidID` VALUES (1735);
INSERT INTO `VLANValidID` VALUES (1736);
INSERT INTO `VLANValidID` VALUES (1737);
INSERT INTO `VLANValidID` VALUES (1738);
INSERT INTO `VLANValidID` VALUES (1739);
INSERT INTO `VLANValidID` VALUES (1740);
INSERT INTO `VLANValidID` VALUES (1741);
INSERT INTO `VLANValidID` VALUES (1742);
INSERT INTO `VLANValidID` VALUES (1743);
INSERT INTO `VLANValidID` VALUES (1744);
INSERT INTO `VLANValidID` VALUES (1745);
INSERT INTO `VLANValidID` VALUES (1746);
INSERT INTO `VLANValidID` VALUES (1747);
INSERT INTO `VLANValidID` VALUES (1748);
INSERT INTO `VLANValidID` VALUES (1749);
INSERT INTO `VLANValidID` VALUES (1750);
INSERT INTO `VLANValidID` VALUES (1751);
INSERT INTO `VLANValidID` VALUES (1752);
INSERT INTO `VLANValidID` VALUES (1753);
INSERT INTO `VLANValidID` VALUES (1754);
INSERT INTO `VLANValidID` VALUES (1755);
INSERT INTO `VLANValidID` VALUES (1756);
INSERT INTO `VLANValidID` VALUES (1757);
INSERT INTO `VLANValidID` VALUES (1758);
INSERT INTO `VLANValidID` VALUES (1759);
INSERT INTO `VLANValidID` VALUES (1760);
INSERT INTO `VLANValidID` VALUES (1761);
INSERT INTO `VLANValidID` VALUES (1762);
INSERT INTO `VLANValidID` VALUES (1763);
INSERT INTO `VLANValidID` VALUES (1764);
INSERT INTO `VLANValidID` VALUES (1765);
INSERT INTO `VLANValidID` VALUES (1766);
INSERT INTO `VLANValidID` VALUES (1767);
INSERT INTO `VLANValidID` VALUES (1768);
INSERT INTO `VLANValidID` VALUES (1769);
INSERT INTO `VLANValidID` VALUES (1770);
INSERT INTO `VLANValidID` VALUES (1771);
INSERT INTO `VLANValidID` VALUES (1772);
INSERT INTO `VLANValidID` VALUES (1773);
INSERT INTO `VLANValidID` VALUES (1774);
INSERT INTO `VLANValidID` VALUES (1775);
INSERT INTO `VLANValidID` VALUES (1776);
INSERT INTO `VLANValidID` VALUES (1777);
INSERT INTO `VLANValidID` VALUES (1778);
INSERT INTO `VLANValidID` VALUES (1779);
INSERT INTO `VLANValidID` VALUES (1780);
INSERT INTO `VLANValidID` VALUES (1781);
INSERT INTO `VLANValidID` VALUES (1782);
INSERT INTO `VLANValidID` VALUES (1783);
INSERT INTO `VLANValidID` VALUES (1784);
INSERT INTO `VLANValidID` VALUES (1785);
INSERT INTO `VLANValidID` VALUES (1786);
INSERT INTO `VLANValidID` VALUES (1787);
INSERT INTO `VLANValidID` VALUES (1788);
INSERT INTO `VLANValidID` VALUES (1789);
INSERT INTO `VLANValidID` VALUES (1790);
INSERT INTO `VLANValidID` VALUES (1791);
INSERT INTO `VLANValidID` VALUES (1792);
INSERT INTO `VLANValidID` VALUES (1793);
INSERT INTO `VLANValidID` VALUES (1794);
INSERT INTO `VLANValidID` VALUES (1795);
INSERT INTO `VLANValidID` VALUES (1796);
INSERT INTO `VLANValidID` VALUES (1797);
INSERT INTO `VLANValidID` VALUES (1798);
INSERT INTO `VLANValidID` VALUES (1799);
INSERT INTO `VLANValidID` VALUES (1800);
INSERT INTO `VLANValidID` VALUES (1801);
INSERT INTO `VLANValidID` VALUES (1802);
INSERT INTO `VLANValidID` VALUES (1803);
INSERT INTO `VLANValidID` VALUES (1804);
INSERT INTO `VLANValidID` VALUES (1805);
INSERT INTO `VLANValidID` VALUES (1806);
INSERT INTO `VLANValidID` VALUES (1807);
INSERT INTO `VLANValidID` VALUES (1808);
INSERT INTO `VLANValidID` VALUES (1809);
INSERT INTO `VLANValidID` VALUES (1810);
INSERT INTO `VLANValidID` VALUES (1811);
INSERT INTO `VLANValidID` VALUES (1812);
INSERT INTO `VLANValidID` VALUES (1813);
INSERT INTO `VLANValidID` VALUES (1814);
INSERT INTO `VLANValidID` VALUES (1815);
INSERT INTO `VLANValidID` VALUES (1816);
INSERT INTO `VLANValidID` VALUES (1817);
INSERT INTO `VLANValidID` VALUES (1818);
INSERT INTO `VLANValidID` VALUES (1819);
INSERT INTO `VLANValidID` VALUES (1820);
INSERT INTO `VLANValidID` VALUES (1821);
INSERT INTO `VLANValidID` VALUES (1822);
INSERT INTO `VLANValidID` VALUES (1823);
INSERT INTO `VLANValidID` VALUES (1824);
INSERT INTO `VLANValidID` VALUES (1825);
INSERT INTO `VLANValidID` VALUES (1826);
INSERT INTO `VLANValidID` VALUES (1827);
INSERT INTO `VLANValidID` VALUES (1828);
INSERT INTO `VLANValidID` VALUES (1829);
INSERT INTO `VLANValidID` VALUES (1830);
INSERT INTO `VLANValidID` VALUES (1831);
INSERT INTO `VLANValidID` VALUES (1832);
INSERT INTO `VLANValidID` VALUES (1833);
INSERT INTO `VLANValidID` VALUES (1834);
INSERT INTO `VLANValidID` VALUES (1835);
INSERT INTO `VLANValidID` VALUES (1836);
INSERT INTO `VLANValidID` VALUES (1837);
INSERT INTO `VLANValidID` VALUES (1838);
INSERT INTO `VLANValidID` VALUES (1839);
INSERT INTO `VLANValidID` VALUES (1840);
INSERT INTO `VLANValidID` VALUES (1841);
INSERT INTO `VLANValidID` VALUES (1842);
INSERT INTO `VLANValidID` VALUES (1843);
INSERT INTO `VLANValidID` VALUES (1844);
INSERT INTO `VLANValidID` VALUES (1845);
INSERT INTO `VLANValidID` VALUES (1846);
INSERT INTO `VLANValidID` VALUES (1847);
INSERT INTO `VLANValidID` VALUES (1848);
INSERT INTO `VLANValidID` VALUES (1849);
INSERT INTO `VLANValidID` VALUES (1850);
INSERT INTO `VLANValidID` VALUES (1851);
INSERT INTO `VLANValidID` VALUES (1852);
INSERT INTO `VLANValidID` VALUES (1853);
INSERT INTO `VLANValidID` VALUES (1854);
INSERT INTO `VLANValidID` VALUES (1855);
INSERT INTO `VLANValidID` VALUES (1856);
INSERT INTO `VLANValidID` VALUES (1857);
INSERT INTO `VLANValidID` VALUES (1858);
INSERT INTO `VLANValidID` VALUES (1859);
INSERT INTO `VLANValidID` VALUES (1860);
INSERT INTO `VLANValidID` VALUES (1861);
INSERT INTO `VLANValidID` VALUES (1862);
INSERT INTO `VLANValidID` VALUES (1863);
INSERT INTO `VLANValidID` VALUES (1864);
INSERT INTO `VLANValidID` VALUES (1865);
INSERT INTO `VLANValidID` VALUES (1866);
INSERT INTO `VLANValidID` VALUES (1867);
INSERT INTO `VLANValidID` VALUES (1868);
INSERT INTO `VLANValidID` VALUES (1869);
INSERT INTO `VLANValidID` VALUES (1870);
INSERT INTO `VLANValidID` VALUES (1871);
INSERT INTO `VLANValidID` VALUES (1872);
INSERT INTO `VLANValidID` VALUES (1873);
INSERT INTO `VLANValidID` VALUES (1874);
INSERT INTO `VLANValidID` VALUES (1875);
INSERT INTO `VLANValidID` VALUES (1876);
INSERT INTO `VLANValidID` VALUES (1877);
INSERT INTO `VLANValidID` VALUES (1878);
INSERT INTO `VLANValidID` VALUES (1879);
INSERT INTO `VLANValidID` VALUES (1880);
INSERT INTO `VLANValidID` VALUES (1881);
INSERT INTO `VLANValidID` VALUES (1882);
INSERT INTO `VLANValidID` VALUES (1883);
INSERT INTO `VLANValidID` VALUES (1884);
INSERT INTO `VLANValidID` VALUES (1885);
INSERT INTO `VLANValidID` VALUES (1886);
INSERT INTO `VLANValidID` VALUES (1887);
INSERT INTO `VLANValidID` VALUES (1888);
INSERT INTO `VLANValidID` VALUES (1889);
INSERT INTO `VLANValidID` VALUES (1890);
INSERT INTO `VLANValidID` VALUES (1891);
INSERT INTO `VLANValidID` VALUES (1892);
INSERT INTO `VLANValidID` VALUES (1893);
INSERT INTO `VLANValidID` VALUES (1894);
INSERT INTO `VLANValidID` VALUES (1895);
INSERT INTO `VLANValidID` VALUES (1896);
INSERT INTO `VLANValidID` VALUES (1897);
INSERT INTO `VLANValidID` VALUES (1898);
INSERT INTO `VLANValidID` VALUES (1899);
INSERT INTO `VLANValidID` VALUES (1900);
INSERT INTO `VLANValidID` VALUES (1901);
INSERT INTO `VLANValidID` VALUES (1902);
INSERT INTO `VLANValidID` VALUES (1903);
INSERT INTO `VLANValidID` VALUES (1904);
INSERT INTO `VLANValidID` VALUES (1905);
INSERT INTO `VLANValidID` VALUES (1906);
INSERT INTO `VLANValidID` VALUES (1907);
INSERT INTO `VLANValidID` VALUES (1908);
INSERT INTO `VLANValidID` VALUES (1909);
INSERT INTO `VLANValidID` VALUES (1910);
INSERT INTO `VLANValidID` VALUES (1911);
INSERT INTO `VLANValidID` VALUES (1912);
INSERT INTO `VLANValidID` VALUES (1913);
INSERT INTO `VLANValidID` VALUES (1914);
INSERT INTO `VLANValidID` VALUES (1915);
INSERT INTO `VLANValidID` VALUES (1916);
INSERT INTO `VLANValidID` VALUES (1917);
INSERT INTO `VLANValidID` VALUES (1918);
INSERT INTO `VLANValidID` VALUES (1919);
INSERT INTO `VLANValidID` VALUES (1920);
INSERT INTO `VLANValidID` VALUES (1921);
INSERT INTO `VLANValidID` VALUES (1922);
INSERT INTO `VLANValidID` VALUES (1923);
INSERT INTO `VLANValidID` VALUES (1924);
INSERT INTO `VLANValidID` VALUES (1925);
INSERT INTO `VLANValidID` VALUES (1926);
INSERT INTO `VLANValidID` VALUES (1927);
INSERT INTO `VLANValidID` VALUES (1928);
INSERT INTO `VLANValidID` VALUES (1929);
INSERT INTO `VLANValidID` VALUES (1930);
INSERT INTO `VLANValidID` VALUES (1931);
INSERT INTO `VLANValidID` VALUES (1932);
INSERT INTO `VLANValidID` VALUES (1933);
INSERT INTO `VLANValidID` VALUES (1934);
INSERT INTO `VLANValidID` VALUES (1935);
INSERT INTO `VLANValidID` VALUES (1936);
INSERT INTO `VLANValidID` VALUES (1937);
INSERT INTO `VLANValidID` VALUES (1938);
INSERT INTO `VLANValidID` VALUES (1939);
INSERT INTO `VLANValidID` VALUES (1940);
INSERT INTO `VLANValidID` VALUES (1941);
INSERT INTO `VLANValidID` VALUES (1942);
INSERT INTO `VLANValidID` VALUES (1943);
INSERT INTO `VLANValidID` VALUES (1944);
INSERT INTO `VLANValidID` VALUES (1945);
INSERT INTO `VLANValidID` VALUES (1946);
INSERT INTO `VLANValidID` VALUES (1947);
INSERT INTO `VLANValidID` VALUES (1948);
INSERT INTO `VLANValidID` VALUES (1949);
INSERT INTO `VLANValidID` VALUES (1950);
INSERT INTO `VLANValidID` VALUES (1951);
INSERT INTO `VLANValidID` VALUES (1952);
INSERT INTO `VLANValidID` VALUES (1953);
INSERT INTO `VLANValidID` VALUES (1954);
INSERT INTO `VLANValidID` VALUES (1955);
INSERT INTO `VLANValidID` VALUES (1956);
INSERT INTO `VLANValidID` VALUES (1957);
INSERT INTO `VLANValidID` VALUES (1958);
INSERT INTO `VLANValidID` VALUES (1959);
INSERT INTO `VLANValidID` VALUES (1960);
INSERT INTO `VLANValidID` VALUES (1961);
INSERT INTO `VLANValidID` VALUES (1962);
INSERT INTO `VLANValidID` VALUES (1963);
INSERT INTO `VLANValidID` VALUES (1964);
INSERT INTO `VLANValidID` VALUES (1965);
INSERT INTO `VLANValidID` VALUES (1966);
INSERT INTO `VLANValidID` VALUES (1967);
INSERT INTO `VLANValidID` VALUES (1968);
INSERT INTO `VLANValidID` VALUES (1969);
INSERT INTO `VLANValidID` VALUES (1970);
INSERT INTO `VLANValidID` VALUES (1971);
INSERT INTO `VLANValidID` VALUES (1972);
INSERT INTO `VLANValidID` VALUES (1973);
INSERT INTO `VLANValidID` VALUES (1974);
INSERT INTO `VLANValidID` VALUES (1975);
INSERT INTO `VLANValidID` VALUES (1976);
INSERT INTO `VLANValidID` VALUES (1977);
INSERT INTO `VLANValidID` VALUES (1978);
INSERT INTO `VLANValidID` VALUES (1979);
INSERT INTO `VLANValidID` VALUES (1980);
INSERT INTO `VLANValidID` VALUES (1981);
INSERT INTO `VLANValidID` VALUES (1982);
INSERT INTO `VLANValidID` VALUES (1983);
INSERT INTO `VLANValidID` VALUES (1984);
INSERT INTO `VLANValidID` VALUES (1985);
INSERT INTO `VLANValidID` VALUES (1986);
INSERT INTO `VLANValidID` VALUES (1987);
INSERT INTO `VLANValidID` VALUES (1988);
INSERT INTO `VLANValidID` VALUES (1989);
INSERT INTO `VLANValidID` VALUES (1990);
INSERT INTO `VLANValidID` VALUES (1991);
INSERT INTO `VLANValidID` VALUES (1992);
INSERT INTO `VLANValidID` VALUES (1993);
INSERT INTO `VLANValidID` VALUES (1994);
INSERT INTO `VLANValidID` VALUES (1995);
INSERT INTO `VLANValidID` VALUES (1996);
INSERT INTO `VLANValidID` VALUES (1997);
INSERT INTO `VLANValidID` VALUES (1998);
INSERT INTO `VLANValidID` VALUES (1999);
INSERT INTO `VLANValidID` VALUES (2000);
INSERT INTO `VLANValidID` VALUES (2001);
INSERT INTO `VLANValidID` VALUES (2002);
INSERT INTO `VLANValidID` VALUES (2003);
INSERT INTO `VLANValidID` VALUES (2004);
INSERT INTO `VLANValidID` VALUES (2005);
INSERT INTO `VLANValidID` VALUES (2006);
INSERT INTO `VLANValidID` VALUES (2007);
INSERT INTO `VLANValidID` VALUES (2008);
INSERT INTO `VLANValidID` VALUES (2009);
INSERT INTO `VLANValidID` VALUES (2010);
INSERT INTO `VLANValidID` VALUES (2011);
INSERT INTO `VLANValidID` VALUES (2012);
INSERT INTO `VLANValidID` VALUES (2013);
INSERT INTO `VLANValidID` VALUES (2014);
INSERT INTO `VLANValidID` VALUES (2015);
INSERT INTO `VLANValidID` VALUES (2016);
INSERT INTO `VLANValidID` VALUES (2017);
INSERT INTO `VLANValidID` VALUES (2018);
INSERT INTO `VLANValidID` VALUES (2019);
INSERT INTO `VLANValidID` VALUES (2020);
INSERT INTO `VLANValidID` VALUES (2021);
INSERT INTO `VLANValidID` VALUES (2022);
INSERT INTO `VLANValidID` VALUES (2023);
INSERT INTO `VLANValidID` VALUES (2024);
INSERT INTO `VLANValidID` VALUES (2025);
INSERT INTO `VLANValidID` VALUES (2026);
INSERT INTO `VLANValidID` VALUES (2027);
INSERT INTO `VLANValidID` VALUES (2028);
INSERT INTO `VLANValidID` VALUES (2029);
INSERT INTO `VLANValidID` VALUES (2030);
INSERT INTO `VLANValidID` VALUES (2031);
INSERT INTO `VLANValidID` VALUES (2032);
INSERT INTO `VLANValidID` VALUES (2033);
INSERT INTO `VLANValidID` VALUES (2034);
INSERT INTO `VLANValidID` VALUES (2035);
INSERT INTO `VLANValidID` VALUES (2036);
INSERT INTO `VLANValidID` VALUES (2037);
INSERT INTO `VLANValidID` VALUES (2038);
INSERT INTO `VLANValidID` VALUES (2039);
INSERT INTO `VLANValidID` VALUES (2040);
INSERT INTO `VLANValidID` VALUES (2041);
INSERT INTO `VLANValidID` VALUES (2042);
INSERT INTO `VLANValidID` VALUES (2043);
INSERT INTO `VLANValidID` VALUES (2044);
INSERT INTO `VLANValidID` VALUES (2045);
INSERT INTO `VLANValidID` VALUES (2046);
INSERT INTO `VLANValidID` VALUES (2047);
INSERT INTO `VLANValidID` VALUES (2048);
INSERT INTO `VLANValidID` VALUES (2049);
INSERT INTO `VLANValidID` VALUES (2050);
INSERT INTO `VLANValidID` VALUES (2051);
INSERT INTO `VLANValidID` VALUES (2052);
INSERT INTO `VLANValidID` VALUES (2053);
INSERT INTO `VLANValidID` VALUES (2054);
INSERT INTO `VLANValidID` VALUES (2055);
INSERT INTO `VLANValidID` VALUES (2056);
INSERT INTO `VLANValidID` VALUES (2057);
INSERT INTO `VLANValidID` VALUES (2058);
INSERT INTO `VLANValidID` VALUES (2059);
INSERT INTO `VLANValidID` VALUES (2060);
INSERT INTO `VLANValidID` VALUES (2061);
INSERT INTO `VLANValidID` VALUES (2062);
INSERT INTO `VLANValidID` VALUES (2063);
INSERT INTO `VLANValidID` VALUES (2064);
INSERT INTO `VLANValidID` VALUES (2065);
INSERT INTO `VLANValidID` VALUES (2066);
INSERT INTO `VLANValidID` VALUES (2067);
INSERT INTO `VLANValidID` VALUES (2068);
INSERT INTO `VLANValidID` VALUES (2069);
INSERT INTO `VLANValidID` VALUES (2070);
INSERT INTO `VLANValidID` VALUES (2071);
INSERT INTO `VLANValidID` VALUES (2072);
INSERT INTO `VLANValidID` VALUES (2073);
INSERT INTO `VLANValidID` VALUES (2074);
INSERT INTO `VLANValidID` VALUES (2075);
INSERT INTO `VLANValidID` VALUES (2076);
INSERT INTO `VLANValidID` VALUES (2077);
INSERT INTO `VLANValidID` VALUES (2078);
INSERT INTO `VLANValidID` VALUES (2079);
INSERT INTO `VLANValidID` VALUES (2080);
INSERT INTO `VLANValidID` VALUES (2081);
INSERT INTO `VLANValidID` VALUES (2082);
INSERT INTO `VLANValidID` VALUES (2083);
INSERT INTO `VLANValidID` VALUES (2084);
INSERT INTO `VLANValidID` VALUES (2085);
INSERT INTO `VLANValidID` VALUES (2086);
INSERT INTO `VLANValidID` VALUES (2087);
INSERT INTO `VLANValidID` VALUES (2088);
INSERT INTO `VLANValidID` VALUES (2089);
INSERT INTO `VLANValidID` VALUES (2090);
INSERT INTO `VLANValidID` VALUES (2091);
INSERT INTO `VLANValidID` VALUES (2092);
INSERT INTO `VLANValidID` VALUES (2093);
INSERT INTO `VLANValidID` VALUES (2094);
INSERT INTO `VLANValidID` VALUES (2095);
INSERT INTO `VLANValidID` VALUES (2096);
INSERT INTO `VLANValidID` VALUES (2097);
INSERT INTO `VLANValidID` VALUES (2098);
INSERT INTO `VLANValidID` VALUES (2099);
INSERT INTO `VLANValidID` VALUES (2100);
INSERT INTO `VLANValidID` VALUES (2101);
INSERT INTO `VLANValidID` VALUES (2102);
INSERT INTO `VLANValidID` VALUES (2103);
INSERT INTO `VLANValidID` VALUES (2104);
INSERT INTO `VLANValidID` VALUES (2105);
INSERT INTO `VLANValidID` VALUES (2106);
INSERT INTO `VLANValidID` VALUES (2107);
INSERT INTO `VLANValidID` VALUES (2108);
INSERT INTO `VLANValidID` VALUES (2109);
INSERT INTO `VLANValidID` VALUES (2110);
INSERT INTO `VLANValidID` VALUES (2111);
INSERT INTO `VLANValidID` VALUES (2112);
INSERT INTO `VLANValidID` VALUES (2113);
INSERT INTO `VLANValidID` VALUES (2114);
INSERT INTO `VLANValidID` VALUES (2115);
INSERT INTO `VLANValidID` VALUES (2116);
INSERT INTO `VLANValidID` VALUES (2117);
INSERT INTO `VLANValidID` VALUES (2118);
INSERT INTO `VLANValidID` VALUES (2119);
INSERT INTO `VLANValidID` VALUES (2120);
INSERT INTO `VLANValidID` VALUES (2121);
INSERT INTO `VLANValidID` VALUES (2122);
INSERT INTO `VLANValidID` VALUES (2123);
INSERT INTO `VLANValidID` VALUES (2124);
INSERT INTO `VLANValidID` VALUES (2125);
INSERT INTO `VLANValidID` VALUES (2126);
INSERT INTO `VLANValidID` VALUES (2127);
INSERT INTO `VLANValidID` VALUES (2128);
INSERT INTO `VLANValidID` VALUES (2129);
INSERT INTO `VLANValidID` VALUES (2130);
INSERT INTO `VLANValidID` VALUES (2131);
INSERT INTO `VLANValidID` VALUES (2132);
INSERT INTO `VLANValidID` VALUES (2133);
INSERT INTO `VLANValidID` VALUES (2134);
INSERT INTO `VLANValidID` VALUES (2135);
INSERT INTO `VLANValidID` VALUES (2136);
INSERT INTO `VLANValidID` VALUES (2137);
INSERT INTO `VLANValidID` VALUES (2138);
INSERT INTO `VLANValidID` VALUES (2139);
INSERT INTO `VLANValidID` VALUES (2140);
INSERT INTO `VLANValidID` VALUES (2141);
INSERT INTO `VLANValidID` VALUES (2142);
INSERT INTO `VLANValidID` VALUES (2143);
INSERT INTO `VLANValidID` VALUES (2144);
INSERT INTO `VLANValidID` VALUES (2145);
INSERT INTO `VLANValidID` VALUES (2146);
INSERT INTO `VLANValidID` VALUES (2147);
INSERT INTO `VLANValidID` VALUES (2148);
INSERT INTO `VLANValidID` VALUES (2149);
INSERT INTO `VLANValidID` VALUES (2150);
INSERT INTO `VLANValidID` VALUES (2151);
INSERT INTO `VLANValidID` VALUES (2152);
INSERT INTO `VLANValidID` VALUES (2153);
INSERT INTO `VLANValidID` VALUES (2154);
INSERT INTO `VLANValidID` VALUES (2155);
INSERT INTO `VLANValidID` VALUES (2156);
INSERT INTO `VLANValidID` VALUES (2157);
INSERT INTO `VLANValidID` VALUES (2158);
INSERT INTO `VLANValidID` VALUES (2159);
INSERT INTO `VLANValidID` VALUES (2160);
INSERT INTO `VLANValidID` VALUES (2161);
INSERT INTO `VLANValidID` VALUES (2162);
INSERT INTO `VLANValidID` VALUES (2163);
INSERT INTO `VLANValidID` VALUES (2164);
INSERT INTO `VLANValidID` VALUES (2165);
INSERT INTO `VLANValidID` VALUES (2166);
INSERT INTO `VLANValidID` VALUES (2167);
INSERT INTO `VLANValidID` VALUES (2168);
INSERT INTO `VLANValidID` VALUES (2169);
INSERT INTO `VLANValidID` VALUES (2170);
INSERT INTO `VLANValidID` VALUES (2171);
INSERT INTO `VLANValidID` VALUES (2172);
INSERT INTO `VLANValidID` VALUES (2173);
INSERT INTO `VLANValidID` VALUES (2174);
INSERT INTO `VLANValidID` VALUES (2175);
INSERT INTO `VLANValidID` VALUES (2176);
INSERT INTO `VLANValidID` VALUES (2177);
INSERT INTO `VLANValidID` VALUES (2178);
INSERT INTO `VLANValidID` VALUES (2179);
INSERT INTO `VLANValidID` VALUES (2180);
INSERT INTO `VLANValidID` VALUES (2181);
INSERT INTO `VLANValidID` VALUES (2182);
INSERT INTO `VLANValidID` VALUES (2183);
INSERT INTO `VLANValidID` VALUES (2184);
INSERT INTO `VLANValidID` VALUES (2185);
INSERT INTO `VLANValidID` VALUES (2186);
INSERT INTO `VLANValidID` VALUES (2187);
INSERT INTO `VLANValidID` VALUES (2188);
INSERT INTO `VLANValidID` VALUES (2189);
INSERT INTO `VLANValidID` VALUES (2190);
INSERT INTO `VLANValidID` VALUES (2191);
INSERT INTO `VLANValidID` VALUES (2192);
INSERT INTO `VLANValidID` VALUES (2193);
INSERT INTO `VLANValidID` VALUES (2194);
INSERT INTO `VLANValidID` VALUES (2195);
INSERT INTO `VLANValidID` VALUES (2196);
INSERT INTO `VLANValidID` VALUES (2197);
INSERT INTO `VLANValidID` VALUES (2198);
INSERT INTO `VLANValidID` VALUES (2199);
INSERT INTO `VLANValidID` VALUES (2200);
INSERT INTO `VLANValidID` VALUES (2201);
INSERT INTO `VLANValidID` VALUES (2202);
INSERT INTO `VLANValidID` VALUES (2203);
INSERT INTO `VLANValidID` VALUES (2204);
INSERT INTO `VLANValidID` VALUES (2205);
INSERT INTO `VLANValidID` VALUES (2206);
INSERT INTO `VLANValidID` VALUES (2207);
INSERT INTO `VLANValidID` VALUES (2208);
INSERT INTO `VLANValidID` VALUES (2209);
INSERT INTO `VLANValidID` VALUES (2210);
INSERT INTO `VLANValidID` VALUES (2211);
INSERT INTO `VLANValidID` VALUES (2212);
INSERT INTO `VLANValidID` VALUES (2213);
INSERT INTO `VLANValidID` VALUES (2214);
INSERT INTO `VLANValidID` VALUES (2215);
INSERT INTO `VLANValidID` VALUES (2216);
INSERT INTO `VLANValidID` VALUES (2217);
INSERT INTO `VLANValidID` VALUES (2218);
INSERT INTO `VLANValidID` VALUES (2219);
INSERT INTO `VLANValidID` VALUES (2220);
INSERT INTO `VLANValidID` VALUES (2221);
INSERT INTO `VLANValidID` VALUES (2222);
INSERT INTO `VLANValidID` VALUES (2223);
INSERT INTO `VLANValidID` VALUES (2224);
INSERT INTO `VLANValidID` VALUES (2225);
INSERT INTO `VLANValidID` VALUES (2226);
INSERT INTO `VLANValidID` VALUES (2227);
INSERT INTO `VLANValidID` VALUES (2228);
INSERT INTO `VLANValidID` VALUES (2229);
INSERT INTO `VLANValidID` VALUES (2230);
INSERT INTO `VLANValidID` VALUES (2231);
INSERT INTO `VLANValidID` VALUES (2232);
INSERT INTO `VLANValidID` VALUES (2233);
INSERT INTO `VLANValidID` VALUES (2234);
INSERT INTO `VLANValidID` VALUES (2235);
INSERT INTO `VLANValidID` VALUES (2236);
INSERT INTO `VLANValidID` VALUES (2237);
INSERT INTO `VLANValidID` VALUES (2238);
INSERT INTO `VLANValidID` VALUES (2239);
INSERT INTO `VLANValidID` VALUES (2240);
INSERT INTO `VLANValidID` VALUES (2241);
INSERT INTO `VLANValidID` VALUES (2242);
INSERT INTO `VLANValidID` VALUES (2243);
INSERT INTO `VLANValidID` VALUES (2244);
INSERT INTO `VLANValidID` VALUES (2245);
INSERT INTO `VLANValidID` VALUES (2246);
INSERT INTO `VLANValidID` VALUES (2247);
INSERT INTO `VLANValidID` VALUES (2248);
INSERT INTO `VLANValidID` VALUES (2249);
INSERT INTO `VLANValidID` VALUES (2250);
INSERT INTO `VLANValidID` VALUES (2251);
INSERT INTO `VLANValidID` VALUES (2252);
INSERT INTO `VLANValidID` VALUES (2253);
INSERT INTO `VLANValidID` VALUES (2254);
INSERT INTO `VLANValidID` VALUES (2255);
INSERT INTO `VLANValidID` VALUES (2256);
INSERT INTO `VLANValidID` VALUES (2257);
INSERT INTO `VLANValidID` VALUES (2258);
INSERT INTO `VLANValidID` VALUES (2259);
INSERT INTO `VLANValidID` VALUES (2260);
INSERT INTO `VLANValidID` VALUES (2261);
INSERT INTO `VLANValidID` VALUES (2262);
INSERT INTO `VLANValidID` VALUES (2263);
INSERT INTO `VLANValidID` VALUES (2264);
INSERT INTO `VLANValidID` VALUES (2265);
INSERT INTO `VLANValidID` VALUES (2266);
INSERT INTO `VLANValidID` VALUES (2267);
INSERT INTO `VLANValidID` VALUES (2268);
INSERT INTO `VLANValidID` VALUES (2269);
INSERT INTO `VLANValidID` VALUES (2270);
INSERT INTO `VLANValidID` VALUES (2271);
INSERT INTO `VLANValidID` VALUES (2272);
INSERT INTO `VLANValidID` VALUES (2273);
INSERT INTO `VLANValidID` VALUES (2274);
INSERT INTO `VLANValidID` VALUES (2275);
INSERT INTO `VLANValidID` VALUES (2276);
INSERT INTO `VLANValidID` VALUES (2277);
INSERT INTO `VLANValidID` VALUES (2278);
INSERT INTO `VLANValidID` VALUES (2279);
INSERT INTO `VLANValidID` VALUES (2280);
INSERT INTO `VLANValidID` VALUES (2281);
INSERT INTO `VLANValidID` VALUES (2282);
INSERT INTO `VLANValidID` VALUES (2283);
INSERT INTO `VLANValidID` VALUES (2284);
INSERT INTO `VLANValidID` VALUES (2285);
INSERT INTO `VLANValidID` VALUES (2286);
INSERT INTO `VLANValidID` VALUES (2287);
INSERT INTO `VLANValidID` VALUES (2288);
INSERT INTO `VLANValidID` VALUES (2289);
INSERT INTO `VLANValidID` VALUES (2290);
INSERT INTO `VLANValidID` VALUES (2291);
INSERT INTO `VLANValidID` VALUES (2292);
INSERT INTO `VLANValidID` VALUES (2293);
INSERT INTO `VLANValidID` VALUES (2294);
INSERT INTO `VLANValidID` VALUES (2295);
INSERT INTO `VLANValidID` VALUES (2296);
INSERT INTO `VLANValidID` VALUES (2297);
INSERT INTO `VLANValidID` VALUES (2298);
INSERT INTO `VLANValidID` VALUES (2299);
INSERT INTO `VLANValidID` VALUES (2300);
INSERT INTO `VLANValidID` VALUES (2301);
INSERT INTO `VLANValidID` VALUES (2302);
INSERT INTO `VLANValidID` VALUES (2303);
INSERT INTO `VLANValidID` VALUES (2304);
INSERT INTO `VLANValidID` VALUES (2305);
INSERT INTO `VLANValidID` VALUES (2306);
INSERT INTO `VLANValidID` VALUES (2307);
INSERT INTO `VLANValidID` VALUES (2308);
INSERT INTO `VLANValidID` VALUES (2309);
INSERT INTO `VLANValidID` VALUES (2310);
INSERT INTO `VLANValidID` VALUES (2311);
INSERT INTO `VLANValidID` VALUES (2312);
INSERT INTO `VLANValidID` VALUES (2313);
INSERT INTO `VLANValidID` VALUES (2314);
INSERT INTO `VLANValidID` VALUES (2315);
INSERT INTO `VLANValidID` VALUES (2316);
INSERT INTO `VLANValidID` VALUES (2317);
INSERT INTO `VLANValidID` VALUES (2318);
INSERT INTO `VLANValidID` VALUES (2319);
INSERT INTO `VLANValidID` VALUES (2320);
INSERT INTO `VLANValidID` VALUES (2321);
INSERT INTO `VLANValidID` VALUES (2322);
INSERT INTO `VLANValidID` VALUES (2323);
INSERT INTO `VLANValidID` VALUES (2324);
INSERT INTO `VLANValidID` VALUES (2325);
INSERT INTO `VLANValidID` VALUES (2326);
INSERT INTO `VLANValidID` VALUES (2327);
INSERT INTO `VLANValidID` VALUES (2328);
INSERT INTO `VLANValidID` VALUES (2329);
INSERT INTO `VLANValidID` VALUES (2330);
INSERT INTO `VLANValidID` VALUES (2331);
INSERT INTO `VLANValidID` VALUES (2332);
INSERT INTO `VLANValidID` VALUES (2333);
INSERT INTO `VLANValidID` VALUES (2334);
INSERT INTO `VLANValidID` VALUES (2335);
INSERT INTO `VLANValidID` VALUES (2336);
INSERT INTO `VLANValidID` VALUES (2337);
INSERT INTO `VLANValidID` VALUES (2338);
INSERT INTO `VLANValidID` VALUES (2339);
INSERT INTO `VLANValidID` VALUES (2340);
INSERT INTO `VLANValidID` VALUES (2341);
INSERT INTO `VLANValidID` VALUES (2342);
INSERT INTO `VLANValidID` VALUES (2343);
INSERT INTO `VLANValidID` VALUES (2344);
INSERT INTO `VLANValidID` VALUES (2345);
INSERT INTO `VLANValidID` VALUES (2346);
INSERT INTO `VLANValidID` VALUES (2347);
INSERT INTO `VLANValidID` VALUES (2348);
INSERT INTO `VLANValidID` VALUES (2349);
INSERT INTO `VLANValidID` VALUES (2350);
INSERT INTO `VLANValidID` VALUES (2351);
INSERT INTO `VLANValidID` VALUES (2352);
INSERT INTO `VLANValidID` VALUES (2353);
INSERT INTO `VLANValidID` VALUES (2354);
INSERT INTO `VLANValidID` VALUES (2355);
INSERT INTO `VLANValidID` VALUES (2356);
INSERT INTO `VLANValidID` VALUES (2357);
INSERT INTO `VLANValidID` VALUES (2358);
INSERT INTO `VLANValidID` VALUES (2359);
INSERT INTO `VLANValidID` VALUES (2360);
INSERT INTO `VLANValidID` VALUES (2361);
INSERT INTO `VLANValidID` VALUES (2362);
INSERT INTO `VLANValidID` VALUES (2363);
INSERT INTO `VLANValidID` VALUES (2364);
INSERT INTO `VLANValidID` VALUES (2365);
INSERT INTO `VLANValidID` VALUES (2366);
INSERT INTO `VLANValidID` VALUES (2367);
INSERT INTO `VLANValidID` VALUES (2368);
INSERT INTO `VLANValidID` VALUES (2369);
INSERT INTO `VLANValidID` VALUES (2370);
INSERT INTO `VLANValidID` VALUES (2371);
INSERT INTO `VLANValidID` VALUES (2372);
INSERT INTO `VLANValidID` VALUES (2373);
INSERT INTO `VLANValidID` VALUES (2374);
INSERT INTO `VLANValidID` VALUES (2375);
INSERT INTO `VLANValidID` VALUES (2376);
INSERT INTO `VLANValidID` VALUES (2377);
INSERT INTO `VLANValidID` VALUES (2378);
INSERT INTO `VLANValidID` VALUES (2379);
INSERT INTO `VLANValidID` VALUES (2380);
INSERT INTO `VLANValidID` VALUES (2381);
INSERT INTO `VLANValidID` VALUES (2382);
INSERT INTO `VLANValidID` VALUES (2383);
INSERT INTO `VLANValidID` VALUES (2384);
INSERT INTO `VLANValidID` VALUES (2385);
INSERT INTO `VLANValidID` VALUES (2386);
INSERT INTO `VLANValidID` VALUES (2387);
INSERT INTO `VLANValidID` VALUES (2388);
INSERT INTO `VLANValidID` VALUES (2389);
INSERT INTO `VLANValidID` VALUES (2390);
INSERT INTO `VLANValidID` VALUES (2391);
INSERT INTO `VLANValidID` VALUES (2392);
INSERT INTO `VLANValidID` VALUES (2393);
INSERT INTO `VLANValidID` VALUES (2394);
INSERT INTO `VLANValidID` VALUES (2395);
INSERT INTO `VLANValidID` VALUES (2396);
INSERT INTO `VLANValidID` VALUES (2397);
INSERT INTO `VLANValidID` VALUES (2398);
INSERT INTO `VLANValidID` VALUES (2399);
INSERT INTO `VLANValidID` VALUES (2400);
INSERT INTO `VLANValidID` VALUES (2401);
INSERT INTO `VLANValidID` VALUES (2402);
INSERT INTO `VLANValidID` VALUES (2403);
INSERT INTO `VLANValidID` VALUES (2404);
INSERT INTO `VLANValidID` VALUES (2405);
INSERT INTO `VLANValidID` VALUES (2406);
INSERT INTO `VLANValidID` VALUES (2407);
INSERT INTO `VLANValidID` VALUES (2408);
INSERT INTO `VLANValidID` VALUES (2409);
INSERT INTO `VLANValidID` VALUES (2410);
INSERT INTO `VLANValidID` VALUES (2411);
INSERT INTO `VLANValidID` VALUES (2412);
INSERT INTO `VLANValidID` VALUES (2413);
INSERT INTO `VLANValidID` VALUES (2414);
INSERT INTO `VLANValidID` VALUES (2415);
INSERT INTO `VLANValidID` VALUES (2416);
INSERT INTO `VLANValidID` VALUES (2417);
INSERT INTO `VLANValidID` VALUES (2418);
INSERT INTO `VLANValidID` VALUES (2419);
INSERT INTO `VLANValidID` VALUES (2420);
INSERT INTO `VLANValidID` VALUES (2421);
INSERT INTO `VLANValidID` VALUES (2422);
INSERT INTO `VLANValidID` VALUES (2423);
INSERT INTO `VLANValidID` VALUES (2424);
INSERT INTO `VLANValidID` VALUES (2425);
INSERT INTO `VLANValidID` VALUES (2426);
INSERT INTO `VLANValidID` VALUES (2427);
INSERT INTO `VLANValidID` VALUES (2428);
INSERT INTO `VLANValidID` VALUES (2429);
INSERT INTO `VLANValidID` VALUES (2430);
INSERT INTO `VLANValidID` VALUES (2431);
INSERT INTO `VLANValidID` VALUES (2432);
INSERT INTO `VLANValidID` VALUES (2433);
INSERT INTO `VLANValidID` VALUES (2434);
INSERT INTO `VLANValidID` VALUES (2435);
INSERT INTO `VLANValidID` VALUES (2436);
INSERT INTO `VLANValidID` VALUES (2437);
INSERT INTO `VLANValidID` VALUES (2438);
INSERT INTO `VLANValidID` VALUES (2439);
INSERT INTO `VLANValidID` VALUES (2440);
INSERT INTO `VLANValidID` VALUES (2441);
INSERT INTO `VLANValidID` VALUES (2442);
INSERT INTO `VLANValidID` VALUES (2443);
INSERT INTO `VLANValidID` VALUES (2444);
INSERT INTO `VLANValidID` VALUES (2445);
INSERT INTO `VLANValidID` VALUES (2446);
INSERT INTO `VLANValidID` VALUES (2447);
INSERT INTO `VLANValidID` VALUES (2448);
INSERT INTO `VLANValidID` VALUES (2449);
INSERT INTO `VLANValidID` VALUES (2450);
INSERT INTO `VLANValidID` VALUES (2451);
INSERT INTO `VLANValidID` VALUES (2452);
INSERT INTO `VLANValidID` VALUES (2453);
INSERT INTO `VLANValidID` VALUES (2454);
INSERT INTO `VLANValidID` VALUES (2455);
INSERT INTO `VLANValidID` VALUES (2456);
INSERT INTO `VLANValidID` VALUES (2457);
INSERT INTO `VLANValidID` VALUES (2458);
INSERT INTO `VLANValidID` VALUES (2459);
INSERT INTO `VLANValidID` VALUES (2460);
INSERT INTO `VLANValidID` VALUES (2461);
INSERT INTO `VLANValidID` VALUES (2462);
INSERT INTO `VLANValidID` VALUES (2463);
INSERT INTO `VLANValidID` VALUES (2464);
INSERT INTO `VLANValidID` VALUES (2465);
INSERT INTO `VLANValidID` VALUES (2466);
INSERT INTO `VLANValidID` VALUES (2467);
INSERT INTO `VLANValidID` VALUES (2468);
INSERT INTO `VLANValidID` VALUES (2469);
INSERT INTO `VLANValidID` VALUES (2470);
INSERT INTO `VLANValidID` VALUES (2471);
INSERT INTO `VLANValidID` VALUES (2472);
INSERT INTO `VLANValidID` VALUES (2473);
INSERT INTO `VLANValidID` VALUES (2474);
INSERT INTO `VLANValidID` VALUES (2475);
INSERT INTO `VLANValidID` VALUES (2476);
INSERT INTO `VLANValidID` VALUES (2477);
INSERT INTO `VLANValidID` VALUES (2478);
INSERT INTO `VLANValidID` VALUES (2479);
INSERT INTO `VLANValidID` VALUES (2480);
INSERT INTO `VLANValidID` VALUES (2481);
INSERT INTO `VLANValidID` VALUES (2482);
INSERT INTO `VLANValidID` VALUES (2483);
INSERT INTO `VLANValidID` VALUES (2484);
INSERT INTO `VLANValidID` VALUES (2485);
INSERT INTO `VLANValidID` VALUES (2486);
INSERT INTO `VLANValidID` VALUES (2487);
INSERT INTO `VLANValidID` VALUES (2488);
INSERT INTO `VLANValidID` VALUES (2489);
INSERT INTO `VLANValidID` VALUES (2490);
INSERT INTO `VLANValidID` VALUES (2491);
INSERT INTO `VLANValidID` VALUES (2492);
INSERT INTO `VLANValidID` VALUES (2493);
INSERT INTO `VLANValidID` VALUES (2494);
INSERT INTO `VLANValidID` VALUES (2495);
INSERT INTO `VLANValidID` VALUES (2496);
INSERT INTO `VLANValidID` VALUES (2497);
INSERT INTO `VLANValidID` VALUES (2498);
INSERT INTO `VLANValidID` VALUES (2499);
INSERT INTO `VLANValidID` VALUES (2500);
INSERT INTO `VLANValidID` VALUES (2501);
INSERT INTO `VLANValidID` VALUES (2502);
INSERT INTO `VLANValidID` VALUES (2503);
INSERT INTO `VLANValidID` VALUES (2504);
INSERT INTO `VLANValidID` VALUES (2505);
INSERT INTO `VLANValidID` VALUES (2506);
INSERT INTO `VLANValidID` VALUES (2507);
INSERT INTO `VLANValidID` VALUES (2508);
INSERT INTO `VLANValidID` VALUES (2509);
INSERT INTO `VLANValidID` VALUES (2510);
INSERT INTO `VLANValidID` VALUES (2511);
INSERT INTO `VLANValidID` VALUES (2512);
INSERT INTO `VLANValidID` VALUES (2513);
INSERT INTO `VLANValidID` VALUES (2514);
INSERT INTO `VLANValidID` VALUES (2515);
INSERT INTO `VLANValidID` VALUES (2516);
INSERT INTO `VLANValidID` VALUES (2517);
INSERT INTO `VLANValidID` VALUES (2518);
INSERT INTO `VLANValidID` VALUES (2519);
INSERT INTO `VLANValidID` VALUES (2520);
INSERT INTO `VLANValidID` VALUES (2521);
INSERT INTO `VLANValidID` VALUES (2522);
INSERT INTO `VLANValidID` VALUES (2523);
INSERT INTO `VLANValidID` VALUES (2524);
INSERT INTO `VLANValidID` VALUES (2525);
INSERT INTO `VLANValidID` VALUES (2526);
INSERT INTO `VLANValidID` VALUES (2527);
INSERT INTO `VLANValidID` VALUES (2528);
INSERT INTO `VLANValidID` VALUES (2529);
INSERT INTO `VLANValidID` VALUES (2530);
INSERT INTO `VLANValidID` VALUES (2531);
INSERT INTO `VLANValidID` VALUES (2532);
INSERT INTO `VLANValidID` VALUES (2533);
INSERT INTO `VLANValidID` VALUES (2534);
INSERT INTO `VLANValidID` VALUES (2535);
INSERT INTO `VLANValidID` VALUES (2536);
INSERT INTO `VLANValidID` VALUES (2537);
INSERT INTO `VLANValidID` VALUES (2538);
INSERT INTO `VLANValidID` VALUES (2539);
INSERT INTO `VLANValidID` VALUES (2540);
INSERT INTO `VLANValidID` VALUES (2541);
INSERT INTO `VLANValidID` VALUES (2542);
INSERT INTO `VLANValidID` VALUES (2543);
INSERT INTO `VLANValidID` VALUES (2544);
INSERT INTO `VLANValidID` VALUES (2545);
INSERT INTO `VLANValidID` VALUES (2546);
INSERT INTO `VLANValidID` VALUES (2547);
INSERT INTO `VLANValidID` VALUES (2548);
INSERT INTO `VLANValidID` VALUES (2549);
INSERT INTO `VLANValidID` VALUES (2550);
INSERT INTO `VLANValidID` VALUES (2551);
INSERT INTO `VLANValidID` VALUES (2552);
INSERT INTO `VLANValidID` VALUES (2553);
INSERT INTO `VLANValidID` VALUES (2554);
INSERT INTO `VLANValidID` VALUES (2555);
INSERT INTO `VLANValidID` VALUES (2556);
INSERT INTO `VLANValidID` VALUES (2557);
INSERT INTO `VLANValidID` VALUES (2558);
INSERT INTO `VLANValidID` VALUES (2559);
INSERT INTO `VLANValidID` VALUES (2560);
INSERT INTO `VLANValidID` VALUES (2561);
INSERT INTO `VLANValidID` VALUES (2562);
INSERT INTO `VLANValidID` VALUES (2563);
INSERT INTO `VLANValidID` VALUES (2564);
INSERT INTO `VLANValidID` VALUES (2565);
INSERT INTO `VLANValidID` VALUES (2566);
INSERT INTO `VLANValidID` VALUES (2567);
INSERT INTO `VLANValidID` VALUES (2568);
INSERT INTO `VLANValidID` VALUES (2569);
INSERT INTO `VLANValidID` VALUES (2570);
INSERT INTO `VLANValidID` VALUES (2571);
INSERT INTO `VLANValidID` VALUES (2572);
INSERT INTO `VLANValidID` VALUES (2573);
INSERT INTO `VLANValidID` VALUES (2574);
INSERT INTO `VLANValidID` VALUES (2575);
INSERT INTO `VLANValidID` VALUES (2576);
INSERT INTO `VLANValidID` VALUES (2577);
INSERT INTO `VLANValidID` VALUES (2578);
INSERT INTO `VLANValidID` VALUES (2579);
INSERT INTO `VLANValidID` VALUES (2580);
INSERT INTO `VLANValidID` VALUES (2581);
INSERT INTO `VLANValidID` VALUES (2582);
INSERT INTO `VLANValidID` VALUES (2583);
INSERT INTO `VLANValidID` VALUES (2584);
INSERT INTO `VLANValidID` VALUES (2585);
INSERT INTO `VLANValidID` VALUES (2586);
INSERT INTO `VLANValidID` VALUES (2587);
INSERT INTO `VLANValidID` VALUES (2588);
INSERT INTO `VLANValidID` VALUES (2589);
INSERT INTO `VLANValidID` VALUES (2590);
INSERT INTO `VLANValidID` VALUES (2591);
INSERT INTO `VLANValidID` VALUES (2592);
INSERT INTO `VLANValidID` VALUES (2593);
INSERT INTO `VLANValidID` VALUES (2594);
INSERT INTO `VLANValidID` VALUES (2595);
INSERT INTO `VLANValidID` VALUES (2596);
INSERT INTO `VLANValidID` VALUES (2597);
INSERT INTO `VLANValidID` VALUES (2598);
INSERT INTO `VLANValidID` VALUES (2599);
INSERT INTO `VLANValidID` VALUES (2600);
INSERT INTO `VLANValidID` VALUES (2601);
INSERT INTO `VLANValidID` VALUES (2602);
INSERT INTO `VLANValidID` VALUES (2603);
INSERT INTO `VLANValidID` VALUES (2604);
INSERT INTO `VLANValidID` VALUES (2605);
INSERT INTO `VLANValidID` VALUES (2606);
INSERT INTO `VLANValidID` VALUES (2607);
INSERT INTO `VLANValidID` VALUES (2608);
INSERT INTO `VLANValidID` VALUES (2609);
INSERT INTO `VLANValidID` VALUES (2610);
INSERT INTO `VLANValidID` VALUES (2611);
INSERT INTO `VLANValidID` VALUES (2612);
INSERT INTO `VLANValidID` VALUES (2613);
INSERT INTO `VLANValidID` VALUES (2614);
INSERT INTO `VLANValidID` VALUES (2615);
INSERT INTO `VLANValidID` VALUES (2616);
INSERT INTO `VLANValidID` VALUES (2617);
INSERT INTO `VLANValidID` VALUES (2618);
INSERT INTO `VLANValidID` VALUES (2619);
INSERT INTO `VLANValidID` VALUES (2620);
INSERT INTO `VLANValidID` VALUES (2621);
INSERT INTO `VLANValidID` VALUES (2622);
INSERT INTO `VLANValidID` VALUES (2623);
INSERT INTO `VLANValidID` VALUES (2624);
INSERT INTO `VLANValidID` VALUES (2625);
INSERT INTO `VLANValidID` VALUES (2626);
INSERT INTO `VLANValidID` VALUES (2627);
INSERT INTO `VLANValidID` VALUES (2628);
INSERT INTO `VLANValidID` VALUES (2629);
INSERT INTO `VLANValidID` VALUES (2630);
INSERT INTO `VLANValidID` VALUES (2631);
INSERT INTO `VLANValidID` VALUES (2632);
INSERT INTO `VLANValidID` VALUES (2633);
INSERT INTO `VLANValidID` VALUES (2634);
INSERT INTO `VLANValidID` VALUES (2635);
INSERT INTO `VLANValidID` VALUES (2636);
INSERT INTO `VLANValidID` VALUES (2637);
INSERT INTO `VLANValidID` VALUES (2638);
INSERT INTO `VLANValidID` VALUES (2639);
INSERT INTO `VLANValidID` VALUES (2640);
INSERT INTO `VLANValidID` VALUES (2641);
INSERT INTO `VLANValidID` VALUES (2642);
INSERT INTO `VLANValidID` VALUES (2643);
INSERT INTO `VLANValidID` VALUES (2644);
INSERT INTO `VLANValidID` VALUES (2645);
INSERT INTO `VLANValidID` VALUES (2646);
INSERT INTO `VLANValidID` VALUES (2647);
INSERT INTO `VLANValidID` VALUES (2648);
INSERT INTO `VLANValidID` VALUES (2649);
INSERT INTO `VLANValidID` VALUES (2650);
INSERT INTO `VLANValidID` VALUES (2651);
INSERT INTO `VLANValidID` VALUES (2652);
INSERT INTO `VLANValidID` VALUES (2653);
INSERT INTO `VLANValidID` VALUES (2654);
INSERT INTO `VLANValidID` VALUES (2655);
INSERT INTO `VLANValidID` VALUES (2656);
INSERT INTO `VLANValidID` VALUES (2657);
INSERT INTO `VLANValidID` VALUES (2658);
INSERT INTO `VLANValidID` VALUES (2659);
INSERT INTO `VLANValidID` VALUES (2660);
INSERT INTO `VLANValidID` VALUES (2661);
INSERT INTO `VLANValidID` VALUES (2662);
INSERT INTO `VLANValidID` VALUES (2663);
INSERT INTO `VLANValidID` VALUES (2664);
INSERT INTO `VLANValidID` VALUES (2665);
INSERT INTO `VLANValidID` VALUES (2666);
INSERT INTO `VLANValidID` VALUES (2667);
INSERT INTO `VLANValidID` VALUES (2668);
INSERT INTO `VLANValidID` VALUES (2669);
INSERT INTO `VLANValidID` VALUES (2670);
INSERT INTO `VLANValidID` VALUES (2671);
INSERT INTO `VLANValidID` VALUES (2672);
INSERT INTO `VLANValidID` VALUES (2673);
INSERT INTO `VLANValidID` VALUES (2674);
INSERT INTO `VLANValidID` VALUES (2675);
INSERT INTO `VLANValidID` VALUES (2676);
INSERT INTO `VLANValidID` VALUES (2677);
INSERT INTO `VLANValidID` VALUES (2678);
INSERT INTO `VLANValidID` VALUES (2679);
INSERT INTO `VLANValidID` VALUES (2680);
INSERT INTO `VLANValidID` VALUES (2681);
INSERT INTO `VLANValidID` VALUES (2682);
INSERT INTO `VLANValidID` VALUES (2683);
INSERT INTO `VLANValidID` VALUES (2684);
INSERT INTO `VLANValidID` VALUES (2685);
INSERT INTO `VLANValidID` VALUES (2686);
INSERT INTO `VLANValidID` VALUES (2687);
INSERT INTO `VLANValidID` VALUES (2688);
INSERT INTO `VLANValidID` VALUES (2689);
INSERT INTO `VLANValidID` VALUES (2690);
INSERT INTO `VLANValidID` VALUES (2691);
INSERT INTO `VLANValidID` VALUES (2692);
INSERT INTO `VLANValidID` VALUES (2693);
INSERT INTO `VLANValidID` VALUES (2694);
INSERT INTO `VLANValidID` VALUES (2695);
INSERT INTO `VLANValidID` VALUES (2696);
INSERT INTO `VLANValidID` VALUES (2697);
INSERT INTO `VLANValidID` VALUES (2698);
INSERT INTO `VLANValidID` VALUES (2699);
INSERT INTO `VLANValidID` VALUES (2700);
INSERT INTO `VLANValidID` VALUES (2701);
INSERT INTO `VLANValidID` VALUES (2702);
INSERT INTO `VLANValidID` VALUES (2703);
INSERT INTO `VLANValidID` VALUES (2704);
INSERT INTO `VLANValidID` VALUES (2705);
INSERT INTO `VLANValidID` VALUES (2706);
INSERT INTO `VLANValidID` VALUES (2707);
INSERT INTO `VLANValidID` VALUES (2708);
INSERT INTO `VLANValidID` VALUES (2709);
INSERT INTO `VLANValidID` VALUES (2710);
INSERT INTO `VLANValidID` VALUES (2711);
INSERT INTO `VLANValidID` VALUES (2712);
INSERT INTO `VLANValidID` VALUES (2713);
INSERT INTO `VLANValidID` VALUES (2714);
INSERT INTO `VLANValidID` VALUES (2715);
INSERT INTO `VLANValidID` VALUES (2716);
INSERT INTO `VLANValidID` VALUES (2717);
INSERT INTO `VLANValidID` VALUES (2718);
INSERT INTO `VLANValidID` VALUES (2719);
INSERT INTO `VLANValidID` VALUES (2720);
INSERT INTO `VLANValidID` VALUES (2721);
INSERT INTO `VLANValidID` VALUES (2722);
INSERT INTO `VLANValidID` VALUES (2723);
INSERT INTO `VLANValidID` VALUES (2724);
INSERT INTO `VLANValidID` VALUES (2725);
INSERT INTO `VLANValidID` VALUES (2726);
INSERT INTO `VLANValidID` VALUES (2727);
INSERT INTO `VLANValidID` VALUES (2728);
INSERT INTO `VLANValidID` VALUES (2729);
INSERT INTO `VLANValidID` VALUES (2730);
INSERT INTO `VLANValidID` VALUES (2731);
INSERT INTO `VLANValidID` VALUES (2732);
INSERT INTO `VLANValidID` VALUES (2733);
INSERT INTO `VLANValidID` VALUES (2734);
INSERT INTO `VLANValidID` VALUES (2735);
INSERT INTO `VLANValidID` VALUES (2736);
INSERT INTO `VLANValidID` VALUES (2737);
INSERT INTO `VLANValidID` VALUES (2738);
INSERT INTO `VLANValidID` VALUES (2739);
INSERT INTO `VLANValidID` VALUES (2740);
INSERT INTO `VLANValidID` VALUES (2741);
INSERT INTO `VLANValidID` VALUES (2742);
INSERT INTO `VLANValidID` VALUES (2743);
INSERT INTO `VLANValidID` VALUES (2744);
INSERT INTO `VLANValidID` VALUES (2745);
INSERT INTO `VLANValidID` VALUES (2746);
INSERT INTO `VLANValidID` VALUES (2747);
INSERT INTO `VLANValidID` VALUES (2748);
INSERT INTO `VLANValidID` VALUES (2749);
INSERT INTO `VLANValidID` VALUES (2750);
INSERT INTO `VLANValidID` VALUES (2751);
INSERT INTO `VLANValidID` VALUES (2752);
INSERT INTO `VLANValidID` VALUES (2753);
INSERT INTO `VLANValidID` VALUES (2754);
INSERT INTO `VLANValidID` VALUES (2755);
INSERT INTO `VLANValidID` VALUES (2756);
INSERT INTO `VLANValidID` VALUES (2757);
INSERT INTO `VLANValidID` VALUES (2758);
INSERT INTO `VLANValidID` VALUES (2759);
INSERT INTO `VLANValidID` VALUES (2760);
INSERT INTO `VLANValidID` VALUES (2761);
INSERT INTO `VLANValidID` VALUES (2762);
INSERT INTO `VLANValidID` VALUES (2763);
INSERT INTO `VLANValidID` VALUES (2764);
INSERT INTO `VLANValidID` VALUES (2765);
INSERT INTO `VLANValidID` VALUES (2766);
INSERT INTO `VLANValidID` VALUES (2767);
INSERT INTO `VLANValidID` VALUES (2768);
INSERT INTO `VLANValidID` VALUES (2769);
INSERT INTO `VLANValidID` VALUES (2770);
INSERT INTO `VLANValidID` VALUES (2771);
INSERT INTO `VLANValidID` VALUES (2772);
INSERT INTO `VLANValidID` VALUES (2773);
INSERT INTO `VLANValidID` VALUES (2774);
INSERT INTO `VLANValidID` VALUES (2775);
INSERT INTO `VLANValidID` VALUES (2776);
INSERT INTO `VLANValidID` VALUES (2777);
INSERT INTO `VLANValidID` VALUES (2778);
INSERT INTO `VLANValidID` VALUES (2779);
INSERT INTO `VLANValidID` VALUES (2780);
INSERT INTO `VLANValidID` VALUES (2781);
INSERT INTO `VLANValidID` VALUES (2782);
INSERT INTO `VLANValidID` VALUES (2783);
INSERT INTO `VLANValidID` VALUES (2784);
INSERT INTO `VLANValidID` VALUES (2785);
INSERT INTO `VLANValidID` VALUES (2786);
INSERT INTO `VLANValidID` VALUES (2787);
INSERT INTO `VLANValidID` VALUES (2788);
INSERT INTO `VLANValidID` VALUES (2789);
INSERT INTO `VLANValidID` VALUES (2790);
INSERT INTO `VLANValidID` VALUES (2791);
INSERT INTO `VLANValidID` VALUES (2792);
INSERT INTO `VLANValidID` VALUES (2793);
INSERT INTO `VLANValidID` VALUES (2794);
INSERT INTO `VLANValidID` VALUES (2795);
INSERT INTO `VLANValidID` VALUES (2796);
INSERT INTO `VLANValidID` VALUES (2797);
INSERT INTO `VLANValidID` VALUES (2798);
INSERT INTO `VLANValidID` VALUES (2799);
INSERT INTO `VLANValidID` VALUES (2800);
INSERT INTO `VLANValidID` VALUES (2801);
INSERT INTO `VLANValidID` VALUES (2802);
INSERT INTO `VLANValidID` VALUES (2803);
INSERT INTO `VLANValidID` VALUES (2804);
INSERT INTO `VLANValidID` VALUES (2805);
INSERT INTO `VLANValidID` VALUES (2806);
INSERT INTO `VLANValidID` VALUES (2807);
INSERT INTO `VLANValidID` VALUES (2808);
INSERT INTO `VLANValidID` VALUES (2809);
INSERT INTO `VLANValidID` VALUES (2810);
INSERT INTO `VLANValidID` VALUES (2811);
INSERT INTO `VLANValidID` VALUES (2812);
INSERT INTO `VLANValidID` VALUES (2813);
INSERT INTO `VLANValidID` VALUES (2814);
INSERT INTO `VLANValidID` VALUES (2815);
INSERT INTO `VLANValidID` VALUES (2816);
INSERT INTO `VLANValidID` VALUES (2817);
INSERT INTO `VLANValidID` VALUES (2818);
INSERT INTO `VLANValidID` VALUES (2819);
INSERT INTO `VLANValidID` VALUES (2820);
INSERT INTO `VLANValidID` VALUES (2821);
INSERT INTO `VLANValidID` VALUES (2822);
INSERT INTO `VLANValidID` VALUES (2823);
INSERT INTO `VLANValidID` VALUES (2824);
INSERT INTO `VLANValidID` VALUES (2825);
INSERT INTO `VLANValidID` VALUES (2826);
INSERT INTO `VLANValidID` VALUES (2827);
INSERT INTO `VLANValidID` VALUES (2828);
INSERT INTO `VLANValidID` VALUES (2829);
INSERT INTO `VLANValidID` VALUES (2830);
INSERT INTO `VLANValidID` VALUES (2831);
INSERT INTO `VLANValidID` VALUES (2832);
INSERT INTO `VLANValidID` VALUES (2833);
INSERT INTO `VLANValidID` VALUES (2834);
INSERT INTO `VLANValidID` VALUES (2835);
INSERT INTO `VLANValidID` VALUES (2836);
INSERT INTO `VLANValidID` VALUES (2837);
INSERT INTO `VLANValidID` VALUES (2838);
INSERT INTO `VLANValidID` VALUES (2839);
INSERT INTO `VLANValidID` VALUES (2840);
INSERT INTO `VLANValidID` VALUES (2841);
INSERT INTO `VLANValidID` VALUES (2842);
INSERT INTO `VLANValidID` VALUES (2843);
INSERT INTO `VLANValidID` VALUES (2844);
INSERT INTO `VLANValidID` VALUES (2845);
INSERT INTO `VLANValidID` VALUES (2846);
INSERT INTO `VLANValidID` VALUES (2847);
INSERT INTO `VLANValidID` VALUES (2848);
INSERT INTO `VLANValidID` VALUES (2849);
INSERT INTO `VLANValidID` VALUES (2850);
INSERT INTO `VLANValidID` VALUES (2851);
INSERT INTO `VLANValidID` VALUES (2852);
INSERT INTO `VLANValidID` VALUES (2853);
INSERT INTO `VLANValidID` VALUES (2854);
INSERT INTO `VLANValidID` VALUES (2855);
INSERT INTO `VLANValidID` VALUES (2856);
INSERT INTO `VLANValidID` VALUES (2857);
INSERT INTO `VLANValidID` VALUES (2858);
INSERT INTO `VLANValidID` VALUES (2859);
INSERT INTO `VLANValidID` VALUES (2860);
INSERT INTO `VLANValidID` VALUES (2861);
INSERT INTO `VLANValidID` VALUES (2862);
INSERT INTO `VLANValidID` VALUES (2863);
INSERT INTO `VLANValidID` VALUES (2864);
INSERT INTO `VLANValidID` VALUES (2865);
INSERT INTO `VLANValidID` VALUES (2866);
INSERT INTO `VLANValidID` VALUES (2867);
INSERT INTO `VLANValidID` VALUES (2868);
INSERT INTO `VLANValidID` VALUES (2869);
INSERT INTO `VLANValidID` VALUES (2870);
INSERT INTO `VLANValidID` VALUES (2871);
INSERT INTO `VLANValidID` VALUES (2872);
INSERT INTO `VLANValidID` VALUES (2873);
INSERT INTO `VLANValidID` VALUES (2874);
INSERT INTO `VLANValidID` VALUES (2875);
INSERT INTO `VLANValidID` VALUES (2876);
INSERT INTO `VLANValidID` VALUES (2877);
INSERT INTO `VLANValidID` VALUES (2878);
INSERT INTO `VLANValidID` VALUES (2879);
INSERT INTO `VLANValidID` VALUES (2880);
INSERT INTO `VLANValidID` VALUES (2881);
INSERT INTO `VLANValidID` VALUES (2882);
INSERT INTO `VLANValidID` VALUES (2883);
INSERT INTO `VLANValidID` VALUES (2884);
INSERT INTO `VLANValidID` VALUES (2885);
INSERT INTO `VLANValidID` VALUES (2886);
INSERT INTO `VLANValidID` VALUES (2887);
INSERT INTO `VLANValidID` VALUES (2888);
INSERT INTO `VLANValidID` VALUES (2889);
INSERT INTO `VLANValidID` VALUES (2890);
INSERT INTO `VLANValidID` VALUES (2891);
INSERT INTO `VLANValidID` VALUES (2892);
INSERT INTO `VLANValidID` VALUES (2893);
INSERT INTO `VLANValidID` VALUES (2894);
INSERT INTO `VLANValidID` VALUES (2895);
INSERT INTO `VLANValidID` VALUES (2896);
INSERT INTO `VLANValidID` VALUES (2897);
INSERT INTO `VLANValidID` VALUES (2898);
INSERT INTO `VLANValidID` VALUES (2899);
INSERT INTO `VLANValidID` VALUES (2900);
INSERT INTO `VLANValidID` VALUES (2901);
INSERT INTO `VLANValidID` VALUES (2902);
INSERT INTO `VLANValidID` VALUES (2903);
INSERT INTO `VLANValidID` VALUES (2904);
INSERT INTO `VLANValidID` VALUES (2905);
INSERT INTO `VLANValidID` VALUES (2906);
INSERT INTO `VLANValidID` VALUES (2907);
INSERT INTO `VLANValidID` VALUES (2908);
INSERT INTO `VLANValidID` VALUES (2909);
INSERT INTO `VLANValidID` VALUES (2910);
INSERT INTO `VLANValidID` VALUES (2911);
INSERT INTO `VLANValidID` VALUES (2912);
INSERT INTO `VLANValidID` VALUES (2913);
INSERT INTO `VLANValidID` VALUES (2914);
INSERT INTO `VLANValidID` VALUES (2915);
INSERT INTO `VLANValidID` VALUES (2916);
INSERT INTO `VLANValidID` VALUES (2917);
INSERT INTO `VLANValidID` VALUES (2918);
INSERT INTO `VLANValidID` VALUES (2919);
INSERT INTO `VLANValidID` VALUES (2920);
INSERT INTO `VLANValidID` VALUES (2921);
INSERT INTO `VLANValidID` VALUES (2922);
INSERT INTO `VLANValidID` VALUES (2923);
INSERT INTO `VLANValidID` VALUES (2924);
INSERT INTO `VLANValidID` VALUES (2925);
INSERT INTO `VLANValidID` VALUES (2926);
INSERT INTO `VLANValidID` VALUES (2927);
INSERT INTO `VLANValidID` VALUES (2928);
INSERT INTO `VLANValidID` VALUES (2929);
INSERT INTO `VLANValidID` VALUES (2930);
INSERT INTO `VLANValidID` VALUES (2931);
INSERT INTO `VLANValidID` VALUES (2932);
INSERT INTO `VLANValidID` VALUES (2933);
INSERT INTO `VLANValidID` VALUES (2934);
INSERT INTO `VLANValidID` VALUES (2935);
INSERT INTO `VLANValidID` VALUES (2936);
INSERT INTO `VLANValidID` VALUES (2937);
INSERT INTO `VLANValidID` VALUES (2938);
INSERT INTO `VLANValidID` VALUES (2939);
INSERT INTO `VLANValidID` VALUES (2940);
INSERT INTO `VLANValidID` VALUES (2941);
INSERT INTO `VLANValidID` VALUES (2942);
INSERT INTO `VLANValidID` VALUES (2943);
INSERT INTO `VLANValidID` VALUES (2944);
INSERT INTO `VLANValidID` VALUES (2945);
INSERT INTO `VLANValidID` VALUES (2946);
INSERT INTO `VLANValidID` VALUES (2947);
INSERT INTO `VLANValidID` VALUES (2948);
INSERT INTO `VLANValidID` VALUES (2949);
INSERT INTO `VLANValidID` VALUES (2950);
INSERT INTO `VLANValidID` VALUES (2951);
INSERT INTO `VLANValidID` VALUES (2952);
INSERT INTO `VLANValidID` VALUES (2953);
INSERT INTO `VLANValidID` VALUES (2954);
INSERT INTO `VLANValidID` VALUES (2955);
INSERT INTO `VLANValidID` VALUES (2956);
INSERT INTO `VLANValidID` VALUES (2957);
INSERT INTO `VLANValidID` VALUES (2958);
INSERT INTO `VLANValidID` VALUES (2959);
INSERT INTO `VLANValidID` VALUES (2960);
INSERT INTO `VLANValidID` VALUES (2961);
INSERT INTO `VLANValidID` VALUES (2962);
INSERT INTO `VLANValidID` VALUES (2963);
INSERT INTO `VLANValidID` VALUES (2964);
INSERT INTO `VLANValidID` VALUES (2965);
INSERT INTO `VLANValidID` VALUES (2966);
INSERT INTO `VLANValidID` VALUES (2967);
INSERT INTO `VLANValidID` VALUES (2968);
INSERT INTO `VLANValidID` VALUES (2969);
INSERT INTO `VLANValidID` VALUES (2970);
INSERT INTO `VLANValidID` VALUES (2971);
INSERT INTO `VLANValidID` VALUES (2972);
INSERT INTO `VLANValidID` VALUES (2973);
INSERT INTO `VLANValidID` VALUES (2974);
INSERT INTO `VLANValidID` VALUES (2975);
INSERT INTO `VLANValidID` VALUES (2976);
INSERT INTO `VLANValidID` VALUES (2977);
INSERT INTO `VLANValidID` VALUES (2978);
INSERT INTO `VLANValidID` VALUES (2979);
INSERT INTO `VLANValidID` VALUES (2980);
INSERT INTO `VLANValidID` VALUES (2981);
INSERT INTO `VLANValidID` VALUES (2982);
INSERT INTO `VLANValidID` VALUES (2983);
INSERT INTO `VLANValidID` VALUES (2984);
INSERT INTO `VLANValidID` VALUES (2985);
INSERT INTO `VLANValidID` VALUES (2986);
INSERT INTO `VLANValidID` VALUES (2987);
INSERT INTO `VLANValidID` VALUES (2988);
INSERT INTO `VLANValidID` VALUES (2989);
INSERT INTO `VLANValidID` VALUES (2990);
INSERT INTO `VLANValidID` VALUES (2991);
INSERT INTO `VLANValidID` VALUES (2992);
INSERT INTO `VLANValidID` VALUES (2993);
INSERT INTO `VLANValidID` VALUES (2994);
INSERT INTO `VLANValidID` VALUES (2995);
INSERT INTO `VLANValidID` VALUES (2996);
INSERT INTO `VLANValidID` VALUES (2997);
INSERT INTO `VLANValidID` VALUES (2998);
INSERT INTO `VLANValidID` VALUES (2999);
INSERT INTO `VLANValidID` VALUES (3000);
INSERT INTO `VLANValidID` VALUES (3001);
INSERT INTO `VLANValidID` VALUES (3002);
INSERT INTO `VLANValidID` VALUES (3003);
INSERT INTO `VLANValidID` VALUES (3004);
INSERT INTO `VLANValidID` VALUES (3005);
INSERT INTO `VLANValidID` VALUES (3006);
INSERT INTO `VLANValidID` VALUES (3007);
INSERT INTO `VLANValidID` VALUES (3008);
INSERT INTO `VLANValidID` VALUES (3009);
INSERT INTO `VLANValidID` VALUES (3010);
INSERT INTO `VLANValidID` VALUES (3011);
INSERT INTO `VLANValidID` VALUES (3012);
INSERT INTO `VLANValidID` VALUES (3013);
INSERT INTO `VLANValidID` VALUES (3014);
INSERT INTO `VLANValidID` VALUES (3015);
INSERT INTO `VLANValidID` VALUES (3016);
INSERT INTO `VLANValidID` VALUES (3017);
INSERT INTO `VLANValidID` VALUES (3018);
INSERT INTO `VLANValidID` VALUES (3019);
INSERT INTO `VLANValidID` VALUES (3020);
INSERT INTO `VLANValidID` VALUES (3021);
INSERT INTO `VLANValidID` VALUES (3022);
INSERT INTO `VLANValidID` VALUES (3023);
INSERT INTO `VLANValidID` VALUES (3024);
INSERT INTO `VLANValidID` VALUES (3025);
INSERT INTO `VLANValidID` VALUES (3026);
INSERT INTO `VLANValidID` VALUES (3027);
INSERT INTO `VLANValidID` VALUES (3028);
INSERT INTO `VLANValidID` VALUES (3029);
INSERT INTO `VLANValidID` VALUES (3030);
INSERT INTO `VLANValidID` VALUES (3031);
INSERT INTO `VLANValidID` VALUES (3032);
INSERT INTO `VLANValidID` VALUES (3033);
INSERT INTO `VLANValidID` VALUES (3034);
INSERT INTO `VLANValidID` VALUES (3035);
INSERT INTO `VLANValidID` VALUES (3036);
INSERT INTO `VLANValidID` VALUES (3037);
INSERT INTO `VLANValidID` VALUES (3038);
INSERT INTO `VLANValidID` VALUES (3039);
INSERT INTO `VLANValidID` VALUES (3040);
INSERT INTO `VLANValidID` VALUES (3041);
INSERT INTO `VLANValidID` VALUES (3042);
INSERT INTO `VLANValidID` VALUES (3043);
INSERT INTO `VLANValidID` VALUES (3044);
INSERT INTO `VLANValidID` VALUES (3045);
INSERT INTO `VLANValidID` VALUES (3046);
INSERT INTO `VLANValidID` VALUES (3047);
INSERT INTO `VLANValidID` VALUES (3048);
INSERT INTO `VLANValidID` VALUES (3049);
INSERT INTO `VLANValidID` VALUES (3050);
INSERT INTO `VLANValidID` VALUES (3051);
INSERT INTO `VLANValidID` VALUES (3052);
INSERT INTO `VLANValidID` VALUES (3053);
INSERT INTO `VLANValidID` VALUES (3054);
INSERT INTO `VLANValidID` VALUES (3055);
INSERT INTO `VLANValidID` VALUES (3056);
INSERT INTO `VLANValidID` VALUES (3057);
INSERT INTO `VLANValidID` VALUES (3058);
INSERT INTO `VLANValidID` VALUES (3059);
INSERT INTO `VLANValidID` VALUES (3060);
INSERT INTO `VLANValidID` VALUES (3061);
INSERT INTO `VLANValidID` VALUES (3062);
INSERT INTO `VLANValidID` VALUES (3063);
INSERT INTO `VLANValidID` VALUES (3064);
INSERT INTO `VLANValidID` VALUES (3065);
INSERT INTO `VLANValidID` VALUES (3066);
INSERT INTO `VLANValidID` VALUES (3067);
INSERT INTO `VLANValidID` VALUES (3068);
INSERT INTO `VLANValidID` VALUES (3069);
INSERT INTO `VLANValidID` VALUES (3070);
INSERT INTO `VLANValidID` VALUES (3071);
INSERT INTO `VLANValidID` VALUES (3072);
INSERT INTO `VLANValidID` VALUES (3073);
INSERT INTO `VLANValidID` VALUES (3074);
INSERT INTO `VLANValidID` VALUES (3075);
INSERT INTO `VLANValidID` VALUES (3076);
INSERT INTO `VLANValidID` VALUES (3077);
INSERT INTO `VLANValidID` VALUES (3078);
INSERT INTO `VLANValidID` VALUES (3079);
INSERT INTO `VLANValidID` VALUES (3080);
INSERT INTO `VLANValidID` VALUES (3081);
INSERT INTO `VLANValidID` VALUES (3082);
INSERT INTO `VLANValidID` VALUES (3083);
INSERT INTO `VLANValidID` VALUES (3084);
INSERT INTO `VLANValidID` VALUES (3085);
INSERT INTO `VLANValidID` VALUES (3086);
INSERT INTO `VLANValidID` VALUES (3087);
INSERT INTO `VLANValidID` VALUES (3088);
INSERT INTO `VLANValidID` VALUES (3089);
INSERT INTO `VLANValidID` VALUES (3090);
INSERT INTO `VLANValidID` VALUES (3091);
INSERT INTO `VLANValidID` VALUES (3092);
INSERT INTO `VLANValidID` VALUES (3093);
INSERT INTO `VLANValidID` VALUES (3094);
INSERT INTO `VLANValidID` VALUES (3095);
INSERT INTO `VLANValidID` VALUES (3096);
INSERT INTO `VLANValidID` VALUES (3097);
INSERT INTO `VLANValidID` VALUES (3098);
INSERT INTO `VLANValidID` VALUES (3099);
INSERT INTO `VLANValidID` VALUES (3100);
INSERT INTO `VLANValidID` VALUES (3101);
INSERT INTO `VLANValidID` VALUES (3102);
INSERT INTO `VLANValidID` VALUES (3103);
INSERT INTO `VLANValidID` VALUES (3104);
INSERT INTO `VLANValidID` VALUES (3105);
INSERT INTO `VLANValidID` VALUES (3106);
INSERT INTO `VLANValidID` VALUES (3107);
INSERT INTO `VLANValidID` VALUES (3108);
INSERT INTO `VLANValidID` VALUES (3109);
INSERT INTO `VLANValidID` VALUES (3110);
INSERT INTO `VLANValidID` VALUES (3111);
INSERT INTO `VLANValidID` VALUES (3112);
INSERT INTO `VLANValidID` VALUES (3113);
INSERT INTO `VLANValidID` VALUES (3114);
INSERT INTO `VLANValidID` VALUES (3115);
INSERT INTO `VLANValidID` VALUES (3116);
INSERT INTO `VLANValidID` VALUES (3117);
INSERT INTO `VLANValidID` VALUES (3118);
INSERT INTO `VLANValidID` VALUES (3119);
INSERT INTO `VLANValidID` VALUES (3120);
INSERT INTO `VLANValidID` VALUES (3121);
INSERT INTO `VLANValidID` VALUES (3122);
INSERT INTO `VLANValidID` VALUES (3123);
INSERT INTO `VLANValidID` VALUES (3124);
INSERT INTO `VLANValidID` VALUES (3125);
INSERT INTO `VLANValidID` VALUES (3126);
INSERT INTO `VLANValidID` VALUES (3127);
INSERT INTO `VLANValidID` VALUES (3128);
INSERT INTO `VLANValidID` VALUES (3129);
INSERT INTO `VLANValidID` VALUES (3130);
INSERT INTO `VLANValidID` VALUES (3131);
INSERT INTO `VLANValidID` VALUES (3132);
INSERT INTO `VLANValidID` VALUES (3133);
INSERT INTO `VLANValidID` VALUES (3134);
INSERT INTO `VLANValidID` VALUES (3135);
INSERT INTO `VLANValidID` VALUES (3136);
INSERT INTO `VLANValidID` VALUES (3137);
INSERT INTO `VLANValidID` VALUES (3138);
INSERT INTO `VLANValidID` VALUES (3139);
INSERT INTO `VLANValidID` VALUES (3140);
INSERT INTO `VLANValidID` VALUES (3141);
INSERT INTO `VLANValidID` VALUES (3142);
INSERT INTO `VLANValidID` VALUES (3143);
INSERT INTO `VLANValidID` VALUES (3144);
INSERT INTO `VLANValidID` VALUES (3145);
INSERT INTO `VLANValidID` VALUES (3146);
INSERT INTO `VLANValidID` VALUES (3147);
INSERT INTO `VLANValidID` VALUES (3148);
INSERT INTO `VLANValidID` VALUES (3149);
INSERT INTO `VLANValidID` VALUES (3150);
INSERT INTO `VLANValidID` VALUES (3151);
INSERT INTO `VLANValidID` VALUES (3152);
INSERT INTO `VLANValidID` VALUES (3153);
INSERT INTO `VLANValidID` VALUES (3154);
INSERT INTO `VLANValidID` VALUES (3155);
INSERT INTO `VLANValidID` VALUES (3156);
INSERT INTO `VLANValidID` VALUES (3157);
INSERT INTO `VLANValidID` VALUES (3158);
INSERT INTO `VLANValidID` VALUES (3159);
INSERT INTO `VLANValidID` VALUES (3160);
INSERT INTO `VLANValidID` VALUES (3161);
INSERT INTO `VLANValidID` VALUES (3162);
INSERT INTO `VLANValidID` VALUES (3163);
INSERT INTO `VLANValidID` VALUES (3164);
INSERT INTO `VLANValidID` VALUES (3165);
INSERT INTO `VLANValidID` VALUES (3166);
INSERT INTO `VLANValidID` VALUES (3167);
INSERT INTO `VLANValidID` VALUES (3168);
INSERT INTO `VLANValidID` VALUES (3169);
INSERT INTO `VLANValidID` VALUES (3170);
INSERT INTO `VLANValidID` VALUES (3171);
INSERT INTO `VLANValidID` VALUES (3172);
INSERT INTO `VLANValidID` VALUES (3173);
INSERT INTO `VLANValidID` VALUES (3174);
INSERT INTO `VLANValidID` VALUES (3175);
INSERT INTO `VLANValidID` VALUES (3176);
INSERT INTO `VLANValidID` VALUES (3177);
INSERT INTO `VLANValidID` VALUES (3178);
INSERT INTO `VLANValidID` VALUES (3179);
INSERT INTO `VLANValidID` VALUES (3180);
INSERT INTO `VLANValidID` VALUES (3181);
INSERT INTO `VLANValidID` VALUES (3182);
INSERT INTO `VLANValidID` VALUES (3183);
INSERT INTO `VLANValidID` VALUES (3184);
INSERT INTO `VLANValidID` VALUES (3185);
INSERT INTO `VLANValidID` VALUES (3186);
INSERT INTO `VLANValidID` VALUES (3187);
INSERT INTO `VLANValidID` VALUES (3188);
INSERT INTO `VLANValidID` VALUES (3189);
INSERT INTO `VLANValidID` VALUES (3190);
INSERT INTO `VLANValidID` VALUES (3191);
INSERT INTO `VLANValidID` VALUES (3192);
INSERT INTO `VLANValidID` VALUES (3193);
INSERT INTO `VLANValidID` VALUES (3194);
INSERT INTO `VLANValidID` VALUES (3195);
INSERT INTO `VLANValidID` VALUES (3196);
INSERT INTO `VLANValidID` VALUES (3197);
INSERT INTO `VLANValidID` VALUES (3198);
INSERT INTO `VLANValidID` VALUES (3199);
INSERT INTO `VLANValidID` VALUES (3200);
INSERT INTO `VLANValidID` VALUES (3201);
INSERT INTO `VLANValidID` VALUES (3202);
INSERT INTO `VLANValidID` VALUES (3203);
INSERT INTO `VLANValidID` VALUES (3204);
INSERT INTO `VLANValidID` VALUES (3205);
INSERT INTO `VLANValidID` VALUES (3206);
INSERT INTO `VLANValidID` VALUES (3207);
INSERT INTO `VLANValidID` VALUES (3208);
INSERT INTO `VLANValidID` VALUES (3209);
INSERT INTO `VLANValidID` VALUES (3210);
INSERT INTO `VLANValidID` VALUES (3211);
INSERT INTO `VLANValidID` VALUES (3212);
INSERT INTO `VLANValidID` VALUES (3213);
INSERT INTO `VLANValidID` VALUES (3214);
INSERT INTO `VLANValidID` VALUES (3215);
INSERT INTO `VLANValidID` VALUES (3216);
INSERT INTO `VLANValidID` VALUES (3217);
INSERT INTO `VLANValidID` VALUES (3218);
INSERT INTO `VLANValidID` VALUES (3219);
INSERT INTO `VLANValidID` VALUES (3220);
INSERT INTO `VLANValidID` VALUES (3221);
INSERT INTO `VLANValidID` VALUES (3222);
INSERT INTO `VLANValidID` VALUES (3223);
INSERT INTO `VLANValidID` VALUES (3224);
INSERT INTO `VLANValidID` VALUES (3225);
INSERT INTO `VLANValidID` VALUES (3226);
INSERT INTO `VLANValidID` VALUES (3227);
INSERT INTO `VLANValidID` VALUES (3228);
INSERT INTO `VLANValidID` VALUES (3229);
INSERT INTO `VLANValidID` VALUES (3230);
INSERT INTO `VLANValidID` VALUES (3231);
INSERT INTO `VLANValidID` VALUES (3232);
INSERT INTO `VLANValidID` VALUES (3233);
INSERT INTO `VLANValidID` VALUES (3234);
INSERT INTO `VLANValidID` VALUES (3235);
INSERT INTO `VLANValidID` VALUES (3236);
INSERT INTO `VLANValidID` VALUES (3237);
INSERT INTO `VLANValidID` VALUES (3238);
INSERT INTO `VLANValidID` VALUES (3239);
INSERT INTO `VLANValidID` VALUES (3240);
INSERT INTO `VLANValidID` VALUES (3241);
INSERT INTO `VLANValidID` VALUES (3242);
INSERT INTO `VLANValidID` VALUES (3243);
INSERT INTO `VLANValidID` VALUES (3244);
INSERT INTO `VLANValidID` VALUES (3245);
INSERT INTO `VLANValidID` VALUES (3246);
INSERT INTO `VLANValidID` VALUES (3247);
INSERT INTO `VLANValidID` VALUES (3248);
INSERT INTO `VLANValidID` VALUES (3249);
INSERT INTO `VLANValidID` VALUES (3250);
INSERT INTO `VLANValidID` VALUES (3251);
INSERT INTO `VLANValidID` VALUES (3252);
INSERT INTO `VLANValidID` VALUES (3253);
INSERT INTO `VLANValidID` VALUES (3254);
INSERT INTO `VLANValidID` VALUES (3255);
INSERT INTO `VLANValidID` VALUES (3256);
INSERT INTO `VLANValidID` VALUES (3257);
INSERT INTO `VLANValidID` VALUES (3258);
INSERT INTO `VLANValidID` VALUES (3259);
INSERT INTO `VLANValidID` VALUES (3260);
INSERT INTO `VLANValidID` VALUES (3261);
INSERT INTO `VLANValidID` VALUES (3262);
INSERT INTO `VLANValidID` VALUES (3263);
INSERT INTO `VLANValidID` VALUES (3264);
INSERT INTO `VLANValidID` VALUES (3265);
INSERT INTO `VLANValidID` VALUES (3266);
INSERT INTO `VLANValidID` VALUES (3267);
INSERT INTO `VLANValidID` VALUES (3268);
INSERT INTO `VLANValidID` VALUES (3269);
INSERT INTO `VLANValidID` VALUES (3270);
INSERT INTO `VLANValidID` VALUES (3271);
INSERT INTO `VLANValidID` VALUES (3272);
INSERT INTO `VLANValidID` VALUES (3273);
INSERT INTO `VLANValidID` VALUES (3274);
INSERT INTO `VLANValidID` VALUES (3275);
INSERT INTO `VLANValidID` VALUES (3276);
INSERT INTO `VLANValidID` VALUES (3277);
INSERT INTO `VLANValidID` VALUES (3278);
INSERT INTO `VLANValidID` VALUES (3279);
INSERT INTO `VLANValidID` VALUES (3280);
INSERT INTO `VLANValidID` VALUES (3281);
INSERT INTO `VLANValidID` VALUES (3282);
INSERT INTO `VLANValidID` VALUES (3283);
INSERT INTO `VLANValidID` VALUES (3284);
INSERT INTO `VLANValidID` VALUES (3285);
INSERT INTO `VLANValidID` VALUES (3286);
INSERT INTO `VLANValidID` VALUES (3287);
INSERT INTO `VLANValidID` VALUES (3288);
INSERT INTO `VLANValidID` VALUES (3289);
INSERT INTO `VLANValidID` VALUES (3290);
INSERT INTO `VLANValidID` VALUES (3291);
INSERT INTO `VLANValidID` VALUES (3292);
INSERT INTO `VLANValidID` VALUES (3293);
INSERT INTO `VLANValidID` VALUES (3294);
INSERT INTO `VLANValidID` VALUES (3295);
INSERT INTO `VLANValidID` VALUES (3296);
INSERT INTO `VLANValidID` VALUES (3297);
INSERT INTO `VLANValidID` VALUES (3298);
INSERT INTO `VLANValidID` VALUES (3299);
INSERT INTO `VLANValidID` VALUES (3300);
INSERT INTO `VLANValidID` VALUES (3301);
INSERT INTO `VLANValidID` VALUES (3302);
INSERT INTO `VLANValidID` VALUES (3303);
INSERT INTO `VLANValidID` VALUES (3304);
INSERT INTO `VLANValidID` VALUES (3305);
INSERT INTO `VLANValidID` VALUES (3306);
INSERT INTO `VLANValidID` VALUES (3307);
INSERT INTO `VLANValidID` VALUES (3308);
INSERT INTO `VLANValidID` VALUES (3309);
INSERT INTO `VLANValidID` VALUES (3310);
INSERT INTO `VLANValidID` VALUES (3311);
INSERT INTO `VLANValidID` VALUES (3312);
INSERT INTO `VLANValidID` VALUES (3313);
INSERT INTO `VLANValidID` VALUES (3314);
INSERT INTO `VLANValidID` VALUES (3315);
INSERT INTO `VLANValidID` VALUES (3316);
INSERT INTO `VLANValidID` VALUES (3317);
INSERT INTO `VLANValidID` VALUES (3318);
INSERT INTO `VLANValidID` VALUES (3319);
INSERT INTO `VLANValidID` VALUES (3320);
INSERT INTO `VLANValidID` VALUES (3321);
INSERT INTO `VLANValidID` VALUES (3322);
INSERT INTO `VLANValidID` VALUES (3323);
INSERT INTO `VLANValidID` VALUES (3324);
INSERT INTO `VLANValidID` VALUES (3325);
INSERT INTO `VLANValidID` VALUES (3326);
INSERT INTO `VLANValidID` VALUES (3327);
INSERT INTO `VLANValidID` VALUES (3328);
INSERT INTO `VLANValidID` VALUES (3329);
INSERT INTO `VLANValidID` VALUES (3330);
INSERT INTO `VLANValidID` VALUES (3331);
INSERT INTO `VLANValidID` VALUES (3332);
INSERT INTO `VLANValidID` VALUES (3333);
INSERT INTO `VLANValidID` VALUES (3334);
INSERT INTO `VLANValidID` VALUES (3335);
INSERT INTO `VLANValidID` VALUES (3336);
INSERT INTO `VLANValidID` VALUES (3337);
INSERT INTO `VLANValidID` VALUES (3338);
INSERT INTO `VLANValidID` VALUES (3339);
INSERT INTO `VLANValidID` VALUES (3340);
INSERT INTO `VLANValidID` VALUES (3341);
INSERT INTO `VLANValidID` VALUES (3342);
INSERT INTO `VLANValidID` VALUES (3343);
INSERT INTO `VLANValidID` VALUES (3344);
INSERT INTO `VLANValidID` VALUES (3345);
INSERT INTO `VLANValidID` VALUES (3346);
INSERT INTO `VLANValidID` VALUES (3347);
INSERT INTO `VLANValidID` VALUES (3348);
INSERT INTO `VLANValidID` VALUES (3349);
INSERT INTO `VLANValidID` VALUES (3350);
INSERT INTO `VLANValidID` VALUES (3351);
INSERT INTO `VLANValidID` VALUES (3352);
INSERT INTO `VLANValidID` VALUES (3353);
INSERT INTO `VLANValidID` VALUES (3354);
INSERT INTO `VLANValidID` VALUES (3355);
INSERT INTO `VLANValidID` VALUES (3356);
INSERT INTO `VLANValidID` VALUES (3357);
INSERT INTO `VLANValidID` VALUES (3358);
INSERT INTO `VLANValidID` VALUES (3359);
INSERT INTO `VLANValidID` VALUES (3360);
INSERT INTO `VLANValidID` VALUES (3361);
INSERT INTO `VLANValidID` VALUES (3362);
INSERT INTO `VLANValidID` VALUES (3363);
INSERT INTO `VLANValidID` VALUES (3364);
INSERT INTO `VLANValidID` VALUES (3365);
INSERT INTO `VLANValidID` VALUES (3366);
INSERT INTO `VLANValidID` VALUES (3367);
INSERT INTO `VLANValidID` VALUES (3368);
INSERT INTO `VLANValidID` VALUES (3369);
INSERT INTO `VLANValidID` VALUES (3370);
INSERT INTO `VLANValidID` VALUES (3371);
INSERT INTO `VLANValidID` VALUES (3372);
INSERT INTO `VLANValidID` VALUES (3373);
INSERT INTO `VLANValidID` VALUES (3374);
INSERT INTO `VLANValidID` VALUES (3375);
INSERT INTO `VLANValidID` VALUES (3376);
INSERT INTO `VLANValidID` VALUES (3377);
INSERT INTO `VLANValidID` VALUES (3378);
INSERT INTO `VLANValidID` VALUES (3379);
INSERT INTO `VLANValidID` VALUES (3380);
INSERT INTO `VLANValidID` VALUES (3381);
INSERT INTO `VLANValidID` VALUES (3382);
INSERT INTO `VLANValidID` VALUES (3383);
INSERT INTO `VLANValidID` VALUES (3384);
INSERT INTO `VLANValidID` VALUES (3385);
INSERT INTO `VLANValidID` VALUES (3386);
INSERT INTO `VLANValidID` VALUES (3387);
INSERT INTO `VLANValidID` VALUES (3388);
INSERT INTO `VLANValidID` VALUES (3389);
INSERT INTO `VLANValidID` VALUES (3390);
INSERT INTO `VLANValidID` VALUES (3391);
INSERT INTO `VLANValidID` VALUES (3392);
INSERT INTO `VLANValidID` VALUES (3393);
INSERT INTO `VLANValidID` VALUES (3394);
INSERT INTO `VLANValidID` VALUES (3395);
INSERT INTO `VLANValidID` VALUES (3396);
INSERT INTO `VLANValidID` VALUES (3397);
INSERT INTO `VLANValidID` VALUES (3398);
INSERT INTO `VLANValidID` VALUES (3399);
INSERT INTO `VLANValidID` VALUES (3400);
INSERT INTO `VLANValidID` VALUES (3401);
INSERT INTO `VLANValidID` VALUES (3402);
INSERT INTO `VLANValidID` VALUES (3403);
INSERT INTO `VLANValidID` VALUES (3404);
INSERT INTO `VLANValidID` VALUES (3405);
INSERT INTO `VLANValidID` VALUES (3406);
INSERT INTO `VLANValidID` VALUES (3407);
INSERT INTO `VLANValidID` VALUES (3408);
INSERT INTO `VLANValidID` VALUES (3409);
INSERT INTO `VLANValidID` VALUES (3410);
INSERT INTO `VLANValidID` VALUES (3411);
INSERT INTO `VLANValidID` VALUES (3412);
INSERT INTO `VLANValidID` VALUES (3413);
INSERT INTO `VLANValidID` VALUES (3414);
INSERT INTO `VLANValidID` VALUES (3415);
INSERT INTO `VLANValidID` VALUES (3416);
INSERT INTO `VLANValidID` VALUES (3417);
INSERT INTO `VLANValidID` VALUES (3418);
INSERT INTO `VLANValidID` VALUES (3419);
INSERT INTO `VLANValidID` VALUES (3420);
INSERT INTO `VLANValidID` VALUES (3421);
INSERT INTO `VLANValidID` VALUES (3422);
INSERT INTO `VLANValidID` VALUES (3423);
INSERT INTO `VLANValidID` VALUES (3424);
INSERT INTO `VLANValidID` VALUES (3425);
INSERT INTO `VLANValidID` VALUES (3426);
INSERT INTO `VLANValidID` VALUES (3427);
INSERT INTO `VLANValidID` VALUES (3428);
INSERT INTO `VLANValidID` VALUES (3429);
INSERT INTO `VLANValidID` VALUES (3430);
INSERT INTO `VLANValidID` VALUES (3431);
INSERT INTO `VLANValidID` VALUES (3432);
INSERT INTO `VLANValidID` VALUES (3433);
INSERT INTO `VLANValidID` VALUES (3434);
INSERT INTO `VLANValidID` VALUES (3435);
INSERT INTO `VLANValidID` VALUES (3436);
INSERT INTO `VLANValidID` VALUES (3437);
INSERT INTO `VLANValidID` VALUES (3438);
INSERT INTO `VLANValidID` VALUES (3439);
INSERT INTO `VLANValidID` VALUES (3440);
INSERT INTO `VLANValidID` VALUES (3441);
INSERT INTO `VLANValidID` VALUES (3442);
INSERT INTO `VLANValidID` VALUES (3443);
INSERT INTO `VLANValidID` VALUES (3444);
INSERT INTO `VLANValidID` VALUES (3445);
INSERT INTO `VLANValidID` VALUES (3446);
INSERT INTO `VLANValidID` VALUES (3447);
INSERT INTO `VLANValidID` VALUES (3448);
INSERT INTO `VLANValidID` VALUES (3449);
INSERT INTO `VLANValidID` VALUES (3450);
INSERT INTO `VLANValidID` VALUES (3451);
INSERT INTO `VLANValidID` VALUES (3452);
INSERT INTO `VLANValidID` VALUES (3453);
INSERT INTO `VLANValidID` VALUES (3454);
INSERT INTO `VLANValidID` VALUES (3455);
INSERT INTO `VLANValidID` VALUES (3456);
INSERT INTO `VLANValidID` VALUES (3457);
INSERT INTO `VLANValidID` VALUES (3458);
INSERT INTO `VLANValidID` VALUES (3459);
INSERT INTO `VLANValidID` VALUES (3460);
INSERT INTO `VLANValidID` VALUES (3461);
INSERT INTO `VLANValidID` VALUES (3462);
INSERT INTO `VLANValidID` VALUES (3463);
INSERT INTO `VLANValidID` VALUES (3464);
INSERT INTO `VLANValidID` VALUES (3465);
INSERT INTO `VLANValidID` VALUES (3466);
INSERT INTO `VLANValidID` VALUES (3467);
INSERT INTO `VLANValidID` VALUES (3468);
INSERT INTO `VLANValidID` VALUES (3469);
INSERT INTO `VLANValidID` VALUES (3470);
INSERT INTO `VLANValidID` VALUES (3471);
INSERT INTO `VLANValidID` VALUES (3472);
INSERT INTO `VLANValidID` VALUES (3473);
INSERT INTO `VLANValidID` VALUES (3474);
INSERT INTO `VLANValidID` VALUES (3475);
INSERT INTO `VLANValidID` VALUES (3476);
INSERT INTO `VLANValidID` VALUES (3477);
INSERT INTO `VLANValidID` VALUES (3478);
INSERT INTO `VLANValidID` VALUES (3479);
INSERT INTO `VLANValidID` VALUES (3480);
INSERT INTO `VLANValidID` VALUES (3481);
INSERT INTO `VLANValidID` VALUES (3482);
INSERT INTO `VLANValidID` VALUES (3483);
INSERT INTO `VLANValidID` VALUES (3484);
INSERT INTO `VLANValidID` VALUES (3485);
INSERT INTO `VLANValidID` VALUES (3486);
INSERT INTO `VLANValidID` VALUES (3487);
INSERT INTO `VLANValidID` VALUES (3488);
INSERT INTO `VLANValidID` VALUES (3489);
INSERT INTO `VLANValidID` VALUES (3490);
INSERT INTO `VLANValidID` VALUES (3491);
INSERT INTO `VLANValidID` VALUES (3492);
INSERT INTO `VLANValidID` VALUES (3493);
INSERT INTO `VLANValidID` VALUES (3494);
INSERT INTO `VLANValidID` VALUES (3495);
INSERT INTO `VLANValidID` VALUES (3496);
INSERT INTO `VLANValidID` VALUES (3497);
INSERT INTO `VLANValidID` VALUES (3498);
INSERT INTO `VLANValidID` VALUES (3499);
INSERT INTO `VLANValidID` VALUES (3500);
INSERT INTO `VLANValidID` VALUES (3501);
INSERT INTO `VLANValidID` VALUES (3502);
INSERT INTO `VLANValidID` VALUES (3503);
INSERT INTO `VLANValidID` VALUES (3504);
INSERT INTO `VLANValidID` VALUES (3505);
INSERT INTO `VLANValidID` VALUES (3506);
INSERT INTO `VLANValidID` VALUES (3507);
INSERT INTO `VLANValidID` VALUES (3508);
INSERT INTO `VLANValidID` VALUES (3509);
INSERT INTO `VLANValidID` VALUES (3510);
INSERT INTO `VLANValidID` VALUES (3511);
INSERT INTO `VLANValidID` VALUES (3512);
INSERT INTO `VLANValidID` VALUES (3513);
INSERT INTO `VLANValidID` VALUES (3514);
INSERT INTO `VLANValidID` VALUES (3515);
INSERT INTO `VLANValidID` VALUES (3516);
INSERT INTO `VLANValidID` VALUES (3517);
INSERT INTO `VLANValidID` VALUES (3518);
INSERT INTO `VLANValidID` VALUES (3519);
INSERT INTO `VLANValidID` VALUES (3520);
INSERT INTO `VLANValidID` VALUES (3521);
INSERT INTO `VLANValidID` VALUES (3522);
INSERT INTO `VLANValidID` VALUES (3523);
INSERT INTO `VLANValidID` VALUES (3524);
INSERT INTO `VLANValidID` VALUES (3525);
INSERT INTO `VLANValidID` VALUES (3526);
INSERT INTO `VLANValidID` VALUES (3527);
INSERT INTO `VLANValidID` VALUES (3528);
INSERT INTO `VLANValidID` VALUES (3529);
INSERT INTO `VLANValidID` VALUES (3530);
INSERT INTO `VLANValidID` VALUES (3531);
INSERT INTO `VLANValidID` VALUES (3532);
INSERT INTO `VLANValidID` VALUES (3533);
INSERT INTO `VLANValidID` VALUES (3534);
INSERT INTO `VLANValidID` VALUES (3535);
INSERT INTO `VLANValidID` VALUES (3536);
INSERT INTO `VLANValidID` VALUES (3537);
INSERT INTO `VLANValidID` VALUES (3538);
INSERT INTO `VLANValidID` VALUES (3539);
INSERT INTO `VLANValidID` VALUES (3540);
INSERT INTO `VLANValidID` VALUES (3541);
INSERT INTO `VLANValidID` VALUES (3542);
INSERT INTO `VLANValidID` VALUES (3543);
INSERT INTO `VLANValidID` VALUES (3544);
INSERT INTO `VLANValidID` VALUES (3545);
INSERT INTO `VLANValidID` VALUES (3546);
INSERT INTO `VLANValidID` VALUES (3547);
INSERT INTO `VLANValidID` VALUES (3548);
INSERT INTO `VLANValidID` VALUES (3549);
INSERT INTO `VLANValidID` VALUES (3550);
INSERT INTO `VLANValidID` VALUES (3551);
INSERT INTO `VLANValidID` VALUES (3552);
INSERT INTO `VLANValidID` VALUES (3553);
INSERT INTO `VLANValidID` VALUES (3554);
INSERT INTO `VLANValidID` VALUES (3555);
INSERT INTO `VLANValidID` VALUES (3556);
INSERT INTO `VLANValidID` VALUES (3557);
INSERT INTO `VLANValidID` VALUES (3558);
INSERT INTO `VLANValidID` VALUES (3559);
INSERT INTO `VLANValidID` VALUES (3560);
INSERT INTO `VLANValidID` VALUES (3561);
INSERT INTO `VLANValidID` VALUES (3562);
INSERT INTO `VLANValidID` VALUES (3563);
INSERT INTO `VLANValidID` VALUES (3564);
INSERT INTO `VLANValidID` VALUES (3565);
INSERT INTO `VLANValidID` VALUES (3566);
INSERT INTO `VLANValidID` VALUES (3567);
INSERT INTO `VLANValidID` VALUES (3568);
INSERT INTO `VLANValidID` VALUES (3569);
INSERT INTO `VLANValidID` VALUES (3570);
INSERT INTO `VLANValidID` VALUES (3571);
INSERT INTO `VLANValidID` VALUES (3572);
INSERT INTO `VLANValidID` VALUES (3573);
INSERT INTO `VLANValidID` VALUES (3574);
INSERT INTO `VLANValidID` VALUES (3575);
INSERT INTO `VLANValidID` VALUES (3576);
INSERT INTO `VLANValidID` VALUES (3577);
INSERT INTO `VLANValidID` VALUES (3578);
INSERT INTO `VLANValidID` VALUES (3579);
INSERT INTO `VLANValidID` VALUES (3580);
INSERT INTO `VLANValidID` VALUES (3581);
INSERT INTO `VLANValidID` VALUES (3582);
INSERT INTO `VLANValidID` VALUES (3583);
INSERT INTO `VLANValidID` VALUES (3584);
INSERT INTO `VLANValidID` VALUES (3585);
INSERT INTO `VLANValidID` VALUES (3586);
INSERT INTO `VLANValidID` VALUES (3587);
INSERT INTO `VLANValidID` VALUES (3588);
INSERT INTO `VLANValidID` VALUES (3589);
INSERT INTO `VLANValidID` VALUES (3590);
INSERT INTO `VLANValidID` VALUES (3591);
INSERT INTO `VLANValidID` VALUES (3592);
INSERT INTO `VLANValidID` VALUES (3593);
INSERT INTO `VLANValidID` VALUES (3594);
INSERT INTO `VLANValidID` VALUES (3595);
INSERT INTO `VLANValidID` VALUES (3596);
INSERT INTO `VLANValidID` VALUES (3597);
INSERT INTO `VLANValidID` VALUES (3598);
INSERT INTO `VLANValidID` VALUES (3599);
INSERT INTO `VLANValidID` VALUES (3600);
INSERT INTO `VLANValidID` VALUES (3601);
INSERT INTO `VLANValidID` VALUES (3602);
INSERT INTO `VLANValidID` VALUES (3603);
INSERT INTO `VLANValidID` VALUES (3604);
INSERT INTO `VLANValidID` VALUES (3605);
INSERT INTO `VLANValidID` VALUES (3606);
INSERT INTO `VLANValidID` VALUES (3607);
INSERT INTO `VLANValidID` VALUES (3608);
INSERT INTO `VLANValidID` VALUES (3609);
INSERT INTO `VLANValidID` VALUES (3610);
INSERT INTO `VLANValidID` VALUES (3611);
INSERT INTO `VLANValidID` VALUES (3612);
INSERT INTO `VLANValidID` VALUES (3613);
INSERT INTO `VLANValidID` VALUES (3614);
INSERT INTO `VLANValidID` VALUES (3615);
INSERT INTO `VLANValidID` VALUES (3616);
INSERT INTO `VLANValidID` VALUES (3617);
INSERT INTO `VLANValidID` VALUES (3618);
INSERT INTO `VLANValidID` VALUES (3619);
INSERT INTO `VLANValidID` VALUES (3620);
INSERT INTO `VLANValidID` VALUES (3621);
INSERT INTO `VLANValidID` VALUES (3622);
INSERT INTO `VLANValidID` VALUES (3623);
INSERT INTO `VLANValidID` VALUES (3624);
INSERT INTO `VLANValidID` VALUES (3625);
INSERT INTO `VLANValidID` VALUES (3626);
INSERT INTO `VLANValidID` VALUES (3627);
INSERT INTO `VLANValidID` VALUES (3628);
INSERT INTO `VLANValidID` VALUES (3629);
INSERT INTO `VLANValidID` VALUES (3630);
INSERT INTO `VLANValidID` VALUES (3631);
INSERT INTO `VLANValidID` VALUES (3632);
INSERT INTO `VLANValidID` VALUES (3633);
INSERT INTO `VLANValidID` VALUES (3634);
INSERT INTO `VLANValidID` VALUES (3635);
INSERT INTO `VLANValidID` VALUES (3636);
INSERT INTO `VLANValidID` VALUES (3637);
INSERT INTO `VLANValidID` VALUES (3638);
INSERT INTO `VLANValidID` VALUES (3639);
INSERT INTO `VLANValidID` VALUES (3640);
INSERT INTO `VLANValidID` VALUES (3641);
INSERT INTO `VLANValidID` VALUES (3642);
INSERT INTO `VLANValidID` VALUES (3643);
INSERT INTO `VLANValidID` VALUES (3644);
INSERT INTO `VLANValidID` VALUES (3645);
INSERT INTO `VLANValidID` VALUES (3646);
INSERT INTO `VLANValidID` VALUES (3647);
INSERT INTO `VLANValidID` VALUES (3648);
INSERT INTO `VLANValidID` VALUES (3649);
INSERT INTO `VLANValidID` VALUES (3650);
INSERT INTO `VLANValidID` VALUES (3651);
INSERT INTO `VLANValidID` VALUES (3652);
INSERT INTO `VLANValidID` VALUES (3653);
INSERT INTO `VLANValidID` VALUES (3654);
INSERT INTO `VLANValidID` VALUES (3655);
INSERT INTO `VLANValidID` VALUES (3656);
INSERT INTO `VLANValidID` VALUES (3657);
INSERT INTO `VLANValidID` VALUES (3658);
INSERT INTO `VLANValidID` VALUES (3659);
INSERT INTO `VLANValidID` VALUES (3660);
INSERT INTO `VLANValidID` VALUES (3661);
INSERT INTO `VLANValidID` VALUES (3662);
INSERT INTO `VLANValidID` VALUES (3663);
INSERT INTO `VLANValidID` VALUES (3664);
INSERT INTO `VLANValidID` VALUES (3665);
INSERT INTO `VLANValidID` VALUES (3666);
INSERT INTO `VLANValidID` VALUES (3667);
INSERT INTO `VLANValidID` VALUES (3668);
INSERT INTO `VLANValidID` VALUES (3669);
INSERT INTO `VLANValidID` VALUES (3670);
INSERT INTO `VLANValidID` VALUES (3671);
INSERT INTO `VLANValidID` VALUES (3672);
INSERT INTO `VLANValidID` VALUES (3673);
INSERT INTO `VLANValidID` VALUES (3674);
INSERT INTO `VLANValidID` VALUES (3675);
INSERT INTO `VLANValidID` VALUES (3676);
INSERT INTO `VLANValidID` VALUES (3677);
INSERT INTO `VLANValidID` VALUES (3678);
INSERT INTO `VLANValidID` VALUES (3679);
INSERT INTO `VLANValidID` VALUES (3680);
INSERT INTO `VLANValidID` VALUES (3681);
INSERT INTO `VLANValidID` VALUES (3682);
INSERT INTO `VLANValidID` VALUES (3683);
INSERT INTO `VLANValidID` VALUES (3684);
INSERT INTO `VLANValidID` VALUES (3685);
INSERT INTO `VLANValidID` VALUES (3686);
INSERT INTO `VLANValidID` VALUES (3687);
INSERT INTO `VLANValidID` VALUES (3688);
INSERT INTO `VLANValidID` VALUES (3689);
INSERT INTO `VLANValidID` VALUES (3690);
INSERT INTO `VLANValidID` VALUES (3691);
INSERT INTO `VLANValidID` VALUES (3692);
INSERT INTO `VLANValidID` VALUES (3693);
INSERT INTO `VLANValidID` VALUES (3694);
INSERT INTO `VLANValidID` VALUES (3695);
INSERT INTO `VLANValidID` VALUES (3696);
INSERT INTO `VLANValidID` VALUES (3697);
INSERT INTO `VLANValidID` VALUES (3698);
INSERT INTO `VLANValidID` VALUES (3699);
INSERT INTO `VLANValidID` VALUES (3700);
INSERT INTO `VLANValidID` VALUES (3701);
INSERT INTO `VLANValidID` VALUES (3702);
INSERT INTO `VLANValidID` VALUES (3703);
INSERT INTO `VLANValidID` VALUES (3704);
INSERT INTO `VLANValidID` VALUES (3705);
INSERT INTO `VLANValidID` VALUES (3706);
INSERT INTO `VLANValidID` VALUES (3707);
INSERT INTO `VLANValidID` VALUES (3708);
INSERT INTO `VLANValidID` VALUES (3709);
INSERT INTO `VLANValidID` VALUES (3710);
INSERT INTO `VLANValidID` VALUES (3711);
INSERT INTO `VLANValidID` VALUES (3712);
INSERT INTO `VLANValidID` VALUES (3713);
INSERT INTO `VLANValidID` VALUES (3714);
INSERT INTO `VLANValidID` VALUES (3715);
INSERT INTO `VLANValidID` VALUES (3716);
INSERT INTO `VLANValidID` VALUES (3717);
INSERT INTO `VLANValidID` VALUES (3718);
INSERT INTO `VLANValidID` VALUES (3719);
INSERT INTO `VLANValidID` VALUES (3720);
INSERT INTO `VLANValidID` VALUES (3721);
INSERT INTO `VLANValidID` VALUES (3722);
INSERT INTO `VLANValidID` VALUES (3723);
INSERT INTO `VLANValidID` VALUES (3724);
INSERT INTO `VLANValidID` VALUES (3725);
INSERT INTO `VLANValidID` VALUES (3726);
INSERT INTO `VLANValidID` VALUES (3727);
INSERT INTO `VLANValidID` VALUES (3728);
INSERT INTO `VLANValidID` VALUES (3729);
INSERT INTO `VLANValidID` VALUES (3730);
INSERT INTO `VLANValidID` VALUES (3731);
INSERT INTO `VLANValidID` VALUES (3732);
INSERT INTO `VLANValidID` VALUES (3733);
INSERT INTO `VLANValidID` VALUES (3734);
INSERT INTO `VLANValidID` VALUES (3735);
INSERT INTO `VLANValidID` VALUES (3736);
INSERT INTO `VLANValidID` VALUES (3737);
INSERT INTO `VLANValidID` VALUES (3738);
INSERT INTO `VLANValidID` VALUES (3739);
INSERT INTO `VLANValidID` VALUES (3740);
INSERT INTO `VLANValidID` VALUES (3741);
INSERT INTO `VLANValidID` VALUES (3742);
INSERT INTO `VLANValidID` VALUES (3743);
INSERT INTO `VLANValidID` VALUES (3744);
INSERT INTO `VLANValidID` VALUES (3745);
INSERT INTO `VLANValidID` VALUES (3746);
INSERT INTO `VLANValidID` VALUES (3747);
INSERT INTO `VLANValidID` VALUES (3748);
INSERT INTO `VLANValidID` VALUES (3749);
INSERT INTO `VLANValidID` VALUES (3750);
INSERT INTO `VLANValidID` VALUES (3751);
INSERT INTO `VLANValidID` VALUES (3752);
INSERT INTO `VLANValidID` VALUES (3753);
INSERT INTO `VLANValidID` VALUES (3754);
INSERT INTO `VLANValidID` VALUES (3755);
INSERT INTO `VLANValidID` VALUES (3756);
INSERT INTO `VLANValidID` VALUES (3757);
INSERT INTO `VLANValidID` VALUES (3758);
INSERT INTO `VLANValidID` VALUES (3759);
INSERT INTO `VLANValidID` VALUES (3760);
INSERT INTO `VLANValidID` VALUES (3761);
INSERT INTO `VLANValidID` VALUES (3762);
INSERT INTO `VLANValidID` VALUES (3763);
INSERT INTO `VLANValidID` VALUES (3764);
INSERT INTO `VLANValidID` VALUES (3765);
INSERT INTO `VLANValidID` VALUES (3766);
INSERT INTO `VLANValidID` VALUES (3767);
INSERT INTO `VLANValidID` VALUES (3768);
INSERT INTO `VLANValidID` VALUES (3769);
INSERT INTO `VLANValidID` VALUES (3770);
INSERT INTO `VLANValidID` VALUES (3771);
INSERT INTO `VLANValidID` VALUES (3772);
INSERT INTO `VLANValidID` VALUES (3773);
INSERT INTO `VLANValidID` VALUES (3774);
INSERT INTO `VLANValidID` VALUES (3775);
INSERT INTO `VLANValidID` VALUES (3776);
INSERT INTO `VLANValidID` VALUES (3777);
INSERT INTO `VLANValidID` VALUES (3778);
INSERT INTO `VLANValidID` VALUES (3779);
INSERT INTO `VLANValidID` VALUES (3780);
INSERT INTO `VLANValidID` VALUES (3781);
INSERT INTO `VLANValidID` VALUES (3782);
INSERT INTO `VLANValidID` VALUES (3783);
INSERT INTO `VLANValidID` VALUES (3784);
INSERT INTO `VLANValidID` VALUES (3785);
INSERT INTO `VLANValidID` VALUES (3786);
INSERT INTO `VLANValidID` VALUES (3787);
INSERT INTO `VLANValidID` VALUES (3788);
INSERT INTO `VLANValidID` VALUES (3789);
INSERT INTO `VLANValidID` VALUES (3790);
INSERT INTO `VLANValidID` VALUES (3791);
INSERT INTO `VLANValidID` VALUES (3792);
INSERT INTO `VLANValidID` VALUES (3793);
INSERT INTO `VLANValidID` VALUES (3794);
INSERT INTO `VLANValidID` VALUES (3795);
INSERT INTO `VLANValidID` VALUES (3796);
INSERT INTO `VLANValidID` VALUES (3797);
INSERT INTO `VLANValidID` VALUES (3798);
INSERT INTO `VLANValidID` VALUES (3799);
INSERT INTO `VLANValidID` VALUES (3800);
INSERT INTO `VLANValidID` VALUES (3801);
INSERT INTO `VLANValidID` VALUES (3802);
INSERT INTO `VLANValidID` VALUES (3803);
INSERT INTO `VLANValidID` VALUES (3804);
INSERT INTO `VLANValidID` VALUES (3805);
INSERT INTO `VLANValidID` VALUES (3806);
INSERT INTO `VLANValidID` VALUES (3807);
INSERT INTO `VLANValidID` VALUES (3808);
INSERT INTO `VLANValidID` VALUES (3809);
INSERT INTO `VLANValidID` VALUES (3810);
INSERT INTO `VLANValidID` VALUES (3811);
INSERT INTO `VLANValidID` VALUES (3812);
INSERT INTO `VLANValidID` VALUES (3813);
INSERT INTO `VLANValidID` VALUES (3814);
INSERT INTO `VLANValidID` VALUES (3815);
INSERT INTO `VLANValidID` VALUES (3816);
INSERT INTO `VLANValidID` VALUES (3817);
INSERT INTO `VLANValidID` VALUES (3818);
INSERT INTO `VLANValidID` VALUES (3819);
INSERT INTO `VLANValidID` VALUES (3820);
INSERT INTO `VLANValidID` VALUES (3821);
INSERT INTO `VLANValidID` VALUES (3822);
INSERT INTO `VLANValidID` VALUES (3823);
INSERT INTO `VLANValidID` VALUES (3824);
INSERT INTO `VLANValidID` VALUES (3825);
INSERT INTO `VLANValidID` VALUES (3826);
INSERT INTO `VLANValidID` VALUES (3827);
INSERT INTO `VLANValidID` VALUES (3828);
INSERT INTO `VLANValidID` VALUES (3829);
INSERT INTO `VLANValidID` VALUES (3830);
INSERT INTO `VLANValidID` VALUES (3831);
INSERT INTO `VLANValidID` VALUES (3832);
INSERT INTO `VLANValidID` VALUES (3833);
INSERT INTO `VLANValidID` VALUES (3834);
INSERT INTO `VLANValidID` VALUES (3835);
INSERT INTO `VLANValidID` VALUES (3836);
INSERT INTO `VLANValidID` VALUES (3837);
INSERT INTO `VLANValidID` VALUES (3838);
INSERT INTO `VLANValidID` VALUES (3839);
INSERT INTO `VLANValidID` VALUES (3840);
INSERT INTO `VLANValidID` VALUES (3841);
INSERT INTO `VLANValidID` VALUES (3842);
INSERT INTO `VLANValidID` VALUES (3843);
INSERT INTO `VLANValidID` VALUES (3844);
INSERT INTO `VLANValidID` VALUES (3845);
INSERT INTO `VLANValidID` VALUES (3846);
INSERT INTO `VLANValidID` VALUES (3847);
INSERT INTO `VLANValidID` VALUES (3848);
INSERT INTO `VLANValidID` VALUES (3849);
INSERT INTO `VLANValidID` VALUES (3850);
INSERT INTO `VLANValidID` VALUES (3851);
INSERT INTO `VLANValidID` VALUES (3852);
INSERT INTO `VLANValidID` VALUES (3853);
INSERT INTO `VLANValidID` VALUES (3854);
INSERT INTO `VLANValidID` VALUES (3855);
INSERT INTO `VLANValidID` VALUES (3856);
INSERT INTO `VLANValidID` VALUES (3857);
INSERT INTO `VLANValidID` VALUES (3858);
INSERT INTO `VLANValidID` VALUES (3859);
INSERT INTO `VLANValidID` VALUES (3860);
INSERT INTO `VLANValidID` VALUES (3861);
INSERT INTO `VLANValidID` VALUES (3862);
INSERT INTO `VLANValidID` VALUES (3863);
INSERT INTO `VLANValidID` VALUES (3864);
INSERT INTO `VLANValidID` VALUES (3865);
INSERT INTO `VLANValidID` VALUES (3866);
INSERT INTO `VLANValidID` VALUES (3867);
INSERT INTO `VLANValidID` VALUES (3868);
INSERT INTO `VLANValidID` VALUES (3869);
INSERT INTO `VLANValidID` VALUES (3870);
INSERT INTO `VLANValidID` VALUES (3871);
INSERT INTO `VLANValidID` VALUES (3872);
INSERT INTO `VLANValidID` VALUES (3873);
INSERT INTO `VLANValidID` VALUES (3874);
INSERT INTO `VLANValidID` VALUES (3875);
INSERT INTO `VLANValidID` VALUES (3876);
INSERT INTO `VLANValidID` VALUES (3877);
INSERT INTO `VLANValidID` VALUES (3878);
INSERT INTO `VLANValidID` VALUES (3879);
INSERT INTO `VLANValidID` VALUES (3880);
INSERT INTO `VLANValidID` VALUES (3881);
INSERT INTO `VLANValidID` VALUES (3882);
INSERT INTO `VLANValidID` VALUES (3883);
INSERT INTO `VLANValidID` VALUES (3884);
INSERT INTO `VLANValidID` VALUES (3885);
INSERT INTO `VLANValidID` VALUES (3886);
INSERT INTO `VLANValidID` VALUES (3887);
INSERT INTO `VLANValidID` VALUES (3888);
INSERT INTO `VLANValidID` VALUES (3889);
INSERT INTO `VLANValidID` VALUES (3890);
INSERT INTO `VLANValidID` VALUES (3891);
INSERT INTO `VLANValidID` VALUES (3892);
INSERT INTO `VLANValidID` VALUES (3893);
INSERT INTO `VLANValidID` VALUES (3894);
INSERT INTO `VLANValidID` VALUES (3895);
INSERT INTO `VLANValidID` VALUES (3896);
INSERT INTO `VLANValidID` VALUES (3897);
INSERT INTO `VLANValidID` VALUES (3898);
INSERT INTO `VLANValidID` VALUES (3899);
INSERT INTO `VLANValidID` VALUES (3900);
INSERT INTO `VLANValidID` VALUES (3901);
INSERT INTO `VLANValidID` VALUES (3902);
INSERT INTO `VLANValidID` VALUES (3903);
INSERT INTO `VLANValidID` VALUES (3904);
INSERT INTO `VLANValidID` VALUES (3905);
INSERT INTO `VLANValidID` VALUES (3906);
INSERT INTO `VLANValidID` VALUES (3907);
INSERT INTO `VLANValidID` VALUES (3908);
INSERT INTO `VLANValidID` VALUES (3909);
INSERT INTO `VLANValidID` VALUES (3910);
INSERT INTO `VLANValidID` VALUES (3911);
INSERT INTO `VLANValidID` VALUES (3912);
INSERT INTO `VLANValidID` VALUES (3913);
INSERT INTO `VLANValidID` VALUES (3914);
INSERT INTO `VLANValidID` VALUES (3915);
INSERT INTO `VLANValidID` VALUES (3916);
INSERT INTO `VLANValidID` VALUES (3917);
INSERT INTO `VLANValidID` VALUES (3918);
INSERT INTO `VLANValidID` VALUES (3919);
INSERT INTO `VLANValidID` VALUES (3920);
INSERT INTO `VLANValidID` VALUES (3921);
INSERT INTO `VLANValidID` VALUES (3922);
INSERT INTO `VLANValidID` VALUES (3923);
INSERT INTO `VLANValidID` VALUES (3924);
INSERT INTO `VLANValidID` VALUES (3925);
INSERT INTO `VLANValidID` VALUES (3926);
INSERT INTO `VLANValidID` VALUES (3927);
INSERT INTO `VLANValidID` VALUES (3928);
INSERT INTO `VLANValidID` VALUES (3929);
INSERT INTO `VLANValidID` VALUES (3930);
INSERT INTO `VLANValidID` VALUES (3931);
INSERT INTO `VLANValidID` VALUES (3932);
INSERT INTO `VLANValidID` VALUES (3933);
INSERT INTO `VLANValidID` VALUES (3934);
INSERT INTO `VLANValidID` VALUES (3935);
INSERT INTO `VLANValidID` VALUES (3936);
INSERT INTO `VLANValidID` VALUES (3937);
INSERT INTO `VLANValidID` VALUES (3938);
INSERT INTO `VLANValidID` VALUES (3939);
INSERT INTO `VLANValidID` VALUES (3940);
INSERT INTO `VLANValidID` VALUES (3941);
INSERT INTO `VLANValidID` VALUES (3942);
INSERT INTO `VLANValidID` VALUES (3943);
INSERT INTO `VLANValidID` VALUES (3944);
INSERT INTO `VLANValidID` VALUES (3945);
INSERT INTO `VLANValidID` VALUES (3946);
INSERT INTO `VLANValidID` VALUES (3947);
INSERT INTO `VLANValidID` VALUES (3948);
INSERT INTO `VLANValidID` VALUES (3949);
INSERT INTO `VLANValidID` VALUES (3950);
INSERT INTO `VLANValidID` VALUES (3951);
INSERT INTO `VLANValidID` VALUES (3952);
INSERT INTO `VLANValidID` VALUES (3953);
INSERT INTO `VLANValidID` VALUES (3954);
INSERT INTO `VLANValidID` VALUES (3955);
INSERT INTO `VLANValidID` VALUES (3956);
INSERT INTO `VLANValidID` VALUES (3957);
INSERT INTO `VLANValidID` VALUES (3958);
INSERT INTO `VLANValidID` VALUES (3959);
INSERT INTO `VLANValidID` VALUES (3960);
INSERT INTO `VLANValidID` VALUES (3961);
INSERT INTO `VLANValidID` VALUES (3962);
INSERT INTO `VLANValidID` VALUES (3963);
INSERT INTO `VLANValidID` VALUES (3964);
INSERT INTO `VLANValidID` VALUES (3965);
INSERT INTO `VLANValidID` VALUES (3966);
INSERT INTO `VLANValidID` VALUES (3967);
INSERT INTO `VLANValidID` VALUES (3968);
INSERT INTO `VLANValidID` VALUES (3969);
INSERT INTO `VLANValidID` VALUES (3970);
INSERT INTO `VLANValidID` VALUES (3971);
INSERT INTO `VLANValidID` VALUES (3972);
INSERT INTO `VLANValidID` VALUES (3973);
INSERT INTO `VLANValidID` VALUES (3974);
INSERT INTO `VLANValidID` VALUES (3975);
INSERT INTO `VLANValidID` VALUES (3976);
INSERT INTO `VLANValidID` VALUES (3977);
INSERT INTO `VLANValidID` VALUES (3978);
INSERT INTO `VLANValidID` VALUES (3979);
INSERT INTO `VLANValidID` VALUES (3980);
INSERT INTO `VLANValidID` VALUES (3981);
INSERT INTO `VLANValidID` VALUES (3982);
INSERT INTO `VLANValidID` VALUES (3983);
INSERT INTO `VLANValidID` VALUES (3984);
INSERT INTO `VLANValidID` VALUES (3985);
INSERT INTO `VLANValidID` VALUES (3986);
INSERT INTO `VLANValidID` VALUES (3987);
INSERT INTO `VLANValidID` VALUES (3988);
INSERT INTO `VLANValidID` VALUES (3989);
INSERT INTO `VLANValidID` VALUES (3990);
INSERT INTO `VLANValidID` VALUES (3991);
INSERT INTO `VLANValidID` VALUES (3992);
INSERT INTO `VLANValidID` VALUES (3993);
INSERT INTO `VLANValidID` VALUES (3994);
INSERT INTO `VLANValidID` VALUES (3995);
INSERT INTO `VLANValidID` VALUES (3996);
INSERT INTO `VLANValidID` VALUES (3997);
INSERT INTO `VLANValidID` VALUES (3998);
INSERT INTO `VLANValidID` VALUES (3999);
INSERT INTO `VLANValidID` VALUES (4000);
INSERT INTO `VLANValidID` VALUES (4001);
INSERT INTO `VLANValidID` VALUES (4002);
INSERT INTO `VLANValidID` VALUES (4003);
INSERT INTO `VLANValidID` VALUES (4004);
INSERT INTO `VLANValidID` VALUES (4005);
INSERT INTO `VLANValidID` VALUES (4006);
INSERT INTO `VLANValidID` VALUES (4007);
INSERT INTO `VLANValidID` VALUES (4008);
INSERT INTO `VLANValidID` VALUES (4009);
INSERT INTO `VLANValidID` VALUES (4010);
INSERT INTO `VLANValidID` VALUES (4011);
INSERT INTO `VLANValidID` VALUES (4012);
INSERT INTO `VLANValidID` VALUES (4013);
INSERT INTO `VLANValidID` VALUES (4014);
INSERT INTO `VLANValidID` VALUES (4015);
INSERT INTO `VLANValidID` VALUES (4016);
INSERT INTO `VLANValidID` VALUES (4017);
INSERT INTO `VLANValidID` VALUES (4018);
INSERT INTO `VLANValidID` VALUES (4019);
INSERT INTO `VLANValidID` VALUES (4020);
INSERT INTO `VLANValidID` VALUES (4021);
INSERT INTO `VLANValidID` VALUES (4022);
INSERT INTO `VLANValidID` VALUES (4023);
INSERT INTO `VLANValidID` VALUES (4024);
INSERT INTO `VLANValidID` VALUES (4025);
INSERT INTO `VLANValidID` VALUES (4026);
INSERT INTO `VLANValidID` VALUES (4027);
INSERT INTO `VLANValidID` VALUES (4028);
INSERT INTO `VLANValidID` VALUES (4029);
INSERT INTO `VLANValidID` VALUES (4030);
INSERT INTO `VLANValidID` VALUES (4031);
INSERT INTO `VLANValidID` VALUES (4032);
INSERT INTO `VLANValidID` VALUES (4033);
INSERT INTO `VLANValidID` VALUES (4034);
INSERT INTO `VLANValidID` VALUES (4035);
INSERT INTO `VLANValidID` VALUES (4036);
INSERT INTO `VLANValidID` VALUES (4037);
INSERT INTO `VLANValidID` VALUES (4038);
INSERT INTO `VLANValidID` VALUES (4039);
INSERT INTO `VLANValidID` VALUES (4040);
INSERT INTO `VLANValidID` VALUES (4041);
INSERT INTO `VLANValidID` VALUES (4042);
INSERT INTO `VLANValidID` VALUES (4043);
INSERT INTO `VLANValidID` VALUES (4044);
INSERT INTO `VLANValidID` VALUES (4045);
INSERT INTO `VLANValidID` VALUES (4046);
INSERT INTO `VLANValidID` VALUES (4047);
INSERT INTO `VLANValidID` VALUES (4048);
INSERT INTO `VLANValidID` VALUES (4049);
INSERT INTO `VLANValidID` VALUES (4050);
INSERT INTO `VLANValidID` VALUES (4051);
INSERT INTO `VLANValidID` VALUES (4052);
INSERT INTO `VLANValidID` VALUES (4053);
INSERT INTO `VLANValidID` VALUES (4054);
INSERT INTO `VLANValidID` VALUES (4055);
INSERT INTO `VLANValidID` VALUES (4056);
INSERT INTO `VLANValidID` VALUES (4057);
INSERT INTO `VLANValidID` VALUES (4058);
INSERT INTO `VLANValidID` VALUES (4059);
INSERT INTO `VLANValidID` VALUES (4060);
INSERT INTO `VLANValidID` VALUES (4061);
INSERT INTO `VLANValidID` VALUES (4062);
INSERT INTO `VLANValidID` VALUES (4063);
INSERT INTO `VLANValidID` VALUES (4064);
INSERT INTO `VLANValidID` VALUES (4065);
INSERT INTO `VLANValidID` VALUES (4066);
INSERT INTO `VLANValidID` VALUES (4067);
INSERT INTO `VLANValidID` VALUES (4068);
INSERT INTO `VLANValidID` VALUES (4069);
INSERT INTO `VLANValidID` VALUES (4070);
INSERT INTO `VLANValidID` VALUES (4071);
INSERT INTO `VLANValidID` VALUES (4072);
INSERT INTO `VLANValidID` VALUES (4073);
INSERT INTO `VLANValidID` VALUES (4074);
INSERT INTO `VLANValidID` VALUES (4075);
INSERT INTO `VLANValidID` VALUES (4076);
INSERT INTO `VLANValidID` VALUES (4077);
INSERT INTO `VLANValidID` VALUES (4078);
INSERT INTO `VLANValidID` VALUES (4079);
INSERT INTO `VLANValidID` VALUES (4080);
INSERT INTO `VLANValidID` VALUES (4081);
INSERT INTO `VLANValidID` VALUES (4082);
INSERT INTO `VLANValidID` VALUES (4083);
INSERT INTO `VLANValidID` VALUES (4084);
INSERT INTO `VLANValidID` VALUES (4085);
INSERT INTO `VLANValidID` VALUES (4086);
INSERT INTO `VLANValidID` VALUES (4087);
INSERT INTO `VLANValidID` VALUES (4088);
INSERT INTO `VLANValidID` VALUES (4089);
INSERT INTO `VLANValidID` VALUES (4090);
INSERT INTO `VLANValidID` VALUES (4091);
INSERT INTO `VLANValidID` VALUES (4092);
INSERT INTO `VLANValidID` VALUES (4093);
INSERT INTO `VLANValidID` VALUES (4094);
/*!40000 ALTER TABLE `VLANValidID` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VS`
--

DROP TABLE IF EXISTS `VS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VS` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VS`
--
-- ORDER BY:  `id`

LOCK TABLES `VS` WRITE;
/*!40000 ALTER TABLE `VS` DISABLE KEYS */;
/*!40000 ALTER TABLE `VS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VSEnabledIPs`
--

DROP TABLE IF EXISTS `VSEnabledIPs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VSEnabledIPs` (
  `object_id` int(10) unsigned NOT NULL,
  `vs_id` int(10) unsigned NOT NULL,
  `vip` varbinary(16) NOT NULL,
  `rspool_id` int(10) unsigned NOT NULL,
  `prio` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`object_id`,`vs_id`,`vip`,`rspool_id`),
  KEY `vip` (`vip`),
  KEY `VSEnabledIPs-FK-vs_id-vip` (`vs_id`,`vip`),
  KEY `VSEnabledIPs-FK-rspool_id` (`rspool_id`),
  CONSTRAINT `VSEnabledIPs-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE,
  CONSTRAINT `VSEnabledIPs-FK-rspool_id` FOREIGN KEY (`rspool_id`) REFERENCES `IPv4RSPool` (`id`) ON DELETE CASCADE,
  CONSTRAINT `VSEnabledIPs-FK-vs_id-vip` FOREIGN KEY (`vs_id`, `vip`) REFERENCES `VSIPs` (`vs_id`, `vip`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VSEnabledIPs`
--
-- ORDER BY:  `object_id`,`vs_id`,`vip`,`rspool_id`

LOCK TABLES `VSEnabledIPs` WRITE;
/*!40000 ALTER TABLE `VSEnabledIPs` DISABLE KEYS */;
/*!40000 ALTER TABLE `VSEnabledIPs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VSEnabledPorts`
--

DROP TABLE IF EXISTS `VSEnabledPorts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VSEnabledPorts` (
  `object_id` int(10) unsigned NOT NULL,
  `vs_id` int(10) unsigned NOT NULL,
  `proto` enum('TCP','UDP','MARK') COLLATE utf8_unicode_ci NOT NULL,
  `vport` int(10) unsigned NOT NULL,
  `rspool_id` int(10) unsigned NOT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`object_id`,`vs_id`,`proto`,`vport`,`rspool_id`),
  KEY `VSEnabledPorts-FK-vs_id-proto-vport` (`vs_id`,`proto`,`vport`),
  KEY `VSEnabledPorts-FK-rspool_id` (`rspool_id`),
  CONSTRAINT `VSEnabledPorts-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE,
  CONSTRAINT `VSEnabledPorts-FK-rspool_id` FOREIGN KEY (`rspool_id`) REFERENCES `IPv4RSPool` (`id`) ON DELETE CASCADE,
  CONSTRAINT `VSEnabledPorts-FK-vs_id-proto-vport` FOREIGN KEY (`vs_id`, `proto`, `vport`) REFERENCES `VSPorts` (`vs_id`, `proto`, `vport`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VSEnabledPorts`
--
-- ORDER BY:  `object_id`,`vs_id`,`proto`,`vport`,`rspool_id`

LOCK TABLES `VSEnabledPorts` WRITE;
/*!40000 ALTER TABLE `VSEnabledPorts` DISABLE KEYS */;
/*!40000 ALTER TABLE `VSEnabledPorts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VSIPs`
--

DROP TABLE IF EXISTS `VSIPs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VSIPs` (
  `vs_id` int(10) unsigned NOT NULL,
  `vip` varbinary(16) NOT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`vs_id`,`vip`),
  KEY `vip` (`vip`),
  CONSTRAINT `VSIPs-vs_id` FOREIGN KEY (`vs_id`) REFERENCES `VS` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VSIPs`
--
-- ORDER BY:  `vs_id`,`vip`

LOCK TABLES `VSIPs` WRITE;
/*!40000 ALTER TABLE `VSIPs` DISABLE KEYS */;
/*!40000 ALTER TABLE `VSIPs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VSPorts`
--

DROP TABLE IF EXISTS `VSPorts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VSPorts` (
  `vs_id` int(10) unsigned NOT NULL,
  `proto` enum('TCP','UDP','MARK') COLLATE utf8_unicode_ci NOT NULL,
  `vport` int(10) unsigned NOT NULL,
  `vsconfig` text COLLATE utf8_unicode_ci,
  `rsconfig` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`vs_id`,`proto`,`vport`),
  KEY `proto-vport` (`proto`,`vport`),
  CONSTRAINT `VS-vs_id` FOREIGN KEY (`vs_id`) REFERENCES `VS` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VSPorts`
--
-- ORDER BY:  `vs_id`,`proto`,`vport`

LOCK TABLES `VSPorts` WRITE;
/*!40000 ALTER TABLE `VSPorts` DISABLE KEYS */;
/*!40000 ALTER TABLE `VSPorts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `Location`
--

/*!50001 DROP TABLE IF EXISTS `Location`*/;
/*!50001 DROP VIEW IF EXISTS `Location`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `Location` AS select `O`.`id` AS `id`,`O`.`name` AS `name`,`O`.`has_problems` AS `has_problems`,`O`.`comment` AS `comment`,`P`.`id` AS `parent_id`,`P`.`name` AS `parent_name` from (`Object` `O` left join (`Object` `P` join `EntityLink` `EL` on(((`EL`.`parent_entity_id` = `P`.`id`) and (`P`.`objtype_id` = 1562) and (`EL`.`parent_entity_type` = 'location') and (`EL`.`child_entity_type` = 'location')))) on((`EL`.`child_entity_id` = `O`.`id`))) where (`O`.`objtype_id` = 1562) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Rack`
--

/*!50001 DROP TABLE IF EXISTS `Rack`*/;
/*!50001 DROP VIEW IF EXISTS `Rack`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `Rack` AS select `O`.`id` AS `id`,`O`.`name` AS `name`,`O`.`asset_no` AS `asset_no`,`O`.`has_problems` AS `has_problems`,`O`.`comment` AS `comment`,`AV_H`.`uint_value` AS `height`,`AV_S`.`uint_value` AS `sort_order`,`RT`.`thumb_data` AS `thumb_data`,`R`.`id` AS `row_id`,`R`.`name` AS `row_name`,`L`.`id` AS `location_id`,`L`.`name` AS `location_name` from (((((((`Object` `O` left join `AttributeValue` `AV_H` on(((`O`.`id` = `AV_H`.`object_id`) and (`AV_H`.`attr_id` = 27)))) left join `AttributeValue` `AV_S` on(((`O`.`id` = `AV_S`.`object_id`) and (`AV_S`.`attr_id` = 29)))) left join `RackThumbnail` `RT` on((`O`.`id` = `RT`.`rack_id`))) left join `EntityLink` `RL` on(((`O`.`id` = `RL`.`child_entity_id`) and (`RL`.`parent_entity_type` = 'row') and (`RL`.`child_entity_type` = 'rack')))) join `Object` `R` on((`R`.`id` = `RL`.`parent_entity_id`))) left join `EntityLink` `LL` on(((`R`.`id` = `LL`.`child_entity_id`) and (`LL`.`parent_entity_type` = 'location') and (`LL`.`child_entity_type` = 'row')))) left join `Object` `L` on((`L`.`id` = `LL`.`parent_entity_id`))) where (`O`.`objtype_id` = 1560) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `RackObject`
--

/*!50001 DROP TABLE IF EXISTS `RackObject`*/;
/*!50001 DROP VIEW IF EXISTS `RackObject`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `RackObject` AS select `Object`.`id` AS `id`,`Object`.`name` AS `name`,`Object`.`label` AS `label`,`Object`.`objtype_id` AS `objtype_id`,`Object`.`asset_no` AS `asset_no`,`Object`.`has_problems` AS `has_problems`,`Object`.`comment` AS `comment` from `Object` where (`Object`.`objtype_id` not in (1560,1561,1562)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Row`
--

/*!50001 DROP TABLE IF EXISTS `Row`*/;
/*!50001 DROP VIEW IF EXISTS `Row`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `Row` AS select `O`.`id` AS `id`,`O`.`name` AS `name`,`L`.`id` AS `location_id`,`L`.`name` AS `location_name` from ((`Object` `O` left join `EntityLink` `EL` on(((`O`.`id` = `EL`.`child_entity_id`) and (`EL`.`parent_entity_type` = 'location') and (`EL`.`child_entity_type` = 'row')))) left join `Object` `L` on(((`EL`.`parent_entity_id` = `L`.`id`) and (`L`.`objtype_id` = 1562)))) where (`O`.`objtype_id` = 1561) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-11-29 22:31:08
