<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 * Retrieves a help page from help and shows this in modal window.
 *
 * Example:
 * InfoIcon to retrieve 'upload' page
 * <span class="fas fa-info-circle infolink" data="upload"></span>
 *
 * Link to retrieve 'upload' page.
 * <a href="#" class="infolink" data="upload">further help</a>
 */
require_once "../../include/IncFiles.php";
use Dmake\HelpDao;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();
$request = RequestFactory::create();
$response = ResponseFactory::create();

$id = $request->getQueryParam('id', '');

if (!empty($id)) {
    $out = HelpDao::getHelpById($id);

    if (empty($out)) {
        $out['title'] = 'Error';
        $out['html'] = 'No data for id <em>"' . htmlspecialchars($id) . '"</em> found.';
    } else {
        // allow further help links within
        $out['html'] .= "
        <script>
            $('.infolink').bind('click', function (e) {
                if ($(this).attr('data')) {
                    openHelp(($(this)).attr('data'));
                } else {
                    alert('Cannot load page, data attribute is missing.');
                }
                return false;
            });
        </script>";
    }
} else {
    $out['title'] = "Error";
    $out['html'] = "No id given";
}

$response->json($out);
