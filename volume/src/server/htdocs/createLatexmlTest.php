<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";

use Dmake\Dao;
use Dmake\UtilManage;
use Dmake\UtilFile;
use Dmake\LatexmlTestCases;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Create Samples');
$page->addScript("/js/scanFiles.js");
$page->addScript('/js/deleteSet.js');
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


$page->showHeader('samples');

if (isset($_POST['createLatexmlTest'])) {
    $documentsDeleted = UtilManage::deleteSet('latexml-test');
    $ltc = new LatexmlTestCases();
    $ltc->create();
}

echo '<h3>Create LaTeXML test cases ' . $page->info('sample') . '</h3>';
?>

      <div class="container">
          <p>
              The article directory is <em><?=ARTICLEDIR ?></em>.
          </p>
          <form id="scanFiles" method="post" action="createLatexmlTest.php">
              <?php
              if (!isset($_POST['createLatexmlTest'])) {
              ?>
              <p>
                  Please press the button to create the LaTeXML test cases. If the <em>latexml-test set</em> already exists, it will
                  be deleted and the test cases will be recreated.
              </p>
                  <button type="submit" class="btn btn-primary" name="createLatexmlTest">Create LaTeXML test cases</button>
<?php
              } else {

?>
                  <p>
                      <em>LaTeXML test cases have succesfully been copied.</em>
                  </p>
            <p>
            Please press the button below to import the LaTeXML test cases.
            </p>
                  <button type="submit" class="btn btn-primary" name="submit"  onclick="scanFiles('latexml-test', true); return false;">Scan latexml-test directory</button>
<?php
              }
?>
            </div>
          </form>
      </div>

    <script>
        $('#myModal').modal('hide');

    </script>
<?php

$deferJS[] = '';


$page->showFooter($deferJS);
