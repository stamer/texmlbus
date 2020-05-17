<?php
require_once "../../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Licenses');
$page->showHeader('Licences');

?>
<h3>Licenses</h3>
<style>
dd { padding-left: 20px }
</style>
<p>
This program is released under <a href="mit-license.txt">MIT License</a>.
</p>
<p>
Several other components are distributed along with this program.<br />
These include:
<dl>
<dt><a href="https://getbootstrap.com/">Bootstrap</a> <a href="https://github.com/twbs/bootstrap/blob/master/LICENSE">(licensed under MIT)</a></dt>
    <dd>&copy; <a href="https://github.com/twbs/bootstrap/graphs/contributors">The Bootstrap Authors</a></dd>
    <dd>CSS Framework</dd>

<dt><a href="https://getbootstrap.com/">Javascript for Bootstrap's docs</a> <a href="https://creativecommons.org/licenses/by/3.0/">(licensed under Creative Commons Attribution 3.0 Unported License)</a></dt>
    <dd>&copy; <a href="https://github.com/twbs/bootstrap/graphs/contributors">The Bootstrap Authors, Twitter, Inc.</a></dd>
    <dd>CSS Framework</dd>

<dt><a href="http://chartjs.org">ChartJs</a> <a href="https://github.com/chartjs/Chart.js/blob/master/LICENSE.md">(licensed under MIT)</a><dt>
    <dd>&copy; Nick Downie</dd>
    <dd>Display graphics</dd>

<dt><a href="jquery.org">jQuery v3.x.x"</a> <a href="https://jquery.org/license">(licensed under MIT)</a></dt>
    <dd>&copy; JS Foundation and other contributors</a></dd>

<dt><a href="jqueryui.com">jQuery UI</a> <a href="https://jqueryui.com/license">(licensed under MIT)</a></dt>
    <dd>&copy; jQuery Foundation and other contributors</a></dd>

<dt><a href="https://popper.js.org">popper.js</a> <a href="http://opensource.org/licenses/MIT">(licensed under MIT)</a></dt>
    <dd>&copy; Federico Zivolo</a></dd>
    <dd>A kickass library used to manage poppers in web applications.</dd>

<dt><a href="https://select2.github.io">select2.js</a> <a href="https://github.com/select2/select2/blob/master/LICENSE.md">(licensed under MIT)</a></dt>
    <dd>&copy; Kevin Brown, Igor Vaynberg, and Select2 contributors</a></dd>
    <dd>Select2 is a jQuery-based replacement for select boxes.</dd>

<dt><a href="https://gitlab.com/latexpand/latexpand">latexpand</a> (licensed under BSD)</dt>
    <dd>&copy; 2012-2019: Matthieu Moy</dd>

</dl>
</p>

<?php

$page->showFooter();



 
