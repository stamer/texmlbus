<?php
/**
 * Released under MIT License
 * (c) 2007 - 2018 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Dmake\PackageUsageDao;
use Server\Config;
use Server\Page;
use Server\UtilMisc;

$page = new Page('Package usage');
$page->showHeader('package_usage');

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$set = $page->getRequest()->getQueryParam('set', '');

parse_str($page->getRequest()->getQueryParam('QUERY_STRING', ''), $queryVars);

if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}
?>

<?php

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$max_pp = $cfg->db->perPage;

$sort = $page->getRequest()->getQueryParam('sort', '');

if ($sort === 'filename') {
    $order = 'pu.styfilename';
} elseif ($sort === 'total') {
    $order = 'total DESC';
} else {
    $order = 'success_rate DESC, pu.styfilename ASC';
}

$numrows = PackageUsageDao::getCount($set);

$rows = PackageUsageDao::getStyCorrelation($set, $order, $min, $max_pp);


    echo '<h3>Correlation of package files to xml successful conversion</h3>';
    echo '<p>Correlation of used package files to the successful (<em>warnings, no_problems</em>) xml conversion of an article.</p>';
	echo '<table border="1">';

    echo '<tr>';
    echo '<th>count</th>';
    $queryVars['sort'] = 'filename';
    $link = $_SERVER['PHP_SELF'].'?'.http_build_query($queryVars);
    echo '<th><a href="'.$link.'">filename</a></th>';
    $queryVars['sort'] = 'success_rate';
    $link = $_SERVER['PHP_SELF'].'?'.http_build_query($queryVars);
    echo '<th><a href="'.$link.'">success_rate</a></th>';
    $queryVars['sort'] = 'total';
    $link = $_SERVER['PHP_SELF'].'?'.http_build_query($queryVars);
    echo '<th><a href="'.$link.'">total</a></th>';
    echo '</tr>';
    
	$count = $min;
	foreach ($rows as $row) {
		$count++;
		echo "<tr>\n";
		echo '<td align="right">'.$count.'</td>';
		echo '<td>'.$row['styfilename'].'</td>';
		echo '<td>'.$row['success_rate'].'</td>';
		echo '<td align="right">'.$row['total'].'</td>';
		echo "</tr>\n";
	}
	
	echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
