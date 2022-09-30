<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\ChartJs;
use Dmake\JwToken;
use Dmake\StatEntry;
use Dmake\HistorySum;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticate();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();

$set = $request->getQueryParam('set', '');
$stage = $request->getQueryParam('stage', '');
$detail = (int) $request->getQueryParam('detail', 0);

$hsArr = HistorySum::getBySetStage($set, $stage);

// get the current stat
$dbTable = $cfg->stages[$stage]->dbTable;
[$stat, $rerun] = StatEntry::getStats($dbTable, $set);

// add current stat to history
$hsArr[] = HistorySum::adaptFromStat($stat, $stage);

foreach ($hsArr as $hs) {

	//print_r($hs);
    $totals = max(1, $hs->getRetvalUnknown()
              + $hs->getRetvalNotQualified()
              + $hs->getRetvalMissingErrlog()
              + $hs->getRetvalTimeout()
              + $hs->getRetvalFatalError()
              + $hs->getRetvalMissingMacros()
              + $hs->getRetvalMissingFigure()
              + $hs->getRetvalMissingBib()
              + $hs->getRetvalMissingFile()
              + $hs->getRetvalError()
              + $hs->getRetvalWarning()
              + $hs->getRetvalNoProblems()
              + $hs->getRetvalOkExitCrash());

	//echo "Totals: $totals\n";

    $labels[]                   = substr($hs->getDateSnapshot(), 0, 10);
    $retval['unknown'][]        = round($hs->getRetvalUnknown() / $totals * 100, 2);
    $retval['not_qualified'][]  = round($hs->getRetvalNotQualified() / $totals * 100, 2);
    $retval['missing_errlog'][] = round($hs->getRetvalMissingErrlog() / $totals * 100, 2);
    $retval['timeout'][]        = round($hs->getRetvalTimeout() / $totals * 100, 2);
    $retval['fatal_error'][]    = round($hs->getRetvalFatalError() / $totals * 100, 2);
    $retval['missing_macros'][] = round($hs->getRetvalMissingMacros() / $totals * 100, 2);
    $retval['missing_figure'][] = round($hs->getRetvalMissingFigure() / $totals * 100, 2);
    $retval['missing_bib'][]    = round($hs->getRetvalMissingBib() / $totals * 100, 2);
    $retval['missing_file'][]   = round($hs->getRetvalMissingFile() / $totals * 100, 2);
    $retval['error'][]          = round($hs->getRetvalError() / $totals * 100, 2);
    $retval['warning'][]        = round($hs->getRetvalWarning() / $totals * 100, 2);
    $retval['no_problems'][]    = round($hs->getRetvalNoProblems() / $totals * 100, 2);
    $retval['ok_exitcrash'][]   = round($hs->getRetvalOkExitCrash() / $totals * 100, 2);
}

$cs = new ChartJs();
$cs->setLabels($labels);


if ($detail == 1) {

	$showRetval = $cfg->stages[$stage]->showRetval;

	if ($showRetval['unknown']) {
	    $cs->addDataset('Unknown', $retval['unknown'], $cfg->chartColors['unknown']);
	}
	if ($showRetval['not_qualified']) {
	    $cs->addDataset('NotQualified', $retval['not_qualified'], $cfg->chartColors['not_qualified']);
	}
	if ($showRetval['missing_errlog']) {
	    $cs->addDataset('Missing Errlog', $retval['missing_errlog'], $cfg->chartColors['missing_errlog']);
	}
	if ($showRetval['timeout']) {
	    $cs->addDataset('Timeout', $retval['timeout'], $cfg->chartColors['timeout']);
	}
	if ($showRetval['fatal_error']) {
        $cs->addDataset('Fatal Error', $retval['fatal_error'], $cfg->chartColors['fatal_error']);
	}
    if ($showRetval['missing_macros']) {
        $cs->addDataset('Missing Macros', $retval['missing_macros'], $cfg->chartColors['missing_macros']);
    }
    if ($showRetval['missing_figure']) {
        $cs->addDataset('Missing Figure', $retval['missing_figure'], $cfg->chartColors['missing_figure']);
    }
    if ($showRetval['missing_bib']) {
        $cs->addDataset('Missing Bib', $retval['missing_bib'], $cfg->chartColors['missing_bib']);
    }
    if ($showRetval['missing_file']) {
        $cs->addDataset('Missing File', $retval['missing_file'], $cfg->chartColors['missing_file']);
    }
	if ($showRetval['error']) {
	    $cs->addDataset('Error', $retval['error'], $cfg->chartColors['error']);
	}
	if ($showRetval['warning']) {
	    $cs->addDataset('Warning', $retval['warning'], $cfg->chartColors['warning']);
	}
    if ($showRetval['no_problems']) {
        $cs->addDataset('No Problems', $retval['no_problems'], $cfg->chartColors['no_problems']);
    }
    if ($showRetval['ok_exitcrash']) {
        $cs->addDataset('Ok ExitCrash', $retval['ok_exitcrash'], $cfg->chartColors['ok_exitcrash']);
    }
} else {
    $classes = array_unique(array_values($cfg->ret_class));

    foreach ($classes as $val) {
        $retClass[$val] = [];
    }

    // merge the individual values to return classes
    foreach ($retval as $key => $val) {
        foreach ($labels as $lkey => $lval) {
            if (isset($retClass[$cfg->ret_class[$key]][$lkey])) {
                $retClass[$cfg->ret_class[$key]][$lkey] += $retval[$key][$lkey];
            } else {
                $retClass[$cfg->ret_class[$key]][$lkey] = $retval[$key][$lkey];
            }
        }
    }

    $cs->addDataset('None', $retClass['none'], $cfg->chartColors['unknown']);
    $cs->addDataset('Exception', $retClass['exception'], $cfg->chartColors['fatal_error']);
    $cs->addDataset('Error', $retClass['error'], $cfg->chartColors['error']);
    $cs->addDataset('Success', $retClass['success'], $cfg->chartColors['no_problems']);
}

$response->json($cs);

