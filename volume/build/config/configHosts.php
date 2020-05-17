<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Array of hosts on which the jobs should be run
 * each host must be reachable via ssh, directory must be
 * mounted somehow.
 *
 * To change the task, you can either modify the Makefile
 * build/script/make/Makefile.paper.in
 *
 */

use Dmake\UtilHost;

define('MAKE_DEFAULT', $config->app->nice . ' -n 4 ' . $config->app->make . ' -f Makefile');
define(
    'MAKE_PDF',
    $config->app->make . ' -f Makefile pdfclean; ' . $config->app->nice . ' -n 4 ' . $config->app->make . ' -f Makefile pdf'
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
    // Determine the number of workers and dynamically create the
    // appropriate host entries.
    $hostnames = UtilHost::getDockerWorkers();

    $hosts = [];
    foreach ($hostnames as $index => $hostname) {
        $hosts['worker_' . $index] =
            array(
                'hostname' => $hostname,
                'enabled' => true, // whether host should be used at all
                'status' => STAT_IDLE,
                'dir' => ARTICLEDIR,
                'make_default' => MAKE_DEFAULT,
                'make_pdf' => MAKE_PDF,
            );
    }

    $config->hosts = $hosts;
} else {
    $config->hosts =
        array(
            'local_0' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => true, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                ),
            'local_1' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => true, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                ),
            'local_2' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                    'make_xml' => MAKE_XML,
                    'make_xhtml' => MAKE_XHTML,
                    'make_jats' => MAKE_JATS,
                ),
            'local_3' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                ),
            'local_4' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                ),
            'local_5' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                ),
            'local_6' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                ),
            'local_7' =>
                array(
                    'hostname' => 'localhost',
                    'enabled' => false, // whether host should be used at all
                    'status' => STAT_IDLE,
                    'dir' => ARTICLEDIR,
                    'make_default' => MAKE_DEFAULT,
                    'make_pdf' => MAKE_PDF,
                ),
        );
}
