-- MariaDB dump 10.18  Distrib 10.5.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: buildsysdb
-- ------------------------------------------------------
-- Server version	10.5.8-MariaDB-1:10.5.8+maria~focal

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
-- Table structure for table `author`
--

DROP TABLE IF EXISTS `author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `author` (
  `id` int(11) NOT NULL,
  `sequence` smallint(6) DEFAULT NULL,
  `name` varchar(768) DEFAULT NULL,
  `email` varchar(768) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clsfile`
--

DROP TABLE IF EXISTS `clsfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clsfile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(768) DEFAULT NULL,
  `macro` varchar(768) DEFAULT NULL,
  `styfilename` varchar(768) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `styfilename` (`styfilename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbversion`
--

DROP TABLE IF EXISTS `dbversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbversion` (
  `dbversion` smallint(6) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dmake_status`
--

DROP TABLE IF EXISTS `dmake_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dmake_status` (
  `id` int(11) NOT NULL,
  `started` datetime DEFAULT NULL,
  `directory` varchar(768) DEFAULT NULL,
  `num_files` int(11) DEFAULT NULL,
  `num_hosts` smallint(6) DEFAULT NULL,
  `hostnames` text DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `errlog_detail`
--

DROP TABLE IF EXISTS `errlog_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `errlog_detail` (
  `document_id` int(11) NOT NULL,
  `pos` smallint(6) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `target` varchar(64) DEFAULT NULL,
  `errclass` varchar(256) DEFAULT NULL,
  `errtype` varchar(256) DEFAULT NULL,
  `errmsg` varchar(6000) DEFAULT NULL,
  `errobject` varchar(800) DEFAULT NULL,
  `md5_errmsg` char(128) DEFAULT NULL,
  UNIQUE KEY `document_id` (`document_id`,`pos`),
  KEY `dt` (`document_id`,`target`),
  KEY `mi` (`md5_errmsg`),
  KEY `ti` (`errtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help` (
  `id` varchar(128) NOT NULL,
  `title` varchar(512) DEFAULT NULL,
  `html` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_created` date DEFAULT NULL,
  `show_entry` smallint(6) DEFAULT NULL,
  `latexml_version` varchar(768) DEFAULT NULL,
  `sty_version` varchar(768) DEFAULT NULL,
  `retval_unknown` int(11) DEFAULT NULL,
  `retval_no_tex` int(11) DEFAULT NULL,
  `retval_missing_errlog` int(11) DEFAULT NULL,
  `retval_timeout` int(11) DEFAULT NULL,
  `retval_fatal_error` int(11) DEFAULT NULL,
  `retval_missing_macros` int(11) DEFAULT NULL,
  `retval_error` int(11) DEFAULT NULL,
  `retval_warning` int(11) DEFAULT NULL,
  `retval_no_problems` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_file`
--

DROP TABLE IF EXISTS `history_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_file` (
  `id` int(11) NOT NULL,
  `set` varchar(128) DEFAULT NULL,
  `filename` varchar(768) DEFAULT NULL,
  `date_snapshot` datetime DEFAULT NULL,
  `show_entry` tinyint(1) DEFAULT NULL,
  `target` varchar(128) DEFAULT NULL,
  `retval` varchar(128) DEFAULT NULL,
  `num_warning` smallint(6) NOT NULL DEFAULT 0,
  `num_error` smallint(6) NOT NULL DEFAULT 0,
  `num_macro` smallint(6) NOT NULL DEFAULT 0,
  `missing_macros` varchar(1000) NOT NULL DEFAULT '0',
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sftr` (`set`(64),`filename`(576),`target`(64),`retval`(64)),
  KEY `ds` (`date_snapshot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_sum`
--

DROP TABLE IF EXISTS `history_sum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_sum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set` varchar(128) DEFAULT NULL,
  `date_snapshot` datetime DEFAULT NULL,
  `show_entry` tinyint(1) DEFAULT NULL,
  `target` varchar(128) DEFAULT NULL,
  `retval_unknown` int(11) NOT NULL DEFAULT 0,
  `retval_not_qualified` int(11) NOT NULL DEFAULT 0,
  `retval_missing_errlog` int(11) NOT NULL DEFAULT 0,
  `retval_timeout` int(11) NOT NULL DEFAULT 0,
  `retval_fatal_error` int(11) NOT NULL DEFAULT 0,
  `retval_missing_macros` int(11) NOT NULL DEFAULT 0,
  `retval_missing_figure` int(11) NOT NULL DEFAULT 0,
  `retval_missing_bib` int(11) NOT NULL DEFAULT 0,
  `retval_missing_file` int(11) NOT NULL DEFAULT 0,
  `retval_error` int(11) NOT NULL DEFAULT 0,
  `retval_warning` int(11) NOT NULL DEFAULT 0,
  `retval_no_problems` int(11) NOT NULL DEFAULT 0,
  `sum_warning` smallint(6) DEFAULT NULL,
  `sum_error` smallint(6) DEFAULT NULL,
  `sum_macro` smallint(6) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `s` (`set`),
  KEY `ds` (`date_snapshot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `macro`
--

DROP TABLE IF EXISTS `macro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `macro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set` varchar(128) DEFAULT NULL,
  `macro` varchar(630) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `styfilename` varchar(768) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sm` (`set`,`macro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mmfile`
--

DROP TABLE IF EXISTS `mmfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mmfile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set` varchar(32) DEFAULT NULL,
  `filename` varchar(768) DEFAULT NULL,
  `macro` varchar(768) DEFAULT NULL,
  `styfilename` varchar(768) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `styfilename` (`styfilename`),
  KEY `fm` (`filename`(384),`macro`(384))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_usage`
--

DROP TABLE IF EXISTS `package_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set` varchar(128) DEFAULT NULL,
  `filename` varchar(768) DEFAULT NULL,
  `styfilename` varchar(768) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fn` (`filename`),
  KEY `sfn` (`styfilename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_jats`
--

DROP TABLE IF EXISTS `retval_jats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_jats` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` varchar(1000) DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_pagelimit`
--

DROP TABLE IF EXISTS `retval_pagelimit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_pagelimit` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` varchar(1000) DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_pdf`
--

DROP TABLE IF EXISTS `retval_pdf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_pdf` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` varchar(1000) DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_pdf_edge`
--

DROP TABLE IF EXISTS `retval_pdf_edge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_pdf_edge` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` varchar(1000) DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_xhtml`
--

DROP TABLE IF EXISTS `retval_xhtml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_xhtml` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_xmarg` smallint(6) DEFAULT NULL,
  `ok_xmarg` smallint(6) DEFAULT NULL,
  `num_xmath` smallint(6) DEFAULT NULL,
  `ok_xmath` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` varchar(1000) DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_xhtml_edge`
--

DROP TABLE IF EXISTS `retval_xhtml_edge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_xhtml_edge` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_xmarg` smallint(6) DEFAULT NULL,
  `ok_xmarg` smallint(6) DEFAULT NULL,
  `num_xmath` smallint(6) DEFAULT NULL,
  `ok_xmath` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` varchar(1000) DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_xml`
--

DROP TABLE IF EXISTS `retval_xml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_xml` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_xmarg` smallint(6) DEFAULT NULL,
  `ok_xmarg` smallint(6) DEFAULT NULL,
  `num_xmath` smallint(6) DEFAULT NULL,
  `ok_xmath` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` text DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `retval_xml_edge`
--

DROP TABLE IF EXISTS `retval_xml_edge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retval_xml_edge` (
  `id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `prev_retval` enum('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems') DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `num_xmarg` smallint(6) DEFAULT NULL,
  `ok_xmarg` smallint(6) DEFAULT NULL,
  `num_xmath` smallint(6) DEFAULT NULL,
  `ok_xmath` smallint(6) DEFAULT NULL,
  `num_warning` smallint(6) DEFAULT NULL,
  `num_error` smallint(6) DEFAULT NULL,
  `num_macro` smallint(6) DEFAULT NULL,
  `missing_macros` text DEFAULT NULL,
  `warnmsg` text DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ret` (`retval`),
  KEY `dc` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `source_to_dir`
--

DROP TABLE IF EXISTS `source_to_dir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source_to_dir` (
  `sourcefile` varchar(768) DEFAULT NULL,
  `directory` varchar(768) DEFAULT NULL,
  UNIQUE KEY `d` (`directory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statistic`
--

DROP TABLE IF EXISTS `statistic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `wq_priority` tinyint(4) DEFAULT NULL,
  `wq_prev_action` varchar(16) DEFAULT NULL,
  `wq_action` varchar(64) DEFAULT NULL,
  `wq_stage` varchar(32) DEFAULT NULL,
  `set` varchar(128) DEFAULT NULL,
  `filename` varchar(768) DEFAULT NULL,
  `sourcefile` varchar(512) DEFAULT NULL,
  `hostgroup` varchar(256) DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `errmsg` text DEFAULT NULL,
  `project_id` varchar(32) DEFAULT NULL,
  `project_src` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`),
  KEY `dc` (`date_created`),
  KEY `wq` (`wq_priority`),
  KEY `wqdc` (`wq_priority`,`date_created`),
  KEY `s` (`set`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sty_linecount`
--

DROP TABLE IF EXISTS `sty_linecount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sty_linecount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(768) DEFAULT NULL,
  `num_lines` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stycross`
--

DROP TABLE IF EXISTS `stycross`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stycross` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(768) DEFAULT NULL,
  `high_filename` varchar(768) DEFAULT NULL,
  `num_diff` int(11) DEFAULT NULL,
  `similarity` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`(384),`high_filename`(384)),
  KEY `bla` (`filename`(740),`similarity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `styusage`
--

DROP TABLE IF EXISTS `styusage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `styusage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set` varchar(128) DEFAULT NULL,
  `filename` varchar(768) DEFAULT NULL,
  `macro` varchar(768) DEFAULT NULL,
  `styfilename` varchar(768) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `styfilename` (`styfilename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workqueue`
--

DROP TABLE IF EXISTS `workqueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workqueue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `statistic_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `priority` tinyint(4) DEFAULT NULL,
  `prev_action` varchar(16) DEFAULT NULL,
  `action` varchar(64) DEFAULT NULL,
  `stage` varchar(32) DEFAULT NULL,
  `hostgroup` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`statistic_id`),
  UNIQUE KEY `sistage` (`statistic_id`,`stage`),
  KEY `si` (`statistic_id`),
  KEY `dc` (`date_created`),
  KEY `wq` (`priority`),
  KEY `wqdc` (`priority`,`date_created`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-10-28 20:49:45
-- MariaDB dump 10.18  Distrib 10.5.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: buildsysdb
-- ------------------------------------------------------
-- Server version	10.5.8-MariaDB-1:10.5.8+maria~focal

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
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help` (
  `id` varchar(128) NOT NULL,
  `title` varchar(512) DEFAULT NULL,
  `html` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help`
--

LOCK TABLES `help` WRITE;
/*!40000 ALTER TABLE `help` DISABLE KEYS */;
INSERT INTO `help` VALUES ('detailedHist','Detailed History','This page shows the detailed result history of conversions. This helps to detect trends when doing mass conversions. Click on <em>create snapshot</em> to save the current result percentages.');
INSERT INTO `help` VALUES ('generalStat','General statistics','This page gives an overview of the current system. \r\n<dl>\r\n<dt>Total number\r\n<dd>total number of articles processed</dd>\r\n</dt>\r\n<dt>Last 24 hours\r\n<dd>number of articles converted in last 24 hours</dd>\r\n</dt>\r\n<dt>Last Hour\r\n<dd>number of articles processed in last hour</dd>\r\n</dt>\r\n<dt>State\r\n<dd>current state of system, are jobs currently running?</dd>\r\n</dt>\r\n<dt>Current job ...\r\n<dd>time of current or last job</dd>\r\n</dt>\r\n<dt>LaTeXML Version\r\n<dd>version of LaTeXML being used.</dd>\r\n</dt>\r\n<dt>Number of files in queue\r\n<dd>number of files that are waiting to be processed.</dd>\r\n</dt>\r\n<dt>Number of concurrent job\r\n<dd>the number of hosts that are configured. If you are running via <tt>docker-compose</tt>, the command to run 4 workers is \r\n<code>\r\ndocker-compose up --scale latexml_dmake=4\r\n</code></dd>\r\n</dt>\r\n<dt>Hosts\r\n<dd>the names of the hosts that are being used. </dd>\r\n<dt>Timeout\r\n<dd>number of seconds when latexml times out and gives up. This value can be adjusted by setting <tt>TIMEOUT_SECONDS</tt> in <tt>docker-compose.yml</tt>\r\n</dd>\r\n<dt>Worker Memory Factor</dt>\r\n<dd>The amount of memory a worker will use. This is a factor with regards to the host of the worker. This value can be adjusted by setting <tt>MEMLIMIT_PERCENT</tt> in <tt>docker-compose.yml</tt>. If you run several workers you might want to decrease the default value.\r\n</dd>\r\n<dt>Worker Memory Absolute Limit</dt>\r\n<dd>If you set this value, a worker will not consume more memory than given value. This value can be adjusted by setting <tt>MEMLIMIT_ABSOLUTE</tt> in <tt>docker-compose.yml</tt>.\r\n<dt>\r\n</dt>\r\n');
INSERT INTO `help` VALUES ('import-overleaf','Import project from Overleaf','Here you can import your projects directly from Overleaf.\r\n<p>\r\nFor details, please read <a href=\"https://www.overleaf.com/learn/how-to/Using_Git_and_GitHub\" target=\"_blank\">Using Git and GitHub with Overleaf</a>.\r\n</p>\r\n<p>\r\nLater you can also update (<em>git pull</em>) your projects on the <a href=\"retval_abc.php\">Documents alphabetically</a> page. Just click on the green Overleaf icon <img src=\"/css/img/overleaf16.svg\"> and your project will be updated. \r\n</p>\r\n<p>\r\nThe access is only readonly. Texmlbus will not change any data on your Overleaf project.\r\n');
INSERT INTO `help` VALUES ('import-overleaf-name','Specify the name of the project','Please enter the name of your project.\r\n<p>\r\nYour project has probably a name on Overleaf. Just use that name or any other name. Within texmlbus, your project will be given the name that you enter here.\r\n\r\n');
INSERT INTO `help` VALUES ('import-overleaf-projectid','Specify projectid','Please specify the project id of your project.\r\nYou can find the Git url from the project url (the url in the browser address bar when you are in a project). If your Overleaf project url looks like:\r\n<p>\r\n<tt>https://www.overleaf.com/project/1234567</tt>\r\n</p>\r\nyou will need to enter <tt>1234567</tt> here.\r\n<p>\n\r\nFor details, please check the <a href=\"https://www.overleaf.com/learn/how-to/Using_Git_and_GitHub\" target=\"_new\">Git help page on Overleaf</a>.\r\n</p>');
INSERT INTO `help` VALUES ('import-overleaf-select','Select set for import','Please select a set where your project should be imported to. You can also just create a new set by just typing the new name, followed by the enter / return key.\r\n<br />\r\nIf you do not specify a set, the set <em>main</em> is automatically chosen. \r\n<p>\r\nA set is basically just a subdirectory in the <em>article</em> folder. ');
INSERT INTO `help` VALUES ('import-overleaf-username','Your username','Please specify your username on Overleaf.\r\n<p>\r\nYou will be then asked for a password. The password will only be used to access Overleaf and <b>never</b> be sent anywhere else.\r\n</p>\r\n<p> \r\nThe password will be cached in shared memory of the container until the docker container is stopped. It will <b>never</b> be saved to disk or stored persistently in any other way.\r\n</p>\r\n');
INSERT INTO `help` VALUES ('index','texmlbus','<ul>\r\n<li>see <a href=\"/doc\">documentation</a> for texmlbus.</li>\r\n<li>an <a href=\"#\" class=\"infolink\" data=\"overallStat\">overview</a> of the values given on this page.\r\n</ul>');
INSERT INTO `help` VALUES ('introduction','Introduction to texmlbus','texmlbus allows you to convert articles written in LaTex to other formats. \r\n\r\n...');
INSERT INTO `help` VALUES ('lastStat','General statistics','This page displays the last jobs that have been run. The page is automatically reloaded after several seconds,  so new entries are shown.\r\n');
INSERT INTO `help` VALUES ('manageQueue','Manage Queue','This page displays the current jobs that are scheduled to run. \r\nYou can remove documents from the queue by clicking on the buttons on the left side.\r\n<p>\r\nThe document is only removed from the queue, not from the build system.\r\n</p>\r\n');
INSERT INTO `help` VALUES ('manageSets','Manage Sets','<p>\r\nThe documents are organized into sets. You can add a new set when you <a href=\"upload.php\">upload files</a> and import new documents. Just type the name of the set that you wish to create before you import new documents.\r\n</p>\r\n<p>\r\nHere you can delete sets (by clicking on the corresponding trash can). All articles that belong to the set will be completely removed from the database and the filesystem (subdirectory below <em>articles</em> directory).\r\n</p>\r\n<p>\r\nIf you want to delete single documents, please go to the <a href=\"/retval_abc.php\">alphabetic list</a> of documents and delete documents there.\r\n</p>');
INSERT INTO `help` VALUES ('manageSty','Manage class and style files','<p>\r\nThe class and style files listed here will be automatically found by <em>latex</em> and <em>latexml</em>.\r\n</p>\r\n<p>\r\nYou can add a new class and style files  when you <a href=\"/uploadSty.php\">upload files</a>.\r\n</p>\r\n<p>\r\nHere you can delete class and sty files (by clicking on the corresponding trash can). They will be removed from the file system.\r\n</p>\r\n');
INSERT INTO `help` VALUES ('overallHist','Overall History','This page shows the result history of conversions. This helps to detect trends when doing mass conversions. Click on <em>create snapshot</em> to save the current result percentages.');
INSERT INTO `help` VALUES ('overallStat','Overall statistics','This page displays the last jobs that have been run and lists the results in two tables. The first table groups the detailed results into general results. \r\n<br />\r\n<b>The general results are:</b><br />\r\n<dl>\r\n<dt>none\r\n<dd>No conversion has taken place yet. This status is also applied to non-tex files, and does not contribute to statistics.</dd>\r\n</dt>\r\n<dt>exception\r\n<dd>A fatal error, the conversion broke up and was unable to produce XHTML (or another destination format).</dd>\r\n<dt>error\r\n<dd>The converter has produced XHTML (or another destination format), but the conversion process registered errors. This might or might not affect display quality.</dd>\r\n</dt>\r\n<dt>success\r\n<dd>The converter has been able to produce XHTML (or another destination format). No or minor difficulties have been encountered during conversion.</dd>\r\n</dt>\r\n</dl>\r\n<b>The detailed results are:</b><br />\r\n<dl>\r\n<dt>unknown\r\n <dd>The conversion finished with unknown state. This might happen if the conversion has been manually interrupted or because of some unknown error. For reruns files may also be set manually to this state, so they do not contribute to statistics.</dd>\r\n</dt>\r\n<dt>not_qualified\r\n<dd>The source file does not seem to be a valid LaTeX file.</dd>\r\n</dt>\r\n <dt>missing_errlog\r\n<dd>Due to some error, an error log has not been created.</dd>\r\n</dt>\r\n<dt>fatal_error\r\n<dd>The conversion broke up due to a fatal error.</dd>\r\n</dt>\r\n<dt>timeout\r\n<dd>After the timeout triggered the conversion has been stopped.</dd>\r\n</dt>\r\n<dt>error\r\n<dd>The conversion completed, however some errors haven been detected.</dd>\r\n</dt>\r\n<dt>missing_macros\r\n<dd>The conversion completed, however due to missing macro support, errors have been detected.</dd>\r\n</dt>\r\n<dt>missing_figure\r\n<dd>The conversion completed, but some figures are missing.</dd>\r\n</dt>\r\n<dt>missing_bib\r\n<dd>The conversion completed, but bibliography files are missing.</dd>\r\n</dt>\r\n<dt>missing_file\r\n<dd>The conversion completed, but referenced files are missing.</dd>\r\n</dt>\r\n<dt>warning\r\n<dd>The conversion successfully completed, however minor issues have been detected, which might affect the display quality</dd>\r\n<dt>no_problems\r\n<dd>The conversion has successfully completed, without any problems at all.</dd>\r\n</dt>\r\n');
INSERT INTO `help` VALUES ('retval_abc','Alphabetic list of documents','<p>\r\nThis page shows the conversion status for the given set or all sets. \r\n</p>\r\n<p>\r\nThe current results as well as the result of the previous conversion are shown for each stage. \r\n</p>\r\n<p>\r\nYou can click on <em>queue</em> for a given document to add the article to the current conversion queue or click on <em>queue</em> on the column header to rerun the conversion for all documents of the given stage.\r\n</p>\r\n<p>\r\nA click on the <button  style=\"transform: scale(0.6)\" class=\"btn btn-danger delete\">\r\n    <i class=\"fas fa-trash\"></i>\r\n</button> will remove the document from the system. The directory will also be deleted.\r\n<p>');
INSERT INTO `help` VALUES ('sample','Sample documents','The build system contains several sample documents. Press \r\n<button class=\"btn btn-primary\" style=\"transform:scale(0.7)\">Create samples</button>\r\nto copy the sample documents into the samples set. If this set already exists, it will be replaced.\r\nThen press <button class=\"btn btn-primary\" style=\"transform:scale(0.7)\">Scan</button> to scan the the sample set and import the documents.');
INSERT INTO `help` VALUES ('scan','Scan files','Documents can be either uploaded (<a href=\"import.php\">see import</a>), or by putting documents into the file system and by performing a scan (this page).\r\n<p></p>\r\nTo scan documents you need to copy documents into a subdirectory of the  articles directory. The structure should be as follows:\r\n\r\n\r\n<pre>\r\n    setName1\r\n        acticle1\r\n            main.tex\r\n            image.png\r\n        article2\r\n            main.tex\r\n            image.png\r\n</pre>');
INSERT INTO `help` VALUES ('scan-select','Select set for scan','Please specify the set / directory where articles should be scanned for import.\r\n<p>\r\nThis directory should be located below the <em>articles</em> directory. All files in this directory will be scanned and be imported to the database if they are valid tex files.\r\n</p>');
INSERT INTO `help` VALUES ('supported','Supported classes','<p>\r\nLatexml needs binding files to support certain TeX classes and packages. </p>\r\n<p>\r\nBelow is a list of classes and packages that are currently supported by latexml.\r\nIf files are supported by build and latexml, the build version takes precedence. Make sure that this version is superior to the one provided by latexml.\r\n</p>');
INSERT INTO `help` VALUES ('supportedPackages','Supported packages','<p>\r\nLatexml needs binding files to support certain TeX classes and packages. </p>\r\n<p>\r\nBelow is a list of packages that are currently supported by latexml.\r\nIf files are supported by build and latexml, the build version takes precedence. Make sure that this version is superior to the one provided by latexml.\r\n</p>');
INSERT INTO `help` VALUES ('upload','Upload files','Documents can either be uploaded (this page), or can be manually copied to an <em>articles</em> subfolder. Please perform a <a href=\"scan.php\">scan</a> then, so the documents are imported.\r\n\r\nYou can either upload a simple <em>.tex</em> file or a <em>.zip</em> file. <p>\r\nThe zip file may have the following structure:\r\n<pre>\r\nfile.zip (extract to same directory)\r\n    main.tex\r\n    image.png\r\n\r\nfile.zip (extract to sub-directory)\r\n    subdir\r\n        main.tex\r\n        image.png\r\n\r\nfile.zip (several documents in sub-directories)\r\n    acticle1\r\n       main.tex\r\n       image.png\r\n   article2\r\n       main.tex\r\n       image.png\r\n</pre>');
INSERT INTO `help` VALUES ('upload-select','Select set for import','When you press the \r\n<button class=\"btn btn-info delete\">\r\n    <i class=\"fas fa-wrench\"></i>\r\n     <span>Import</span>\r\n</button> button, you need to specify a set where the imported files should go to. You can also just create a new set by just typing the new name, followed by the enter / return key.\r\n<br />\r\nIf you do not specify a set, the set <em>main</em> is automatically chosen. \r\n<p>\r\nA set is basically just a subdirectory in the <em>article</em> folder. ');
INSERT INTO `help` VALUES ('uploadSty','Upload class- and style files','Personal class and style can be added to the individual article directories.\r\n<p>\r\nBut you can also make them globally available by  either uploading (this page), or by putting them manually into the <em>sty</em> subfolder of the <em>articles</em> directory. \r\n\r\nThe class files will then be automatically found by any document. It is not necessary to put the class or style file in the documents folder then.\r\n\r\nYou can either upload a .cls or .sty file or zip files. The zip files may have the following structure:\r\n<pre>\r\nfile.zip (extract to same directory)\r\n    aaa.cls\r\n    bbb.sty\r\n\r\nfile.zip (extract to subdirectory)\r\n    subdir\r\n        aaa.cls\r\n        bbb.sty\r\n\r\nfile.zip (extracts to several subdirectories) \r\n    subdir1\r\n       aaa.cls\r\n       bbb.sty\r\n   subdir2\r\n       ccc.cls\r\n       ddd.sty\r\n</pre>');
/*!40000 ALTER TABLE `help` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-10-28 20:49:45
-- MariaDB dump 10.18  Distrib 10.5.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: buildsysdb
-- ------------------------------------------------------
-- Server version	10.5.8-MariaDB-1:10.5.8+maria~focal

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
-- Table structure for table `dbversion`
--

DROP TABLE IF EXISTS `dbversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbversion` (
  `dbversion` smallint(6) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dbversion`
--

LOCK TABLES `dbversion` WRITE;
/*!40000 ALTER TABLE `dbversion` DISABLE KEYS */;
INSERT INTO `dbversion` VALUES (8);
/*!40000 ALTER TABLE `dbversion` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-10-28 20:49:45
