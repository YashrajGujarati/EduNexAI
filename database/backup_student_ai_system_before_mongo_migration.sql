-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: student_ai_system
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
-- Table structure for table `assignment_submissions`
--

DROP TABLE IF EXISTS `assignment_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_text` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('submitted','late','graded') DEFAULT 'submitted',
  `marks_obtained` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`submission_id`),
  UNIQUE KEY `unique_student_assignment` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_submissions`
--

LOCK TABLES `assignment_submissions` WRITE;
/*!40000 ALTER TABLE `assignment_submissions` DISABLE KEYS */;
INSERT INTO `assignment_submissions` VALUES (1,1,1,'Submitted code for BST and Graph Traversal algorithms with test cases.',NULL,'2026-10-10 09:00:00','graded',48,'Outstanding implementation and clean modular code architecture!'),(2,1,2,'Completed assignment with C++ solution.',NULL,'2026-10-12 05:50:00','graded',42,'Good attempt. Ensure memory deallocation for dynamic nodes.'),(3,2,4,'Submitted python notebook containing DecisionTree and RandomForest accuracy report.',NULL,'2026-10-14 11:15:00','graded',95,'Excellent hyperparameter tuning and visualization plots.'),(4,3,1,'SQL scripts and ER diagrams attached.',NULL,'2026-10-11 03:45:00','graded',49,'Flawless 3NF normalization.');
/*!40000 ALTER TABLE `assignment_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `due_date` datetime NOT NULL,
  `total_marks` int(11) DEFAULT 100,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assignment_id`),
  KEY `subject_id` (`subject_id`),
  KEY `faculty_id` (`faculty_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (1,'Binary Search Tree & Graph Traversal Implementation','Write clean C++/Java code implementing BST insert, search, delete, BFS, and DFS operations.',1,2,'2026-10-15 23:59:00',50,NULL,'2026-09-25 02:57:17'),(2,'Machine Learning Supervised Classifier Project','Train a Decision Tree and Random Forest model on the provided dataset and submit report with metrics.',2,3,'2026-10-20 23:59:00',100,NULL,'2026-09-25 02:57:17'),(3,'Database ER Model & SQL Normalization','Design an normalized 3NF relational schema for an e-commerce platform and write 10 complex SQL queries.',3,2,'2026-10-18 23:59:00',50,NULL,'2026-09-25 02:57:17'),(4,'Responsive Full-Stack Portfolio App','Build a modern web app using HTML5, CSS3, JS, and PHP with MySQL integration.',4,4,'2026-10-25 23:59:00',100,NULL,'2026-09-25 02:57:17');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `present_days` int(11) DEFAULT 0,
  `total_days` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`attendance_id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,1,47,50,94.00),(2,1,2,48,50,96.00),(3,1,3,46,50,92.00),(4,2,1,44,50,88.00),(5,2,3,45,50,90.00),(6,3,2,38,50,76.00),(7,3,4,39,50,78.00),(8,4,2,46,50,92.00),(9,5,4,29,50,58.00),(10,5,5,28,50,56.00),(11,6,1,49,50,98.00),(12,6,3,48,50,96.00),(13,7,4,32,50,64.00),(14,8,2,41,50,82.00),(15,9,1,26,50,52.00),(16,10,4,45,50,90.00);
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_logs`
--

DROP TABLE IF EXISTS `attendance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `topic` varchar(255) DEFAULT NULL,
  `attendance_date` date DEFAULT curdate(),
  `total_students` int(11) DEFAULT 0,
  `present_count` int(11) DEFAULT 0,
  `absent_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_logs`
--

LOCK TABLES `attendance_logs` WRITE;
/*!40000 ALTER TABLE `attendance_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_payments`
--

DROP TABLE IF EXISTS `fee_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `receipt_no` varchar(50) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('success','failed','pending') DEFAULT 'success',
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  UNIQUE KEY `receipt_no` (`receipt_no`),
  KEY `fee_id` (`fee_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`fee_id`) REFERENCES `student_fees` (`fee_id`) ON DELETE CASCADE,
  CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_payments`
--

LOCK TABLES `fee_payments` WRITE;
/*!40000 ALTER TABLE `fee_payments` DISABLE KEYS */;
INSERT INTO `fee_payments` VALUES (1,1,1,50000.00,'UPI','TXN982734101','RCPT-2026-001','2026-09-01 04:45:00','success'),(2,2,2,50000.00,'Credit Card','TXN982734102','RCPT-2026-002','2026-09-02 06:00:00','success'),(3,3,3,25000.00,'Net Banking','TXN982734103','RCPT-2026-003','2026-09-05 08:50:00','success'),(4,4,4,50000.00,'UPI','TXN982734104','RCPT-2026-004','2026-09-03 04:15:00','success'),(5,6,6,50000.00,'UPI','TXN982734106','RCPT-2026-006','2026-09-04 09:40:00','success'),(6,7,7,25000.00,'Debit Card','TXN982734107','RCPT-2026-007','2026-09-10 11:20:00','success'),(7,8,8,50000.00,'UPI','TXN982734108','RCPT-2026-008','2026-09-06 06:30:00','success'),(8,10,10,50000.00,'Net Banking','TXN982734110','RCPT-2026-010','2026-09-08 12:00:00','success');
/*!40000 ALTER TABLE `fee_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marks`
--

DROP TABLE IF EXISTS `marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marks` (
  `mark_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `internal_marks` int(11) DEFAULT 0,
  `external_marks` int(11) DEFAULT 0,
  `total_marks` int(11) DEFAULT 0,
  `exam_date` date DEFAULT NULL,
  PRIMARY KEY (`mark_id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marks`
--

LOCK TABLES `marks` WRITE;
/*!40000 ALTER TABLE `marks` DISABLE KEYS */;
INSERT INTO `marks` VALUES (1,1,1,28,65,93,'2026-05-10'),(2,1,2,29,66,95,'2026-05-12'),(3,1,3,27,63,90,'2026-05-15'),(4,2,1,24,58,82,'2026-05-10'),(5,2,3,25,60,85,'2026-05-15'),(6,2,6,23,56,79,'2026-05-18'),(7,3,2,20,48,68,'2026-05-12'),(8,3,4,21,50,71,'2026-05-16'),(9,4,2,28,64,92,'2026-05-12'),(10,4,4,27,62,89,'2026-05-16'),(11,5,4,12,32,44,'2026-05-16'),(12,5,5,14,30,44,'2026-05-20'),(13,6,1,30,68,98,'2026-05-10'),(14,6,3,29,67,96,'2026-05-15'),(15,7,4,18,42,60,'2026-05-16'),(16,7,5,17,40,57,'2026-05-20'),(17,8,2,24,57,81,'2026-05-12'),(18,9,1,10,25,35,'2026-05-10'),(19,9,6,11,28,39,'2026-05-18'),(20,10,4,26,61,87,'2026-05-16');
/*!40000 ALTER TABLE `marks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prediction_history`
--

DROP TABLE IF EXISTS `prediction_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prediction_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `predicted_score` decimal(5,2) DEFAULT NULL,
  `result` varchar(20) DEFAULT NULL,
  `risk_level` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `prediction_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prediction_history`
--

LOCK TABLES `prediction_history` WRITE;
/*!40000 ALTER TABLE `prediction_history` DISABLE KEYS */;
INSERT INTO `prediction_history` VALUES (1,1,93.00,'Excellent','None','2026-09-25 02:57:17'),(2,2,82.00,'Good','Low','2026-09-25 02:57:17'),(3,3,70.00,'Good','Low','2026-09-25 02:57:17'),(4,4,91.00,'Excellent','None','2026-09-25 02:57:17'),(5,5,44.00,'Poor','High','2026-09-25 02:57:17'),(6,6,97.00,'Excellent','None','2026-09-25 02:57:17'),(7,7,58.00,'Average','Medium','2026-09-25 02:57:17'),(8,8,81.00,'Good','Low','2026-09-25 02:57:17'),(9,9,37.00,'Poor','High','2026-09-25 02:57:17'),(10,10,87.00,'Good','Low','2026-09-25 02:57:17'),(11,1,93.00,'Excellent','None','2026-09-25 03:20:23'),(12,1,93.00,'Excellent','None','2026-09-25 03:20:53'),(13,1,93.00,'Excellent','None','2026-09-25 03:43:16'),(14,1,93.00,'Excellent','None','2026-09-25 03:43:20'),(15,1,93.00,'The system cannot ex','High','2026-09-25 05:04:04'),(16,1,93.00,'The system cannot ex','High','2026-09-25 05:04:19'),(17,1,93.00,'The system cannot ex','High','2026-09-25 05:05:28'),(18,1,93.00,'Excellent','None','2026-09-25 05:06:24'),(19,1,93.00,'Excellent','None','2026-09-25 16:18:32');
/*!40000 ALTER TABLE `prediction_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `predictions`
--

DROP TABLE IF EXISTS `predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `predictions` (
  `prediction_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `predicted_score` decimal(5,2) DEFAULT NULL,
  `result` varchar(20) DEFAULT NULL,
  `risk_level` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`prediction_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `predictions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `predictions`
--

LOCK TABLES `predictions` WRITE;
/*!40000 ALTER TABLE `predictions` DISABLE KEYS */;
INSERT INTO `predictions` VALUES (1,1,93.00,'Excellent','None','2026-09-25 02:57:17'),(2,2,82.00,'Good','Low','2026-09-25 02:57:17'),(3,3,70.00,'Good','Low','2026-09-25 02:57:17'),(4,4,91.00,'Excellent','None','2026-09-25 02:57:17'),(5,5,44.00,'Poor','High','2026-09-25 02:57:17'),(6,6,97.00,'Excellent','None','2026-09-25 02:57:17'),(7,7,58.00,'Average','Medium','2026-09-25 02:57:17'),(8,8,81.00,'Good','Low','2026-09-25 02:57:17'),(9,9,37.00,'Poor','High','2026-09-25 02:57:17'),(10,10,87.00,'Good','Low','2026-09-25 02:57:17');
/*!40000 ALTER TABLE `predictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_fees`
--

DROP TABLE IF EXISTS `student_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_fees` (
  `fee_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `total_fee` decimal(10,2) NOT NULL DEFAULT 50000.00,
  `paid_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `status` enum('paid','partial','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`fee_id`),
  UNIQUE KEY `student_id` (`student_id`),
  CONSTRAINT `student_fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_fees`
--

LOCK TABLES `student_fees` WRITE;
/*!40000 ALTER TABLE `student_fees` DISABLE KEYS */;
INSERT INTO `student_fees` VALUES (1,1,50000.00,50000.00,'2026-11-01','paid','2026-09-25 02:57:17'),(2,2,50000.00,50000.00,'2026-11-01','paid','2026-09-25 02:57:17'),(3,3,50000.00,25000.00,'2026-10-30','partial','2026-09-25 02:57:17'),(4,4,50000.00,50000.00,'2026-11-01','paid','2026-09-25 02:57:17'),(5,5,50000.00,0.00,'2026-10-15','pending','2026-09-25 02:57:17'),(6,6,50000.00,50000.00,'2026-11-01','paid','2026-09-25 02:57:17'),(7,7,50000.00,25000.00,'2026-10-30','partial','2026-09-25 02:57:17'),(8,8,50000.00,50000.00,'2026-11-01','paid','2026-09-25 02:57:17'),(9,9,50000.00,0.00,'2026-10-15','pending','2026-09-25 02:57:17'),(10,10,50000.00,50000.00,'2026-11-01','paid','2026-09-25 02:57:17');
/*!40000 ALTER TABLE `student_fees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `class` varchar(50) DEFAULT NULL,
  `roll_number` varchar(20) DEFAULT NULL,
  `attendance` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`student_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,5,'BTech-CS','CS-101',94.50),(2,6,'BTech-CS','CS-102',88.00),(3,7,'BTech-AI','AI-201',76.50),(4,8,'BTech-AI','AI-202',91.20),(5,9,'BTech-IT','IT-301',58.00),(6,10,'BTech-CS','CS-103',96.00),(7,11,'BTech-IT','IT-302',64.00),(8,12,'BTech-AI','AI-203',82.50),(9,13,'BTech-CS','CS-104',52.00),(10,14,'BTech-IT','IT-303',89.00);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`subject_id`),
  KEY `faculty_id` (`faculty_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'Data Structures & Algorithms','CS301',2),(2,'Machine Learning & AI','AI401',3),(3,'Database Management Systems','CS302',2),(4,'Web Application Development','IT301',4),(5,'Computer Networks & Security','IT302',4),(6,'Operating Systems','CS303',2);
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','faculty','student') NOT NULL,
  `enrollment_no` varchar(50) DEFAULT NULL,
  `first_login` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `enrollment_no` (`enrollment_no`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Administrator','admin@edunexai.com','$2y$10$kVEC449w.Tz8xXJ2PBv1vehylbB9A4N2aDKf5/RCjk2Pi6ZMmqD9i','admin',NULL,0,'2026-09-25 02:57:17'),(2,'Prof. Rajesh Sharma','prof.sharma@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','faculty',NULL,0,'2026-09-25 02:57:17'),(3,'Dr. Ananya Verma','prof.verma@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','faculty',NULL,0,'2026-09-25 02:57:17'),(4,'Prof. Vikram Gupta','prof.gupta@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','faculty',NULL,0,'2026-09-25 02:57:17'),(5,'Aarav Mehta','aarav@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026001',0,'2026-09-25 02:57:17'),(6,'Priya Patel','priya@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026002',0,'2026-09-25 02:57:17'),(7,'Rohan Deshmukh','rohan@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026003',0,'2026-09-25 02:57:17'),(8,'Ananya Sen','ananya@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026004',0,'2026-09-25 02:57:17'),(9,'Kabir Nair','kabir@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026005',0,'2026-09-25 02:57:17'),(10,'Sneha Joshi','sneha@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026006',0,'2026-09-25 02:57:17'),(11,'Aditya Rao','aditya@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026007',0,'2026-09-25 02:57:17'),(12,'Diya Kapoor','diya@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026008',0,'2026-09-25 02:57:17'),(13,'Siddharth Iyer','siddharth@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026009',0,'2026-09-25 02:57:17'),(14,'Neha Redy','neha@edunexai.com','$2y$10$.R5nm0jOg/imNvBC0eaL4uyuM5ZobErJe3Wcrgvhj7pWbaQKxDHu.','student','EN2026010',0,'2026-09-25 02:57:17');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-04 10:59:52
