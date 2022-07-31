<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Server\Config;
use Dmake\JwToken;
use Dmake\StatEntry;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticateByCookie();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();

$set = $request->getQueryParam('set', '');
$target = $request->getQueryParam('target', '');
$detail = (int) $request->getQueryParam('detail', 0);

// get the current stat
$dbTable = $cfg->stages[$target]->dbTable;
[$stat, $rerun] = StatEntry::getStats($dbTable, $set);

$total_retval = 0;
foreach ($stat as $retval => $num) {
    $total_retval += $num;
    $retval_class = $cfg->ret_class[$retval];
    if (isset($stat_class[$retval_class])) {
        $stat_class[$retval_class] += $num;
    } else {
        $stat_class[$retval_class] = $num;
    }
}

$title = 'TeX Table';
$html = '
<style>pre {font-size: 70%;}</style> 
<pre>
\usepackage{array}
\usepackage{colortbl}
\usepackage{xcolor}

%% rowcolors for tables
\definecolor{bgpurple}{HTML}{ffafff}
\definecolor{bgred}{HTML}{ff9999}
\definecolor{bggreen}{HTML}{99ff99}
\definecolor{bgwhite}{HTML}{ffffff}

\begin{table}
\begin{center}
\begin{tabular}{|c|c|c|}
\hline
Result   & Qty. & \% \\\\
\hline';

foreach ($stat as $retval => $num) {

    if ($total_retval > 0) {
        $percent = $num / $total_retval * 100.0;
    } else {
        $percent = 0;
    }

    $texRetval = str_replace('_', '\_', $retval);
    $html .=
        '\rowcolor{'.$cfg->ret_color[$cfg->ret_class[$retval]].'} '.$texRetval.' & '.$num.' & '
        . number_format($percent, 2).'\\\\' . PHP_EOL
        . '\hline'.PHP_EOL;
}

$html .= '
\hline
\end{tabular}
\caption{Stage: '. htmlspecialchars($target) .' Set: '. htmlspecialchars($set). '}
\end{center}
\end{table}
</pre>';

$data['title'] = $title;
$data['html'] = $html;

$response->json($data);

