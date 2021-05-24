<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

use Dmake\AbstractStage;
use Dmake\Config;
use Dmake\Dao;
use Dmake\ErrDetEntry;
use Dmake\StatEntry;
use Dmake\UtilFile;
use Dmake\UtilStage;

class StageXmlEdge extends StageXml
{
	public $num_xmarg = 0;
	public $ok_xmarg = 0;
	public $num_xmath = 0;
	public $ok_xmath = 0;

    public function __construct()
    {
        $this->config = static::register();
        $this->debug = true;
    }

    public static function register(): array
    {
        $cfg = Config::getConfig();

        $stage = 'xml_edge';
        $target = 'xml';

        $config = [
            'stage' => $stage,
            'classname' => __CLASS__,
            'target' => $target,
            'hostGroup' => 'worker_edge',
            'command' => 'set -o pipefail; '
                . $cfg->app->make . ' -f Makefile',
            'dbTable' => 'retval_' . $stage,
            'tableTitle' => $stage,
            'toolTip' => 'Latexml XML intermediate format creation.',
            'parseXml' => true,
            'timeout' => 1200,
            'destFile' => '%MAINFILEPREFIX%.tex.xml',
            'stdoutLog' => 'stdout.log', // this needs to match entry in Makefile
            'stderrLog' => 'stderr.log', // needs to match entry in Makefile
            'makeLog' => 'make_' . $target . '.log',
            'dependentStages' => [],
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
            ],
            'showTopErrors' => [
                'error' => true,
                'fatal_error' => true,
                'missing_macros' => true,
            ],
            'showDetailErrors' => [
                'error' => true,
            ],
        ];

        return $config;
    }
}
