-- MySQL dump 10.13  Distrib 5.1.63, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sown_data
-- ------------------------------------------------------
-- Server version	5.1.63-0ubuntu0.10.04.1

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
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the certificate',
  `public_key` blob,
  `private_key` blob,
  `current` tinyint(1) NOT NULL COMMENT 'is the certificate current',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='certificates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deployment_admins`
--

DROP TABLE IF EXISTS `deployment_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deployment_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'admin id',
  `user_id` int(11) NOT NULL COMMENT 'link to users table in other database',
  `deployment_id` int(11) NOT NULL COMMENT 'id of the deployment',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'start date that the admin is admin of the deployment',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'end date that the admin is admin of the deployment',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `admin_to_deployment` (`deployment_id`),
  KEY `admin_to_user` (`user_id`),
  CONSTRAINT `admin_to_deployment` FOREIGN KEY (`deployment_id`) REFERENCES `deployments` (`id`),
  CONSTRAINT `admin_to_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deployments`
--

DROP TABLE IF EXISTS `deployments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deployments` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'node deployment id',
  `name` varchar(255) DEFAULT NULL COMMENT 'node deployment name',
  `is_development` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is a development node',
  `is_private` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is a private node (dont display on wiki/maps)',
  `firewall` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is the firewall enabled',
  `advanced_firewall` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is the advanced firewall enabled',
  `cap` bigint(20) NOT NULL DEFAULT '0' COMMENT 'bandwidth cap per month in MB',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'start date of the deployment',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'end date of the deployment',
  `range` int(11) NOT NULL DEFAULT '20' COMMENT 'range of the circle to draw on google maps',
  `allowed_ports` varchar(255) DEFAULT NULL COMMENT 'DEPRECATED. DO NOT USE.',
  `type` enum('campus','home') DEFAULT 'home' COMMENT 'type of deployment',
  `url` text COMMENT 'url associated with the deployment (eg http://example.com/my-pub-website)',
  `longitude` decimal(14,7) DEFAULT NULL COMMENT 'longitude of the deployment',
  `latitude` decimal(14,7) DEFAULT NULL COMMENT 'latitude of the deployment',
  `address` text COMMENT 'postal adress of the deployment',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'admin id',
  `user_id` int(11) NOT NULL COMMENT 'link to users table in other database',
  `mac` text NOT NULL COMMENT 'mac address of the device',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `device_to_user` (`user_id`),
  CONSTRAINT `device_to_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interfaces`
--

DROP TABLE IF EXISTS `interfaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interfaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the interface',
  `node_id` int(11) NOT NULL COMMENT 'id of the node the interface is on',
  `ipv4_addr` varchar(15) NOT NULL,
  `ipv4_addr_cidr` int(2) NOT NULL,
  `ipv6_addr` varchar(39) NOT NULL,
  `ipv6_addr_cidr` int(3) NOT NULL,
  `name` text NOT NULL COMMENT 'name of the interface',
  `ssid` text NOT NULL COMMENT 'ssid of the interface',
  `network_adapter_id` int(11) NOT NULL COMMENT 'id of the network adapter the interface uses',
  `type` enum('dhcp','bridge','static') NOT NULL COMMENT 'type of config DHCP,BRIDGE,STATIC',
  `offer_dhcp` tinyint(1) NOT NULL COMMENT 'does the interface offer DHCP',
  `is_1x` tinyint(1) NOT NULL COMMENT 'is the interface 802.1x encrypted',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `interface_to_node` (`node_id`),
  KEY `interface_to_adapter` (`network_adapter_id`),
  CONSTRAINT `interface_to_adapter` FOREIGN KEY (`network_adapter_id`) REFERENCES `network_adapters` (`id`),
  CONSTRAINT `interface_to_node` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='interfaces installed on node';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `network_adapters`
--

DROP TABLE IF EXISTS `network_adapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `network_adapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the adapter',
  `node_id` int(11) NOT NULL COMMENT 'node the adapter is installed into',
  `mac` text NOT NULL COMMENT 'mac address of the adapter',
  `wireless_channel` tinyint(4) NOT NULL COMMENT 'wireless channel of the adapter. 0 for non-wireless adapters.',
  `type` text NOT NULL COMMENT 'type of adapter e.g. a/b/g/n/1000M/100M/10M',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `interface_node` (`node_id`),
  CONSTRAINT `interface_node` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='networking adapters';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `node_deployments`
--

DROP TABLE IF EXISTS `node_deployments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_deployments` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of node_deployment',
  `node_id` int(11) NOT NULL COMMENT 'node of a node deployment',
  `deployment_id` int(11) NOT NULL COMMENT 'deployment of node deployment',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'start date of the node being part of the deployment',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'end date of the node being part of the deployment',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `node_deployment_to_node` (`node_id`),
  KEY `node_deployment_to_deployment` (`deployment_id`),
  CONSTRAINT `node_deployment_to_deployment` FOREIGN KEY (`deployment_id`) REFERENCES `deployments` (`id`),
  CONSTRAINT `node_deployment_to_node` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nodes`
--

DROP TABLE IF EXISTS `nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the node',
  `vpn_endpoint_id` int(11) DEFAULT NULL COMMENT 'link to the vpn endpoints table',
  `certificate_id` int(11) DEFAULT NULL COMMENT 'certificate to use from certificate file',
  `box_number` int(11) DEFAULT NULL COMMENT 'DEPRECATED. DO NOT USE.',
  `firmware_image` text NOT NULL COMMENT 'version of firmware installed on the node (e.g. Backfire 10.03)',
  `notes` longtext COMMENT 'notes about the node',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `node_to_endpoint` (`vpn_endpoint_id`),
  KEY `node_to_certificate` (`certificate_id`),
  CONSTRAINT `node_to_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`),
  CONSTRAINT `node_to_endpoint` FOREIGN KEY (`vpn_endpoint_id`) REFERENCES `vpn_endpoints` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `type` varchar(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'short name (eg. auth)',
  `certificate_id` int(11) DEFAULT NULL COMMENT 'certificate to use from certificate file',
  `external_ipv4` varchar(15) DEFAULT NULL COMMENT 'external (ECS)  IPv4 address',
  `internal_ipv4` varchar(15) DEFAULT NULL COMMENT 'internal (SOWN) IPv4 address',
  `external_ipv6` varchar(39) DEFAULT NULL COMMENT 'external (ECS)  IPv6 address',
  `internal_ipv6` varchar(39) DEFAULT NULL COMMENT 'internal (SOWN) IPv6 address',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `server_to_certificate` (`certificate_id`),
  CONSTRAINT `server_to_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user id',
  `email` varchar(255) NOT NULL COMMENT 'email address',
  `is_system_admin` tinyint(1) NOT NULL COMMENT 'is the user a system level admin',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vpn_endpoints`
--

DROP TABLE IF EXISTS `vpn_endpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vpn_endpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'endpoint id',
  `vpn_server_id` int(11) DEFAULT NULL COMMENT 'id of the server',
  `port` int(5) DEFAULT NULL COMMENT 'port to use',
  `protocol` enum('tcp','udp') DEFAULT 'udp' COMMENT 'protocol to use',
  `ipv4_addr` varchar(15) NOT NULL COMMENT 'IPv4 address',
  `ipv4_addr_cidr` int(2) NOT NULL COMMENT 'IPv6 address cidr prefix size (eg 24)',
  `ipv6_addr` varchar(39) NOT NULL COMMENT 'IPv6 address',
  `ipv6_addr_cidr` int(3) NOT NULL COMMENT 'IPv6 address cidr prefix size (eg 48)',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `vpn_server_id` (`vpn_server_id`),
  CONSTRAINT `vpn_endpoint_to_vpn_server` FOREIGN KEY (`vpn_server_id`) REFERENCES `vpn_servers` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vpn_servers`
--

DROP TABLE IF EXISTS `vpn_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vpn_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `server_id` int(11) DEFAULT NULL COMMENT 'id of the server',
  `ipv4_addr` varchar(15) NOT NULL COMMENT 'IPv4 address',
  `ipv4_addr_cidr` int(2) NOT NULL COMMENT 'IPv6 address cidr prefix size (eg 24)',
  `ipv6_addr` varchar(39) NOT NULL COMMENT 'IPv6 address',
  `ipv6_addr_cidr` int(3) NOT NULL COMMENT 'IPv6 address cidr prefix size (eg 48)',
  `port_start` int(5) DEFAULT NULL,
  `port_end` int(5) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`),
  CONSTRAINT `vpn_server_to_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-07-17 23:12:01
