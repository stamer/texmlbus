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

$page = new Page('History');
$page->addScript('js/Chart.bundle.min.js');
$page->addScript('js/historySum.js');
$page->showHeader('history');

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$set = $page->getRequest()->getQueryParam('set', '');
$statsTab = $page->getRequest()->getCookieParam('statsTab', 'tab-1');
$stage = $page->getRequest()->getQueryParam('stage', 'xml');
$detail = (int) $page->getRequest()->getQueryParam('detail', 0);

$stages = array_keys($cfg->stages);

if (in_array($stage, $stages)) {
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
} else {
    echo "Unknown stage: ".htmlspecialchars($stage);
    exit;
}
?>
    <div class="container">
    <div class="row">
        <div class="col-12">
<?php
if ($detail) {
    if ($set != '') {
        echo '<h4>Detailed History for <em>'.htmlspecialchars($set).'</em> ' . $page->info('detailedHist') . '</h4>' . PHP_EOL;
        echo '<a href="#" onClick="javascript:createSnapshotBySet(\''.htmlspecialchars($set).'\')">create snapshot</a>' . PHP_EOL;
    } else {
        echo '<h4>Detailed Overall History ' . $page->info('detailedHist') . '</h4>';
        echo '<a href="#" onClick="javascript:createSnapshotBySet(\'\')">create snapshot for all sets</a>' . PHP_EOL;
    }
} else {
    if ($set != '') {
        echo '<h4>History for <em>'.htmlspecialchars($set).'</em> ' . $page->info('overallHist') . '</h4>'.PHP_EOL;
        echo '<a href="#" onClick="javascript:createSnapshotBySet(\''.htmlspecialchars($set).'\')">create snapshot</a>' . PHP_EOL;
    } else {
        echo '<h4>Overall History ' . $page->info('overallHist') . '</h4>';
        echo '<a href="#" onClick="javascript:createSnapshotBySet(\'\')">create snapshot for all sets</a>' . PHP_EOL;
    }
}
echo '&nbsp;<span id="snapshot"></span>' .  PHP_EOL;
?>

            <div class="container">
                <p></p>
                <ul class="nav nav-tabs">


<?php

$stages = array_keys($cfg->stages);

$idx = 0;
foreach ($stages as $stage) {

    $idx++;
    $dbTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
    $toolTip = $cfg->stages[$stage]->toolTip;
    $showRetval = $cfg->stages[$stage]->showRetval;
?>
        <li id="tab-<?=$idx ?>"
            onclick="setCookie('statsTab', this.id, 30); getHistSum('<?=$set ?>', '<?=$stage ?>', '<?=$idx ?>', '<?=$detail ?>', 'false'); return true;"
            class="nav-item<?=($statsTab == 'tab-' . $idx) ? ' active' : '' ?>">
            <a class="nav-link<?=($statsTab == 'tab-' . $idx) ? ' active' : '' ?>" data-toggle="tab" href="#menu<?= $idx ?>"><?= htmlspecialchars($tableTitle) ?></a></li>
<?php
}
?>
                </ul>
                <div class="tab-content">
<?php
    $idx = 0;
    foreach ($stages as $stage) {
        $idx++;
        $toolTip = $cfg->stages[$stage]->toolTip;
        ?>
        <div id="menu<?=$idx ?>" class="tab-pane<?=($statsTab == 'tab-' . $idx) ? ' active' : ' fade' ?>">
            <div class="card">
                <p></p>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="mycanvas_<?=$idx ?>"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // draw the graph for active tab
        if ($statsTab == 'tab-'.$idx) {
            $deferJs[] = "getHistSum('".$set."', '".$stage."', $idx, $detail)";
        }
    }
?>
            </div>
        </div>
    </div>
</div>
<?php

$page->showFooter($deferJs);

