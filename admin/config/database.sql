-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: garden_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_log`
--

DROP TABLE IF EXISTS `access_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `gate_code_entered` varchar(20) DEFAULT NULL,
  `is_valid` tinyint(1) DEFAULT 0,
  `access_type` enum('entry','exit') DEFAULT 'entry',
  `accessed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `access_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_log`
--

LOCK TABLES `access_log` WRITE;
/*!40000 ALTER TABLE `access_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_answers`
--

DROP TABLE IF EXISTS `advice_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `answerer_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `credits_awarded` int(11) DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `answerer_id` (`answerer_id`),
  CONSTRAINT `advice_answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `advice_questions` (`id`),
  CONSTRAINT `advice_answers_ibfk_2` FOREIGN KEY (`answerer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_answers`
--

LOCK TABLES `advice_answers` WRITE;
/*!40000 ALTER TABLE `advice_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_questions`
--

DROP TABLE IF EXISTS `advice_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asker_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `status` enum('open','answered','closed') DEFAULT 'open',
  `best_answer_id` int(11) DEFAULT NULL,
  `asked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `asker_id` (`asker_id`),
  CONSTRAINT `advice_questions_ibfk_1` FOREIGN KEY (`asker_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_questions`
--

LOCK TABLES `advice_questions` WRITE;
/*!40000 ALTER TABLE `advice_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `target_table` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,'plot_created','land','plots',5,'Code: A-03, 15m², grid(3,1)','::1','2026-05-05 18:50:41'),(2,1,'logout','auth','users',1,'User logged out','::1','2026-05-05 19:41:03'),(3,4,'login','auth','users',4,'User logged in','::1','2026-05-05 19:41:11'),(4,4,'waitlist_joined','land','waitlist',1,NULL,'::1','2026-05-05 19:41:21'),(5,4,'waitlist_left','land','waitlist',4,NULL,'::1','2026-05-05 19:44:59'),(6,4,'waitlist_joined','land','waitlist',2,NULL,'::1','2026-05-05 19:45:00'),(7,4,'waitlist_left','land','waitlist',4,NULL,'::1','2026-05-05 19:45:02'),(8,4,'lease_created','land','leases',2,'Plot A-02 rented to user 4','::1','2026-05-05 19:46:41'),(9,4,'logout','auth','users',4,'User logged out','::1','2026-05-05 19:47:30'),(10,2,'login','auth','users',2,'User logged in','::1','2026-05-05 19:47:42'),(11,2,'file_uploaded','media','media_files',1,'Uploaded Done.png','::1','2026-05-05 19:50:17');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_transactions`
--

DROP TABLE IF EXISTS `billing_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lease_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank_transfer') DEFAULT 'cash',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('paid','pending','overdue','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lease_id` (`lease_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `billing_transactions_ibfk_1` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`),
  CONSTRAINT `billing_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_transactions`
--

LOCK TABLES `billing_transactions` WRITE;
/*!40000 ALTER TABLE `billing_transactions` DISABLE KEYS */;
INSERT INTO `billing_transactions` VALUES (1,2,4,144.00,'card','2026-05-05 19:46:41','paid',NULL);
/*!40000 ALTER TABLE `billing_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `broadcasts`
--

DROP TABLE IF EXISTS `broadcasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `broadcasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `affected_plots` text DEFAULT NULL,
  `site_status` enum('normal','warning','closed','emergency') DEFAULT 'warning',
  `is_false_alarm` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `broadcasts_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `broadcasts`
--

LOCK TABLES `broadcasts` WRITE;
/*!40000 ALTER TABLE `broadcasts` DISABLE KEYS */;
/*!40000 ALTER TABLE `broadcasts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compost_contributions`
--

DROP TABLE IF EXISTS `compost_contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compost_contributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount_kg` decimal(8,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `contributed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `compost_contributions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compost_contributions`
--

LOCK TABLES `compost_contributions` WRITE;
/*!40000 ALTER TABLE `compost_contributions` DISABLE KEYS */;
/*!40000 ALTER TABLE `compost_contributions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consumable_usage_log`
--

DROP TABLE IF EXISTS `consumable_usage_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumable_usage_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumable_id` int(11) NOT NULL,
  `used_by` int(11) NOT NULL,
  `amount_used` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `consumable_id` (`consumable_id`),
  KEY `used_by` (`used_by`),
  CONSTRAINT `consumable_usage_log_ibfk_1` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`),
  CONSTRAINT `consumable_usage_log_ibfk_2` FOREIGN KEY (`used_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumable_usage_log`
--

LOCK TABLES `consumable_usage_log` WRITE;
/*!40000 ALTER TABLE `consumable_usage_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `consumable_usage_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consumables`
--

DROP TABLE IF EXISTS `consumables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `unit` varchar(30) DEFAULT 'kg',
  `stock_level` decimal(10,2) DEFAULT 0.00,
  `reorder_threshold` decimal(10,2) DEFAULT 5.00,
  `alert_sent` tinyint(1) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumables`
--

LOCK TABLES `consumables` WRITE;
/*!40000 ALTER TABLE `consumables` DISABLE KEYS */;
INSERT INTO `consumables` VALUES (1,'Organic Fertilizer','kg',12.00,5.00,0,'2026-05-05 18:40:10'),(2,'Mulch','bags',3.00,5.00,0,'2026-05-05 18:40:10'),(3,'Plant Pots (small)','units',150.00,20.00,0,'2026-05-05 18:40:10'),(4,'Garden Twine','rolls',2.00,5.00,0,'2026-05-05 18:40:10');
/*!40000 ALTER TABLE `consumables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `damage_reports`
--

DROP TABLE IF EXISTS `damage_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `damage_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `description` text NOT NULL,
  `damage_type` enum('natural_wear','negligence','unknown') DEFAULT 'unknown',
  `repair_fee` decimal(10,2) DEFAULT 0.00,
  `is_exempt` tinyint(1) DEFAULT 0,
  `admin_reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tool_id` (`tool_id`),
  KEY `reported_by` (`reported_by`),
  KEY `admin_reviewed_by` (`admin_reviewed_by`),
  CONSTRAINT `damage_reports_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`),
  CONSTRAINT `damage_reports_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`),
  CONSTRAINT `damage_reports_ibfk_3` FOREIGN KEY (`admin_reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `damage_reports`
--

LOCK TABLES `damage_reports` WRITE;
/*!40000 ALTER TABLE `damage_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `damage_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donations`
--

DROP TABLE IF EXISTS `donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donor_id` int(11) NOT NULL,
  `produce_name` varchar(150) NOT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `karma_points_awarded` int(11) DEFAULT 0,
  `is_rejected` tinyint(1) DEFAULT 0,
  `rejection_reason` text DEFAULT NULL,
  `donated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `donor_id` (`donor_id`),
  CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donations`
--

LOCK TABLES `donations` WRITE;
/*!40000 ALTER TABLE `donations` DISABLE KEYS */;
/*!40000 ALTER TABLE `donations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_trades`
--

DROP TABLE IF EXISTS `flash_trades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flash_trades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `allergen_flag` tinyint(1) DEFAULT 0,
  `allergen_category` varchar(100) DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','claimed','expired','cancelled') DEFAULT 'active',
  `claimed_by` int(11) DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `seller_id` (`seller_id`),
  KEY `claimed_by` (`claimed_by`),
  CONSTRAINT `flash_trades_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  CONSTRAINT `flash_trades_ibfk_2` FOREIGN KEY (`claimed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_trades`
--

LOCK TABLES `flash_trades` WRITE;
/*!40000 ALTER TABLE `flash_trades` DISABLE KEYS */;
/*!40000 ALTER TABLE `flash_trades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidents`
--

DROP TABLE IF EXISTS `incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reported_by` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('open','in_process','resolved') DEFAULT 'open',
  `resolved_by` int(11) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reported_by` (`reported_by`),
  KEY `resolved_by` (`resolved_by`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`),
  CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidents`
--

LOCK TABLES `incidents` WRITE;
/*!40000 ALTER TABLE `incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inspections`
--

DROP TABLE IF EXISTS `inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_id` int(11) NOT NULL,
  `warden_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `photo_paths` text DEFAULT NULL,
  `result` enum('pass','warning','fail') NOT NULL,
  `violation_details` text DEFAULT NULL,
  `penalty_applied` decimal(10,2) DEFAULT 0.00,
  `inspected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plot_id` (`plot_id`),
  KEY `warden_id` (`warden_id`),
  CONSTRAINT `inspections_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`),
  CONSTRAINT `inspections_ibfk_2` FOREIGN KEY (`warden_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspections`
--

LOCK TABLES `inspections` WRITE;
/*!40000 ALTER TABLE `inspections` DISABLE KEYS */;
/*!40000 ALTER TABLE `inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leases`
--

DROP TABLE IF EXISTS `leases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `base_fee` decimal(10,2) NOT NULL,
  `soil_multiplier` decimal(4,2) DEFAULT 1.00,
  `membership_discount` decimal(4,2) DEFAULT 0.00,
  `total_fee` decimal(10,2) NOT NULL,
  `status` enum('active','expired','terminated','grace_period') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plot_id` (`plot_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `leases_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`),
  CONSTRAINT `leases_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leases`
--

LOCK TABLES `leases` WRITE;
/*!40000 ALTER TABLE `leases` DISABLE KEYS */;
INSERT INTO `leases` VALUES (1,1,3,'2025-01-01','2025-12-31',200.00,1.30,0.00,260.00,'active','2026-05-05 18:40:10'),(2,2,4,'2026-05-05','2027-05-05',144.00,1.00,0.00,144.00,'active','2026-05-05 19:46:40');
/*!40000 ALTER TABLE `leases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_files`
--

DROP TABLE IF EXISTS `media_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `media_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_files`
--

LOCK TABLES `media_files` WRITE;
/*!40000 ALTER TABLE `media_files` DISABLE KEYS */;
INSERT INTO `media_files` VALUES (1,2,'media_69fa49f9c6bb24.06501348.png','Done.png','image/png',12715972,'my pfp','2026-05-05 19:50:17');
/*!40000 ALTER TABLE `media_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications_log`
--

DROP TABLE IF EXISTS `notifications_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'general',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications_log`
--

LOCK TABLES `notifications_log` WRITE;
/*!40000 ALTER TABLE `notifications_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_perm` (`role_id`,`module`,`action`),
  CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (5,1,'land','approve'),(2,1,'land','create'),(4,1,'land','delete'),(3,1,'land','edit'),(1,1,'land','view'),(20,1,'marketplace','approve'),(17,1,'marketplace','create'),(19,1,'marketplace','delete'),(18,1,'marketplace','edit'),(16,1,'marketplace','view'),(10,1,'resources','approve'),(7,1,'resources','create'),(9,1,'resources','delete'),(8,1,'resources','edit'),(6,1,'resources','view'),(15,1,'volunteer','approve'),(12,1,'volunteer','create'),(14,1,'volunteer','delete'),(13,1,'volunteer','edit'),(11,1,'volunteer','view'),(22,2,'land','approve'),(23,2,'land','edit'),(21,2,'land','view'),(26,2,'marketplace','view'),(24,2,'resources','view'),(25,2,'volunteer','view'),(28,3,'land','create'),(29,3,'land','edit'),(27,3,'land','view'),(36,3,'marketplace','create'),(37,3,'marketplace','edit'),(35,3,'marketplace','view'),(31,3,'resources','create'),(32,3,'resources','edit'),(30,3,'resources','view'),(34,3,'volunteer','create'),(33,3,'volunteer','view'),(38,4,'land','view'),(44,4,'marketplace','create'),(43,4,'marketplace','view'),(40,4,'resources','create'),(39,4,'resources','view'),(42,4,'volunteer','create'),(41,4,'volunteer','view'),(45,5,'land','view'),(46,5,'marketplace','view');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pest_reports`
--

DROP TABLE IF EXISTS `pest_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pest_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `pest_type` varchar(100) NOT NULL,
  `severity` enum('low','medium','high') DEFAULT 'medium',
  `is_transmissible` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` enum('open','investigating','resolved') DEFAULT 'open',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plot_id` (`plot_id`),
  KEY `reported_by` (`reported_by`),
  CONSTRAINT `pest_reports_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`),
  CONSTRAINT `pest_reports_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pest_reports`
--

LOCK TABLES `pest_reports` WRITE;
/*!40000 ALTER TABLE `pest_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `pest_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plots`
--

DROP TABLE IF EXISTS `plots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_code` varchar(20) NOT NULL,
  `boundary_coords` text DEFAULT NULL,
  `area_sqm` decimal(8,2) DEFAULT NULL,
  `sunlight_level` enum('full','partial','shade') DEFAULT 'full',
  `soil_quality` enum('premium','standard','poor') DEFAULT 'standard',
  `status` enum('available','occupied','maintenance','reserved') DEFAULT 'available',
  `compliance_status` enum('compliant','warning','violation') DEFAULT 'compliant',
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `grid_x` tinyint(3) unsigned DEFAULT NULL,
  `grid_y` tinyint(3) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plot_code` (`plot_code`),
  UNIQUE KEY `unique_grid_cell` (`grid_x`,`grid_y`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plots`
--

LOCK TABLES `plots` WRITE;
/*!40000 ALTER TABLE `plots` DISABLE KEYS */;
INSERT INTO `plots` VALUES (1,'A-01','[[30.0444,31.2357],[30.0446,31.2357],[30.0446,31.2360],[30.0444,31.2360]]',24.00,'full','premium','occupied','compliant',30.04450000,31.23580000,1,1,'2026-05-05 18:40:10'),(2,'A-02','[[30.0448,31.2357],[30.0450,31.2357],[30.0450,31.2360],[30.0448,31.2360]]',18.00,'partial','standard','occupied','compliant',30.04490000,31.23580000,2,1,'2026-05-05 18:40:10'),(3,'B-01','[[30.0452,31.2357],[30.0454,31.2357],[30.0454,31.2360],[30.0452,31.2360]]',30.00,'full','premium','available','compliant',30.04530000,31.23580000,1,2,'2026-05-05 18:40:10'),(4,'B-02','[[30.0456,31.2357],[30.0458,31.2357],[30.0458,31.2360],[30.0456,31.2360]]',20.00,'shade','poor','maintenance','compliant',30.04570000,31.23580000,2,2,'2026-05-05 18:40:10'),(5,'A-03',NULL,15.00,'partial','standard','available','compliant',NULL,NULL,3,1,'2026-05-05 18:50:41');
/*!40000 ALTER TABLE `plots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produce_ratings`
--

DROP TABLE IF EXISTS `produce_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produce_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trade_id` int(11) NOT NULL,
  `rater_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `notes` text DEFAULT NULL,
  `rated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `trade_id` (`trade_id`),
  KEY `rater_id` (`rater_id`),
  CONSTRAINT `produce_ratings_ibfk_1` FOREIGN KEY (`trade_id`) REFERENCES `flash_trades` (`id`),
  CONSTRAINT `produce_ratings_ibfk_2` FOREIGN KEY (`rater_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produce_ratings`
--

LOCK TABLES `produce_ratings` WRITE;
/*!40000 ALTER TABLE `produce_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `produce_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proposals`
--

DROP TABLE IF EXISTS `proposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('open','closed','tie','decided') DEFAULT 'open',
  `winner_id` int(11) DEFAULT NULL,
  `voting_ends_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proposals`
--

LOCK TABLES `proposals` WRITE;
/*!40000 ALTER TABLE `proposals` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Full system access'),(2,'warden','Inspection and compliance access'),(3,'plot_owner','Plot management and marketplace'),(4,'member','Community features, no plot'),(5,'guest','View-only public access');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seeds`
--

DROP TABLE IF EXISTS `seeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `variety` varchar(100) DEFAULT NULL,
  `quantity_packets` int(11) DEFAULT 0,
  `stored_date` date NOT NULL,
  `expiry_months` int(11) NOT NULL DEFAULT 24,
  `status` enum('viable','nearing_expiry','expired','flagged_for_testing','recommended_planting') DEFAULT 'viable',
  `parent_plant_notes` text DEFAULT NULL,
  `allergen_category` varchar(100) DEFAULT NULL,
  `media_links` text DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `added_by` (`added_by`),
  CONSTRAINT `seeds_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seeds`
--

LOCK TABLES `seeds` WRITE;
/*!40000 ALTER TABLE `seeds` DISABLE KEYS */;
INSERT INTO `seeds` VALUES (1,'Tomato','Cherry Roma',12,'2024-06-01',4,'expired',NULL,'Nightshades',NULL,3),(2,'Basil','Sweet Genovese',8,'2025-01-01',24,'viable',NULL,NULL,NULL,3),(3,'Sunflower','Giant Russian',20,'2024-01-01',18,'expired',NULL,NULL,NULL,1),(4,'Carrot','Nantes',15,'2025-03-01',36,'viable',NULL,NULL,NULL,3),(5,'Peanut','Valencia',6,'2025-02-01',12,'expired',NULL,'Tree Nuts',NULL,1);
/*!40000 ALTER TABLE `seeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_hour_requirements`
--

DROP TABLE IF EXISTS `service_hour_requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_hour_requirements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `required_hours` decimal(5,2) DEFAULT 4.00,
  `effective_from` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_hour_requirements`
--

LOCK TABLES `service_hour_requirements` WRITE;
/*!40000 ALTER TABLE `service_hour_requirements` DISABLE KEYS */;
INSERT INTO `service_hour_requirements` VALUES (1,4.00,'2025-01-01');
/*!40000 ALTER TABLE `service_hour_requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_hours`
--

DROP TABLE IF EXISTS `service_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hours_logged` decimal(5,2) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `month_year` varchar(7) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `service_hours_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `service_hours_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_hours`
--

LOCK TABLES `service_hours` WRITE;
/*!40000 ALTER TABLE `service_hours` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_hours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_swap_requests`
--

DROP TABLE IF EXISTS `shift_swap_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_swap_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected','expired') DEFAULT 'pending',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_id` (`shift_id`),
  KEY `requester_id` (`requester_id`),
  KEY `target_id` (`target_id`),
  CONSTRAINT `shift_swap_requests_ibfk_1` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`),
  CONSTRAINT `shift_swap_requests_ibfk_2` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`),
  CONSTRAINT `shift_swap_requests_ibfk_3` FOREIGN KEY (`target_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_swap_requests`
--

LOCK TABLES `shift_swap_requests` WRITE;
/*!40000 ALTER TABLE `shift_swap_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `shift_swap_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `status` enum('scheduled','completed','cancelled','swapped') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `shifts_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `soil_events`
--

DROP TABLE IF EXISTS `soil_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `soil_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` enum('fertilizer','ph_test','crop_rotation','amendment','other') NOT NULL,
  `fertilizer_type` varchar(100) DEFAULT NULL,
  `ph_level` decimal(4,2) DEFAULT NULL,
  `crop_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_at_risk` tinyint(1) DEFAULT 0,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plot_id` (`plot_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `soil_events_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`),
  CONSTRAINT `soil_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `soil_events`
--

LOCK TABLES `soil_events` WRITE;
/*!40000 ALTER TABLE `soil_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `soil_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_completions`
--

DROP TABLE IF EXISTS `task_completions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_completions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `completion_type` enum('full','partial') DEFAULT 'full',
  `points_awarded` int(11) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `verified_by` (`verified_by`),
  CONSTRAINT `task_completions_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  CONSTRAINT `task_completions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `task_completions_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_completions`
--

LOCK TABLES `task_completions` WRITE;
/*!40000 ALTER TABLE `task_completions` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_completions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `difficulty_score` int(11) DEFAULT 1,
  `points_reward` int(11) DEFAULT 10,
  `status` enum('open','in_progress','partial','completed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,'Mow the main path','Cut grass along path A and B',2,20,'open',NULL,1,NULL,'2026-05-05 18:40:10'),(2,'Turn compost pile','Mix and aerate the large pile',4,40,'open',NULL,1,NULL,'2026-05-05 18:40:10'),(3,'Clear plot B-02 weeds','Remove all weeds from plot B-02',3,30,'open',NULL,1,NULL,'2026-05-05 18:40:10'),(4,'Clean shared tool storage','Organise and clean the tool shed',2,20,'open',NULL,1,NULL,'2026-05-05 18:40:10');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tool_penalties`
--

DROP TABLE IF EXISTS `tool_penalties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tool_penalties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `days_late` int(11) NOT NULL,
  `penalty_type` enum('fine','community_service_hours') DEFAULT 'fine',
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `service_hours` decimal(5,2) DEFAULT 0.00,
  `status` enum('pending','paid','served','waived') DEFAULT 'pending',
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tool_penalties_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `tool_reservations` (`id`),
  CONSTRAINT `tool_penalties_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tool_penalties`
--

LOCK TABLES `tool_penalties` WRITE;
/*!40000 ALTER TABLE `tool_penalties` DISABLE KEYS */;
/*!40000 ALTER TABLE `tool_penalties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tool_reservations`
--

DROP TABLE IF EXISTS `tool_reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tool_reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `slot_start` time NOT NULL,
  `slot_end` time NOT NULL,
  `status` enum('confirmed','cancelled','completed','overdue') DEFAULT 'confirmed',
  `due_date` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tool_id` (`tool_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tool_reservations_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`),
  CONSTRAINT `tool_reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tool_reservations`
--

LOCK TABLES `tool_reservations` WRITE;
/*!40000 ALTER TABLE `tool_reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `tool_reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tool_state_log`
--

DROP TABLE IF EXISTS `tool_state_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tool_state_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tool_id` (`tool_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `tool_state_log_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`),
  CONSTRAINT `tool_state_log_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tool_state_log`
--

LOCK TABLES `tool_state_log` WRITE;
/*!40000 ALTER TABLE `tool_state_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `tool_state_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tools`
--

DROP TABLE IF EXISTS `tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('available','checked_out','in_repair','decommissioned','missing') DEFAULT 'available',
  `total_usage_hours` decimal(8,2) DEFAULT 0.00,
  `maintenance_threshold_hours` decimal(8,2) DEFAULT 50.00,
  `needs_maintenance` tinyint(1) DEFAULT 0,
  `last_maintained_at` timestamp NULL DEFAULT NULL,
  `media_links` text DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tools`
--

LOCK TABLES `tools` WRITE;
/*!40000 ALTER TABLE `tools` DISABLE KEYS */;
INSERT INTO `tools` VALUES (1,'Rotavator','Heavy-duty soil tiller','available',42.00,50.00,0,NULL,NULL,NULL,'2026-05-05 18:40:10'),(2,'Wheelbarrow','Large capacity wheelbarrow','available',5.00,100.00,0,NULL,NULL,NULL,'2026-05-05 18:40:10'),(3,'Lawnmower','Electric lawnmower','checked_out',78.00,50.00,0,NULL,NULL,NULL,'2026-05-05 18:40:10'),(4,'Garden Fork','Heavy duty digging fork','in_repair',15.00,200.00,0,NULL,NULL,NULL,'2026-05-05 18:40:10'),(5,'Hose Reel','30m hose with spray nozzle','available',8.00,500.00,0,NULL,NULL,NULL,'2026-05-05 18:40:10');
/*!40000 ALTER TABLE `tools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 5,
  `phone` varchar(20) DEFAULT NULL,
  `gate_code` varchar(20) DEFAULT NULL,
  `membership_status` enum('standard','premium','senior') DEFAULT 'standard',
  `community_points` int(11) DEFAULT 0,
  `karma_points` int(11) DEFAULT 0,
  `seed_bank_credits` int(11) DEFAULT 0,
  `residency_months` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `gate_code` (`gate_code`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Garden Admin','admin@garden.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NULL,'GATE001','premium',0,0,0,0,1,'2026-05-05 18:40:10'),(2,'Sam Warden','warden@garden.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',2,NULL,'GATE002','standard',0,0,0,0,1,'2026-05-05 18:40:10'),(3,'Alice Owner','alice@garden.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',3,NULL,'GATE003','standard',45,0,0,12,1,'2026-05-05 18:40:10'),(4,'Bob Member','bob@garden.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',4,NULL,'GATE004','standard',0,0,0,0,1,'2026-05-05 18:40:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proposal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`proposal_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`),
  CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `votes`
--

LOCK TABLES `votes` WRITE;
/*!40000 ALTER TABLE `votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `waitlist`
--

DROP TABLE IF EXISTS `waitlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `waitlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `priority_score` decimal(8,2) DEFAULT 0.00,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('waiting','notified','accepted','declined') DEFAULT 'waiting',
  `notified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `waitlist`
--

LOCK TABLES `waitlist` WRITE;
/*!40000 ALTER TABLE `waitlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `waitlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-05 23:00:23
