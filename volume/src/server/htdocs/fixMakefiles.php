<?php
/**
 * Released under MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Fix Makefiles');
$page->addScript("/js/fixMakefiles.js");

$page->showHeader('import');

echo '<h4>Fix Makefiles</h4>';
?>

      <div class="container">
          <p>
              The article directory is <em><?=ARTICLEDIR ?></em>.
          </p>
          <p>
              The sourcecode has been moved from <em>build</em> to <em>src</em>.
          </p>
          <p>
              Conversion will still run correctly inside docker, but not if manually run directly via make.
          <br />
              Here you can fix all Makefiles in the articles subfolder.
          </p>

          <form id="fixMakeFiles">
            <div class="mt-4">
                <button class="btn btn-primary" type="submit" name="submit" onclick="fixMakefiles(); return false;">Fix Makefiles</button>
            </div>
          </form>
      </div>

    <script>
        $('#myModal').modal('hide');

    </script>
<?php

$page->showFooter();
