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

$bindingDir = UtilBindingFile::getBindingFilesDir();
$clsPattern = "/\.cls\.ltxml$/";
$styPattern = "/\.sty\.ltxml$/";

$latexmlClsFiles = 
    array_fill_keys(
        UtilBindingFile::getBindingFiles($bindingDir, $useCache,  'latexmlClsFiles.tmp', $clsPattern),
        'latexml');
$latexmlStyFiles = 
    array_fill_keys(
        UtilBindingFile::getBindingFiles($bindingDir, $useCache, 'latexmlStyFiles.tmp', $styPattern),
        'latexml');

$buildClsFiles = 
    array_fill_keys(
        UtilBindingFile::getBindingFiles(STYDIR, $useCache, 'buildClsFiles.tmp', $clsPattern),
        'build');
$buildStyFiles = 
    array_fill_keys(
        UtilBindingFile::getBindingFiles(STYDIR, $useCache, 'buildStyFiles.tmp', $styPattern),
        'build');

// find files that exist in both arrays
$clsIntersectFiles = array_intersect_key($latexmlClsFiles, $buildClsFiles);
foreach ($clsIntersectFiles as $key => &$val) {
    $val = 'build/latexml';
}

// find files that exist in both arrays
$styIntersectFiles = array_intersect_key($latexmlStyFiles, $buildStyFiles);
foreach ($styIntersectFiles as $key => &$val) {
    $val = 'build/latexml';
}

// last ones will overwrite previous ones
$clsFiles = array_merge($latexmlClsFiles, $buildClsFiles, $clsIntersectFiles);

// last ones will overwrite previous ones
$styFiles = array_merge($latexmlStyFiles, $buildStyFiles, $styIntersectFiles);

ksort($clsFiles, SORT_ASC);
ksort($styFiles, SORT_ASC);

echo '<a name="class"></a><h4>Supported Classes ' . $page->info('supported', 0.7) . '</h4>' . PHP_EOL;

?>
    <div style="overflow-x:auto;">
    <table>
    <th style="min-width:200px">filename</th><th>support via</th>
<?php

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
    <th style="min-width:200px">filename</th><th>support via</th>
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

