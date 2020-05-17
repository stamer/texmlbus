<?php
/**
 * MIT License
 * (c) 2019 Heinrich Stamerjohanns
 *
 * This file exports configuration variables to bash.
 * It should be called by eval `php bashConfig.php`.
 */
require_once('configData.php');

echo 'export BASEDIR='.BASEDIR.PHP_EOL;
echo 'export STYDIR='.STYDIR.PHP_EOL;

/**
 * export app config to bash
 * LATEXML=/usr/local/bin/latexml etc.
 */
if (isset($config->app)) {
	foreach ($config->app as $key => $val) {
		echo 'export '.strtoupper($key).'='.$val.PHP_EOL;
	}
}





