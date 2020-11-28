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

if (isset($_POST['createSamples'])) {
    $documentsDeleted = UtilManage::deleteSet('samples');
    UtilFile::copyR(SERVERDIR . '/samples/samples/', ARTICLEDIR . '/samples');
}

echo '<h3>Create sample set ' . $page->info('sample') . '</h3>';
?>

      <div class="container">
          <p>
              The article directory is <em><?=ARTICLEDIR ?></em>.
          </p>
          <form id="scanFiles" method="post" action="createSamples.php">
              <?php
              if (!isset($_POST['createSamples'])) {
              ?>
              <p>
                  Please press the button to create the samples. If the <em>sample set</em> already exists, it will
                  be deleted and the default samples will be loaded.
              </p>
                  <button type="submit" class="btn btn-primary" name="createSamples">Create samples</button>
<?php
              } else {

?>
                  <p>
                      <em>Sample documents have succesfully been copied.</em>
                  </p>
            <p>
            Please press the button below to import the sample documents.
            </p>
                  <button type="submit" class="btn btn-primary" name="submit"  onclick="scanFiles('samples'); return false;">Scan samples directory</button>
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
