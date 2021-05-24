<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

use Dmake\Config;

 /**
  * Class StagePdf
  */
class StagePdfEdge extends StagePdf
{
    public static function register(): array
    {
        $cfg = Config::getConfig();

        $stage = 'pdf_edge';
        $target = 'pdf';

        $config = [
            'stage' => $stage,
            'classname' => __CLASS__,
            'target' => $target,
            'hostGroup' => 'worker_edge',
            'command' => 'set -o pipefail; '
                . $cfg->app->make . ' -f Makefile',
            'dbTable' => 'retval_' . $stage,
            'tableTitle' => $stage,
            'toolTip' => 'PDF creation.',
            'parseXml' => false,
            'timeout' => 240,
            /* use %MAINFILEPREFIX%, if the logfile use same prefix as the main tex file */
            'destFile' => '%MAINFILEPREFIX%.pdf',
            'stdoutLog' => '%MAINFILEPREFIX%.log', // this needs to match entry in Makefile
            'stderrLog' => '%MAINFILEPREFIX%.log', // needs to match entry in Makefile
            'makeLog' => 'make_' . $target . '.log',
            'dependentStages' => [], // which log files need to be parsed?
            'showRetval' => [
                'unknown' => true,
                'not_qualified' => true,
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
            'retvalDetail' => [
                'missing_figures' => [
                    ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'missing_bib' => [
                    ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'missing_file' => [
                    ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'missing_macros' => [
                    ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'error' => [
                    ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
            ],
            'showTopErrors' => [
                'error' => true,
                'fatal_error' => false,
                'missing_macros' => false,
            ],
            'showDetailErrors' => [
                'error' => false,
            ],
        ];

        return $config;
    }
}
