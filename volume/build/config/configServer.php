<?php
/**
 * MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 * Base Configuration for the server.
 */

// Set this to true if you upgrade and do not want to
// provide access to the main site.
$MAINTENANCE = false;

if ($MAINTENANCE) {
    // only allow EXCEPTION_HOST to access site
    $EXCEPTION_HOST = "127.0.0.1";
    if ($_SERVER['REMOTE_ADDR'] != $EXCEPTION_HOST) {
        echo "<h1>Maintenance, please come back later!</h1>";
        echo "(22.00 h UTC)";
        exit;
    }
}

/**
 * @var StdClass $config
 */
if (!isset($config)) {
    $config = new stdClass();
}
$config->isCrawler = false;

$config->ret_class = [
    '' => 'none',
    'unknown' => 'none',
    'not_qualified' => 'none',
    'missing_errlog' => 'notice',
    'fatal_error' => 'exception',
    'timeout' => 'exception',
    'error' => 'error',
    'missing_macros' => 'error',
    'missing_figure' => 'error',
    'missing_bib' => 'error',
    'missing_file' => 'error',
    'warning' => 'success',
    'no_problems' => 'success'
];

$config->chartColors = [
    'unknown' => 'lightGrey',
    'not_qualified' => 'darkGrey',
    'missing_errlog' => 'brown',
    'fatal_error' => 'darkPink',
    'timeout' => 'blue',
    'error' => 'darkRed',
    'missing_macros' => 'yellow',
    'missing_figure' => 'lightOrange',
    'missing_bib' => 'orange',
    'missing_file' => 'darkOrange',
    'warning' => 'lightGreen',
    'no_problems' => 'darkGreen'
];

$config->ret_color = [
    'none' => 'bgwhite',
    'exception' => 'bgpurple',
    'notice' => 'bglightred',
    'error' => 'bgred',
    'success' => 'bggreen'
];

$config->ret_color_sm = [
    'none' => 'bgwhite-sm',
    'exception' => 'bgpurple-sm',
    'notice' => 'bglightred-sm',
    'error' => 'bgred-sm',
    'success' => 'bggreen-sm'
];

$config->tt_class = [
    'unknown' => 'The conversion finished with unknown state. This might happen if the conversion has been manually interrupted or because of some unknown error. For reruns files may also be set manually to this state, so they do not contribute to statistics.',
    'not_qualified' => 'The source file does not seem to be a valid LaTeX file.',
    'missing_errlog' => 'Due to some error, an error log has not been created.',
    'fatal_error' => 'The conversion broke up due to a fatal error.',
    'timeout' => 'After the timeout triggered the conversion has been stopped.',
    'error' => 'The conversion completed, however some errors haven been detected.',
    'missing_macros' => 'The conversion completed, however due to missing macro support, errors have been detected.',
    'missing_figure' => 'The conversion completed, but some figures are missing.',
    'missing_bib' => 'The conversion completed, but bibliography files are missing.',
    'missing_file' => 'The conversion completed, but referenced files are missing.',
    'warning' => 'The conversion successfully completed, however minor issues have been detected, which might affect the display quality.',
    'no_problems' => 'The conversion has successfully completed, without any problems at all.'
];

$config->tt_cat = [
    'none' => 'This status is applied to non-tex files, and does not contribute to statistics.',
    'notice' => 'The converter has been able to produce XHTML, minor difficulties have been encountered',
    'exception' => 'A fatal error, the conversion broke up and was unable to produce XHTML.',
    'error' => 'The converter has produced XHTML, but the conversion process registered errors. This might or might not affect display quality.',
    'success' => 'The converter has been able to produce XHTML, No or minor difficulties have been encountered during conversion.'
];

// controls what to show in menu or page
$config->show = new StdClass();

// show incomplete features
$config->show->experimental = false;

// show even more incomplete features
$config->show->evenMoreExperimental = false;

// enable, disable internal pages
// this is for development only, it allows you to edit help pages.
// do not activate this on any production system.
$config->show->internal = true;
