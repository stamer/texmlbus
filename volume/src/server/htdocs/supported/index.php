<?php
/**
 * Latexml needs binding (.ltxml) files to support classes and packages
 * The location of latexml .ltxml files and the list of files is determined on-the-fly,
 * and also cached.
 *
 * In addition the additional files of the build system are added to the list.
 */

require_once "../../include/IncFiles.php";

use Dmake\Dao;
use Dmake\UtilBindingFile;
use Server\Config;
use Server\Page;
use Server\RequestFactory;

$cfg = Config::getConfig();
$dao = Dao::getInstance();
$request = RequestFactory::create();

$useCache = (bool) $request->getQueryParam('nocache', '');

$page = new Page('Supported Classes');
$page->showHeader('supported');

echo '<a name="class"></a><h4>Supported Classes ' . $page->info('supported', 0.7) . '</h4>' . PHP_EOL;

?>
    <p>
        You can also additionally check the whether the tex installation provides
    specific cls/sty files. This <a href="/supported/detail.php">check</a> will take
    some seconds though.
    </p>
    <div style="overflow-x:auto;">
    <table>
        <tr>
            <th style="min-width:200px">filename</th>
            <th>support via</th>
        </tr>
<?php

$clsFiles = UtilBindingFile::getClsFiles($useCache);
$styFiles = UtilBindingFile::getStyFiles($useCache);

foreach ($clsFiles as $name => $type) {
    if ($type == 'build/latexml') {
        $class = 'red';
    } else {
        $class = $type.'grey';
    }
    echo '<tr><td>' . htmlspecialchars($name).' </td><td><span class="'.$class.'">'.$type . '</span></td></tr>' . PHP_EOL;
}
?>
    </table>
<?php
echo '<br />'.count($clsFiles).' class files.<br />'.PHP_EOL;

echo '<a name="package"><p></p></a>';
echo '<h4>Supported Packages ' . $page->info('supportedPackages', 0.7) . '</h4>'.PHP_EOL;
?>
    <table>
        <tr>
        <th style="min-width:200px">filename</th>
        <th>support via</th>
        </tr>
<?php
foreach ($styFiles as $name => $type) {
    if ($type == 'build/latexml') {
        $class = 'red';
    } else {
        $class = $type.'grey';
    }
    echo '<tr><td>' . htmlspecialchars($name).' </td><td><span class="'.$class.'">' . $type . '</span></td></tr>' . PHP_EOL;
}
?>
    </table>

<?php
echo '<br />'.count($styFiles).' package files.<br />'.PHP_EOL;

$page->showFooter();

