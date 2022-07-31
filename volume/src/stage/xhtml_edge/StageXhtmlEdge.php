<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle post log files
 *
 */

use Dmake\Config;
use Dmake\ConfigStage;

class StageXhtmlEdge extends StageXhtml
{
    public static function register(): ConfigStage
    {
        $cfg = Config::getConfig();

        $stage = 'xhtml_edge';
        $target = 'xhtml';

        $config = new ConfigStage();
        $config
            ->setStage($stage)
            ->setClassname(__CLASS__)
            ->setTarget($target)
            ->setHostGroup('worker_edge')
            ->setCommand('set -o pipefail; ' . $cfg->app->make . ' -f Makefile')
            ->setDbTable('retval_' . $stage)
            ->setTableTitle($stage)
            ->setToolTip('Xhtml creation.')
            ->setTimeout(1200)
            ->setDestFile('%MAINFILEPREFIX%.xhtml')
            ->setStdOutLog($target . '.stdout.log')  // this needs to match entry in Makefile
            ->setStdErrLog($target . '.stderr.log')  // needs to match entry in Makefile
            ->setMakeLog('make_' . $target . '.log')
            // which log files need to be parsed?
            // the dependent stage needs to have the same hostGroup as this stage
            ->setDependentStages(['xml_edge'])
            ->setShowRetval(
                [
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
                ]
            )
            ->setShowTopErrors(
                [
                    'error' => true,
                    'fatal_error' => true,
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
