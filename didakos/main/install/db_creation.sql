-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch8-log


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema {MYSQL_PREFIX}_main
--


CREATE DATABASE IF NOT EXISTS {MYSQL_PREFIX}_main DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;;
USE {MYSQL_PREFIX}_main;

CREATE TABLE `{MYSQL_PREFIX}_main`.`fchat` (
  `server` varchar(32) NOT NULL DEFAULT '',
  `group` varchar(64) NOT NULL DEFAULT '',
  `subgroup` varchar(128) NOT NULL DEFAULT '',
  `leaf` varchar(128) NOT NULL DEFAULT '',
  `leafvalue` text NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`server`,`group`,`subgroup`,`leaf`),
  KEY `server` (`server`,`group`,`subgroup`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creacion de tablas para dispositivos moviles

CREATE TABLE `{MYSQL_PREFIX}_main`.`oauth_app_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

CREATE TABLE `{MYSQL_PREFIX}_main`.`oauth_consumer_registry` (
  `ocr_id` int(11) NOT NULL AUTO_INCREMENT,
  `ocr_usa_id_ref` int(11) DEFAULT NULL,
  `ocr_consumer_key` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ocr_consumer_secret` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ocr_signature_methods` varchar(255) NOT NULL DEFAULT 'HMAC-SHA1,PLAINTEXT',
  `ocr_server_uri` varchar(255) NOT NULL,
  `ocr_server_uri_host` varchar(128) NOT NULL,
  `ocr_server_uri_path` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ocr_request_token_uri` varchar(255) NOT NULL,
  `ocr_authorize_uri` varchar(255) NOT NULL,
  `ocr_access_token_uri` varchar(255) NOT NULL,
  `ocr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ocr_id`),
  UNIQUE KEY `ocr_consumer_key` (`ocr_consumer_key`,`ocr_usa_id_ref`,`ocr_server_uri`),
  KEY `ocr_server_uri` (`ocr_server_uri`),
  KEY `ocr_server_uri_host` (`ocr_server_uri_host`,`ocr_server_uri_path`),
  KEY `ocr_usa_id_ref` (`ocr_usa_id_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE `{MYSQL_PREFIX}_main`.`oauth_consumer_token` (
  `oct_id` int(11) NOT NULL AUTO_INCREMENT,
  `oct_ocr_id_ref` int(11) NOT NULL,
  `oct_usa_id_ref` int(11) NOT NULL,
  `oct_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `oct_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `oct_token_secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `oct_token_type` enum('request','authorized','access') DEFAULT NULL,
  `oct_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `oct_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`oct_id`),
  UNIQUE KEY `oct_ocr_id_ref` (`oct_ocr_id_ref`,`oct_token`),
  UNIQUE KEY `oct_usa_id_ref` (`oct_usa_id_ref`,`oct_ocr_id_ref`,`oct_token_type`,`oct_name`),
  KEY `oct_token_ttl` (`oct_token_ttl`),
  CONSTRAINT `oauth_consumer_token_ibfk_1` FOREIGN KEY (`oct_ocr_id_ref`) REFERENCES `oauth_consumer_registry` (`ocr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=774 DEFAULT CHARSET=utf8;


CREATE TABLE `{MYSQL_PREFIX}_main`.`oauth_server_nonce` (
  `osn_id` int(11) NOT NULL AUTO_INCREMENT,
  `osn_consumer_key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osn_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osn_timestamp` bigint(20) NOT NULL,
  `osn_nonce` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`osn_id`),
  UNIQUE KEY `osn_consumer_key` (`osn_consumer_key`,`osn_token`,`osn_timestamp`,`osn_nonce`)
) ENGINE=InnoDB AUTO_INCREMENT=12604 DEFAULT CHARSET=utf8;


CREATE TABLE `{MYSQL_PREFIX}_main`.`oauth_server_registry` (
  `osr_id` int(11) NOT NULL AUTO_INCREMENT,
  `osr_usa_id_ref` int(11) DEFAULT NULL,
  `osr_consumer_key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osr_consumer_secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osr_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `osr_status` varchar(16) NOT NULL,
  `osr_requester_name` varchar(64) NOT NULL,
  `osr_requester_email` varchar(64) NOT NULL,
  `osr_callback_uri` varchar(255) NOT NULL,
  `osr_application_uri` varchar(255) NOT NULL,
  `osr_application_title` varchar(80) NOT NULL,
  `osr_application_descr` text NOT NULL,
  `osr_application_notes` text NOT NULL,
  `osr_application_type` varchar(20) NOT NULL,
  `osr_application_commercial` tinyint(1) NOT NULL DEFAULT '0',
  `osr_issue_date` datetime NOT NULL,
  `osr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`osr_id`),
  UNIQUE KEY `osr_consumer_key` (`osr_consumer_key`),
  KEY `osr_usa_id_ref` (`osr_usa_id_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


CREATE TABLE `{MYSQL_PREFIX}_main`.`oauth_server_token` (
  `ost_id` int(11) NOT NULL AUTO_INCREMENT,
  `ost_osr_id_ref` int(11) NOT NULL,
  `ost_usa_id_ref` int(11) NOT NULL,
  `ost_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ost_token_secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ost_token_type` enum('request','access') DEFAULT NULL,
  `ost_authorized` tinyint(1) NOT NULL DEFAULT '0',
  `ost_referrer_host` varchar(128) NOT NULL,
  `ost_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `ost_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ost_verifier` char(10) DEFAULT NULL,
  `ost_callback_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`ost_id`),
  UNIQUE KEY `ost_token` (`ost_token`),
  KEY `ost_osr_id_ref` (`ost_osr_id_ref`),
  KEY `ost_token_ttl` (`ost_token_ttl`),
  CONSTRAINT `oauth_server_token_ibfk_1` FOREIGN KEY (`ost_osr_id_ref`) REFERENCES `oauth_server_registry` (`osr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=460 DEFAULT CHARSET=utf8;

 CREATE TABLE `{MYSQL_PREFIX}_main`.`user_oauth` (
  `id_oauth_user` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `request_token` varchar(200) DEFAULT NULL,
  `access_token` varchar(200) DEFAULT NULL,
  `verifier` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_oauth_user`)
) ENGINE=MyISAM AUTO_INCREMENT=502 DEFAULT CHARSET=latin1;


-- insercion de registros en tabla de moviles
INSERT INTO oauth_consumer_registry VALUES (1,1,'claveconsumer1','noneatthismoment','HMAC-SHA1,PLAINTEXT','{PLATFORM_URL}/ilearning/oauth/','{PLATFORM_URL}','ilearning/oauth/','{PLATFORM_URL}/ilearning/oauth/request_token.php','{PLATFORM_URL}/ilearning/oauth/authorize.php','{PLATFORM_URL}/ilearning/oauth/access_token.php',sysdate());
insert into oauth_server_registry values (1,1,'claveconsumer1','noneatthismoment',1,'active','Ilearning Iphone app','ilearning@formaciondigital.com','','','Ilearning Iphone app','','','',0,'','');




-- tablas para el control de laboratorio virtual

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`vlab_practicas`;
CREATE TABLE `{MYSQL_PREFIX}_main`.`vlab_practicas` (
  `id_practica` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_curso` varchar(50)  NOT NULL,
  `id_maquina` varchar(10) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `activa` varchar(1) NOT NULL DEFAULT 'S',
  `tiempo` varchar(3) NOT NULL,
  PRIMARY KEY (`id_practica`,`id_curso`)
);

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`vlab_control_peticiones`;
CREATE TABLE `{MYSQL_PREFIX}_main`.`vlab_control_peticiones` (
  `id_peticion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `maquina_id` varchar(10) NOT NULL,
  `minutos` int(10) unsigned DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `fecha_peticion` bigint(20) NOT NULL,
  `id_practica` int(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_peticion`) 
) ENGINE=MyISAM,AUTO_INCREMENT = 1000000000 CHARSET=utf8 COLLATE=utf8_spanish_ci;


-- Tablas que se crean en la bd MAIN para soporte de REDES SOCIALES

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`parametros_facebook`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`parametros_facebook` (
  `id_facebook` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `access_token` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fecha_operacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_facebook`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`parametros_twitter`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`parametros_twitter` (
  `id_twitter` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `toauth_at` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `toauth_ats` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fecha_operacion` date DEFAULT NULL,
  PRIMARY KEY (`id_twitter`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`access_url`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`access_url`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`access_url` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) collate utf8_spanish_ci NOT NULL default 'http://localhost/',
  `description` text collate utf8_spanish_ci,
  `active` int(10) unsigned NOT NULL default '0',
  `created_by` int(11) NOT NULL,
  `tms` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`access_url`
--

/*!40000 ALTER TABLE `access_url` DISABLE KEYS */;
LOCK TABLES `access_url` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `access_url` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`admin`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`admin`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`admin` (
  `user_id` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`admin`
--

/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
LOCK TABLES `admin` WRITE;
INSERT INTO `{MYSQL_PREFIX}_main`.`admin` VALUES  (1),
 (2);
UNLOCK TABLES;
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`class`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`class`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`class` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `code` varchar(40) collate utf8_spanish_ci default '',
  `name` text collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`class`
--

/*!40000 ALTER TABLE `class` DISABLE KEYS */;
LOCK TABLES `class` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `class` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`class_user`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`class_user`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`class_user` (
  `class_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`class_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`class_user`
--

/*!40000 ALTER TABLE `class_user` DISABLE KEYS */;
LOCK TABLES `class_user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `class_user` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`course`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`course`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`course` (
  `code` varchar(40) collate utf8_spanish_ci NOT NULL,
  `directory` varchar(40) collate utf8_spanish_ci default NULL,
  `db_name` varchar(40) collate utf8_spanish_ci default NULL,
  `course_language` varchar(20) collate utf8_spanish_ci default NULL,
  `title` varchar(250) collate utf8_spanish_ci default NULL,
  `description` text collate utf8_spanish_ci,
  `category_code` varchar(40) collate utf8_spanish_ci default NULL,
  `visibility` tinyint(4) default '0',
  `show_score` int(11) NOT NULL default '1',
  `tutor_name` varchar(200) collate utf8_spanish_ci default NULL,
  `visual_code` varchar(40) collate utf8_spanish_ci default NULL,
  `department_name` varchar(30) collate utf8_spanish_ci default NULL,
  `department_url` varchar(180) collate utf8_spanish_ci default NULL,
  `disk_quota` int(10) unsigned default NULL,
  `last_visit` datetime default NULL,
  `last_edit` datetime default NULL,
  `creation_date` datetime default NULL,
  `expiration_date` datetime default NULL,
  `target_course_code` varchar(40) collate utf8_spanish_ci default NULL,
  `subscribe` tinyint(4) NOT NULL default '1',
  `unsubscribe` tinyint(4) NOT NULL default '1',
  `registration_code` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  PRIMARY KEY  (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`course`
--

/*!40000 ALTER TABLE `course` DISABLE KEYS */;
LOCK TABLES `course` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `course` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`course_category`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`course_category`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`course_category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) collate utf8_spanish_ci NOT NULL default '',
  `code` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `parent_id` varchar(40) collate utf8_spanish_ci default NULL,
  `tree_pos` int(10) unsigned default NULL,
  `children_count` smallint(6) default NULL,
  `auth_course_child` enum('TRUE','FALSE') collate utf8_spanish_ci default 'TRUE',
  `auth_cat_child` enum('TRUE','FALSE') collate utf8_spanish_ci default 'TRUE',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  KEY `tree_pos` (`tree_pos`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`course_category`
--

/*!40000 ALTER TABLE `course_category` DISABLE KEYS */;
LOCK TABLES `course_category` WRITE;
INSERT INTO `{MYSQL_PREFIX}_main`.`course_category` VALUES  (1,'Language skills','LANG',NULL,1,0,'TRUE','TRUE'),
 (2,'PC Skills','PC',NULL,2,0,'TRUE','TRUE'),
 (3,'Projects','PROJ',NULL,3,0,'TRUE','TRUE');
UNLOCK TABLES;
/*!40000 ALTER TABLE `course_category` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`course_rel_class`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`course_rel_class`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`course_rel_class` (
  `course_code` char(40) collate utf8_spanish_ci NOT NULL,
  `class_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`course_code`,`class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`course_rel_class`
--

/*!40000 ALTER TABLE `course_rel_class` DISABLE KEYS */;
LOCK TABLES `course_rel_class` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `course_rel_class` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`course_rel_user`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`course_rel_user`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`course_rel_user` (
  `course_code` varchar(40) collate utf8_spanish_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '5',
  `role` varchar(60) collate utf8_spanish_ci default NULL,
  `group_id` int(11) NOT NULL default '0',
  `tutor_id` int(10) unsigned NOT NULL default '0',
  `sort` int(11) default NULL,
  `user_course_cat` int(11) default '0',
  PRIMARY KEY  (`course_code`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`course_rel_user`
--

/*!40000 ALTER TABLE `course_rel_user` DISABLE KEYS */;
LOCK TABLES `course_rel_user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `course_rel_user` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`course_rel_user_fd`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`course_rel_user_fd`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`course_rel_user_fd` (
  `course_code` varchar(40) collate utf8_spanish_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `f_matriculacion` datetime NOT NULL,
  `f_finalizacion` datetime default NULL,
  PRIMARY KEY  (`course_code`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`course_rel_user_fd`
--

/*!40000 ALTER TABLE `course_rel_user_fd` DISABLE KEYS */;
LOCK TABLES `course_rel_user_fd` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `course_rel_user_fd` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`fd_bienvenida`
-- 31-03-2010. Incluye nuevos campos para mejora en la gestión de las cartas de bienvenida
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`fd_bienvenida`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`fd_bienvenida` (  
  id int(11) NOT NULL auto_increment,
  nombre varchar(100) collate utf8_spanish_ci NOT NULL,
  html longtext collate utf8_spanish_ci NOT NULL,  
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`fd_bienvenida`
--

/*!40000 ALTER TABLE `fd_bienvenida` DISABLE KEYS */;
LOCK TABLES `fd_bienvenida` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `fd_bienvenida` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`gradebook_category`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`gradebook_category`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`gradebook_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` text collate utf8_spanish_ci NOT NULL,
  `description` text collate utf8_spanish_ci,
  `user_id` int(11) NOT NULL,
  `course_code` varchar(40) collate utf8_spanish_ci default NULL,
  `parent_id` int(11) default NULL,
  `weight` smallint(6) NOT NULL,
  `visible` tinyint(4) NOT NULL,
  `certif_min_score` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`gradebook_category`
--

/*!40000 ALTER TABLE `gradebook_category` DISABLE KEYS */;
LOCK TABLES `gradebook_category` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `gradebook_category` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`gradebook_evaluation`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`gradebook_evaluation`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`gradebook_evaluation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` text collate utf8_spanish_ci NOT NULL,
  `description` text collate utf8_spanish_ci,
  `user_id` int(11) NOT NULL,
  `course_code` varchar(40) collate utf8_spanish_ci default NULL,
  `category_id` int(11) default NULL,
  `date` int(11) default '0',
  `weight` smallint(6) NOT NULL,
  `max` float unsigned NOT NULL,
  `visible` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`gradebook_evaluation`
--

/*!40000 ALTER TABLE `gradebook_evaluation` DISABLE KEYS */;
LOCK TABLES `gradebook_evaluation` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `gradebook_evaluation` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`gradebook_link`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`gradebook_link`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`gradebook_link` (
  `id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL,
  `ref_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_code` varchar(40) collate utf8_spanish_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `date` int(11) default NULL,
  `weight` smallint(6) NOT NULL,
  `visible` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`gradebook_link`
--

/*!40000 ALTER TABLE `gradebook_link` DISABLE KEYS */;
LOCK TABLES `gradebook_link` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `gradebook_link` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`gradebook_result`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`gradebook_result`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`gradebook_result` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `score` float unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`gradebook_result`
--

/*!40000 ALTER TABLE `gradebook_result` DISABLE KEYS */;
LOCK TABLES `gradebook_result` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `gradebook_result` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`gradebook_score_display`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`gradebook_score_display`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`gradebook_score_display` (
  `id` int(11) NOT NULL auto_increment,
  `score` float unsigned NOT NULL,
  `display` varchar(40) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`gradebook_score_display`
--

/*!40000 ALTER TABLE `gradebook_score_display` DISABLE KEYS */;
LOCK TABLES `gradebook_score_display` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `gradebook_score_display` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`language`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`language`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`language` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `original_name` varchar(255) collate utf8_spanish_ci default NULL,
  `english_name` varchar(255) collate utf8_spanish_ci default NULL,
  `isocode` varchar(10) collate utf8_spanish_ci default NULL,
  `dokeos_folder` varchar(250) collate utf8_spanish_ci default NULL,
  `available` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`language`
--

/*!40000 ALTER TABLE `language` DISABLE KEYS */;
LOCK TABLES `{MYSQL_PREFIX}_main`.`language` WRITE;

INSERT INTO `{MYSQL_PREFIX}_main`.`language` VALUES  
 (1,'Arabija (el)','arabic','ar','arabic',0),
 (2,'Portugu&ecirc;s (Brazil)','brazilian_fd','pt-BR','brazilian_fd',1),
 (3,'Balgarski','bulgarian','bg','bulgarian',0),
 (4,'Catal&agrave;','catalan_fd','ca_fd','catalan_fd',1),
 (5,'Hrvatski','croatian','hr','croatian',0),
 (6,'Dansk','danish','da','danish',0),
 (7,'Nederlands','dutch','nl','dutch',0),
 (8,'English','english_fd','en_fd','english_fd',1),
 (9,'Suomi','finnish','fi','finnish',0),
 (10,'Fran&ccedil;ais','french_fd','fr_fd','french_fd',1),
 (11,'Galego','galician','gl','galician',0),
 (12,'Deutsch','german','de','german',0),
 (13,'Ellinika','greek','el','greek',0),
 (14,'Magyar','hungarian','hu','hungarian',0),
 (15,'Indonesia (Bahasa I.)','indonesian','id','indonesian',0),
 (16,'Italiano','italian','it','italian',0),
 (17,'Nihongo','japanese','ja','japanese',0),
 (18,'Melayu (Bahasa M.)','malay','ms','malay',0),
 (19,'Polski','polish','pl','polish',0),
 (20,'Portugu&ecirc;s (Portugal)','portuguese','pt','portuguese',0),
 (21,'Russkij','russian','ru','russian',0),
 (22,'Chinese (simplified)','simpl_chinese','zh','simpl_chinese',0),
 (23,'Slovenscina','slovenian','sl','slovenian',0),
 (24,'Espa&ntilde;ol','spanish_fd','es_fd','spanish_fd',1),
 (25,'Svenska','swedish','sv','swedish',0),
 (26,'Thai','thai','th','thai',0),
 (27,'T&uuml;rk&ccedil;e','turkce','tr','turkce',0),
 (28,'Vi&ecirc;t (Ti&ecirc;ng V.)','vietnamese','vi','vietnamese',0),
 (29,'Norsk','norwegian','no','norwegian',0),
 (30,'Farsi','persian','fa','persian',0),
 (31,'Srpski','serbian','sr','serbian',0),
 (32,'Bosanski','bosnian',NULL,'bosnian',0),
 (33,'Swahili (kiSw.)','swahili','sw','swahili',0),
 (34,'Esperanto','esperanto','eo','esperanto',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `language` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`openid_association`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`openid_association`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`openid_association` (
  `id` int(11) NOT NULL auto_increment,
  `idp_endpoint_uri` text collate utf8_spanish_ci NOT NULL,
  `session_type` varchar(30) collate utf8_spanish_ci NOT NULL,
  `assoc_handle` text collate utf8_spanish_ci NOT NULL,
  `assoc_type` text collate utf8_spanish_ci NOT NULL,
  `expires_in` bigint(20) NOT NULL,
  `mac_key` text collate utf8_spanish_ci NOT NULL,
  `created` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`openid_association`
--

/*!40000 ALTER TABLE `openid_association` DISABLE KEYS */;
LOCK TABLES `openid_association` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `openid_association` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`php_session`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`php_session`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`php_session` (
  `session_id` varchar(32) collate utf8_spanish_ci NOT NULL default '',
  `session_name` varchar(10) collate utf8_spanish_ci NOT NULL default '',
  `session_time` int(11) NOT NULL default '0',
  `session_start` int(11) NOT NULL default '0',
  `session_value` text collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`php_session`
--

/*!40000 ALTER TABLE `php_session` DISABLE KEYS */;
LOCK TABLES `php_session` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `php_session` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`session`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`session`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`session` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `id_coach` int(10) unsigned NOT NULL default '0',
  `name` char(50) collate utf8_spanish_ci NOT NULL default '',
  `nbr_courses` smallint(5) unsigned NOT NULL default '0',
  `nbr_users` mediumint(8) unsigned NOT NULL default '0',
  `nbr_classes` mediumint(8) unsigned NOT NULL default '0',
  `date_start` date NOT NULL default '0000-00-00',
  `date_end` date NOT NULL default '0000-00-00',
  `session_admin_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `session_admin_id` (`session_admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`session`
--

/*!40000 ALTER TABLE `session` DISABLE KEYS */;
LOCK TABLES `session` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`session_rel_course`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`session_rel_course`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`session_rel_course` (
  `id_session` smallint(5) unsigned NOT NULL default '0',
  `course_code` char(40) collate utf8_spanish_ci NOT NULL default '',
  `id_coach` int(10) unsigned NOT NULL default '0',
  `nbr_users` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_session`,`course_code`),
  KEY `course_code` (`course_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`session_rel_course`
--

/*!40000 ALTER TABLE `session_rel_course` DISABLE KEYS */;
LOCK TABLES `session_rel_course` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `session_rel_course` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`session_rel_course_rel_user`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`session_rel_course_rel_user`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`session_rel_course_rel_user` (
  `id_session` smallint(5) unsigned NOT NULL default '0',
  `course_code` char(40) collate utf8_spanish_ci NOT NULL default '',
  `id_user` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_session`,`course_code`,`id_user`),
  KEY `id_user` (`id_user`),
  KEY `course_code` (`course_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`session_rel_course_rel_user`
--

/*!40000 ALTER TABLE `session_rel_course_rel_user` DISABLE KEYS */;
LOCK TABLES `session_rel_course_rel_user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `session_rel_course_rel_user` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`session_rel_user`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`session_rel_user`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`session_rel_user` (
  `id_session` mediumint(8) unsigned NOT NULL default '0',
  `id_user` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_session`,`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`session_rel_user`
--

/*!40000 ALTER TABLE `session_rel_user` DISABLE KEYS */;
LOCK TABLES `session_rel_user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `session_rel_user` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`settings_current`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`settings_current`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`settings_current` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `variable` varchar(255) collate utf8_spanish_ci default NULL,
  `subkey` varchar(255) collate utf8_spanish_ci default NULL,
  `type` varchar(255) collate utf8_spanish_ci default NULL,
  `category` varchar(255) collate utf8_spanish_ci default NULL,
  `selected_value` varchar(255) collate utf8_spanish_ci default NULL,
  `title` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  `comment` varchar(255) collate utf8_spanish_ci default NULL,
  `scope` varchar(50) collate utf8_spanish_ci default NULL,
  `subkeytext` varchar(255) collate utf8_spanish_ci default NULL,
  `access_url` int(10) unsigned NOT NULL default '1',
  `access_url_changeable` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `access_url` (`access_url`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`settings_current`
--

/*!40000 ALTER TABLE `settings_current` DISABLE KEYS */;
LOCK TABLES `settings_current` WRITE;
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  
 (1,'Institution',NULL,'textfield','Platform','{ORGANISATIONNAME}','InstitutionTitle','InstitutionComment','platform',NULL,1,0),
 (2,'InstitutionUrl',NULL,'textfield','Platform','{ORGANISATIONURL}','InstitutionUrlTitle','InstitutionUrlComment',NULL,NULL,1,0),
 (3,'siteName',NULL,'textfield','Platform','{CAMPUSNAME}','SiteNameTitle','SiteNameComment',NULL,NULL,1,0),
 (4,'emailAdministrator',NULL,'textfield','Platform','{ADMINEMAIL}','emailAdministratorTitle','emailAdministratorComment',NULL,NULL,1,0),
 (5,'administratorSurname',NULL,'textfield','Platform','{ADMINLASTNAME}','administratorSurnameTitle','administratorSurnameComment',NULL,NULL,1,0),
 (6,'administratorName',NULL,'textfield','Platform','{ADMINFIRSTNAME}','administratorNameTitle','administratorNameComment',NULL,NULL,1,0),
 (7,'show_administrator_data',NULL,'radio','Platform','false','ShowAdministratorDataTitle','ShowAdministratorDataComment',NULL,NULL,1,0),
 (8,'homepage_view',NULL,'radio','Course','activity','HomepageViewTitle','HomepageViewComment',NULL,NULL,1,0),
 (9,'show_toolshortcuts',NULL,'radio','Course','false','ShowToolShortcutsTitle','ShowToolShortcutsComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (10,'allow_group_categories',NULL,'radio','Course','false','AllowGroupCategories','AllowGroupCategoriesComment',NULL,NULL,1,0),
 (11,'server_type',NULL,'radio','Platform','production','ServerStatusTitle','ServerStatusComment',NULL,NULL,1,0),
 (12,'platformLanguage',NULL,'link','Languages','{PLATFORMLANGUAGE}','PlatformLanguageTitle','PlatformLanguageComment',NULL,NULL,1,0),
 (13,'showonline','world','checkbox','Platform','true','ShowOnlineTitle','ShowOnlineComment',NULL,'ShowOnlineWorld',1,0),
 (14,'showonline','users','checkbox','Platform','true','ShowOnlineTitle','ShowOnlineComment',NULL,'ShowOnlineUsers',1,0),
 (15,'showonline','course','checkbox','Platform','true','ShowOnlineTitle','ShowOnlineComment',NULL,'ShowOnlineCourse',1,0),
 (16,'profile','name','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'name',1,0),
 (17,'profile','officialcode','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'officialcode',1,0),
 (18,'profile','email','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'Email',1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (19,'profile','picture','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'UserPicture',1,0),
 (20,'profile','login','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'Login',1,0),
 (21,'profile','password','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'UserPassword',1,0),
 (22,'profile','language','checkbox','User','true','ProfileChangesTitle','ProfileChangesComment',NULL,'Language',1,0),
 (23,'default_document_quotum',NULL,'textfield','Course','50000000','DefaultDocumentQuotumTitle','DefaultDocumentQuotumComment',NULL,NULL,1,0),
 (24,'registration','officialcode','checkbox','User','false','RegistrationRequiredFormsTitle','RegistrationRequiredFormsComment',NULL,'OfficialCode',1,0),
 (25,'registration','email','checkbox','User','true','RegistrationRequiredFormsTitle','RegistrationRequiredFormsComment',NULL,'Email',1,0),
 (26,'registration','language','checkbox','User','true','RegistrationRequiredFormsTitle','RegistrationRequiredFormsComment',NULL,'Language',1,0),
 (27,'default_group_quotum',NULL,'textfield','Course','5000000','DefaultGroupQuotumTitle','DefaultGroupQuotumComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  
 (28,'allow_registration',NULL,'radio','Platform','{ALLOWSELFREGISTRATION}','AllowRegistrationTitle','AllowRegistrationComment',NULL,NULL,1,0),
 (29,'allow_registration_as_teacher',NULL,'radio','Platform','{ALLOWTEACHERSELFREGISTRATION}','AllowRegistrationAsTeacherTitle','AllowRegistrationAsTeacherComment',NULL,NULL,1,0),
 (30,'allow_lostpassword',NULL,'radio','Platform','true','AllowLostPasswordTitle','AllowLostPasswordComment',NULL,NULL,1,0),
 (31,'allow_user_headings',NULL,'radio','Course','false','AllowUserHeadings','AllowUserHeadingsComment',NULL,NULL,1,0),
 (32,'course_create_active_tools','course_description','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'CourseDescription',1,0),
 (33,'course_create_active_tools','agenda','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Agenda',1,0),
 (34,'course_create_active_tools','documents','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Documents',1,0),
 (35,'course_create_active_tools','learning_path','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'LearningPath',1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (36,'course_create_active_tools','links','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Links',1,0),
 (37,'course_create_active_tools','announcements','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Announcements',1,0),
 (38,'course_create_active_tools','forums','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Forums',1,0),
 (39,'course_create_active_tools','dropbox','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Dropbox',1,0),
 (40,'course_create_active_tools','quiz','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Quiz',1,0),
 (41,'course_create_active_tools','users','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Users',1,0),
 (42,'course_create_active_tools','groups','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Groups',1,0),
 (43,'course_create_active_tools','chat','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'Chat',1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (44,'course_create_active_tools','online_conference','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'OnlineConference',1,0),
 (45,'course_create_active_tools','student_publications','checkbox','Tools','true','CourseCreateActiveToolsTitle','CourseCreateActiveToolsComment',NULL,'StudentPublications',1,0),
 (46,'allow_personal_agenda',NULL,'radio','User','false','AllowPersonalAgendaTitle','AllowPersonalAgendaComment',NULL,NULL,1,0),
 (47,'display_coursecode_in_courselist',NULL,'radio','Platform','true','DisplayCourseCodeInCourselistTitle','DisplayCourseCodeInCourselistComment',NULL,NULL,1,0),
 (48,'display_teacher_in_courselist',NULL,'radio','Platform','true','DisplayTeacherInCourselistTitle','DisplayTeacherInCourselistComment',NULL,NULL,1,0),
 (49,'use_document_title',NULL,'radio','Tools','false','UseDocumentTitleTitle','UseDocumentTitleComment',NULL,NULL,1,0),
 (50,'permanently_remove_deleted_files',NULL,'radio','Tools','false','PermanentlyRemoveFilesTitle','PermanentlyRemoveFilesComment',NULL,NULL,1,0),
 (51,'dropbox_allow_overwrite',NULL,'radio','Tools','true','DropboxAllowOverwriteTitle','DropboxAllowOverwriteComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (52,'dropbox_max_filesize',NULL,'textfield','Tools','100000000','DropboxMaxFilesizeTitle','DropboxMaxFilesizeComment',NULL,NULL,1,0),
 (53,'dropbox_allow_just_upload',NULL,'radio','Tools','true','DropboxAllowJustUploadTitle','DropboxAllowJustUploadComment',NULL,NULL,1,0),
 (54,'dropbox_allow_student_to_student',NULL,'radio','Tools','false','DropboxAllowStudentToStudentTitle','DropboxAllowStudentToStudentComment',NULL,NULL,1,0),
 (55,'dropbox_allow_group',NULL,'radio','Tools','true','DropboxAllowGroupTitle','DropboxAllowGroupComment',NULL,NULL,1,0),
 (56,'dropbox_allow_mailing',NULL,'radio','Tools','false','DropboxAllowMailingTitle','DropboxAllowMailingComment',NULL,NULL,1,0),
 (57,'administratorTelephone',NULL,'textfield','Platform','902 36 68 39','administratorTelephoneTitle','administratorTelephoneComment',NULL,NULL,1,0),
 (58,'extended_profile',NULL,'radio','User','true','ExtendedProfileTitle','ExtendedProfileComment',NULL,NULL,1,0),
 (59,'student_view_enabled',NULL,'radio','Platform','true','StudentViewEnabledTitle','StudentViewEnabledComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (60,'show_navigation_menu',NULL,'radio','Course','false','ShowNavigationMenuTitle','ShowNavigationMenuComment',NULL,NULL,1,0),
 (61,'enable_tool_introduction',NULL,'radio','course','false','EnableToolIntroductionTitle','EnableToolIntroductionComment',NULL,NULL,1,0),
 (62,'page_after_login',NULL,'radio','Platform','user_portal.php','PageAfterLoginTitle','PageAfterLoginComment',NULL,NULL,1,0),
 (63,'time_limit_whosonline',NULL,'textfield','Platform','30','TimeLimitWhosonlineTitle','TimeLimitWhosonlineComment',NULL,NULL,1,0),
 (64,'breadcrumbs_course_homepage',NULL,'radio','Course','course_title','BreadCrumbsCourseHomepageTitle','BreadCrumbsCourseHomepageComment',NULL,NULL,1,0),
 (65,'example_material_course_creation',NULL,'radio','Platform','false','ExampleMaterialCourseCreationTitle','ExampleMaterialCourseCreationComment',NULL,NULL,1,0),
 (66,'account_valid_duration',NULL,'textfield','Platform','3660','AccountValidDurationTitle','AccountValidDurationComment',NULL,NULL,1,0),
 (67,'use_session_mode',NULL,'radio','Platform','false','UseSessionModeTitle','UseSessionModeComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (68,'allow_email_editor',NULL,'radio','Tools','false','AllowEmailEditorTitle','AllowEmailEditorComment',NULL,NULL,1,0),
 (69,'registered',NULL,'textfield',NULL,'false','',NULL,NULL,NULL,1,0),
 (70,'donotlistcampus',NULL,'textfield',NULL,'false','',NULL,NULL,NULL,1,0),
 (71,'show_email_addresses',NULL,'radio','Platform','false','ShowEmailAddresses','ShowEmailAddressesComment',NULL,NULL,1,0),
 (72,'profile','phone','checkbox','User','true','ProfileChangesTitle','ProfileChangesComment',NULL,'phone',1,0),
 (73,'service_visio','active','radio',NULL,'false','VisioEnable','',NULL,NULL,1,0),
 (74,'service_visio','visio_host','textfield',NULL,'','VisioHost','',NULL,NULL,1,0),
 (75,'service_visio','visio_port','textfield',NULL,'1935','VisioPort','',NULL,NULL,1,0),
 (76,'service_visio','visio_pass','textfield',NULL,'','VisioPassword','',NULL,NULL,1,0),
 (77,'service_ppt2lp','active','radio',NULL,'false','ppt2lp_actived','',NULL,NULL,1,0),
 (78,'service_ppt2lp','host','textfield',NULL,NULL,'Host',NULL,NULL,NULL,1,0),
 (79,'service_ppt2lp','port','textfield',NULL,'2002','Port',NULL,NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (80,'service_ppt2lp','user','textfield',NULL,NULL,'UserOnHost',NULL,NULL,NULL,1,0),
 (81,'service_ppt2lp','ftp_password','textfield',NULL,NULL,'FtpPassword',NULL,NULL,NULL,1,0),
 (82,'service_ppt2lp','path_to_lzx','textfield',NULL,NULL,'',NULL,NULL,NULL,1,0),
 (83,'service_ppt2lp','size','radio',NULL,'720x540','',NULL,NULL,NULL,1,0),
 (84,'wcag_anysurfer_public_pages',NULL,'radio','Platform','false','PublicPagesComplyToWAITitle','PublicPagesComplyToWAIComment',NULL,NULL,1,0),
 (85,'stylesheets',NULL,'textfield','stylesheets','public_admin','',NULL,NULL,NULL,1,0),
 (86,'upload_extensions_list_type',NULL,'radio','Security','blacklist','UploadExtensionsListType','UploadExtensionsListTypeComment',NULL,NULL,1,0),
 (87,'upload_extensions_blacklist',NULL,'textfield','Security','','UploadExtensionsBlacklist','UploadExtensionsBlacklistComment',NULL,NULL,1,0),
 (88,'upload_extensions_whitelist',NULL,'textfield','Security','htm;html;jpg;jpeg;gif;png;swf;avi;mpg;mpeg','UploadExtensionsWhitelist','UploadExtensionsWhitelistComment',NULL,NULL,1,0),
 (89,'upload_extensions_skip',NULL,'radio','Security','true','UploadExtensionsSkip','UploadExtensionsSkipComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (90,'upload_extensions_replace_by',NULL,'textfield','Security','dangerous','UploadExtensionsReplaceBy','UploadExtensionsReplaceByComment',NULL,NULL,1,0),
 (91,'show_number_of_courses',NULL,'radio','Platform','false','ShowNumberOfCourses','ShowNumberOfCoursesComment',NULL,NULL,1,0),
 (92,'show_empty_course_categories',NULL,'radio','Platform','true','ShowEmptyCourseCategories','ShowEmptyCourseCategoriesComment',NULL,NULL,1,0),
 (93,'show_back_link_on_top_of_tree',NULL,'radio','Platform','false','ShowBackLinkOnTopOfCourseTree','ShowBackLinkOnTopOfCourseTreeComment',NULL,NULL,1,0),
 (94,'show_different_course_language',NULL,'radio','Platform','true','ShowDifferentCourseLanguage','ShowDifferentCourseLanguageComment',NULL,NULL,1,0),
 (95,'split_users_upload_directory',NULL,'radio','Tuning','false','SplitUsersUploadDirectory','SplitUsersUploadDirectoryComment',NULL,NULL,1,0),
 (96,'hide_dltt_markup',NULL,'radio','Platform','true','HideDLTTMarkup','HideDLTTMarkupComment',NULL,NULL,1,0),
 (97,'display_categories_on_homepage',NULL,'radio','Platform','false','DisplayCategoriesOnHomepageTitle','DisplayCategoriesOnHomepageComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (98,'permissions_for_new_directories',NULL,'textfield','Security','0777','PermissionsForNewDirs','PermissionsForNewDirsComment',NULL,NULL,1,0),
 (99,'permissions_for_new_files',NULL,'textfield','Security','0666','PermissionsForNewFiles','PermissionsForNewFilesComment',NULL,NULL,1,0),
 (100,'show_tabs','campus_homepage','checkbox','Platform','false','ShowTabsTitle','ShowTabsComment',NULL,'TabsCampusHomepage',1,0),
 (101,'show_tabs','my_courses','checkbox','Platform','true','ShowTabsTitle','ShowTabsComment',NULL,'TabsMyCourses',1,0),
 (102,'show_tabs','reporting','checkbox','Platform','true','ShowTabsTitle','ShowTabsComment',NULL,'TabsReporting',1,0),
 (103,'show_tabs','platform_administration','checkbox','Platform','true','ShowTabsTitle','ShowTabsComment',NULL,'TabsPlatformAdministration',1,0),
 (104,'show_tabs','my_agenda','checkbox','Platform','false','ShowTabsTitle','ShowTabsComment',NULL,'TabsMyAgenda',1,0),
 (105,'show_tabs','my_profile','checkbox','Platform','false','ShowTabsTitle','ShowTabsComment',NULL,'TabsMyProfile',1,0),
 (106,'default_forum_view',NULL,'radio','Course','flat','DefaultForumViewTitle','DefaultForumViewComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (107,'platform_charset',NULL,'textfield','Platform','utf-8','PlatformCharsetTitle','PlatformCharsetComment','platform',NULL,1,0),
 (108,'noreply_email_address','','textfield','Platform','','NoReplyEmailAddress','NoReplyEmailAddressComment',NULL,NULL,1,0),
 (109,'survey_email_sender_noreply','','radio','Course','coach','SurveyEmailSenderNoReply','SurveyEmailSenderNoReplyComment',NULL,NULL,1,0),
 (110,'openid_authentication',NULL,'radio','Security','false','OpenIdAuthentication','OpenIdAuthenticationComment',NULL,NULL,1,0),
 (111,'profile','openid','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'OpenIDURL',1,0),
 (112,'gradebook_enable',NULL,'radio','Gradebook','false','GradebookActivation','GradebookActivationComment',NULL,NULL,1,0),
 (113,'show_tabs','my_gradebook','checkbox','Platform','false','ShowTabsTitle','ShowTabsComment',NULL,'TabsMyGradebook',1,0),
 (114,'gradebook_score_display_coloring','my_display_coloring','checkbox','Gradebook','false','GradebookScoreDisplayColoring','GradebookScoreDisplayColoringComment',NULL,'TabsGradebookEnableColoring',1,0),
 (115,'gradebook_score_display_custom','my_display_custom','checkbox','Gradebook','false','GradebookScoreDisplayCustom','GradebookScoreDisplayCustomComment',NULL,'TabsGradebookEnableCustom',1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (116,'gradebook_score_display_colorsplit',NULL,'textfield','Gradebook','50','GradebookScoreDisplayColorSplit','GradebookScoreDisplayColorSplitComment',NULL,NULL,1,0),
 (117,'gradebook_score_display_upperlimit','my_display_upperlimit','checkbox','Gradebook','false','GradebookScoreDisplayUpperLimit','GradebookScoreDisplayUpperLimitComment',NULL,'TabsGradebookEnableUpperLimit',1,0),
 (118,'user_selected_theme',NULL,'radio','Platform','false','UserThemeSelection','UserThemeSelectionComment',NULL,NULL,1,0),
 (119,'profile','theme','checkbox','User','false','ProfileChangesTitle','ProfileChangesComment',NULL,'UserTheme',1,0),
 (120,'allow_course_theme',NULL,'radio','Course','true','AllowCourseThemeTitle','AllowCourseThemeComment',NULL,NULL,1,0),
 (121,'display_mini_month_calendar',NULL,'radio','Tools','true','DisplayMiniMonthCalendarTitle','DisplayMiniMonthCalendarComment',NULL,NULL,1,0),
 (122,'display_upcoming_events',NULL,'radio','Tools','true','DisplayUpcomingEventsTitle','DisplayUpcomingEventsComment',NULL,NULL,1,0),
 (123,'number_of_upcoming_events',NULL,'textfield','Tools','1','NumberOfUpcomingEventsTitle','NumberOfUpcomingEventsComment',NULL,NULL,1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (124,'show_closed_courses',NULL,'radio','Platform','false','ShowClosedCoursesTitle','ShowClosedCoursesComment',NULL,NULL,1,0),
 (125,'ldap_main_server_address',NULL,'textfield','LDAP','localhost','LDAPMainServerAddressTitle','LDAPMainServerAddressComment',NULL,NULL,1,0),
 (126,'ldap_main_server_port',NULL,'textfield','LDAP','389','LDAPMainServerPortTitle','LDAPMainServerPortComment',NULL,NULL,1,0),
 (127,'ldap_domain',NULL,'textfield','LDAP','dc=nodomain','LDAPDomainTitle','LDAPDomainComment',NULL,NULL,1,0),
 (128,'ldap_replicate_server_address',NULL,'textfield','LDAP','localhost','LDAPReplicateServerAddressTitle','LDAPReplicateServerAddressComment',NULL,NULL,1,0),
 (129,'ldap_replicate_server_port',NULL,'textfield','LDAP','389','LDAPReplicateServerPortTitle','LDAPReplicateServerPortComment',NULL,NULL,1,0),
 (130,'ldap_search_term',NULL,'textfield','LDAP','','LDAPSearchTermTitle','LDAPSearchTermComment',NULL,NULL,1,0),
 (131,'ldap_version',NULL,'radio','LDAP','3','LDAPVersionTitle','LDAPVersionComment',NULL,'',1,0),
 (132,'ldap_filled_tutor_field',NULL,'textfield','LDAP','employeenumber','LDAPFilledTutorFieldTitle','LDAPFilledTutorFieldComment',NULL,'',1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (133,'ldap_authentication_login',NULL,'textfield','LDAP','','LDAPAuthenticationLoginTitle','LDAPAuthenticationLoginComment',NULL,'',1,0),
 (134,'ldap_authentication_password',NULL,'textfield','LDAP','','LDAPAuthenticationPasswordTitle','LDAPAuthenticationPasswordComment',NULL,'',1,0),
 (135,'service_visio','visio_use_rtmpt','radio',NULL,'false','VisioUseRtmptTitle','VisioUseRtmptComment',NULL,NULL,1,0),
 (136,'extendedprofile_registration','mycomptetences','checkbox','User','false','ExtendedProfileRegistrationTitle','ExtendedProfileRegistrationComment',NULL,'MyCompetences',1,0),
 (137,'extendedprofile_registration','mydiplomas','checkbox','User','false','ExtendedProfileRegistrationTitle','ExtendedProfileRegistrationComment',NULL,'MyDiplomas',1,0),
 (138,'extendedprofile_registration','myteach','checkbox','User','false','ExtendedProfileRegistrationTitle','ExtendedProfileRegistrationComment',NULL,'MyTeach',1,0),
 (139,'extendedprofile_registration','mypersonalopenarea','checkbox','User','false','ExtendedProfileRegistrationTitle','ExtendedProfileRegistrationComment',NULL,'MyPersonalOpenArea',1,0),
 (140,'extendedprofile_registrationrequired','mycomptetences','checkbox','User','false','ExtendedProfileRegistrationRequiredTitle','ExtendedProfileRegistrationRequiredComment',NULL,'MyCompetences',1,0);
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_current` VALUES  (141,'extendedprofile_registrationrequired','mydiplomas','checkbox','User','false','ExtendedProfileRegistrationRequiredTitle','ExtendedProfileRegistrationRequiredComment',NULL,'MyDiplomas',1,0),
 (142,'extendedprofile_registrationrequired','myteach','checkbox','User','false','ExtendedProfileRegistrationRequiredTitle','ExtendedProfileRegistrationRequiredComment',NULL,'MyTeach',1,0),
 (143,'extendedprofile_registrationrequired','mypersonalopenarea','checkbox','User','false','ExtendedProfileRegistrationRequiredTitle','ExtendedProfileRegistrationRequiredComment',NULL,'MyPersonalOpenArea',1,0),
 (144,'ldap_filled_tutor_field_value',NULL,'textfield','LDAP','','LDAPFilledTutorFieldValueTitle','LDAPFilledTutorFieldValueComment',NULL,'',1,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `settings_current` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`settings_options`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`settings_options`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`settings_options` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `variable` varchar(255) collate utf8_spanish_ci default NULL,
  `value` varchar(255) collate utf8_spanish_ci default NULL,
  `display_text` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`settings_options`
--

/*!40000 ALTER TABLE `settings_options` DISABLE KEYS */;
LOCK TABLES `settings_options` WRITE;
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_options` VALUES  (1,'show_administrator_data','true','Yes'),
 (2,'show_administrator_data','false','No'),
 (3,'homepage_view','activity','HomepageViewActivity'),
 (4,'homepage_view','2column','HomepageView2column'),
 (5,'homepage_view','3column','HomepageView3column'),
 (6,'show_toolshortcuts','true','Yes'),
 (7,'show_toolshortcuts','false','No'),
 (8,'allow_group_categories','true','Yes'),
 (9,'allow_group_categories','false','No'),
 (10,'server_type','production','ProductionServer'),
 (11,'server_type','test','TestServer'),
 (12,'allow_name_change','true','Yes'),
 (13,'allow_name_change','false','No'),
 (14,'allow_officialcode_change','true','Yes'),
 (15,'allow_officialcode_change','false','No'),
 (16,'allow_registration','true','Yes'),
 (17,'allow_registration','false','No'),
 (18,'allow_registration','approval','AfterApproval'),
 (19,'allow_registration_as_teacher','true','Yes'),
 (20,'allow_registration_as_teacher','false','No'),
 (21,'allow_lostpassword','true','Yes'),
 (22,'allow_lostpassword','false','No');
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_options` VALUES  (23,'allow_user_headings','true','Yes'),
 (24,'allow_user_headings','false','No'),
 (25,'allow_personal_agenda','true','Yes'),
 (26,'allow_personal_agenda','false','No'),
 (27,'display_coursecode_in_courselist','true','Yes'),
 (28,'display_coursecode_in_courselist','false','No'),
 (29,'display_teacher_in_courselist','true','Yes'),
 (30,'display_teacher_in_courselist','false','No'),
 (31,'use_document_title','true','Yes'),
 (32,'use_document_title','false','No'),
 (33,'permanently_remove_deleted_files','true','Yes'),
 (34,'permanently_remove_deleted_files','false','No'),
 (35,'dropbox_allow_overwrite','true','Yes'),
 (36,'dropbox_allow_overwrite','false','No'),
 (37,'dropbox_allow_just_upload','true','Yes'),
 (38,'dropbox_allow_just_upload','false','No'),
 (39,'dropbox_allow_student_to_student','true','Yes'),
 (40,'dropbox_allow_student_to_student','false','No'),
 (41,'dropbox_allow_group','true','Yes'),
 (42,'dropbox_allow_group','false','No'),
 (43,'dropbox_allow_mailing','true','Yes');
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_options` VALUES  (44,'dropbox_allow_mailing','false','No'),
 (45,'extended_profile','true','Yes'),
 (46,'extended_profile','false','No'),
 (47,'student_view_enabled','true','Yes'),
 (48,'student_view_enabled','false','No'),
 (49,'show_navigation_menu','false','No'),
 (50,'show_navigation_menu','icons','IconsOnly'),
 (51,'show_navigation_menu','text','TextOnly'),
 (52,'show_navigation_menu','iconstext','IconsText'),
 (53,'enable_tool_introduction','true','Yes'),
 (54,'enable_tool_introduction','false','No'),
 (55,'page_after_login','index.php','CampusHomepage'),
 (56,'page_after_login','user_portal.php','MyCourses'),
 (57,'breadcrumbs_course_homepage','get_lang','CourseHomepage'),
 (58,'breadcrumbs_course_homepage','course_code','CourseCode'),
 (59,'breadcrumbs_course_homepage','course_title','CourseTitle'),
 (60,'example_material_course_creation','true','Yes'),
 (61,'example_material_course_creation','false','No'),
 (62,'use_session_mode','true','Yes'),
 (63,'use_session_mode','false','No');
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_options` VALUES  (64,'allow_email_editor','true','Yes'),
 (65,'allow_email_editor','false','No'),
 (66,'show_email_addresses','true','Yes'),
 (67,'show_email_addresses','false','No'),
 (68,'wcag_anysurfer_public_pages','true','Yes'),
 (69,'wcag_anysurfer_public_pages','false','No'),
 (70,'upload_extensions_list_type','blacklist','Blacklist'),
 (71,'upload_extensions_list_type','whitelist','Whitelist'),
 (72,'upload_extensions_skip','true','Remove'),
 (73,'upload_extensions_skip','false','Rename'),
 (74,'show_number_of_courses','true','Yes'),
 (75,'show_number_of_courses','false','No'),
 (76,'show_empty_course_categories','true','Yes'),
 (77,'show_empty_course_categories','false','No'),
 (78,'show_back_link_on_top_of_tree','true','Yes'),
 (79,'show_back_link_on_top_of_tree','false','No'),
 (80,'show_different_course_language','true','Yes'),
 (81,'show_different_course_language','false','No'),
 (82,'split_users_upload_directory','true','Yes'),
 (83,'split_users_upload_directory','false','No'),
 (84,'hide_dltt_markup','false','No');
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_options` VALUES  (85,'hide_dltt_markup','true','Yes'),
 (86,'display_categories_on_homepage','true','Yes'),
 (87,'display_categories_on_homepage','false','No'),
 (88,'default_forum_view','flat','Flat'),
 (89,'default_forum_view','threaded','Threaded'),
 (90,'default_forum_view','nested','Nested'),
 (91,'survey_email_sender_noreply','coach','CourseCoachEmailSender'),
 (92,'survey_email_sender_noreply','noreply','NoReplyEmailSender'),
 (93,'openid_authentication','true','Yes'),
 (94,'openid_authentication','false','No'),
 (95,'gradebook_enable','true','Yes'),
 (96,'gradebook_enable','false','No'),
 (97,'user_selected_theme','true','Yes'),
 (98,'user_selected_theme','false','No'),
 (99,'allow_course_theme','true','Yes'),
 (100,'allow_course_theme','false','No'),
 (101,'display_mini_month_calendar','true','Yes'),
 (102,'display_mini_month_calendar','false','No'),
 (103,'display_upcoming_events','true','Yes'),
 (104,'display_upcoming_events','false','No'),
 (105,'show_closed_courses','true','Yes');
INSERT INTO `{MYSQL_PREFIX}_main`.`settings_options` VALUES  (106,'show_closed_courses','false','No'),
 (107,'ldap_version','2','LDAPVersion2'),
 (108,'ldap_version','3','LDAPVersion3'),
 (109,'visio_use_rtmpt','true','Yes'),
 (110,'visio_use_rtmpt','false','No');
UNLOCK TABLES;
/*!40000 ALTER TABLE `settings_options` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`shared_survey`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`shared_survey`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`shared_survey` (
  `survey_id` int(10) unsigned NOT NULL auto_increment,
  `code` varchar(20) collate utf8_spanish_ci default NULL,
  `title` text collate utf8_spanish_ci,
  `subtitle` text collate utf8_spanish_ci,
  `author` varchar(250) collate utf8_spanish_ci default NULL,
  `lang` varchar(20) collate utf8_spanish_ci default NULL,
  `template` varchar(20) collate utf8_spanish_ci default NULL,
  `intro` text collate utf8_spanish_ci,
  `surveythanks` text collate utf8_spanish_ci,
  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `course_code` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  PRIMARY KEY  (`survey_id`),
  UNIQUE KEY `id` (`survey_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`shared_survey`
--

/*!40000 ALTER TABLE `shared_survey` DISABLE KEYS */;
LOCK TABLES `shared_survey` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `shared_survey` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`shared_survey_question`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`shared_survey_question`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`shared_survey_question` (
  `question_id` int(11) NOT NULL auto_increment,
  `survey_id` int(11) NOT NULL default '0',
  `survey_question` text collate utf8_spanish_ci NOT NULL,
  `survey_question_comment` text collate utf8_spanish_ci NOT NULL,
  `type` varchar(250) collate utf8_spanish_ci NOT NULL default '',
  `display` varchar(10) collate utf8_spanish_ci NOT NULL default '',
  `sort` int(11) NOT NULL default '0',
  `code` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `max_value` int(11) NOT NULL,
  PRIMARY KEY  (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`shared_survey_question`
--

/*!40000 ALTER TABLE `shared_survey_question` DISABLE KEYS */;
LOCK TABLES `shared_survey_question` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `shared_survey_question` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`shared_survey_question_option`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`shared_survey_question_option`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`shared_survey_question_option` (
  `question_option_id` int(11) NOT NULL auto_increment,
  `question_id` int(11) NOT NULL default '0',
  `survey_id` int(11) NOT NULL default '0',
  `option_text` text collate utf8_spanish_ci NOT NULL,
  `sort` int(11) NOT NULL default '0',
  PRIMARY KEY  (`question_option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`shared_survey_question_option`
--

/*!40000 ALTER TABLE `shared_survey_question_option` DISABLE KEYS */;
LOCK TABLES `shared_survey_question_option` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `shared_survey_question_option` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`sys_announcement`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`sys_announcement`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`sys_announcement` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_start` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_end` datetime NOT NULL default '0000-00-00 00:00:00',
  `visible_teacher` tinyint(4) NOT NULL default '0',
  `visible_student` tinyint(4) NOT NULL default '0',
  `visible_guest` tinyint(4) NOT NULL default '0',
  `title` varchar(250) collate utf8_spanish_ci NOT NULL default '',
  `content` text collate utf8_spanish_ci NOT NULL,
  `lang` varchar(70) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`sys_announcement`
--

/*!40000 ALTER TABLE `sys_announcement` DISABLE KEYS */;
LOCK TABLES `sys_announcement` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sys_announcement` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`templates`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`templates`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`templates` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(100) collate utf8_spanish_ci NOT NULL,
  `description` varchar(250) collate utf8_spanish_ci NOT NULL,
  `course_code` varchar(40) collate utf8_spanish_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `ref_doc` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`templates`
--

/*!40000 ALTER TABLE `templates` DISABLE KEYS */;
LOCK TABLES `templates` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `templates` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`user`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`user`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`user` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `lastname` varchar(60) collate utf8_spanish_ci default NULL,
  `firstname` varchar(60) collate utf8_spanish_ci default NULL,
  `username` varchar(20) collate utf8_spanish_ci NOT NULL default '',
  `password` varchar(50) collate utf8_spanish_ci NOT NULL default '',
  `auth_source` varchar(50) collate utf8_spanish_ci default 'platform',
  `email` varchar(100) collate utf8_spanish_ci default NULL,
  `status` tinyint(4) NOT NULL default '5',
  `official_code` varchar(40) collate utf8_spanish_ci default NULL,
  `phone` varchar(30) collate utf8_spanish_ci default NULL,
  `picture_uri` varchar(250) collate utf8_spanish_ci default NULL,
  `creator_id` int(10) unsigned default NULL,
  `competences` text collate utf8_spanish_ci,
  `diplomas` text collate utf8_spanish_ci,
  `openarea` text collate utf8_spanish_ci,
  `teach` text collate utf8_spanish_ci,
  `productions` varchar(250) collate utf8_spanish_ci default NULL,
  `chatcall_user_id` int(10) unsigned NOT NULL default '0',
  `chatcall_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `chatcall_text` varchar(50) collate utf8_spanish_ci NOT NULL default '',
  `language` varchar(40) collate utf8_spanish_ci default NULL,
  `registration_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `expiration_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(3) unsigned NOT NULL default '1',
  `openid` varchar(255) collate utf8_spanish_ci default NULL,
  `theme` varchar(255) collate utf8_spanish_ci default NULL,
  `hr_dept_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
LOCK TABLES `user` WRITE;

INSERT INTO `{MYSQL_PREFIX}_main`.`user` VALUES  
(1,'{ADMINLASTNAME}','{ADMINFIRSTNAME}','{ADMINLOGIN}','{ADMINPASSWORD}','{PLATFORM_AUTH_SOURCE}','{ADMINEMAIL}',1,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,0,'0000-00-00 00:00:00','','spanish',SYSDATE(),'0000-00-00 00:00:00',1,NULL,NULL,0),
(2,'{GESTORLASTNAME}','{GESTORFIRSTNAME}','{GESTORLOGIN}','{GESTORPASSWORD}','{PLATFORM_AUTH_SOURCE}','{GESTOREMAIL}',1,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,0,'0000-00-00 00:00:00','','spanish',SYSDATE(),'0000-00-00 00:00:00',1,NULL,NULL,0);

UNLOCK TABLES;



--
-- Definition of table `{MYSQL_PREFIX}_main`.`user_field`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`user_field`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`user_field` (
  `id` int(11) NOT NULL auto_increment,
  `field_type` int(11) NOT NULL default '1',
  `field_variable` varchar(64) collate utf8_spanish_ci NOT NULL,
  `field_display_text` varchar(64) collate utf8_spanish_ci default NULL,
  `field_default_value` text collate utf8_spanish_ci,
  `field_order` int(11) default NULL,
  `field_visible` tinyint(4) default '0',
  `field_changeable` tinyint(4) default '0',
  `tms` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`user_field`
--

/*!40000 ALTER TABLE `user_field` DISABLE KEYS */;
LOCK TABLES `user_field` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `user_field` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`user_field_options`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`user_field_options`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`user_field_options` (
  `id` int(11) NOT NULL auto_increment,
  `field_id` int(11) NOT NULL,
  `option_value` text collate utf8_spanish_ci,
  `option_display_text` varchar(64) collate utf8_spanish_ci default NULL,
  `option_order` int(11) default NULL,
  `tms` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`user_field_options`
--

/*!40000 ALTER TABLE `user_field_options` DISABLE KEYS */;
LOCK TABLES `user_field_options` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `user_field_options` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_main`.`user_field_values`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_main`.`user_field_values`;
CREATE TABLE  `{MYSQL_PREFIX}_main`.`user_field_values` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` text collate utf8_spanish_ci,
  `tms` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_main`.`user_field_values`
--

/*!40000 ALTER TABLE `user_field_values` DISABLE KEYS */;
LOCK TABLES `user_field_values` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `user_field_values` ENABLE KEYS */;

--
-- Create schema {MYSQL_PREFIX}_stats
--

CREATE DATABASE IF NOT EXISTS {MYSQL_PREFIX}_stats DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
USE {MYSQL_PREFIX}_stats;

--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_c_browsers`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_c_browsers`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_c_browsers` (
  `id` int(11) NOT NULL auto_increment,
  `browser` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_c_browsers`
--

/*!40000 ALTER TABLE `track_c_browsers` DISABLE KEYS */;
LOCK TABLES `track_c_browsers` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_c_browsers` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_c_countries`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_c_countries`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_c_countries` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `country` varchar(50) collate utf8_spanish_ci NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_c_countries`
--

/*!40000 ALTER TABLE `track_c_countries` DISABLE KEYS */;
LOCK TABLES `track_c_countries` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_c_countries` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_c_os`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_c_os`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_c_os` (
  `id` int(11) NOT NULL auto_increment,
  `os` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_c_os`
--

/*!40000 ALTER TABLE `track_c_os` DISABLE KEYS */;
LOCK TABLES `track_c_os` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_c_os` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_c_providers`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_c_providers`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_c_providers` (
  `id` int(11) NOT NULL auto_increment,
  `provider` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_c_providers`
--

/*!40000 ALTER TABLE `track_c_providers` DISABLE KEYS */;
LOCK TABLES `track_c_providers` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_c_providers` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_c_referers`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_c_referers`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_c_referers` (
  `id` int(11) NOT NULL auto_increment,
  `referer` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_c_referers`
--

/*!40000 ALTER TABLE `track_c_referers` DISABLE KEYS */;
LOCK TABLES `track_c_referers` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_c_referers` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_access`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_access`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_access` (
  `access_id` int(11) NOT NULL auto_increment,
  `access_user_id` int(10) unsigned default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_cours_code` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `access_tool` varchar(30) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`access_id`),
  KEY `access_user_id` (`access_user_id`),
  KEY `access_cours_code` (`access_cours_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_access`
--

/*!40000 ALTER TABLE `track_e_access` DISABLE KEYS */;
LOCK TABLES `track_e_access` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_access` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_attempt`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_attempt`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_attempt` (
  `exe_id` int(11) default NULL,
  `user_id` int(11) NOT NULL default '0',
  `question_id` int(11) NOT NULL default '0',
  `answer` text collate utf8_spanish_ci NOT NULL,
  `teacher_comment` text collate utf8_spanish_ci NOT NULL,
  `marks` int(11) NOT NULL default '0',
  `course_code` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `position` int(11) default '0',
  `tms` datetime NOT NULL default '0000-00-00 00:00:00',
  KEY `exe_id` (`exe_id`),
  KEY `user_id` (`user_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_attempt`
--

/*!40000 ALTER TABLE `track_e_attempt` DISABLE KEYS */;
LOCK TABLES `track_e_attempt` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_attempt` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_course_access`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_course_access`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_course_access` (
  `course_access_id` int(11) NOT NULL auto_increment,
  `course_code` varchar(40) collate utf8_spanish_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_course_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `logout_course_date` datetime default NULL,
  `counter` int(11) NOT NULL,
  PRIMARY KEY  (`course_access_id`),
  KEY `user_id` (`user_id`),
  KEY `login_course_date` (`login_course_date`),
  KEY `course_code` (`course_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_course_access`
--

/*!40000 ALTER TABLE `track_e_course_access` DISABLE KEYS */;
LOCK TABLES `track_e_course_access` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_course_access` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_default`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_default`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_default` (
  `default_id` int(11) NOT NULL auto_increment,
  `default_user_id` int(10) unsigned NOT NULL default '0',
  `default_cours_code` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `default_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `default_event_type` varchar(20) collate utf8_spanish_ci NOT NULL default '',
  `default_value_type` varchar(20) collate utf8_spanish_ci NOT NULL default '',
  `default_value` tinytext collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`default_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_default`
--

/*!40000 ALTER TABLE `track_e_default` DISABLE KEYS */;
LOCK TABLES `track_e_default` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_default` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_downloads`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_downloads`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_downloads` (
  `down_id` int(11) NOT NULL auto_increment,
  `down_user_id` int(10) unsigned default NULL,
  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `down_cours_id` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `down_doc_path` varchar(255) collate utf8_spanish_ci NOT NULL default '',
  PRIMARY KEY  (`down_id`),
  KEY `down_user_id` (`down_user_id`),
  KEY `down_cours_id` (`down_cours_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_downloads`
--

/*!40000 ALTER TABLE `track_e_downloads` DISABLE KEYS */;
LOCK TABLES `track_e_downloads` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_downloads` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_exercices`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_exercices`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_exercices` (
  `exe_id` int(11) NOT NULL auto_increment,
  `exe_user_id` int(10) unsigned default NULL,
  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `exe_cours_id` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `exe_exo_id` mediumint(8) unsigned NOT NULL default '0',
  `exe_result` smallint(6) NOT NULL default '0',
  `exe_weighting` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`exe_id`),
  KEY `exe_user_id` (`exe_user_id`),
  KEY `exe_cours_id` (`exe_cours_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_exercices`
--

/*!40000 ALTER TABLE `track_e_exercices` DISABLE KEYS */;
LOCK TABLES `track_e_exercices` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_exercices` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_hotpotatoes`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_hotpotatoes`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_hotpotatoes` (
  `exe_name` varchar(255) collate utf8_spanish_ci NOT NULL,
  `exe_user_id` int(10) unsigned default NULL,
  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `exe_cours_id` varchar(40) collate utf8_spanish_ci NOT NULL,
  `exe_result` smallint(6) NOT NULL default '0',
  `exe_weighting` smallint(6) NOT NULL default '0',
  KEY `exe_user_id` (`exe_user_id`),
  KEY `exe_cours_id` (`exe_cours_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_hotpotatoes`
--

/*!40000 ALTER TABLE `track_e_hotpotatoes` DISABLE KEYS */;
LOCK TABLES `track_e_hotpotatoes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_hotpotatoes` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_hotspot`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_hotspot`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_hotspot` (
  `hotspot_id` int(11) NOT NULL auto_increment,
  `hotspot_user_id` int(11) NOT NULL,
  `hotspot_course_code` varchar(50) NOT NULL,
  `hotspot_exe_id` int(11) NOT NULL,
  `hotspot_question_id` int(11) NOT NULL,
  `hotspot_answer_id` int(11) NOT NULL,
  `hotspot_correct` tinyint(3) unsigned NOT NULL,
  `hotspot_coordinate` varchar(50) NOT NULL,
  PRIMARY KEY  (`hotspot_id`),
  KEY `hotspot_course_code` (`hotspot_course_code`),
  KEY `hotspot_user_id` (`hotspot_user_id`),
  KEY `hotspot_exe_id` (`hotspot_exe_id`),
  KEY `hotspot_question_id` (`hotspot_question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_hotspot`
--

/*!40000 ALTER TABLE `track_e_hotspot` DISABLE KEYS */;
LOCK TABLES `track_e_hotspot` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_hotspot` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_lastaccess`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_lastaccess`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_lastaccess` (
  `access_id` bigint(20) NOT NULL auto_increment,
  `access_user_id` int(10) unsigned default NULL,
  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `access_cours_code` varchar(40) collate utf8_spanish_ci NOT NULL,
  `access_tool` varchar(30) collate utf8_spanish_ci default NULL,
  `access_session_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`access_id`),
  KEY `access_user_id` (`access_user_id`),
  KEY `access_cours_code` (`access_cours_code`),
  KEY `access_session_id` (`access_session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_lastaccess`
--

/*!40000 ALTER TABLE `track_e_lastaccess` DISABLE KEYS */;
LOCK TABLES `track_e_lastaccess` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_lastaccess` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_links`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_links`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_links` (
  `links_id` int(11) NOT NULL auto_increment,
  `links_user_id` int(10) unsigned default NULL,
  `links_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `links_cours_id` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `links_link_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`links_id`),
  KEY `links_cours_id` (`links_cours_id`),
  KEY `links_user_id` (`links_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_links`
--

/*!40000 ALTER TABLE `track_e_links` DISABLE KEYS */;
LOCK TABLES `track_e_links` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_links` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_login`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_login`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_login` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_user_id` int(10) unsigned NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_ip` varchar(39) collate utf8_spanish_ci NOT NULL default '',
  `logout_date` datetime default NULL,
  PRIMARY KEY  (`login_id`),
  KEY `login_user_id` (`login_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_login`
--

/*!40000 ALTER TABLE `track_e_login` DISABLE KEYS */;
LOCK TABLES `track_e_login` WRITE;
INSERT INTO `{MYSQL_PREFIX}_stats`.`track_e_login` VALUES  (1,1,'2008-11-25 18:47:45','10.1.27.205','2008-11-25 18:49:32'),
 (2,1,'2008-11-26 13:23:27','10.1.27.205','2008-11-26 15:18:58'),
 (3,1,'2008-11-26 15:19:04','10.1.27.205','2008-11-26 15:19:31'),
 (4,1,'2008-11-26 15:20:14','10.1.27.205','2008-11-26 15:20:26'),
 (5,1,'2008-11-27 16:41:42','10.1.27.204','2008-11-27 16:44:09'),
 (6,1,'2008-11-27 16:44:24','10.1.27.204','2008-11-27 16:49:15'),
 (7,1,'2008-11-27 16:54:15','10.1.27.204','2008-11-27 16:54:40'),
 (8,1,'2008-11-28 09:05:42','10.1.27.213','2008-11-28 09:05:54'),
 (9,1,'2008-11-28 13:07:01','10.1.27.99','2008-11-28 13:07:01'),
 (10,1,'2008-12-02 12:22:21','10.1.27.209','2008-12-02 12:46:39'),
 (11,3,'2008-12-02 12:47:29','10.1.27.209',NULL),
 (12,3,'2008-12-02 12:47:46','10.1.27.209',NULL),
 (13,1,'2008-12-02 13:00:01','10.1.27.209','2008-12-02 13:09:29');
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_login` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_online`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_online`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_online` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_user_id` int(10) unsigned NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_ip` varchar(39) collate utf8_spanish_ci NOT NULL default '',
  `course` varchar(40) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`login_id`),
  KEY `login_user_id` (`login_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_online`
--

/*!40000 ALTER TABLE `track_e_online` DISABLE KEYS */;
LOCK TABLES `track_e_online` WRITE;
INSERT INTO `{MYSQL_PREFIX}_stats`.`track_e_online` VALUES  (2,2,'2008-12-02 12:47:12','10.1.27.209',NULL),
 (1,1,'2008-12-02 13:09:29','10.1.27.209',NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_online` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_open`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_open`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_open` (
  `open_id` int(11) NOT NULL auto_increment,
  `open_remote_host` tinytext collate utf8_spanish_ci NOT NULL,
  `open_agent` tinytext collate utf8_spanish_ci NOT NULL,
  `open_referer` tinytext collate utf8_spanish_ci NOT NULL,
  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`open_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_open`
--

/*!40000 ALTER TABLE `track_e_open` DISABLE KEYS */;
LOCK TABLES `track_e_open` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_open` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_stats`.`track_e_uploads`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_stats`.`track_e_uploads`;
CREATE TABLE  `{MYSQL_PREFIX}_stats`.`track_e_uploads` (
  `upload_id` int(11) NOT NULL auto_increment,
  `upload_user_id` int(10) unsigned default NULL,
  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `upload_cours_id` varchar(40) collate utf8_spanish_ci NOT NULL default '',
  `upload_work_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`upload_id`),
  KEY `upload_user_id` (`upload_user_id`),
  KEY `upload_cours_id` (`upload_cours_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_stats`.`track_e_uploads`
--

/*!40000 ALTER TABLE `track_e_uploads` DISABLE KEYS */;
LOCK TABLES `track_e_uploads` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `track_e_uploads` ENABLE KEYS */;

--
-- Create schema {MYSQL_PREFIX}_user
--

CREATE DATABASE IF NOT EXISTS {MYSQL_PREFIX}_user DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
USE {MYSQL_PREFIX}_user;

--
-- Definition of table `{MYSQL_PREFIX}_user`.`personal_agenda`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_user`.`personal_agenda`;
CREATE TABLE  `{MYSQL_PREFIX}_user`.`personal_agenda` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(10) unsigned default NULL,
  `title` text collate utf8_spanish_ci,
  `text` text collate utf8_spanish_ci,
  `date` datetime default NULL,
  `enddate` datetime default NULL,
  `course` varchar(255) collate utf8_spanish_ci default NULL,
  `parent_event_id` int(11) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_user`.`personal_agenda`
--

/*!40000 ALTER TABLE `personal_agenda` DISABLE KEYS */;
LOCK TABLES `personal_agenda` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `personal_agenda` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_user`.`personal_agenda_repeat`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_user`.`personal_agenda_repeat`;
CREATE TABLE  `{MYSQL_PREFIX}_user`.`personal_agenda_repeat` (
  `cal_id` int(11) NOT NULL default '0',
  `cal_type` varchar(20) collate utf8_spanish_ci default NULL,
  `cal_end` int(11) default NULL,
  `cal_frequency` int(11) default '1',
  `cal_days` char(7) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`cal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_user`.`personal_agenda_repeat`
--

/*!40000 ALTER TABLE `personal_agenda_repeat` DISABLE KEYS */;
LOCK TABLES `personal_agenda_repeat` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `personal_agenda_repeat` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_user`.`personal_agenda_repeat_not`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_user`.`personal_agenda_repeat_not`;
CREATE TABLE  `{MYSQL_PREFIX}_user`.`personal_agenda_repeat_not` (
  `cal_id` int(11) NOT NULL,
  `cal_date` int(11) NOT NULL,
  PRIMARY KEY  (`cal_id`,`cal_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_user`.`personal_agenda_repeat_not`
--

/*!40000 ALTER TABLE `personal_agenda_repeat_not` DISABLE KEYS */;
LOCK TABLES `personal_agenda_repeat_not` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `personal_agenda_repeat_not` ENABLE KEYS */;


--
-- Definition of table `{MYSQL_PREFIX}_user`.`user_course_category`
--

-- DROP TABLE IF EXISTS `{MYSQL_PREFIX}_user`.`user_course_category`;
CREATE TABLE  `{MYSQL_PREFIX}_user`.`user_course_category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `title` text collate utf8_spanish_ci NOT NULL,
  `sort` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `{MYSQL_PREFIX}_user`.`user_course_category`
--

/*!40000 ALTER TABLE `user_course_category` DISABLE KEYS */;
LOCK TABLES `user_course_category` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `user_course_category` ENABLE KEYS */;

--
-- Create schema {MYSQL_PREFIX}_main
--

CREATE DATABASE IF NOT EXISTS {MYSQL_PREFIX}_main DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
USE {MYSQL_PREFIX}_main;

--
-- Definition of procedure `{MYSQL_PREFIX}_main`.`pr_i_nuevos_campos_alumno`
--

DROP PROCEDURE IF EXISTS `{MYSQL_PREFIX}_main`.`pr_i_nuevos_campos_alumno`;

-- Añadimos nuevo campo NOTA_GLOBAL en tabla  COURSE_REL_USER_FD
ALTER TABLE `{MYSQL_PREFIX}_main`.`course_rel_user_fd` ADD nota_global INTEGER;

-- Añadimos un índice por username y password en la tabla USER de bd MAIN
ALTER TABLE `{MYSQL_PREFIX}_main`.`user` ADD INDEX `ind_login_password`(`username`, `password`);

-- CAMBIOS PARA LA FUNCIONALIDAD DE REDES SOCIALES:

-- Para poner la nueva visualización
update `{MYSQL_PREFIX}_main`.`settings_current` set selected_value='2column' where variable='homepage_view';

-- Para permitir ver el perfil de los alumnos
update `{MYSQL_PREFIX}_main`.`settings_current` set selected_value='true' where variable='show_tabs' and subkey='my_profile';

-- Para permitir el uso de fotos
update `{MYSQL_PREFIX}_main`.`settings_current` set selected_value='true' where variable='profile' and subkey='picture';

-- Todas las tablas para los diplomas
-- DROP TABLE IF EXISTS {MYSQL_PREFIX}_main.`diplomas_config`;
CREATE TABLE  {MYSQL_PREFIX}_main.`diplomas_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property` varchar(50) CHARACTER SET latin1 NOT NULL,
  `value` varchar(50) CHARACTER SET latin1 NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- DROP TABLE IF EXISTS {MYSQL_PREFIX}_main.`diplomas_courses`;
CREATE TABLE  {MYSQL_PREFIX}_main.`diplomas_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `back_text` longtext COLLATE utf8_spanish_ci NOT NULL,
  `design_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- DROP TABLE IF EXISTS {MYSQL_PREFIX}_main.`diplomas_design`;
CREATE TABLE  {MYSQL_PREFIX}_main.`diplomas_design` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `title` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `up_text` longtext COLLATE utf8_spanish_ci NOT NULL,
  `center_text` longtext COLLATE utf8_spanish_ci NOT NULL,
  `bottom_text` longtext COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- DROP TABLE IF EXISTS {MYSQL_PREFIX}_main.`diplomas_track`;
CREATE TABLE  {MYSQL_PREFIX}_main.`diplomas_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `download_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Insert necesarios para la configuración inicial de diplomas
Insert into {MYSQL_PREFIX}_main.diplomas_config (property,value,status) values ('GLOBALSCORE','50',1);
Insert into {MYSQL_PREFIX}_main.diplomas_config (property,value,status) values ('QUIZSCOREAVERAGE','50',0);
Insert into {MYSQL_PREFIX}_main.diplomas_config (property,value,status) values ('SCOSCOREAVERAGE','50',0);
Insert into {MYSQL_PREFIX}_main.diplomas_config (property,value,status) values ('DEFAULTTEMPLATE','1',1);
Insert into {MYSQL_PREFIX}_main.diplomas_design (image,title,up_text,center_text,bottom_text) values ('fondo.jpg','default','Upper text','Center text','Bottom text');

-- DROP TABLE IF EXISTS {MYSQL_PREFIX}_main.`redes_sociales`;
CREATE TABLE `{MYSQL_PREFIX}_main`.`redes_sociales` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150)  NOT NULL,
  `consumer_key` VARCHAR(250) ,
  `consumer_secret` VARCHAR(250) ,
  PRIMARY KEY (`id`)
)
ENGINE = MyISAM
CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Redes Sociales Apps
Insert into {MYSQL_PREFIX}_main.redes_sociales (name) values ('twitter');
Insert into {MYSQL_PREFIX}_main.redes_sociales (name) values ('facebook');

-- Proxy configuration
insert into {MYSQL_PREFIX}_main.settings_current (variable,type,category,title,comment) values ('proxyauth','textfield','Security','proxyauth','proxyauth_desc');
insert into {MYSQL_PREFIX}_main.settings_current (variable,type,category,title,comment) values ('proxyuserpwd','textfield','Security','proxyuserpwd','proxyuserpwd_desc');
insert into {MYSQL_PREFIX}_main.settings_current (variable,type,category,title,comment) values ('proxy','textfield','Security','proxy','proxy_desc');


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
