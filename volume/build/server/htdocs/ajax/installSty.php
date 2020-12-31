<?php
/**
 * Released under MIT License
 * (c) 2020 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\UtilManage;
use Dmake\JwToken;
use Dmake\UtilFile;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticate();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();

$className = $request->getQueryParam('name', '');

if (!empty($className)) {
    try {
        $filename = BUILDDIR . '/dmake/clsloader/' . $className . '.php';
        require_once $filename;
        $nsClassName = "Dmake\\ClsLoader\\" . $className;
        $obj = new $nsClassName;
        try {
            $success = $obj->install();
            if ($success) {
                $out['message'] = 'Files installed.';
            } else {
                $out['message'] = 'Unable to install ' . $className;
            }
            $out['installed'] = count($obj->getInstalledFiles()) > 0;
            $out['installedCls'] = implode(', ', $obj->getInstalledFiles());
        } catch (\Exception $e) {
            $success = false;
            $out['message'] = $e->getMessage();
        }
    } catch(\Exception $e) {
        $success = false;
        $out['message'] = 'Unable to load class ' . $className;
    }
    $out['success'] = $success;
} else {
    $out['success'] = false;
    $out['message'] = 'No name specified. Unable to install cls/sty.';
}

$data['result'] = $out;

$response->json($data);
