<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * include all needed files
 */

require __DIR__ . '/../../vendor/autoload.php';
$currentDir = dirname(__FILE__);
$buildDir = dirname(__FILE__, 3);

use Server\Config;

require_once $buildDir .'/config/registerStages.php';

class IncFiles
{
}