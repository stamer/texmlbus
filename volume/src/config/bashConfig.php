<?php
/**
 * MIT License
 * (c) 2019-2020 Heinrich Stamerjohanns
 *
 * This file exports configuration variables to bash.
 * It should be called by eval `php bashConfig.php`.
 *
 * It is needed to import config->app variables into Makefile.paper.vars
 */
require_once('configData.php');

#echo 'export BASEDIR='.BASEDIR.PHP_EOL;
#echo 'export STYDIR='.STYDIR.PHP_EOL;
echo 'BASEDIR='. BASEDIR . PHP_EOL;
echo 'STYDIR=' . STYDIR . PHP_EOL;

/**
 * export app config to bash
 * LATEXML=/usr/local/bin/latexml etc.
 */
if (isset($config->app)) {
	foreach ($config->app as $key => $val) {
		echo strtoupper($key) . '=' . $val . PHP_EOL;
	}
}

