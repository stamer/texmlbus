<?php
require_once "../../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Documentation');
$page->showHeader('documentation');

require_once('../../../doc/manual.tex.xhtml');


$page->showFooter();



 
