<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
require __DIR__ . '/../../config/registerClsLoaders.php';

use Dmake\Dao;
use Server\Page;
use Server\Config;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Install Class and Sty files');
$page->addScript('/js/installSty.js');

$page->showHeader('import');


echo '<h4>Install sty classes and file ' . $page->info('installSty') . '</h4>';
?>

  <div class="container">
      <p>
      The sty directory is <em><?=ARTICLEDIR . '/sty' ?></em>.
      </p>

<?php
$prevDir = '__empty__'; // avoid warning in strpos
$depth = 0;
foreach ($cfg->clsLoader as $publisher => $val) {
    echo $publisher;
    echo '<ul style="margin-top: 10px">' . PHP_EOL;
    foreach ($val as $name => $val2) {
?>
      <div style="margin-bottom: 10px;">
<?php
        if ($val2['installed']) {
            $icon = 'fa-chevron-down';
        } else {
            $icon = 'fa-cloud-download-alt';
        }
?>
        <button style="font-size: 0.9rem; padding: 0.1rem 0.25rem; margin-right: 10px" type="button" class="btn btn-success install" onclick="installSty(this, '<?=$val2['className'] ?>')">
        <i class="fas <?=$icon ?>"></i>
            <span></span>
        </button>
        <?=htmlspecialchars($name . ' (' . implode(', ', $val2['files']) . ')') ?>
      </div>
<?php
    }
    echo '</ul>' . PHP_EOL;
}

$page->showFooter();

