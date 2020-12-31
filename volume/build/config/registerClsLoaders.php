<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Registers the stages.
 * To add a stage, see documentation on how to add a stage.
 * To disable a stage, set 'enabled' of the stage to false.
 */

use Dmake\BaseConfig as Config;
use Dmake\UtilFile;
use Dmake\UtilStylefile;
/**
 * Register the stages that should be shown and be available for possible conversions
 */
$config = Config::getConfig();

// this file carries the current dynamically determined
// list of active stages
define('CLSLOADERDIR', BUILDDIR . '/dmake/clsloader');

$files = UtilFile::listDir(
    CLSLOADERDIR,
    true,
    true,
    '/\\.php$/',
    true,
    false);

$config->clsLoader = [];

$installedFiles = UtilStylefile::getInstalledClsStyFiles(ARTICLEDIR . '/sty');

foreach ($files as $filename) {
    require_once CLSLOADERDIR . '/' . $filename;
    $className = UtilFile::getPrefix($filename);
    $nsClassName =  "Dmake\\ClsLoader\\" . $className;
    $obj = new $nsClassName;
    $installed = true;

    $styFilenames = $obj->getFiles();
    foreach ($styFilenames as $styFilename) {
        if (!isset($installedFiles[$styFilename])) {
            $installed = false;
            break;
        }
    }

    $config->clsLoader[$obj->getPublisher()][$obj->getName()] = [
        'className' => $className,
        'installed' => $installed,
        'files' => $styFilenames,
    ];
}
