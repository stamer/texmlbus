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
    /* jquery.fileupload bootstrap4 fix */ 
    .fade.in {
        opacity: 1
    }
    /* bootstrap4 modal fix */
    .modal-backdrop {
        /* bug fix - no overlay */    
        display: none;    
    }
    .modal {
        z-index: 10001;
    }
    .btn {
        font-size: 0.8rem;
    }
    
    </style>
');

$page->showHeader('import');

$set = $page->getRequest()->getQueryParam('set', '');
$statsTab = $page->getRequest()->getCookieParam('statsTab', 'tab-1');

if ($set != '') {
    echo '<h4>Import from Overleaf to <em>'.htmlspecialchars($set).'</em> <span class="fas fa-info-circle"></span></h4>'.PHP_EOL;
} else {
    echo '<h4>Import articles from Overleaf' . $page->info('import_overleaf') . '</h4>';
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
        </div>

        <select id="destset" name="destset" class="js-data-get-sets" style="width: 400px"></select> <?=$page->info('upload-select', 0.9) ?>
        <p></p>
        <label for="project" style="min-width:90px">ProjectId</label>
        <input type="text" id="project_id" name="project_id" placeholder="Id of project"/> <?=$page->info('upload-select', 0.9) ?>
        <p></p>
        <label for="name" style="min-width:90px">Name</label>
        <input type="text" id="name" name="name" placeholder="Name"/> <?=$page->info('upload-select', 0.9) ?>
        <p></p>
        <label for="username" style="min-width:90px">Username</label>
        <input type="text" id="username" name="username" />
        <p></p>
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
