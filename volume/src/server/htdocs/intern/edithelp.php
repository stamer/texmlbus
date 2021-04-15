<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A very simple script to add/edit/delete help pages
 */
require_once "../../include/IncFiles.php";
use Dmake\Dao;
use Dmake\HelpDao;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

if (!$cfg->show->internal) {
    exit;
}

$page = new Page('Build System');

$page->showHeader('edithelp');

if (isset($_POST['delete'])) {
    $id = $_POST['helpid'];
    HelpDao::deleteById($id);
}

$ids = helpDao::getAllIds();

if (isset($_POST['load'])) {
    $id = $_POST['helpid'];
    $row = HelpDao::getHelpById($id);
} elseif (isset($_POST['add'])) {
    $id = $_POST['newid'];
    $title = $_POST['title'];
    $html = $_POST['html'];
    HelpDao::save($id, $title, $html);
    $ids = helpDao::getAllIds();
    $row = ['id' => $id, 'title' => $title, 'html' => $html];
} elseif (isset($_POST['save'])) {
    $id = $_POST['helpid'];
    $title = $_POST['title'];
    $html = $_POST['html'];
    HelpDao::save($id, $title, $html);
    $row = ['id' => $id, 'title' => $title, 'html' => $html];
} else {
    $id = $ids[0];
    $row = HelpDao::getHelpById($id);
}

echo '<h3>Edit Help</h3>';
?>

      <div class="container">
          <form id="editHelp" method="post" action="edithelp.php">
              <div style="margin-top:20px">

                <select name="helpid">
                    <?php
                    foreach ($ids as $id) {
                        echo '<option value="' . $id . '" ' . ($id == $row['id'] ? 'selected="selected" ' : '') .' >' . $id . '</option>' . PHP_EOL;
                    }
                    ?>
                </select> <input type="submit" name="load" value="Load" />
                <input style="margin-left:200px" type="submit" name="delete" value="Delete" />
              </div>
              <br />
              <input type="text" name="title" value="<?=htmlspecialchars($row['title']) ?>" />
              <p style="margin-top:20px">
              <textarea name="html" style="width:600px; height:400px"><?=htmlspecialchars($row['html']) ?></textarea>
              </p>
            <div style="margin-top:1rem">

                <input type="submit" name="save" value="Save">
                <br /><br />
                <input type="text" name="newid" />
                <input type="submit" name="add" value="Add new Id"/>
            </div>
            <p style="margin-top:20px">
                <a href="javascript:openHelp('<?=$row['id'] ?>')">Show helptext in window</a>
            </p>
            <div>
                <?php echo '<h3>' . $row['title'] . '</h3>' . PHP_EOL; ?>

                <?=$row['html'] ?>
            </div>
          </form>
      </div>

<?php

$page->showFooter();
