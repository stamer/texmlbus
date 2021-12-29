<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

use Dmake\Config;
use Dmake\ConfigStage;
 /**
  * Class StagePdf
  */
class StagePdfEdge extends StagePdf
{
    public static function register(): ConfigStage
    {
        $cfg = Config::getConfig();

        $stage = 'pdf_edge';
        $target = 'pdf';

        $config = new ConfigStage();
        $config
            ->setStage($stage)
            ->setClassname(__CLASS__)
            ->setTarget($target)
            ->setHostGroup('worker_edge')
            ->setCommand('set -o pipefail; ' . $cfg->app->make . ' -f Makefile')
            ->setDbTable('retval_' . $stage)
            ->setTableTitle($stage)
            ->setToolTip('PDF creation.')
            ->setParseXml(false)
            ->setTimeout(240)
            /* use %MAINFILEPREFIX%, if the logfile use same prefix as the main tex file */
            ->setDestFile('%MAINFILEPREFIX%.pdf')
            ->setStdOutLog('%MAINFILEPREFIX%.log') // this needs to match entry in Makefile
            ->setStdErrLog('%MAINFILEPREFIX%.log') // needs to match entry in Makefile
            ->setMakeLog('make_' . $target . '.log')
            ->setDependentStages([]) // which log files need to be parsed?
            ->setShowRetval(
                [
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
                ]
            )
            ->setRetvalDetail(
                [
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
            )
            ->setShowTopErrors(
                [
                    'error' => true,
                    'fatal_error' => false,
                    'missing_macros' => false,
                ]
            )
            ->setShowDetailErrors(
                [
                    'error' => false,
                ]
            );

        return $config;
    }
}
