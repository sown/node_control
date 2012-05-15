-- vim: ts=3
SET character_set_client = utf8;

DROP TABLE IF EXISTS `node_admins`;
DROP TABLE IF EXISTS `node_deployments`;
DROP TABLE IF EXISTS `interfaces`;
DROP TABLE IF EXISTS `network_adapters`;
DROP TABLE IF EXISTS `nodes`;
DROP TABLE IF EXISTS `vpn_endpoints`;
DROP TABLE IF EXISTS `vpn_servers`;
DROP TABLE IF EXISTS `certificates`;

CREATE TABLE `certificates` (
	`id`					int(11)							NOT NULL auto_increment											COMMENT 'id of the certificate',
	`public_key`			blob							NOT NULL														COMMENT 'public key',
	`private_key`			blob							NOT NULL														COMMENT 'private key',
	`current`				tinyint(1)						NOT NULL														COMMENT 'is the certificate current',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',

	PRIMARY KEY	 (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='certificates';

CREATE TABLE `vpn_servers` (
	`id`					int(11)							NOT NULL auto_increment											COMMENT 'id',
	`name`					varchar(255)					default NULL	           										COMMENT 'short name (eg. auth)',
	`external_ipv4`			varchar(15)						default NULL           											COMMENT 'external (ECS)	 IPv4 address',
	`internal_ipv4`			varchar(15)						default NULL           											COMMENT 'internal (SOWN) IPv4 address',
	`external_ipv6`			varchar(39)						default NULL           											COMMENT 'external (ECS)	 IPv6 address',
	`internal_ipv6`			varchar(39)						default NULL           											COMMENT 'internal (SOWN) IPv6 address',
	`ipv4_addr`				varchar(15)						NOT NULL														COMMENT 'IPv4 address',
	`ipv4_addr_cidr`		int(2)							NOT NULL														COMMENT 'IPv6 address cidr prefix size (eg 24)',
	`ipv6_addr`				varchar(39)						NOT NULL														COMMENT 'IPv6 address',
	`ipv6_addr_cidr`		int(3)							NOT NULL														COMMENT 'IPv6 address cidr prefix size (eg 48)',
	`port_start`			int(5)							default NULL           											COMMENT '',
	`port_end`				int(5)							default NULL           											COMMENT '',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',
	
	PRIMARY KEY	 (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vpn_endpoints` (
	`id`					int(11)							NOT NULL auto_increment											COMMENT 'endpoint id',
	`vpn_server_id`			int(11)							default NULL													COMMENT 'id of the server',
	`port`					int(5)							default NULL													COMMENT 'port to use',
	`protocol`				enum('tcp','udp')				default 'udp'													COMMENT 'protocol to use',
	`ipv4_addr`				varchar(15)						NOT NULL														COMMENT 'IPv4 address',
	`ipv4_addr_cidr`		int(2)							NOT NULL														COMMENT 'IPv6 address cidr prefix size (eg 24)',
	`ipv6_addr`				varchar(39)						NOT NULL														COMMENT 'IPv6 address',
	`ipv6_addr_cidr`		int(3)							NOT NULL														COMMENT 'IPv6 address cidr prefix size (eg 48)',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',
	
	PRIMARY KEY	 (`id`),
	KEY `vpn_server_id` (`vpn_server_id`),
	CONSTRAINT `vpn_endpoint_to_vpn_server`	FOREIGN KEY (`vpn_server_id`)	REFERENCES `vpn_servers` (`id`)	ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `nodes` (
	`id`					int(11)							NOT NULL auto_increment											COMMENT 'id of the node',
	`vpn_endpoint_id`		int(11)							default NULL													COMMENT 'link to the vpn endpoints table',
	`certificate_id`		int(11)							default NULL													COMMENT 'certificate to use from certificate file',
	`box_number`			int(11)							default NULL													COMMENT 'DEPRECATED. DO NOT USE.',
	`firmware_image`		text							NOT NULL														COMMENT 'version of firmware installed on the node (e.g. Backfire 10.03)',
	`notes`					longtext						default NULL													COMMENT 'notes about the node',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',

	PRIMARY KEY	 (`id`),
	KEY `node_to_endpoint` (`vpn_endpoint_id`),
	KEY `node_to_certificate` (`certificate_id`),
	CONSTRAINT `node_to_certificate`	FOREIGN KEY (`certificate_id`)	REFERENCES `certificates` (`id`),
	CONSTRAINT `node_to_endpoint`		FOREIGN KEY (`vpn_endpoint_id`)	REFERENCES `vpn_endpoints` (`id`)	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `network_adapters` (
	`id`					int(11)				  			NOT NULL auto_increment											COMMENT 'id of the adapter',
	`node_id`				int(11)				  	  		NOT NULL														COMMENT 'node the adapter is installed into',
	`mac`					text				  	  		NOT NULL														COMMENT 'mac address of the adapter',
	`wireless_channel`		tinyint(4)			  			NOT NULL														COMMENT 'wireless channel of the adapter. 0 for non-wireless adapters.',
	`type`					text				  	 		NOT NULL														COMMENT 'type of adapter e.g. a/b/g/n/1000M/100M/10M',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',

	PRIMARY KEY	 (`id`),
	KEY `interface_node` (`node_id`),
	CONSTRAINT `interface_node`	FOREIGN KEY (`node_id`)	REFERENCES `nodes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='networking adapters';

CREATE TABLE `interfaces` (
	`id`					int(11)							NOT NULL auto_increment											COMMENT 'id of the interface',
	`node_id`				int(11)							NOT NULL														COMMENT 'id of the node the interface is on',
	`ipv4_addr`				varchar(15)						NOT NULL														COMMENT 'IPv4 address',
	`ipv4_addr_cidr`		int(2)							NOT NULL														COMMENT 'IPv6 address cidr prefix size (eg 24)',
	`ipv6_addr`				varchar(39)						NOT NULL														COMMENT 'IPv6 address',
	`ipv6_addr_cidr`		int(3)							NOT NULL														COMMENT 'IPv6 address cidr prefix size (eg 48)',
	`name`					text							NOT NULL														COMMENT 'name of the interface',
	`ssid`					text							NOT NULL														COMMENT 'ssid of the interface',
	`network_adapter_id`	int(11)							NOT NULL														COMMENT 'id of the network adapter the interface uses',
	`type`					enum('dhcp','bridge','static')	NOT NULL														COMMENT 'type of config DHCP,BRIDGE,STATIC',
	`offer_dhcp`			tinyint(1)						NOT NULL														COMMENT 'does the interface offer DHCP',
	`is_1x`					tinyint(1)						NOT NULL														COMMENT 'is the interface 802.1x encrypted',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',

	PRIMARY KEY	 (`id`),
	KEY `interface_to_node` (`node_id`),
	KEY `interface_to_adapter` (`network_adapter_id`),
	CONSTRAINT `interface_to_adapter`	FOREIGN KEY (`network_adapter_id`)	REFERENCES `network_adapters` (`id`),
	CONSTRAINT `interface_to_node`		FOREIGN KEY (`node_id`)				REFERENCES `nodes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='interfaces installed on node';

CREATE TABLE `node_deployments` (
	`id`					int(11)							NOT NULL auto_increment											COMMENT 'node deployment id',
	`node_id`				int(11)							NOT NULL														COMMENT 'the id of the node being deployed',
	`name`					varchar(255)					default NULL													COMMENT 'node deployment name',
	`is_development`		tinyint(1)						NOT NULL default '0'											COMMENT 'is a development node',
	`is_private`			tinyint(1)						NOT NULL default '0'											COMMENT 'is a private node (dont display on wiki/maps)',
	`firewall`				tinyint(1)						NOT NULL default '0'											COMMENT 'is the firewall enabled',
	`advanced_firewall`		tinyint(1)						NOT NULL default '0'											COMMENT 'is the advanced firewall enabled',
	`cap`					bigint(20)						NOT NULL default '0'											COMMENT 'bandwidth cap per month in MB',
	`start_date`			timestamp						NOT NULL default '0000-00-00 00:00:00'							COMMENT 'start date of the deployment',
	`end_date`				timestamp						NOT NULL default '0000-00-00 00:00:00'							COMMENT 'end date of the deployment',
	`range`					int(11)							NOT NULL default '20'											COMMENT 'range of the circle to draw on google maps',
	`allowed_ports`			varchar(255)					default NULL													COMMENT 'DEPRECATED. DO NOT USE.',
	`type`					enum('campus','home')			default 'home'													COMMENT 'type of deployment',
	`url`					text																							COMMENT 'url associated with the deployment (eg http://example.com/my-pub-website)',
	`longitude`				decimal(14,7)					default NULL													COMMENT 'longitude of the deployment',
	`latitude`				decimal(14,7)					default NULL													COMMENT 'latitude of the deployment',
	`address`				text																							COMMENT 'postal adress of the deployment',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',

	PRIMARY KEY	 (`id`),
	KEY `deployment_to_node` (`node_id`),
	CONSTRAINT `deployment_to_node`	FOREIGN KEY	(`node_id`)	REFERENCES `nodes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `node_admins` (
	`id`					int(11)							NOT NULL auto_increment											COMMENT 'admin id',
	`user_id`				int(11)							NOT NULL														COMMENT 'link to users table in other database',
	`node_deployment_id`	int(11)							NOT NULL														COMMENT 'id of the node deployment',
	`start_date`			timestamp						NOT NULL default '0000-00-00 00:00:00'							COMMENT 'start date that the admin is admin of the node',
	`end_date`				timestamp						NOT NULL default '0000-00-00 00:00:00'							COMMENT 'end date that the admin is admin of the node',
	`last_modified`			timestamp						NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP	COMMENT 'time the row was last modified',

	PRIMARY KEY	 (`id`),
	KEY `admin_to_deployment` (`node_deployment_id`),
	CONSTRAINT `admin_to_deployment`	FOREIGN KEY (`node_deployment_id`)	REFERENCES `node_deployments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `vpn_servers` (id,name,external_ipv4,internal_ipv4,external_ipv6,internal_ipv6,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,port_start,port_end) VALUES (1,'sown-vpn.ecs.soton.ac.uk','152.78.189.83','10.13.0.253','2001:630:d0:f104::5032:253','2001:630:d0:f700::253','10.13.128.0',17,'2001:630:d0:f780::',57,5000,5200);
INSERT INTO `vpn_servers` (id,name,external_ipv4,internal_ipv4,external_ipv6,internal_ipv6,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,port_start,port_end) VALUES (2,'sown-dev.ecs.soton.ac.uk','152.78.189.39','10.13.0.250','2001:630:d0:f104::5032:250','2001:630:d0:f700::250','10.13.112.0',20,'2001:630:d0:f770::',60,5000,5200);

INSERT INTO `certificates` (id,public_key,private_key,current) VALUES (1,'','',true);
INSERT INTO `certificates` (id,public_key,private_key,current) VALUES (2,'','',true);
INSERT INTO `certificates` (id,public_key,private_key,current) VALUES (3,'','',true);
INSERT INTO `certificates` (id,public_key,private_key,current) VALUES (4,'','',true);
INSERT INTO `certificates` (id,public_key,private_key,current) VALUES (5,'','',true);

INSERT INTO `vpn_endpoints` (id,vpn_server_id,port,protocol,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr) VALUES (1,1,5035,'udp','10.13.128.148',30,'2001:630:d0:f780::94',126);
INSERT INTO `vpn_endpoints` (id,vpn_server_id,port,protocol,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr) VALUES (2,2,5001,'udp','10.13.112.4',30,'2001:630:d0:f770::4',126);
INSERT INTO `vpn_endpoints` (id,vpn_server_id,port,protocol,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr) VALUES (3,2,5002,'udp','10.13.112.8',30,'2001:630:d0:f770::8',126);
INSERT INTO `vpn_endpoints` (id,vpn_server_id,port,protocol,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr) VALUES (4,1,5002,'udp','10.13.128.0',30,'2001:630:d0:f780::',126);
INSERT INTO `vpn_endpoints` (id,vpn_server_id,port,protocol,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr) VALUES (5,1,5003,'udp','10.13.128.4',30,'2001:630:d0:f780::4',126);

INSERT INTO `nodes` (id,vpn_endpoint_id,certificate_id,box_number,firmware_image,notes) VALUES (1,1,1,900,'',NULL);
INSERT INTO `nodes` (id,vpn_endpoint_id,certificate_id,box_number,firmware_image,notes) VALUES (2,2,2,901,'',NULL);
INSERT INTO `nodes` (id,vpn_endpoint_id,certificate_id,box_number,firmware_image,notes) VALUES (3,3,3,902,'',NULL);
INSERT INTO `nodes` (id,vpn_endpoint_id,certificate_id,box_number,firmware_image,notes) VALUES (4,4,4,903,'',NULL);
INSERT INTO `nodes` (id,vpn_endpoint_id,certificate_id,box_number,firmware_image,notes) VALUES (5,5,5,904,'',NULL);

INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (1,1,'00:11:5b:e4:7e:cb','0','100M');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (2,1,'00:0b:6b:56:2e:7e','1','n');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (3,2,'00:0d:b9:20:a4:55','0','100M');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (4,2,'00:20:6a:15:67:3b','6','n');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (5,3,'00:0d:b9:20:a5:66','0','100M');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (6,3,'00:20:6a:15:67:3c','11','n');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (7,4,'00:0d:b9:20:a6:77','0','100M');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (8,4,'00:20:6a:15:67:3d','1','n');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (9,5,'00:0d:b9:20:a7:88','0','100M');
INSERT INTO `network_adapters` (id,node_id,mac,wireless_channel,type) VALUES (10,5,'00:20:6a:15:67:3e','6','n');

INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (1,1,'',0,'',0,'eth0','',1,'DHCP',0,0);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (2,1,'10.0.1.254',24,'2001:630:d0:f701::1',64,'wlan0','SOWN',2,'DHCP',1,1);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (3,2,'',0,'',0,'eth0','',3,'DHCP',0,0);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (4,2,'10.0.113.254',24,'2001:630:d0:f771::1',64,'wlan0','teduroam',4,'DHCP',1,1);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (5,3,'',0,'',0,'eth0','',5,'DHCP',0,0);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (6,3,'10.0.114.254',24,'2001:630:d0:f772::1:',64,'wlan0','beduroam',6,'DHCP',1,1);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (7,4,'',0,'',0,'eth0','',7,'DHCP',0,0);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (8,4,'10.0.151.254',24,'2001:630:d0:f797::1',64,'wlan0','feeduroam',8,'DHCP',1,1);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (9,5,'',0,'',0,'eth0','',9,'DHCP',0,0);
INSERT INTO `interfaces` (id,node_id,ipv4_addr,ipv4_addr_cidr,ipv6_addr,ipv6_addr_cidr,name,ssid,network_adapter_id,type,offer_dhcp,is_1x) VALUES (10,5,'10.0.152.254',24,'2001:630:d0:f798::1',64,'wlan0','qeduroam',10,'DHCP',1,1);

INSERT INTO `node_deployments` (id,node_id,name,is_development,is_private,firewall,advanced_firewall,cap,start_date,end_date,`range`,allowed_ports,type,url,longitude,latitude,address) VALUES (1,1,'London Avenue',1,0,0,0,0,'2011-12-13 17:06:28','0000-00-00 00:00:00',20,NULL,'home',NULL,'-1.07','50.9','');
INSERT INTO `node_deployments` (id,node_id,name,is_development,is_private,firewall,advanced_firewall,cap,start_date,end_date,`range`,allowed_ports,type,url,longitude,latitude,address) VALUES (2,2,'Paris Avenue',1,0,0,0,0,'2011-12-13 17:06:28','0000-00-00 00:00:00',20,NULL,'home',NULL,'-1.07','50.9','');
INSERT INTO `node_deployments` (id,node_id,name,is_development,is_private,firewall,advanced_firewall,cap,start_date,end_date,`range`,allowed_ports,type,url,longitude,latitude,address) VALUES (3,3,'Madrid Avenue',1,0,0,0,0,'2011-12-13 17:06:28','0000-00-00 00:00:00',20,NULL,'home',NULL,'-1.07','50.9','');
INSERT INTO `node_deployments` (id,node_id,name,is_development,is_private,firewall,advanced_firewall,cap,start_date,end_date,`range`,allowed_ports,type,url,longitude,latitude,address) VALUES (4,4,'Rome Avenue',1,0,0,0,0,'2011-12-13 17:06:28','0000-00-00 00:00:00',20,NULL,'home',NULL,'-1.07','50.9','');
INSERT INTO `node_deployments` (id,node_id,name,is_development,is_private,firewall,advanced_firewall,cap,start_date,end_date,`range`,allowed_ports,type,url,longitude,latitude,address) VALUES (5,5,'Lisbon Avenue',1,0,0,0,0,'2011-12-13 17:06:28','0000-00-00 00:00:00',20,NULL,'home',NULL,'-1.07','50.9','');



