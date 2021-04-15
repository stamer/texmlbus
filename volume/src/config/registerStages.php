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

/**
 * Register the stages that should be shown and be available for possible conversions
 */
$config = Config::getConfig();

// this file carries the current dynamically determined
// list of active stages
define('ACTIVESTAGESFILE', SRCDIR . '/run/activestages.json');

/*
 * The list of (enabled) stages.
 * This list might be dynamically reduced if corresponding hosts
 * are not found because a necessary dockerfile for given stage might not have been included.
 * E.g. texmlbus-edge.yml is not included in list of started containers.
 *
 * The order of entries determines the order how the stages
 * are displayed left-to-right.
 */
$stages = [
    'pdf' => ['class' => 'StagePdf', 'enabled' => true],
    'pdf_edge' => ['class' => 'StagePdfEdge', 'enabled' => true],
    'xml' => ['class' => 'StageXml', 'enabled' => true],
    'xhtml' => ['class' => 'StageXhtml', 'enabled' => true],
    'jats' => ['class' => 'StageJats', 'enabled' => true],
    'pagelimit' => ['class' => 'StagePagelimit', 'enabled' => true],
];

$config->stages = array();

// Setup the stages, this list might be reduced later on
// when hosts are tested for availability.
foreach ($stages as $stagename => $stage) {
    if (!$stage['enabled']) {
        continue;
    }

    $filename = __DIR__ . '/../stage/' . $stagename . '/' . $stage['class'].'.php';
    require_once $filename;

    $tc = $stage['class']::register();
    if (empty($tc['stage'])) {
        die("$stagename: No stage given!");
    }
    if (empty($tc['timeout'])) {
        die("$stagename: No timeout given!");
    }
    if (empty($tc['showRetval'])) {
        die("$stagename: No showRetval given!");
    }

    $config->stages[$tc['stage']] = new stdClass;
    foreach ($tc as $key => $value) {
        if ($key === 'stage') {
            continue;
        }
        $config->stages[$tc['stage']]->{$key} = $tc[$key];
    }
}
