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

