<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Dmake\StatEntry;
use Server\Page;
use Server\Config;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Manage Sets');
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

$page->showHeader('import');
$sets = StatEntry::getSetsCount();

echo '<h4>Manage Sets ' . $page->info('manageSets') . '</h4>';
?>

      <div class="container">
          <p>
              The article directory is <em><?=ARTICLEDIR ?></em>.
          </p>

<?php
    foreach ($sets as $set) {
?>
    <div style="margin-bottom: 10px;">

    <button style="font-size: 0.8rem; padding: 0.3rem 0.45rem; margin-right: 10px" type="button" class="btn btn-danger delete" onclick="deleteSet(this, '<?=htmlspecialchars($set['set'])?>')">
         <i class="fas fa-trash"></i>
              <span></span>
         </button>
        <?=htmlspecialchars($set['set']) ?> (<?=$set['num_documents'] ?> documents)</div>
<?php
    }
?>


<?php
$page->showFooter();

