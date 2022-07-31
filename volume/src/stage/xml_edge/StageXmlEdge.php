<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

use Dmake\Config;
use Dmake\ConfigStage;

class StageXmlEdge extends StageXml
{
	public int $num_xmarg = 0;
	public int $ok_xmarg = 0;
	public int $num_xmath = 0;
	public int $ok_xmath = 0;

    public function __construct()
    {
        $this->config = static::register();
        $this->debug = true;
    }

    public static function register(): ConfigStage
    {
        $cfg = Config::getConfig();

        $stage = 'xml_edge';
        $target = 'xml';

        $config = new ConfigStage();
        $config
            ->setStage($stage)
            ->setClassname(__CLASS__)
            ->setTarget($target)
            ->setHostGroup('worker_edge')
            ->setCommand('set -o pipefail; ' . $cfg->app->make . ' -f Makefile')
            ->setDbTable('retval_' . $stage)
            ->setTableTitle($stage)
            ->setToolTip('Latexml XML intermediate format creation.')
            ->setParseXml(true)
            ->setTimeout(1200)
            ->setDestFile('%MAINFILEPREFIX%.tex.xml')
            ->setStdOutLog('stdout.log') // this needs to match entry in Makefile
            ->setStdErrLog('stderr.log') // needs to match entry in Makefile
            ->setMakeLog('make_' . $target . '.log')
            ->setDependentStages([])
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
                    'missing_macros' => [
                        ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                        ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                        ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                        ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                        ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                        ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                        ['sql' => 'missing_macros', 'html' => 'Missing macros', 'align' => 'left'],
                    ],
                    'warning' => [
                        ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                        ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                        ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                        ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                        ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                        ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                    ],
                    'error' => [
                        ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                        ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                        ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                        ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                        ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                        ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                    ],
                    'fatal_error' => [
                        ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                        ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                        ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                        ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                        ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                        ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                    ],
                    'no_problems' => [
                        ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                        ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                        ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                        ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                        ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                        ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                    ],
                ]
            )
            ->setShowTopErrors(
                [
                    'error' => true,
                    'fatal_error' => true,
                    'missing_macros' => true,
                ]
            )
            ->setShowDetailErrors(
                [
                    'error' => true,
                ]
            );

        return $config;
    }
}
