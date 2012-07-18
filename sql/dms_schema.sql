-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: dms
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

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
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `istat_code` varchar(9) DEFAULT NULL,
  `province` varchar(2) DEFAULT NULL,
  `postal_code` varchar(5) DEFAULT NULL,
  `region` varchar(45) DEFAULT NULL,
  `name` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8095 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `comments_document_id` (`document_id`) USING BTREE,
  KEY `comments_user_id` (`user_id`) USING BTREE,
  CONSTRAINT `comments_document_id` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `comments_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `document_history`
--

DROP TABLE IF EXISTS `document_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `revision` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `log_user_id` (`user_id`) USING BTREE,
  KEY `log_document_id` (`document_id`) USING BTREE,
  CONSTRAINT `document_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `document_history_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `document_type` tinyint(4) NOT NULL,
  `main_document_type` tinyint(4) NOT NULL DEFAULT '0',
  `creator_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revision` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `publication_status` tinyint(4) NOT NULL DEFAULT '0',
  `publication_requested` tinyint(4) NOT NULL DEFAULT '0',
  `sender_id` int(11) DEFAULT NULL,
  `date_received` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `priority` int(11) DEFAULT '1',
  `entity` varchar(128) DEFAULT NULL,
  `proposer_service` varchar(128) DEFAULT NULL,
  `act_number` varchar(128) DEFAULT NULL,
  `act_date` date DEFAULT NULL,
  `publication_date_from` timestamp NULL DEFAULT NULL,
  `publication_date_to` timestamp NULL DEFAULT NULL,
  `last_updater_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `num_pages` int(11) NOT NULL,
  `document_size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier_UNIQUE` (`identifier`) USING BTREE,
  KEY `document_creator_id` (`creator_id`) USING BTREE,
  KEY `document_sender_id` (`sender_id`) USING BTREE,
  KEY `last_updater_id` (`last_updater_id`) USING BTREE,
  CONSTRAINT `documents_creator_id` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `documents_last_updater_id` FOREIGN KEY (`last_updater_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `documents_sender_id` FOREIGN KEY (`sender_id`) REFERENCES `senders` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_folders`
--

DROP TABLE IF EXISTS `documents_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents_folders` (
  `document_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`document_id`,`folder_id`) USING BTREE,
  KEY `document_id` (`document_id`),
  KEY `folder_id` (`folder_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `documents_folders_document_id_fk` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `documents_folders_folder_id_fk` FOREIGN KEY (`folder_id`) REFERENCES `user_gui_folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `documents_folders_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_rights`
--

DROP TABLE IF EXISTS `documents_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents_rights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `right` tinyint(4) NOT NULL DEFAULT '1',
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `drights_group_id_idx` (`group_id`),
  KEY `drights_user_id_idx` (`user_id`),
  KEY `drights_document_id_idx` (`document_id`),
  CONSTRAINT `documents_rights_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `documents_rights_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `documents_rights_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `telephone` varchar(45) DEFAULT NULL,
  `fax` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `folder_name` varchar(100) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uffici_nome_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indirizzo_ip` varchar(40) NOT NULL,
  `login` varchar(100) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `protocol_sessions`
--

DROP TABLE IF EXISTS `protocol_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `protocol_sessions` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rights`
--

DROP TABLE IF EXISTS `rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(45) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rights_key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles_rights`
--

DROP TABLE IF EXISTS `roles_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_rights` (
  `role_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`right_id`),
  KEY `right_id` (`role_id`),
  KEY `role_id` (`role_id`),
  KEY `roles_rights_right_id_fk` (`right_id`),
  CONSTRAINT `roles_rights_right_id_fk` FOREIGN KEY (`right_id`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `roles_rights_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `senders`
--

DROP TABLE IF EXISTS `senders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `senders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` tinytext NOT NULL,
  `province` varchar(45) NOT NULL,
  `country_id` int(11) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `postal_code` varchar(45) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1',
  `city` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`) USING BTREE,
  KEY `city_id` (`city_id`) USING BTREE,
  CONSTRAINT `senders_city_id_fk` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `senders_country_id_fk` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_name_UNIQUE` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags_documents`
--

DROP TABLE IF EXISTS `tags_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags_documents` (
  `tag_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`document_id`) USING BTREE,
  KEY `tags_has_documenti_tag_id` (`tag_id`),
  KEY `tags_has_documenti_documento_id` (`document_id`) USING BTREE,
  CONSTRAINT `tags_documents_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tags_documents_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_gui_folders`
--

DROP TABLE IF EXISTS `user_gui_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_gui_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `folder_name` varchar(128) NOT NULL,
  `folder_key` varchar(128) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `folder_key` (`folder_key`),
  CONSTRAINT `user_gui_folders_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `document_id` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `document_id` (`document_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `telephone` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(100) NOT NULL,
  `salt` varchar(128) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `last_ip` varchar(40) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `new_password_key` varchar(128) DEFAULT NULL,
  `new_password_requested` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `num_logins` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_UNIQUE` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_document_requests`
--

DROP TABLE IF EXISTS `users_document_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_document_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `replier_id` int(11) DEFAULT NULL,
  `request` text NOT NULL,
  `access_level` tinyint(4) NOT NULL,
  `reply` text,
  `granted_access_level` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `document_id` (`document_id`),
  KEY `replier_id` (`replier_id`),
  CONSTRAINT `users_doc_request_document_id` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_doc_request_replier_id_fk` FOREIGN KEY (`replier_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_doc_request_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_groups` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`) USING BTREE,
  KEY `users_groups_user_id` (`user_id`) USING BTREE,
  KEY `users_groups_group_id` (`group_id`) USING BTREE,
  CONSTRAINT `users_groups_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_groups_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_roles_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_roles_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-07-18 11:05:07
