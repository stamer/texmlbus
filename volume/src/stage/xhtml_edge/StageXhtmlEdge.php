<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle post log files
 *
 */

use Dmake\AbstractStage;
use Dmake\Config;
use Dmake\Dao;
use Dmake\StatEntry;
use Dmake\UtilFile;
use Dmake\UtilStage;

class StageXhtmlEdge extends StageXhtml
{
    public static function register(): array
    {
        $cfg = Config::getConfig();

        $stage = 'xhtml_edge';
        $target = 'xhtml';

        $config = [
            'stage' => $stage,
            'classname' => __CLASS__,
            'target' => $target,
            'hostGroup' => 'worker_edge',
            'command' => 'set -o pipefail; '
                . $cfg->app->make . ' -f Makefile',
            'dbTable' => 'retval_' . $stage,
            'tableTitle' => $stage,
            'toolTip' => 'Xhtml creation.',
            'timeout' => 1200,
            'destFile' => '%MAINFILEPREFIX%.xhtml',
            'stdoutLog' => $target . '.stdout.log',  // this needs to match entry in Makefile
            'stderrLog' => $target . '.stderr.log',  // needs to match entry in Makefile
            'makeLog' => 'make_' . $target . '.log',
            // which log files need to be parsed?
            // the dependent stage needs to have the same hostGroup as this stage
            'dependentStages' => ['xml_edge'],
            'showRetval' => [
                'unknown' => false,
                'not_qualified' => false,
                'missing_errlog' => true,
                'fatal_error' => true,
                'timeout' => true,
                'error' => true,
                'missing_macros' => true,
                'missing_figure' => true,
                'missing_bib' => true,
                'missing_file' => true,
                'warning' => true,
                'no_problems' => true
            ],
            'showTopErrors' => [
                'error' => true,
                'fatal_error' => true,
                'missing_macros' => false,
            ],
            'showDetailErrors' => [
                'error' => false,
            ],
        ];

        return $config;
    }
}
