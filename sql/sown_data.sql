-- MySQL dump 10.13  Distrib 5.1.73, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sown_data
-- ------------------------------------------------------
-- Server version	5.1.73-0ubuntu0.10.04.1

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
-- Table structure for table `certificate_sets`
--

DROP TABLE IF EXISTS `certificate_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificate_sets` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `setid` int(11) NOT NULL COMMENT 'identifier for a certificate set',
  `certificate_id` int(11) NOT NULL COMMENT 'id of the certificate',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_in_set` (`certificate_id`,`setid`),
  KEY `certificate_id` (`certificate_id`),
  KEY `setid` (`setid`),
  CONSTRAINT `certificate_set_to_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='set of certificates';
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `certificate_authority` text,
  `current` tinyint(1) NOT NULL COMMENT 'is the certificate current',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='certificates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cron_jobs`
--

DROP TABLE IF EXISTS `cron_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `command` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `misc` varchar(8191) DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT '0',
  `required` int(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `nfsen_name` varchar(19) DEFAULT NULL,
  `is_development` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is a development node',
  `is_private` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is a private node (dont display on wiki/maps)',
  `firewall` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is the firewall enabled',
  `advanced_firewall` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is the advanced firewall enabled',
  `cap` bigint(20) NOT NULL DEFAULT '0' COMMENT 'bandwidth cap per month in MB',
  `cap_exceeded` tinyint(1) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'start date of the deployment',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'end date of the deployment',
  `radius` int(11) DEFAULT '20',
  `allowed_ports` varchar(255) DEFAULT NULL COMMENT 'DEPRECATED. DO NOT USE.',
  `type` enum('campus','home') DEFAULT 'home',
  `url` text COMMENT 'url associated with the deployment (eg http://example.com/my-pub-website)',
  `longitude` decimal(14,7) DEFAULT NULL COMMENT 'longitude of the deployment',
  `latitude` decimal(14,7) DEFAULT NULL COMMENT 'latitude of the deployment',
  `address` text COMMENT 'postal adress of the deployment',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `enabled_enquiry_types`
--

DROP TABLE IF EXISTS `enabled_enquiry_types`;
/*!50001 DROP VIEW IF EXISTS `enabled_enquiry_types`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `enabled_enquiry_types` (
 `id` tinyint NOT NULL,
  `title` tinyint NOT NULL,
  `description` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `enabled_message` tinyint NOT NULL,
  `disabled` tinyint NOT NULL,
  `disabled_message` tinyint NOT NULL,
  `last_modified` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `enquiries`
--

DROP TABLE IF EXISTS `enquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `date_sent` datetime NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `response_summary` varchar(255) DEFAULT NULL,
  `response` text,
  `acknowledged_until` datetime DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enquiry_types`
--

DROP TABLE IF EXISTS `enquiry_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiry_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `email` varchar(255) NOT NULL,
  `enabled_message` text NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `disabled_message` text NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `host_cron_jobs`
--

DROP TABLE IF EXISTS `host_cron_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_cron_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cron_job_id` int(11) NOT NULL,
  `server_id` int(11) DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `aggregate` varchar(255) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
  `disabled` tinyint(1) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `interface_to_node` (`node_id`),
  KEY `interface_to_adapter` (`network_adapter_id`),
  CONSTRAINT `interface_to_adapter` FOREIGN KEY (`network_adapter_id`) REFERENCES `network_adapters` (`id`),
  CONSTRAINT `interface_to_node` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='interfaces installed on node';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `written_off` datetime NOT NULL,
  `hardware_desc` text NOT NULL,
  `price` varchar(24) DEFAULT NULL,
  `location` varchar(255) NOT NULL DEFAULT 'PENDING',
  `photo` mediumblob,
  `link_to_wiki` varchar(235) NOT NULL,
  `added_by` varchar(235) NOT NULL,
  `purchased_on` datetime NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `architecture` varchar(255) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table stores a list of items held by SOWN';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL COMMENT 'name for lthe ocation',
  `long_name` varchar(255) DEFAULT NULL,
  `longitude` decimal(14,7) DEFAULT NULL COMMENT 'longitude of the location',
  `latitude` decimal(14,7) DEFAULT NULL COMMENT 'latitude of the location',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='locations of things';
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='networking adapters';
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `node_requests`
--

DROP TABLE IF EXISTS `node_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year` varchar(5) DEFAULT NULL,
  `houseno` varchar(100) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `postcode` varchar(8) DEFAULT NULL,
  `facilities` text,
  `timestamp` datetime DEFAULT NULL,
  `lat` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT NULL,
  `notes` text,
  `deployment_id` int(11) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `node_setup_requests`
--

DROP TABLE IF EXISTS `node_setup_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_setup_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the node setup request',
  `nonce` varchar(128) DEFAULT NULL COMMENT 'nonce generated by the node',
  `mac` varchar(17) DEFAULT NULL COMMENT 'connected mac address of node',
  `requested_date` datetime NOT NULL COMMENT 'initial datetime node requested setup',
  `ip_address` varchar(39) DEFAULT NULL COMMENT 'IP address node setup was requested from',
  `status` varchar(255) DEFAULT 'pending' COMMENT 'the current status or the node setup request',
  `approved_by` int(11) DEFAULT NULL COMMENT 'user who approved the request',
  `approved_date` datetime DEFAULT NULL COMMENT 'datetime user approved request',
  `password` varchar(255) DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL COMMENT 'datetime until node can no longer download its config tarball',
  `node_id` int(11) DEFAULT NULL COMMENT 'the node this request ultimately gets associated with',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `node_setup_request_to_user` (`approved_by`),
  KEY `node_setup_request_to_node` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `hardware` varchar(255) DEFAULT NULL,
  `wireless_chipset` varchar(255) DEFAULT NULL,
  `firmware_version` varchar(255) DEFAULT NULL,
  `firmware_image` text NOT NULL COMMENT 'version of firmware installed on the node (e.g. Backfire 10.03)',
  `password_hash` varchar(255) NOT NULL,
  `undeployable` tinyint(1) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `node_to_endpoint` (`vpn_endpoint_id`),
  KEY `node_to_certificate` (`certificate_id`),
  CONSTRAINT `node_to_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`),
  CONSTRAINT `node_to_endpoint` FOREIGN KEY (`vpn_endpoint_id`) REFERENCES `vpn_endpoints` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_text` text,
  `notetaker_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deployment_id` int(11) DEFAULT NULL,
  `inventory_id` int(11) DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `note_to_notetaker` (`notetaker_id`),
  KEY `note_to_user` (`user_id`),
  KEY `note_to_node` (`node_id`),
  KEY `note_to_deployment` (`deployment_id`),
  CONSTRAINT `note_to_deployment` FOREIGN KEY (`deployment_id`) REFERENCES `deployments` (`id`),
  CONSTRAINT `note_to_node` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`),
  CONSTRAINT `note_to_notetaker` FOREIGN KEY (`notetaker_id`) REFERENCES `users` (`id`),
  CONSTRAINT `note_to_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `other_hosts`
--

DROP TABLE IF EXISTS `other_hosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `other_hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) DEFAULT NULL COMMENT 'short name (eg. AUTH)',
  `type` varchar(50) DEFAULT NULL COMMENT 'the type of host (e.g. switch, link, web server etc.)',
  `parent` varchar(255) DEFAULT NULL COMMENT 'the parent of the other_host for monitoring purposes',
  `description` text COMMENT 'a descripton of the other_host',
  `acquired_date` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'the date the other_host was acquired',
  `retired` int(1) DEFAULT '0' COMMENT 'whether the other_host has been retired',
  `internal` int(1) DEFAULT '0' COMMENT 'whether the other_host is internal',
  `host_case` varchar(255) DEFAULT NULL COMMENT 'the form factor, made and model of the other_host',
  `location_id` int(11) DEFAULT NULL COMMENT 'the locatinn of the other_host',
  `hostname` varchar(255) DEFAULT NULL COMMENT 'the hostname associated with the other_host',
  `cname` varchar(255) DEFAULT NULL COMMENT 'any CNAME associated with the other_host',
  `mac` varchar(17) DEFAULT NULL,
  `ipv4_addr` varchar(17) NOT NULL COMMENT 'the IPv4 address associated with the other_host',
  `ipv6_addr` varchar(39) NOT NULL COMMENT 'the IPv6 address associated with the other_host',
  `alias` varchar(255) DEFAULT NULL COMMENT 'an alias for use by monitoring',
  `check_command` varchar(255) DEFAULT NULL COMMENT 'alternative check command for use by monitoring',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reserved_subnets`
--

DROP TABLE IF EXISTS `reserved_subnets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reserved_subnets` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the used subnet',
  `name` text NOT NULL COMMENT 'name of the used subnet',
  `ipv4_addr` varchar(15) NOT NULL,
  `ipv4_addr_cidr` int(2) NOT NULL,
  `ipv6_addr` varchar(39) NOT NULL,
  `ipv6_addr_cidr` int(3) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='used subnets that should not be allocated to nodes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `server_interfaces`
--

DROP TABLE IF EXISTS `server_interfaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_interfaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `server_id` int(11) NOT NULL COMMENT 'ther server the server_interface is on',
  `vlan_id` int(11) NOT NULL COMMENT 'the vlan the server_interface is on',
  `name` varchar(20) NOT NULL COMMENT 'the name of the server_interface as it appears in ifconfig',
  `hostname` varchar(255) NOT NULL COMMENT 'the primary hostname associated with the IPv4/IPv6 address associated with the server_interface',
  `cname` varchar(255) NOT NULL COMMENT 'any cname for the IPv4/IPv6 address associated with the server_interface',
  `mac` varchar(17) NOT NULL COMMENT 'the mac address associated with the server_interface',
  `switchport` varchar(255) NOT NULL COMMENT 'the switchport to which the server_interface is attached',
  `cable` varchar(255) NOT NULL COMMENT 'a description of the cable connecting the server_interace to the switchport',
  `ipv4_addr` varchar(17) NOT NULL COMMENT 'the IPv4 address associated with the server_interface',
  `ipv6_addr` varchar(39) NOT NULL COMMENT 'the IPv6 address associated with the server_interface',
  `subordinate` int(1) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='locations of things';
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
  `state` varchar(50) DEFAULT NULL,
  `purpose` varchar(50) DEFAULT NULL,
  `parent` varchar(255) DEFAULT NULL,
  `internal_name` varchar(255) DEFAULT NULL,
  `internal_cname` varchar(255) DEFAULT NULL,
  `icinga_name` varchar(255) DEFAULT NULL,
  `description` text,
  `location_id` int(11) DEFAULT NULL,
  `certificate_id` int(11) DEFAULT NULL COMMENT 'certificate to use from certificate file',
  `external_interface` varchar(20) DEFAULT NULL,
  `internal_interface` varchar(20) DEFAULT NULL,
  `external_mac` varchar(17) DEFAULT NULL,
  `internal_mac` varchar(17) DEFAULT NULL,
  `external_switchport` varchar(255) DEFAULT NULL,
  `internal_switchport` varchar(255) DEFAULT NULL,
  `external_cable` varchar(50) DEFAULT NULL,
  `internal_cable` varchar(50) DEFAULT NULL,
  `external_ipv4` varchar(15) DEFAULT NULL COMMENT 'external (ECS)  IPv4 address',
  `internal_ipv4` varchar(15) DEFAULT NULL COMMENT 'internal (SOWN) IPv4 address',
  `external_ipv6` varchar(39) DEFAULT NULL COMMENT 'external (ECS)  IPv6 address',
  `internal_ipv6` varchar(39) DEFAULT NULL COMMENT 'internal (SOWN) IPv6 address',
  `acquired_date` datetime DEFAULT '0000-00-00 00:00:00',
  `retired` int(1) DEFAULT '0',
  `server_case` varchar(255) DEFAULT NULL,
  `processor` varchar(255) DEFAULT NULL,
  `memory` varchar(255) DEFAULT NULL,
  `hard_drive` varchar(255) DEFAULT NULL,
  `network_ports` varchar(255) DEFAULT NULL,
  `wake_on_lan` varchar(255) DEFAULT NULL,
  `kernel` varchar(255) DEFAULT NULL,
  `os` varchar(255) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `server_to_certificate` (`certificate_id`),
  CONSTRAINT `server_to_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_login`
--

DROP TABLE IF EXISTS `stats_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `remote_ip` varchar(15) NOT NULL COMMENT 'the IP address of the user',
  `domain` varchar(255) NOT NULL COMMENT 'the domain of the user account',
  `result` varchar(7) NOT NULL COMMENT 'whether the user login was successful',
  `time_logged` datetime NOT NULL COMMENT 'the time the user attempted to login',
  `user_agent` text COMMENT 'the user agent string of the application',
  `details` text COMMENT 'any other details about the login',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user id',
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL COMMENT 'email address',
  `is_system_admin` tinyint(1) NOT NULL COMMENT 'is the user a system level admin',
  `can_access_wiki` tinyint(1) NOT NULL,
  `wiki_username` varchar(255) NOT NULL,
  `reset_password_hash` varchar(255) NOT NULL,
  `reset_password_time` timestamp NULL DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vlans`
--

DROP TABLE IF EXISTS `vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vlans` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL COMMENT 'name of the vlan',
  `prefix` varchar(20) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='vlans server_interfaces are on';
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `certificate_set_setid` int(11) DEFAULT NULL,
  `ipv4_addr` varchar(15) NOT NULL COMMENT 'IPv4 address',
  `ipv4_addr_cidr` int(2) NOT NULL COMMENT 'IPv6 address cidr prefix size (eg 24)',
  `ipv6_addr` varchar(39) NOT NULL COMMENT 'IPv6 address',
  `ipv6_addr_cidr` int(3) NOT NULL COMMENT 'IPv6 address cidr prefix size (eg 48)',
  `port_start` int(5) DEFAULT NULL,
  `port_end` int(5) DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time the row was last modified',
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`),
  KEY `vpn_server_certificate_set` (`certificate_set_setid`),
  CONSTRAINT `vpn_server_certificate_set` FOREIGN KEY (`certificate_set_setid`) REFERENCES `certificate_sets` (`setid`),
  CONSTRAINT `vpn_server_to_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `enabled_enquiry_types`
--

/*!50001 DROP TABLE IF EXISTS `enabled_enquiry_types`*/;
/*!50001 DROP VIEW IF EXISTS `enabled_enquiry_types`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `enabled_enquiry_types` AS select `enquiry_types`.`id` AS `id`,`enquiry_types`.`title` AS `title`,`enquiry_types`.`description` AS `description`,`enquiry_types`.`email` AS `email`,`enquiry_types`.`enabled_message` AS `enabled_message`,`enquiry_types`.`disabled` AS `disabled`,`enquiry_types`.`disabled_message` AS `disabled_message`,`enquiry_types`.`last_modified` AS `last_modified` from `enquiry_types` where (`enquiry_types`.`disabled` <> 1) */;
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

-- Dump completed on 2016-01-30 14:53:15
