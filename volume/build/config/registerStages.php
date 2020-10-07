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

$config = Config::getConfig();

/*
 * The order of entries determines the order how the stages
 * are displayed.
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

// Setup the stages
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
