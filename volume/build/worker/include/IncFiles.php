<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * include all needed files
 */

require __DIR__ . '/../../vendor/autoload.php';
require_once 'Config.php';
require_once 'ApiWorkerHandler.php';

$currentDir = dirname(__FILE__);
$buildDir = dirname(__FILE__, 3);

use Worker\Config;
use Dmake\UtilStage;
use Dmake\ApiWorkerRequest;

Config::getConfig(null, false);

require_once $buildDir .'/config/registerStages.php';

// some stages might have been disabled.
UtilStage::determineActiveStages();

class IncFiles
{
}
