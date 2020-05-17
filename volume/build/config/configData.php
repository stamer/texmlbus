<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

// determine if we run in a dockerized container
$dockerized = getenv('DOCKERIZED');

$ostype = getenv('OSTYPE');

define('BASEDIR', '/srv/texmlbus');
define('MAKEDIR', BASEDIR);
define('BUILDDIR', BASEDIR.'/build');
//define('PAPERDIR', BASEDIR.'/articles');
define('ARTICLEDIR', BASEDIR.'/articles');
define('UPLOADDIR', ARTICLEDIR . '/upload');
define('STYDIR', BUILDDIR.'/sty');
define('BINDIR', BUILDDIR.'/bin');
define('STYARXMLIVDIR', BUILDDIR.'/sty.arxmliv');
define('SERVERDIR', BUILDDIR.'/server');
define('HTDOCS', SERVERDIR.'/htdocs');

/**
 * Application
 */
if (!isset($config)) {
	$config = new stdClass;
}

$config->app = new stdClass();
// running inside docker?
if ($dockerized || $ostype == 'linux-musl') {
    $config->app->diff = '/usr/bin/diff';
    $config->app->epstopdf = '/usr/bin/repstopdf'; // restricted epstopdf
    $config->app->file = '/usr/bin/file';
    $config->app->gunzip = '/bin/gunzip';
    $config->app->gzip = '/bin/gzip';
    $config->app->make = '/usr/bin/make';
    $config->app->nice = '/bin/nice';

    $config->app->latexmk = '/usr/bin/latexmk';
    $config->app->latexml = '/usr/local/bin/latexml';
    $config->app->latexmlpost = '/usr/local/bin/latemlpost';
    $config->app->ssh = '/usr/bin/ssh'; // -o BatchMode=yes;
    $config->app->unrar = '/usr/bin/unrar';
    $config->app->unzip = '/usr/bin/unzip';
    $config->app->wc = '/usr/bin/wc';
    $config->app->zip = '/usr/bin/zip';

    // this applies to the server, it is not installed.
    $config->server = new stdClass();
    $config->server->app = new stdClass();
    $config->server->app->latexml = '/opt/latexml/bin/latexml';

// typical linux distribution paths
} else {
    $config->app->diff = '/usr/bin/diff';
    $config->app->epstopdf = '/usr/bin/repstopdf'; // restricted epstopdf
    $config->app->file = '/usr/bin/file';
    $config->app->gunzip = '/usr/bin/gunzip';
    $config->app->gzip = '/usr/bin/gzip';
    $config->app->make = '/usr/bin/make';
    $config->app->nice = '/usr/bin/nice';
    /*
     * latexmk is typically too old, make sure we use
     * our own version
     */
    $config->app->latexmk = '/usr/bin/latexmk';
    $config->app->latexml = '/usr/local/bin/latexml';
    $config->app->latexmlpost = '/usr/local/bin/latemlpost';
    $config->app->ssh = '/usr/bin/ssh'; // -o BatchMode=yes;
    $config->app->unrar = '/usr/bin/unrar';
    $config->app->unzip = '/usr/bin/unzip';
    $config->app->wc = '/usr/bin/wc';
    $config->app->zip = '/usr/bin/zip';
}
/**
 * Modes for uncompressors
 */
$config->uncompress = new stdClass();
$config->uncompress->unrar = new StdClass();
$config->uncompress->unrar->interactive = '';
$config->uncompress->unrar->overwriteOn = '-o+';
$config->uncompress->unrar->overwriteOff = '-o-';

$config->uncompress->unzip = new StdClass();
$config->uncompress->unzip->interactive = '';
$config->uncompress->unzip->overwriteOn = '-o';
$config->uncompress->unzip->overwriteOff = '-n';


