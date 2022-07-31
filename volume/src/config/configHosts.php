<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Array of hosts on which the jobs should be run.
 * On these hosts a http server must be run, to be accessible
 * via api.
 *
 * To change the task, you can either modify the Makefile
 * build/script/make/Makefile.paper.in
 *
 */

use Dmake\UtilHost;
use Dmake\UtilStage;
/**
 * @var StdClass $config
 * set -o pipefail: return status of failing program
 */
define('MAKE_DEFAULT',
       'set -o pipefail; '
       . $config->app->make . ' -f Makefile');

// __MAKELOG__ is replaced by $cfg->stages[$stage]->makeLog
define('MAKE_OUTPUT', '2>&1 | tee __MAKELOG__');

define(
    'MAKE_PDF',
    'set -o pipefail; '
    . $config->app->make . ' -f Makefile pdfclean; '
    . $config->app->make . ' -f Makefile pdf'
);

/**
 * you can override commands for specific hosts you can then set make_TARGET in the host directory.
 *
 */
define('MAKE_XML', $config->app->nice . ' -n 4 ' . $config->app->make . ' -f Makefile xml');
define('MAKE_XHTML', $config->app->nice . ' -n 4 ' . $config->app->make . ' -f Makefile xhtml');
define('MAKE_JATS', $config->app->nice . ' -n 4 ' . $config->app->make . ' -f Makefile jats');

$dockerized = getenv('DOCKERIZED');

if ($dockerized) {
    // Determine the active HostGroups or registered stages
    $hostGroups = UtilStage::getHostGroups();

    // Determine the number of workers and dynamically create the
    // appropriate host entries.
    $hostnames = UtilHost::getDockerWorkers($hostGroups);

    $hosts = [];
    foreach ($hostnames as $hostGroupName => $hostGroup) {
        foreach ($hostGroup as $index => $hostname) {
            $hosts[$hostGroupName][$hostGroupName . '_' . $index] =
                [
                    'hostname' => $hostname,
                    'enabled' => true, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ];
        }
    }
    $config->hosts = $hosts;

} else {
    $config->hosts =
        [
            'local_0' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => true, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ],
            'local_1' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => true, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ],
            'local_2' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                    'make_xml' => MAKE_XML,
                    'make_xhtml' => MAKE_XHTML,
                    'make_jats' => MAKE_JATS,
                ],
            'local_3' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ],
            'local_4' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ],
            'local_5' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ],
            'local_6' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ],
            'local_7' =>
                [
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_output' => MAKE_OUTPUT,
                    'make_pdf' => MAKE_PDF,
                ],
        ];
}
