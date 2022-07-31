<?php
/**
 * Released under MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Import Overleaf');
$page->addScript("/js/local.js");
$page->addScript("/js/select2.min.js");
$page->addScript("/js/importOverleaf.js");
$page->addCss('
<link href="/css/select2.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Bootstrap styles -->
    <!-- Generic page styles -->
    <style>
      @media (max-width: 767px) {
        .description {
          display: none;
        }
      }
    /* bootstrap4 modal fix */
    .modal-backdrop {
        /* bug fix - no overlay */    
        display: none;    
    }
    .modal {
        z-index: 10001;
    }
    </style>
');

$page->showHeader('import');

$set = $page->getRequest()->getQueryParam('set', '');
$statsTab = $page->getRequest()->getCookieParam('statsTab', 'tab-1');

if ($set !== '') {
    echo '<h4>Import from Overleaf to <em>'.htmlspecialchars($set).'</em> <span class="fas fa-info-circle"></span></h4>'.PHP_EOL;
} else {
    echo '<h4>Import articles from Overleaf' . $page->info('import-overleaf') . '</h4>';
}
?>
    <div class="container">
        <form
            id="importOverleaf"
            action="/importOverleaf.php"
            method="POST"
            enctype="multipart/form-data"
        >
        <noscript
          ><input
            type="hidden"
            name="redirect"
            value="/importOverleaf.php"
        /></noscript>

        <p>&nbsp;</p>

        <div class="form-group row">
            <label for="destset" class="col-sm-3 col-form-label">Set to import to</label>
            <div class="col-sm-8">
                <select id="destset" name="destset" style="width:100%" class="js-data-get-sets form-control"></select>
            </div>
            <div class="col">
                <?=$page->info('import-overleaf-select', 1.0, '-5px') ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="project" class="col-sm-3 col-form-label">ProjectId</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="project_id" name="project_id" placeholder="Id of project"/>
            </div>
            <div class="col">
                <?=$page->info('import-overleaf-projectid', 1.0, '-5px') ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">Name</label>
            <div class="col-sm-8">
            <input type="text" class="form-control" id="name" name="name" placeholder="The project name you would like to use" />
            </div>
            <div class="col">
                <?=$page->info('import-overleaf-name', 1.0, '-5px') ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="username" class="col-sm-3 col-form-label">Username</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="username" name="username" placeholder="Your username"/>
            </div>
            <div class="col">
                <?=$page->info('import-overleaf-username', 1.0, '-5px') ?>
            </div>
        </div>
        <button class="btn btn-primary" type="submit" name="submit" onclick="importOverleaf($('#destset').val(), $('#name').val(), $('#project_id').val(), $('#username').val()); return false;">Import project</button>
      </form>
    </div>

<?php

$deferJS[] = '
$(".js-data-get-sets").select2({
    tags: true,
    placeholder: "Please specify a set when you import",
    ajax: {
    url: "/ajax/getSets.php",
    dataType: "json"
    }
});';

$page->showFooter($deferJS);
