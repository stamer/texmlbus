<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * include all needed files
 */

require __DIR__ . '/../../vendor/autoload.php';
$currentDir = dirname(__FILE__);
$srcDir = dirname(__FILE__, 3);

use Server\Config;
use Dmake\UtilStage;

require_once $srcDir .'/config/registerStages.php';

// some stages might have been disabled.
UtilStage::determineActiveStages();

class IncFiles
{
}