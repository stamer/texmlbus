<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Dmake\Set;
use Dmake\UtilFile;
use Server\Page;
use Server\Config;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Manage Class and Sty files');
$page->addScript('/js/deleteSty.js');
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
$sets = Set::getSetsCount();

$result_dirs = [];
$current_depth = 0;

UtilFile::listDirR(ARTICLEDIR . '/sty', $result_dirs, $current_depth, true, false);

    echo '<h4>Manage sty classes and file ' . $page->info('manageSty') . '</h4>';
?>

      <div class="container">
          <p>
              The sty directory is <em><?=ARTICLEDIR . '/sty' ?></em>.
          </p>

<?php
    $prevDir = '__empty__'; // avoid warning in strpos
    $depth = 0;
    sort($result_dirs);
    foreach ($result_dirs as $fullFilename) {
        $isDir = false;
        $file = str_replace(ARTICLEDIR . '/sty/', '', $fullFilename);

        if (substr($file, -1, 1) === '/') {
            $isDir = true;
        }

        // does the path change?
        if (strpos($file, $prevDir) !== 0
            && $prevDir !== '__empty__'
        ) {
            // do not count last /
            $num = substr_count($file, '/', 0, -1) ;
            while ($depth > $num) {
                echo '</ul>' . PHP_EOL;
                $depth--;
            }
            $prevDir = $file;
        }
?>
    <div style="margin-bottom: 10px;">
        <button style="font-size: 0.5rem; padding: 0.3rem 0.45rem; margin-right: 10px" type="button" class="btn btn-danger delete" onclick="deleteSty(this, '<?=$isDir ?>', '<?=htmlspecialchars($file)?>')">
         <i class="fas fa-trash"></i>
              <span></span>
         </button>
<?php
        echo htmlspecialchars(basename($file) . ($isDir ? '/' : ''));
?>
    </div>
<?php
        if ($isDir === true) {
            $prevDir = $file;
            echo '<ul>' . PHP_EOL;
            $depth++;
        }

    }

    while ($depth > 0) {
        echo '</ul>' . PHP_EOL;
        $depth--;
    }
?>
      </div>

<?php
$page->showFooter();

