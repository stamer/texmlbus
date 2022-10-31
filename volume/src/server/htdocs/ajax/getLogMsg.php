<?php
/**
 * Released under MIT License
 * (c) 2007 - 2022 Heinrich Stamerjohanns
 *
 * Shows error/warn message in modal window.
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\RetvalDao;
use Dmake\JwToken;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;
use Server\View;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticateByCookie();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();

$id = $request->getQueryParam('id', '');

if (!empty($id)) {
    [$retval, $stage, $id] = explode('__', $id);
    $joinTable = $cfg->stages[$stage]->dbTable;
    $columns = View::getColumnsByRetval($stage, $retval);

    $row = RetvalDao::getDetailsByRetval(
        $retval,
        $stage,
        $joinTable,
        '',
        $id,
        $columns);

    $row = array_shift($row);

    if (empty($row)) {
        $out['title'] = 'Error';
        $out['html'] = 'No data for id <em>"' . htmlspecialchars($id) . '"</em> found.';
    } else {
        $out['title'] = htmlspecialchars($row['filename']);
        $out['html'] = '';
        if (isset($row['num_warning'])) {
            $out['html'] .= '<h4>Warnings: ' . $row['num_warning'] . '</h4>';
        }
        if (isset($row['num_error'])) {
            $out['html'] .= '<h4>Errors: ' . $row['num_error'] . '</h4>';
        }
        if (isset($row['missing_macros'])) {
            $out['html'] .= '<h4>Missing macros:</h4>'. nl2br($row['missing_macros']);
        }
        if (!empty($row['warnmsg'])) {
            $out['html'] .= '<h4>Warning messages:</h4> ' . nl2br($row['warnmsg']);
        }
        if (!empty($row['errmsg'])) {
            $out['html'] .= '<h4>Error messages:</h4>' . nl2br($row['errmsg']);
        }
    }
} else {
    $out['title'] = "Error";
    $out['html'] = "No id given";
}

$response->json($out);
