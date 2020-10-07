<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

use Dmake\StageInterface;

 /**
  * Class StagePdf
  */
class StagePdfEdge extends StagePdf implements StageInterface
{
    /**
     * @return array|mixed
     */
    public static function register()
    {
        $config = array(
            'stage' => 'pdf_edge',
            'classname' => __CLASS__,
            'target' => 'pdf',
            'hostGroup' => 'worker_edge',
            'parseXml' => false,
            'dbTable' => 'retval_pdf_edge',
            'timeout' => 240,
            /* use %MAINFILEPREFIX%, if the logfile use same prefix as the main tex file */
            'destFile' => '%MAINFILEPREFIX%.pdf',
            'stdoutLog' => '%MAINFILEPREFIX%.log', // this needs to match entry in Makefile
            'stderrLog' => '%MAINFILEPREFIX%.log', // needs to match entry in Makefile
            'dependentStages' => array(), // which log files need to be parsed?
            'showRetval' =>
                array(
                    'unknown'           => true,
                    'not_qualified'     => true,
                    'missing_errlog'    => true,
                    'fatal_error'       => true,
                    'timeout'           => true,
                    'error'             => true,
                    'missing_macros'    => true,
                    'missing_figure'    => true,
                    'missing_bib'       => true,
                    'missing_file'      => true,
                    'warning'           => true,
                    'no_problems'       => true
                ),
            'retvalDetail' => array(
                'missing_figures' =>
                    array(0 =>
                        array('sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left')
                    ),
                'missing_bib' =>
                    array(0 =>
                        array('sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left')
                    ),
                'missing_file' =>
                    array(0 =>
                        array('sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left')
                    ),
                'missing_macros' =>
                    array(0 =>
                        array('sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left')
                    ),
                'error' =>
                    array(0 =>
                        array('sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left')
                    ),
            ),
            'showTopErrors' =>
                array(
                    'error'             => true,
                    'fatal_error'       => false,
                    'missing_macros'    => false,
                ),
            'showDetailErrors' =>
                array(
                    'error'             => false,
                ),
            'tableTitle' => 'pdf',
            'toolTip' => 'PDF creation.'

        );

        return $config;
    }
}
