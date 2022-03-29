ALTER TABLE `statistic` ADD `comment` TEXT NULL DEFAULT NULL AFTER `project_src`, ADD `comment_status` ENUM('none','todo','working','revisit','cannot fix','ok') NULL DEFAULT 'none' AFTER `comment`; 
ALTER TABLE `statistic` ADD `comment_date` DATETIME NULL DEFAULT NULL AFTER `comment_status`;

