<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\GeneralStatistics;
use Server\Page;
use Server\UtilMisc;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Build System');
$page->showHeader('general');


$num_state = GeneralStatistics::getCurrentState();
if ($num_state) {
	$state = '<span class="ok">running</span>';
} else {
	$state = '<span class="error">stopped</span>';
}

$dmakeStatus = GeneralStatistics::getDmakeStatus();

$num_conv = GeneralStatistics::getNumCompiledFiles();
$num_last_24 = GeneralStatistics::getNumLast24();
$num_last_hour = GeneralStatistics::getNumLastHour();

$wq_num = GeneralStatistics::wqGetNumEntries();
?>
    <h3 style="text-align: center"><em><b>Tex</b> to X<b>ML BU</b>ild <b>S</b>ystem (texmlbus)</em></h3>
    <h4>General statistics <?=$page->info('generalStat') ?></h4>
<table>
    <tr>
        <td colspan="2"><em>Converted documents</em></td>
    </tr>
    <tr>
        <td width="300">Total number</td><td><b><?=$num_conv ?></b></td>
    </tr>
    <tr>
        <td width="200">Last 24 hours</td><td><?=$num_last_24 ?></td>
    </tr>
    <tr>
        <td width="200">Last Hour</td><td><?=$num_last_hour ?></td>
    </tr>
    <tr>
        <td colspan="2"><em>Build statistics</em></td>
    </tr>
    <tr>
        <td width="300">State</td><td><?=$state ?></td>
    </tr>
    <tr>
        <td width="200">Current job started at (UTC)</td><td><?=$dmakeStatus['started']; ?></td>
    </tr>
    <tr>
        <td width="200">LaTeXML versions</td><td><b><?=implode('<br />', UtilMisc::getLatexmlVersion()) ?></b></td>
    </tr>
    <tr>
        <td>Number of documents in queue</td><td><?=$wq_num; ?></td>
    </tr>
    <tr>
        <td>Number of concurrent conversion jobs</td><td><?=$dmakeStatus['num_hosts']; ?></td>
    </tr>
    <tr>
        <td>Hosts</td><td><?=htmlspecialchars($dmakeStatus['hostnames']); ?></td>
    </tr>
    <tr>
        <td>Timeout</td><td><?=$dmakeStatus['timeout']; ?> s</td>
    </tr>
    <tr>
        <td>Worker Memory Factor</td><td><?=($cfg->memory->factor ?? 'unset') ?></td>
    </tr>
    <tr>
        <td>Worker Memory Absolute Limit</td><td><?=($cfg->memory->absolute ?? 'unset') ?></td>
    </tr>
</table>


<?php
$page->showFooter();




