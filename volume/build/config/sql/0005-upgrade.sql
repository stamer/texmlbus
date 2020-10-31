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

