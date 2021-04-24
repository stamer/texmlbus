<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Scan / Import');
$page->addScript("/js/select2.min.js");
$page->addScript("/js/scanFiles.js");
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
</style>    
');

$page->showHeader('import');

echo '<h4>Scan and import articles ' . $page->info('scan') . '</h4>';
?>

      <div class="container">
          <p>
              The article directory is <em><?=ARTICLEDIR ?></em>.
          </p>
          <form id="scanFiles">
              <div>
                  Select set:
                <select id="destset" name="destset" class="js-data-get-subdirs" style="width: 400px"></select> <?=$page->info('scan-select', 0.9) ?>
              </div>
            <div class="mt-4">

                <button class="btn btn-primary" type="submit" name="submit" onclick="scanFiles($('#destset').val()); return false;">Scan</button>
            </div>
          </form>
      </div>

    <script>
        $('#myModal').modal('hide');

    </script>
<?php

$deferJS[] = '
$(\'.js-data-get-subdirs\').select2({
    tags: false,
    placeholder: "Select a set / directory",
    ajax: {
        url: \'/ajax/getSubDirs.php\',
        dataType: \'json\'
    }
});';


$page->showFooter($deferJS);
