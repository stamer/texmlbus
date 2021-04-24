<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Dmake\StatEntry;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Statistics');
$page->showHeader('index');

$set = $page->getRequest()->getQueryParam('set', '');
$statsTab = $page->getRequest()->getCookieParam('statsTab', 'tab-1');


?>

    <div class="container">
        <div class="row">
            <div class="col-12">
                    <?php
                    if ($set != '') {
                        echo '<h4>Statistics for <em>'.htmlspecialchars($set).'</em> ' . $page->info('index') .'</h4>'.PHP_EOL;
                    } else {
                        echo '<h4>Overall statistics ' . $page->info('index') . '</h4>';
                    }
                    ?>
                <div class="container">
                    <ul class="nav nav-tabs">

<?php

$stages = array_keys($cfg->stages);

$idx = 0;
foreach ($stages as $stage) {
    $idx++;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
    $toolTip = $cfg->stages[$stage]->toolTip;

    //$total_retval = max(1, array_sum($stat_class));

?>
        <li id="tab-<?=$idx ?>"
            onclick="setCookie('statsTab', this.id, 30); return true;"
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
        // just to get the right order..
        $stat = array();
        foreach ($cfg->ret_class as $class=>$stclass) {
            $stat[$class] = 0;
        }

        // just to get the right order..
        $stat_class = array();
        foreach ($cfg->ret_color as $class=>$color) {
            $stat_class[$class] = 0;
        }

        $toolTip = $cfg->stages[$stage]->toolTip;
        $dbTable = $cfg->stages[$stage]->dbTable;
        $showRetval = $cfg->stages[$stage]->showRetval;
        $showTopErrors = $cfg->stages[$stage]->showTopErrors;
        $showDetailErrors = $cfg->stages[$stage]->showDetailErrors;

        list($stat, $rerun) = StatEntry::getStats($dbTable, $set);

        $total_retval = 0;
        foreach ($stat as $retval => $num) {
            $total_retval += $num;
            $retval_class = $cfg->ret_class[$retval];
            if (isset($stat_class[$retval_class])) {
                $stat_class[$retval_class] += $num;
            } else {
                $stat_class[$retval_class] = $num;
            }
        }
?>
            <div id="menu<?=$idx ?>" class="tab-pane<?=($statsTab == 'tab-' . $idx) ? ' active' : ' fade' ?>">
                <div class="card">
                    <div class="card-body">
                    <table border="1">
                        <tr>
                            <th>result</th>
                            <th>count</th>
                            <th> % </th>
                        </tr>
<?php

        foreach ($stat_class as $retval => $num) {
            echo '<tr class="'.$cfg->ret_color[$retval].'">'."\n";

            echo '<td title="'.$cfg->tt_cat[$retval].'">'.$retval."</td>\n";
            echo '  <td align="right">'.$num."</td>\n";
            if ($total_retval > 0) {
                $percent = $num / $total_retval * 100.0;
            } else {
                $percent = 0;
            }
            echo '  <td align="right">'.number_format($percent, 2)."</td>\n";
            echo "</tr>\n";
        }
?>
                    </table>
                    <br />
                    <h5>Detailed return values</h5>

                    <table border="1">
                        <tr>
                            <th>return value</th>
                            <th>count</th>
                            <th> % </th>
                            <th>marked<br />for rerun</th>
                        </tr>
<?php

        foreach ($stat as $retval=>$num) {

            if (!$showRetval[$retval]) {
                continue;
            }
            $color = $cfg->ret_color[$cfg->ret_class[$retval]];
            echo '<tr class="'.$color.'">'."\n";

            $link = 'retval_detail.php?set='.$set.'&amp;stage='.$stage.'&amp;retval='.$retval;
            echo '  <td title="'.$cfg->tt_class[$retval].'"><a href="'.$link.'">'.$retval."</a></td>\n";
            echo '  <td align="right">'.$num."</td>\n";

            if ($total_retval > 0) {
                $percent = $num / $total_retval * 100.0;
            } else {
                $percent = 0;
            }

            echo '  <td align="right">'.number_format($percent, 2)."</td>\n";

            if (isset($rerun[$retval])) {
                echo '  <td align="right">'.$rerun[$retval]."</td>\n";
            } else {
                echo "  <td>&nbsp;</td>\n";
            }
            echo "</tr>\n";
        }
?>
                    </table>
<?php
        $renderTexTableUrl = '/ajax/renderTexTable.php?set='.htmlspecialchars($set).'&amp;target='
            .htmlspecialchars($stage).'&amp;detail=1';
?>
                    <small><a href="#" onClick="javascript:showUrlInModal('<?=$renderTexTableUrl ?>');">table as TeX</a></small>
                    </p>
<?php
        if ($showTopErrors['fatal_error']) {
?>
                    <p>
                        <a href="top_msg.php?set=<?= htmlspecialchars($set) ?>&amp;stage=<?= htmlspecialchars($stage) ?>&amp;retval=fatal_error">
                            Top Fatal Errors for (<?= htmlspecialchars($stage) ?>)</a>
                    </p>
<?php
        }
        if ($showTopErrors['error']) {
?>
                    <p>
                        <a href="top_msg.php?set=<?=htmlspecialchars($set) ?>&amp;stage=<?= htmlspecialchars($stage) ?>&amp;retval=error">
                            Top Errors for (<?=htmlspecialchars($stage) ?>)</a>
                    </p>

<?php
        }
        if ($showTopErrors['missing_macros']) {
?>
                    <p>
                        <a href="top_macros.php?set=<?=htmlspecialchars($set) ?>&amp;stage=<?= htmlspecialchars($stage) ?>">
                            Top Missing Macros for (<?=htmlspecialchars($stage) ?>)</a>
                    </p>
<?php
        }
        if ($showDetailErrors['error']) {
?>
                    <p>
                        <a href="top_errdetail.php?set=<?=htmlspecialchars($set) ?>&amp;stage=<?= htmlspecialchars($stage) ?>&amp;retval=error ?>">
                            Top Detail Error Messages for (<?=htmlspecialchars($stage) ?>)</a>
                    </p>
<?php
        }
?>
                    </div>
                </div>
            </div>
<?php
    }
?>
       </div>
    </div>
</div>
<?php

// just to get the right order..
$stat = array();
foreach ($cfg->ret_class as $class=>$stclass) {
	$stat[$class] = 0;
}

// just to get the right order..
$stat_class = array();
foreach ($cfg->ret_color as $class=>$color) {
	$stat_class[$class] = 0;
}

$page->showFooter();




