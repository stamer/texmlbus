<?php
/**
 * MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
 *
 */

// determine if we run in a dockerized container
$dockerized = getenv('DOCKERIZED');

$ostype = getenv('OSTYPE');

/**
 * Location of the project.
 */
define('BASEDIR', '/srv/texmlbus');

/**
 * Base for Make.
 */
define('MAKEDIR', BASEDIR);

/**
 * Location of sourcecode.
 */
define('BUILDDIR', BASEDIR . '/build');

/**
 * Location of articles.
 */
define('ARTICLEDIR', BASEDIR . '/articles');

/**
 * Location of addtitional sty files.
 */
define('ARTICLESTYDIR', ARTICLEDIR . '/sty');

/**
 * Location where files are first uploaded to.
 */
define('UPLOADDIR', ARTICLEDIR . '/upload');

/**
 * Location of additional sty/ltxml files provided by build system
 */
define('STYDIR', BUILDDIR . '/sty');

/**
 * Location of additional programs
 */
define('BINDIR', BUILDDIR . '/bin');

/**
 * @deprecated
 */
define('STYARXMLIVDIR', BUILDDIR . '/sty.arxmliv');

/**
 * Location of webserver related files.
 */
define('SERVERDIR', BUILDDIR . '/server');

/**
 * Location of publically accessible files of webserver
 */
define('HTDOCS', SERVERDIR . '/htdocs');

/**
 * Application
 */
if (!isset($config)) {
	$config = new stdClass();
}

$config->app = new stdClass();
// running inside docker?
if ($dockerized || $ostype == 'linux-musl') {
    $config->app->diff = '/usr/bin/diff';
    $config->app->epstopdf = '/usr/bin/repstopdf'; // restricted epstopdf
    $config->app->file = '/usr/bin/file';
    $config->app->gunzip = '/bin/gunzip';
    $config->app->gzip = '/bin/gzip';

    $config->app->latexmk = '/usr/bin/latexmk';
    $config->app->latexml = '/usr/local/bin/latexml';
    $config->app->latexmlpost = '/usr/local/bin/latexmlpost';

    $config->app->make = '/usr/bin/make';
    $config->app->nice = '/bin/nice';
    $config->app->ssh = '/usr/bin/ssh'; // -o BatchMode=yes;
    $config->app->unrar = '/usr/bin/unrar';
    $config->app->unzip = '/usr/bin/unzip';
    $config->app->wc = '/usr/bin/wc';
    $config->app->xmllint = '/usr/bin/xmllint';
    $config->app->zip = '/usr/bin/zip';

    // this applies to the server, it is not installed.
    $config->server = new stdClass();
    $config->server->app = new stdClass();
    $config->server->app->latexml = '/opt/latexml/bin/latexml';

    $config->defaultTexEngines = 'pdflatex';

    // List of valid TexEngines, and translated as option to latexmk.
    $config->validPdfTexEngines = [
        'pdftex' => '-pdflatex=pdftex',
        'pdflatex' => '-pdf',
        'xelatex' => '-pdfxe',
        'luatex' => '-pdflua'
    ];

// typical linux distribution paths
} else {
    $config->app->diff = '/usr/bin/diff';
    $config->app->epstopdf = '/usr/bin/repstopdf'; // restricted epstopdf
    $config->app->file = '/usr/bin/file';
    $config->app->gunzip = '/usr/bin/gunzip';
    $config->app->gzip = '/usr/bin/gzip';

    $config->app->latexmk = '/usr/bin/latexmk';
    $config->app->latexml = '/usr/local/bin/latexml';
    $config->app->latexmlpost = '/usr/local/bin/latexmlpost';

    $config->app->make = '/usr/bin/make';
    $config->app->nice = '/usr/bin/nice';
    $config->app->ssh = '/usr/bin/ssh'; // -o BatchMode=yes;
    $config->app->unrar = '/usr/bin/unrar';
    $config->app->unzip = '/usr/bin/unzip';
    $config->app->wc = '/usr/bin/wc';
    $config->app->xmllint = '/usr/bin/xmllint';
    $config->app->zip = '/usr/bin/zip';

    $config->defaultTexEngine = 'pdflatex';

    // list of valid TexEngines. Must be supported as option to latexmk
    $config->validPdfTexEngines = [
        'pdftex',
        'pdflatex',
        'xelatex',
    ];
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

$config->upload = new stdClass();

// special names of sets which cannot be used

$config->upload->specialDirs = [
    'sty', // global directory for style and class files
    'overall', // virtual set overall
    'upload', // directory for uploads
];

$config->upload->styDirs = [
    'sty', // global directory for style and class files
];

$config->upload->forbiddenSubstrings = [
    [
        'substring' => '..',
        'message' => 'The set name may not contain two dot characters: ..'
    ],
    [
        'substring' => '/',
        'message' => 'The set name may not contain a forward slash: / '
    ],
];

