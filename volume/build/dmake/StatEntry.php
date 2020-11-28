<?php
/**
 * MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 * A class to handle entries in the statistic database.
 *
 */
namespace Dmake;

use \Dmake\WorkqueueEntry;
use \PDO;

class StatEntry
{
    const WQ_ACTION_NONE = 'none';
    const WQ_ACTION_DEFAULT = 'default';
    const WQ_ACTION_FORCE = 'force';
    const WQ_ENTRY_DISABLED = 0;

    const PDF_RETVAL = 'pdf_retval';
    const XML_RETVAL = 'xml_retval';
    const XHTML_RETVAL = 'xhtml_retval';
    const JATS_RETVAL = 'jats_RETVAL';

    const PDF_ERRMSG = 'pdf_errmsg';
    const XML_ERRMSG = 'xml_errmsg';
    const XHTML_ERRMSG = 'xhtml_errmsg';
    const JATS_ERRMSG = 'jats_errmsg';

    public $id = 0;
    public $date_created = '';
    public $date_modified = '';
    public $set = '';
    public $filename = '';
    public $sourcefile = '';
    public $timeout = -1;
    public $hostgroup = '';
    public $errmsg = '';
    public $wq_id = 0;
    public $wq_priority = self::WQ_ENTRY_DISABLED; // if > 0 entry is part of workqueue
    public $wq_action = self::WQ_ACTION_NONE;
    public $wq_stage = '';
    public $wq_date_created = '';
    public $wq_date_modified = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param string $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * @return string
     */
    public function getDateModified()
    {
        return $this->date_modified;
    }

    /**
     * @param string $date_modified
     */
    public function setDateModified($date_modified)
    {
        $this->date_modified = $date_modified;
    }

    /**
     * @return int
     */
    public function getWqId()
    {
        return $this->wq_id;
    }

    /**
     * @param int $wq_id
     */
    public function setWqId($wq_id)
    {
        $this->wq_id = $wq_id;
    }

    /**
     * @return int
     */
    public function getWqPriority()
    {
        return $this->wq_priority;
    }

    /**
     * @param int $wq_priority
     */
    public function setWqPriority($wq_priority)
    {
        $this->wq_priority = $wq_priority;
    }

    /**
     * @return string
     */
    public function getWqAction()
    {
        return $this->wq_action;
    }

    /**
     * @param string $wq_action
     */
    public function setWqAction($wq_action)
    {
        $this->wq_action = $wq_action;
    }

    /**
     * @return string
     */
    public function getWqPrevAction()
    {
        return $this->wq_prev_action;
    }

    /**
     * @param string $wq_prev_action
     */
    public function setWqPrevAction($wq_prev_action)
    {
        $this->wq_prev_action = $wq_prev_action;
    }

    /**
     * @return string
     */
    public function getWqStage()
    {
        return $this->wq_stage;
    }

    /**
     * @param string $wq_stage
     */
    public function setWqStage($wq_stage)
    {
        $this->wq_stage = $wq_stage;
    }

    /**
     * @return string
     */
    public function getWqDateCreated()
    {
        return $this->wq_date_created;
    }

    /**
     * @param string $wq_date_created
     */
    public function setWqDateCreated($wq_date_created)
    {
        $this->wq_date_created = $wq_date_created;
    }

    /**
     * @return string
     */
    public function getWqDateModified()
    {
        return $this->wq_date_modified;
    }

    /**
     * @param string $wq_date_modified
     */
    public function setWqDateModified($wq_date_modified)
    {
        $this->wq_date_modified = $wq_date_modified;
    }

    /**
     * @return string
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * @param string $set
     */
    public function setSet($set)
    {
        $this->set = $set;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getWqHostGroup()
    {
        return $this->wq_hostgroup;
    }

    /**
     * @param string $hostgroup
     */
    public function setWqHostgroup($hostGroup)
    {
        $this->wq_hostgroup = $hostGroup;
    }

    /**
     * @return mixed
     */
    public function getErrmsg()
    {
        return $this->errmsg;
    }

    /**
     * @param mixed $errmsg
     */
    public function setErrmsg($errmsg)
    {
        $this->errmsg = $errmsg;
    }

    /**
     * returns raw filename (possibly with or without .tex)
     *
     * @return string
     */
    public function getSourcefile()
    {
        return $this->sourcefile;
    }

    /**
     * sets raw filename (possibly with or without .tex)
     *
     * @return string
     */
    public function setSourcefile($sourcefile)
    {
        $this->sourcefile = $sourcefile;
    }

    /**
     * returns the TeX source (it is guaranteed to have a .tex suffix).
     *
     * @return string
     */
    public function getSourcefileTex()
    {
        if (!preg_match('/\.tex$/', $this->sourcefile)) {
            return $this->sourcefile . '.tex';
        } else {
            return $this->sourcefile;
        }
    }

    /**
     * returns the the prefix of the sourcefile (without .tex)
     *
     * @return string
     */
    public function getSourcefilePrefix()
    {
        return preg_replace('/\.tex$/', '', $this->sourcefile);
    }

    /**
     */
    public function save()
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        $matches = array();
        preg_match('#^(.*?)/#', $this->filename, $matches);
        if (isset($matches[1])) {
            $this->set = $matches[1];
        } else {
            $this->set = '';
        }

        $dao = Dao::getInstance();

        $query = '
            INSERT INTO
                statistic
            SET
                id = 0,
                date_created    = :date_created,
                date_modified   = :i_date_modified,
                wq_priority     = :i_wq_priority,
                wq_prev_action  = NULL,
                wq_action       = :i_wq_action,
                wq_stage        = :i_wq_stage,
                `set`           = :i_set,
                filename        = :i_filename,
                sourcefile      = :i_sourcefile,
                hostgroup       = :i_hostgroup,
                timeout         = :i_timeout,
                errmsg          = :i_errmsg
            ON DUPLICATE KEY UPDATE
                date_modified   = :u_date_modified,
                wq_priority     = :u_wq_priority,
                wq_prev_action  = wq_action,
                wq_action       = :u_wq_action,
                wq_stage        = :u_wq_stage,
                `set`           = :u_set,
                filename        = :u_filename,
                sourcefile      = :u_sourcefile,
                hostgroup       = :u_hostgroup,
                timeout         = :u_timeout,
                errmsg          = :u_errmsg';

        $stmt = $dao->prepare($query);

        $stmt->bindValue(':date_created', $cfg->now->datestamp);
        $stmt->bindValue(':i_date_modified', $cfg->now->datestamp);
        $stmt->bindValue(':u_date_modified', $cfg->now->datestamp);
        $stmt->bindValue(':i_wq_priority', $this->wq_priority);
        $stmt->bindValue(':u_wq_priority', $this->wq_priority);
        $stmt->bindValue(':i_wq_action', $this->wq_action);
        $stmt->bindValue(':u_wq_action', $this->wq_action);
        $stmt->bindValue(':i_wq_stage', $this->wq_stage);
        $stmt->bindValue(':u_wq_stage', $this->wq_stage);
        $stmt->bindValue(':i_set', $this->set);
        $stmt->bindValue(':u_set', $this->set);
        $stmt->bindValue(':i_filename', $this->filename);
        $stmt->bindValue(':u_filename', $this->filename);
        $stmt->bindValue(':i_sourcefile', $this->sourcefile);
        $stmt->bindValue(':u_sourcefile', $this->sourcefile);
        $stmt->bindValue(':i_hostgroup', $this->hostgroup);
        $stmt->bindValue(':u_hostgroup', $this->hostgroup);
        $stmt->bindValue(':i_timeout', $this->timeout);
        $stmt->bindValue(':u_timeout', $this->timeout);
        $stmt->bindValue(':i_errmsg', $this->errmsg);
        $stmt->bindValue(':u_errmsg', $this->errmsg);

        $stmt->execute();

        if ($this->id == 0) {
            $this->id = $dao->lastInsertId();
        }
    }

    /**
     * @param $row
     * @return StatEntry
     */
    public static function fillEntry($row)
    {
        $se = new StatEntry();
        if (isset($row['id'])) {
            $se->setId($row['id']);
        }
        if (isset($row['date_created'])) {
            $se->setDateCreated($row['date_created']);
        }
        if (isset($row['date_modified'])) {
            $se->setDateModified($row['date_modified']);
        }
        if (isset($row['wq_priority'])) {
            $se->setWqPriority($row['wq_priority']);
        }
        if (isset($row['wq_prev_action'])) {
            $se->setWqPrevAction($row['wq_prev_action']);
        }
        if (isset($row['wq_action'])) {
            $se->setWqAction($row['wq_action']);
        }
        if (isset($row['wq_stage'])) {
            $se->setWqStage($row['wq_stage']);
        }
        if (isset($row['wq_date_created'])) {
            $se->setWqDateCreated($row['wq_date_created']);
        }
        if (isset($row['wq_date_modified'])) {
            $se->setWqDateModified($row['wq_date_modified']);
        }
        if (isset($row['set'])) {
            $se->setSet($row['set']);
        }
        if (isset($row['filename'])) {
            $se->setFilename($row['filename']);
        }
        if (isset($row['sourcefile'])) {
            $se->setSourcefile($row['sourcefile']);
        }
        if (isset($row['wq_hostgroup'])) {
            $se->setWqHostgroup($row['wq_hostgroup']);
        }
        if (isset($row['timeout'])) {
            $se->setTimeout($row['timeout']);
        }
        if (isset($row['errmsg'])) {
            $se->setErrmsg($row['errmsg']);
        }

        return $se;
    }


    public static function exists($filename)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                filename
            FROM
                statistic
            WHERE
                filename = :filename";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':filename', $filename);

        $stmt->execute();

        // @TODO reliable?
        $num = $stmt->rowCount();

        return ($num > 0);
    }

    public static function existsById($id)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                filename
            FROM
                statistic
            WHERE
                id = :id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        $stmt->execute();

        $num = $stmt->rowCount();

        return ($num > 0);
    }

    /**
     * We only want one file for given subdirectory of depth $mindepth
     * @param string $filename
     * @param int $mindepth
     * @return bool
     */
    public static function pathMatches($filename, $minDepth)
    {
        $pattern = '#^' . str_repeat('.*/', $minDepth - 1) . '.*[^/]#';
        $matches = [];
        preg_match($pattern, $filename, $matches);

        if (empty($matches[0])) {
            echo "No match, \$depth too deep?";
            $cond = $filename;
        } else {
            $cond = $matches[0] . '%';
        }

        $dao = Dao::getInstance();

        $query = "
            SELECT
                filename
            FROM
                statistic
            WHERE
                filename like :cond";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':cond', $cond);

        $stmt->execute();

        $num = $stmt->rowCount();
        return ($num > 0);
    }

    /**
     * @param $action
     * @param $id
     * @return bool
     */
    public static function alreadyDone($action, $id)
    {
        $dao = Dao::getInstance();
        $cfg = Config::getConfig();

        $stages = array_keys($cfg->stages);
        if (in_array($action, $stages)) {
            $retvalTable = $cfg->stages[$action]->dbTable;
        } else {
            error_log(__METHOD__ . ": Unknown action: $action");
            return false;
        }

        $query = "
            SELECT
                retval
            FROM
                $retvalTable
            WHERE
                id = :id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        $stmt->execute();

        $row = $stmt->fetch();

        if (empty($row['retval'])
            || (isset($row['retval'])
                && (($row['retval'] == 'unknown')
                    || strpos($row['retval'], 'rerun') !== false))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $filename
     * @param $action
     * @return bool|null
     */
    public static function getRetval($filename, $stage, $action)
    {
        $dao = Dao::getInstance();
        $cfg = Config::getConfig();

        $stages = array_keys($cfg->stages);
        if (in_array($stage, $stages)) {
            $retvalTable = $cfg->stages[$stage]->dbTable;
        } else {
            error_log(__METHOD__ . ": Unknown action: $action");
            return false;
        }

        $query = "
            SELECT
                retval
            FROM
                statistic as s
            JOIN
                $retvalTable as rt
            ON
                s.id = rt.id
            WHERE
                s.filename = :filename";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':filename', $filename);

        $stmt->execute();

        $row = $stmt->fetch();

        if (isset($row['retval'])) {
            return $row['retval'];
        } else {
            return null;
        }
    }

    /**
     * @param $action
     * @param $restrict
     * @return array
     */
    public static function getFilenamesByRestriction($action, $restrict)
    {
        $dao = Dao::getInstance();
        $cfg = Config::getConfig();

        if (!empty($restrict['dir'])) {
            $q_rd = "AND s.filename like '%" . $restrict['dir'] . "%'";
        } else {
            $q_rd = "";
        }
        if (!empty($restrict['id'])) {
            $q_rdid = "AND s.filename like '%" . $restrict['id'] . "%'";
        } else {
            $q_rdid = "";
        }
        if (!empty($restrict['retval'])) {
            $q_retval = "AND t3.retval = '" . $restrict['retval'] . "'";
            $stages = array_keys($cfg->stages);

            if (in_array($restrict['retval_target'], $stages)) {
                $joinTable = $cfg->stages[$restrict['retval_target']]->dbTable;
            } else {
                echo "Unknown Target " . $restrict['retval_target'] . "\n";
                exit;
            }
            $rjoin = "
                JOIN
                    $joinTable as t3
                ON
                    s.id = t3.id ";
        } else {
            $q_retval = '';
            $rjoin = '';
        }

        if (!empty($restrict['macro'])) {
            $q_macro = "AND s.missing_macros like '%" . $restrict['macro'] . "%'";
        } else {
            $q_macro = "";
        }
        if (!empty($restrict['stylefile'])) {
            $q_style = "AND t2.styfilename = '" . $restrict['stylefile'] . "'";
            $sjoin = "
				JOIN
					mmfile as t2
				ON
					s.filename = t2.filename ";
        } else {
            $q_style = "";
            $sjoin = "";
        }
        if (!empty($restrict['time_before'])) {
            $q_time_before = "AND s.date_modified <  '" . $restrict['time_before'] . "'";
        } else {
            $q_time_before = "";
        }

        if (!empty($restrict['time_after'])) {
            $q_time_after = "AND s.date_modified >  '" . $restrict['time_after'] . "'";
        } else {
            $q_time_after = "";
        }

        $query = "
            SELECT
                s.id,
                s.filename
            FROM
                statistic as s
            $rjoin
            $sjoin
            WHERE
                1 = 1
                $q_retval
                $q_rd
                $q_rdid
                $q_macro
                $q_style
                $q_time_before
                $q_time_after";

        // echo $query.PHP_EOL;

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $filenames = array();
        while ($row = $stmt->fetch()) {
            $filenames[$row['id']] = $row['filename'];
        }

        return $filenames;
    }

    /**
     * @param array $restrict
     * @return array
     */
    public static function getFilenamesByRestrictionXml(array $restrict)
    {
        $dao = Dao::getInstance();
        $cfg = Config::getConfig();

        if (!empty($restrict['dir'])) {
            $q_rd = "AND t1.filename like '%" . $restrict['dir'] . "%'";
        } else {
            $q_rd = "";
        }
        if (!empty($restrict['id'])) {
            $q_rdid = "AND t1.filename like '%" . $restrict['id'] . "%'";
        } else {
            $q_rdid = "";
        }
        if (!empty($restrict['retval'])) {
            $q_retval = "AND r.retval = '" . $restrict['retval'] . "'";
            $joinTable = $cfg->stages['xml']->dbTable;
            $retvalJoin = "
				JOIN
					retval_xml as r
				ON
					t1.id = r.id ";
        } else {
            $q_retval = "";
            $retvalJoin = '';
        }
        if (!empty($restrict['macro'])) {
            $q_macro = "AND t1.missing_macros like '%" . $restrict['macro'] . "%'";
        } else {
            $q_macro = "";
        }
        if (!empty($restrict['set'])) {
            $q_set = "AND t1.set like '%" . $restrict['set'] . "%'";
        } else {
            $q_set = "";
        }
        if (!empty($restrict['stylefile'])) {
            $q_style = "AND mm.styfilename = '" . $restrict['stylefile'] . "'";
            $mmJoin = "
				JOIN
					mmfile as mm
				ON
					t1.filename = mm.filename ";
        } else {
            $q_style = "";
            $mmJoin = "";
        }
        if (!empty($restrict['time_before'])) {
            $q_time_before = "AND t1.date_modified <  '" . $restrict['time_before'] . "'";
        } else {
            $q_time_before = "";
        }

        if (!empty($restrict['time_after'])) {
            $q_time_after = "AND t1.date_modified >  '" . $restrict['time_after'] . "'";
        } else {
            $q_time_after = "";
        }

        $query = "
            SELECT
                t1.id,
                t1.filename
            FROM
                statistic as t1
            $mmJoin
            $retvalJoin
            WHERE
                1 = 1
                $q_retval
                $q_rd
                $q_rdid
                $q_macro
                $q_set
                $q_style
                $q_time_before
                $q_time_after";

        // echo $query.PHP_EOL;

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $filenames = array();
        while ($row = $stmt->fetch()) {
            $filenames[$row['id']] = $row['filename'];
        }

        return $filenames;
    }

    /**
     * @param $filename
     * @return bool
     */
    public static function deleteByDirectory($directory)
    {
        $dao = Dao::getInstance();

        $entry = self::getByDir($directory);

        $query = "
            DELETE FROM
                statistic
            WHERE
                filename = :directory";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':filename', $directory);

        $result = $stmt->execute();

        WorkqueueEntry::deleteByStatisticId($entry->getId());

        return $result;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function deleteById($id)
    {
        $dao = Dao::getInstance();

        $query = "
            DELETE FROM
                statistic
            WHERE
                id = :id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        $result = $stmt->execute();

        WorkqueueEntry::deleteByStatisticId($id);

        return $result;
    }

    /**
     * @param $filename
     * @param $action
     */
    public static function markRerun($filename, $stage, $action)
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        $dao = Dao::getInstance();

        echo $action . PHP_EOL;
        echo $filename . PHP_EOL . PHP_EOL;

        $retval = StatEntry::getRetval($filename, $stage, $action);

        if (strpos($retval, 'rerun') === false) {
            $retval = 'rerun_' . $retval;
        }

        $entry = self::getByDir($filename);

        $wqEntry = new WorkqueueEntry();
        $wqEntry->setStatisticId($entry->getId());
        $wqEntry->setPriority(1);
        $wqEntry->setStage($stage);
        $wqEntry->setDateModified($cfg->now->datestamp);
        $wqEntry->setAction($action);
        $wqEntry->updateButHostgroup();
    }


    /**
     * Get next entries
     * @return ?StatEntry[]
     */
    public static function wqGetNextEntries($hostGroupName = '', $limit = 10, $toStdout = true)
    {
        // here we might get "server has gone away message"
        // therefore explicitly check, whether connection is still there
        $dao = Dao::checkAndGetInstance();

        if ($toStdout) {
            echo ($limit == 1 ? "$hostGroupName: Getting next entry... " : "$hostGroupName: Getting next $limit entries... ");
        }

        $where = 'wq.priority > 0';

        if ($hostGroupName !== '') {
            $where .= ' AND wq.hostgroup = :hostGroupName';
        }

        $query = "
            SELECT
                s.id,
                s.date_created,
                s.date_modified,
                s.filename,
                s.sourcefile,
                wq.id as wq_id,   
                wq.priority as wq_priority,
                wq.prev_action as wq_prev_action,
                wq.action as wq_action,
                wq.stage as wq_stage,
                wq.hostgroup as wq_hostgroup,
                wq.date_modified as wq_date_modified
            FROM
                statistic as s
            JOIN
                workqueue as wq
            ON 
                s.id = wq.statistic_id
            WHERE
                $where
            ORDER BY
                wq.priority DESC, 
                wq.date_modified
            LIMIT :limit";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':limit', $limit);
        if ($hostGroupName !== '') {
            $stmt->bindValue(':hostGroupName', $hostGroupName);
        }

        $stmt->execute();

        $dbEntries = array();
        while ($row = $stmt->fetch()) {
            $dbEntries[$row['wq_id']] = self::fillEntry($row);
        }

        $count = count($dbEntries);
        if ($toStdout) {
            echo "fetched " . $count . ($count == 1 ? ' entry.' : ' entries.') . PHP_EOL;
        }
        return $dbEntries;
    }


    /**
     * @param $directory
     * @param $action
     * @param $priority
     * @return bool|void
     */
    public static function addToWorkqueue($directory, $hostGroupName, $stage, $action, $priority)
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        $dao = Dao::getInstance();

        $entry = StatEntry::getByDir($directory);
        if ($entry) {
            if ($action == StatEntry::WQ_ACTION_FORCE) {
                UtilFile::cleanupDir($directory);
            }
        } else {
            $entry = new StatEntry;
            $entry->filename = $directory;
            $result = $entry->save();
        }

        $wqe = new WorkqueueEntry();
        $wqe->setStatisticId($entry->getId());
        $wqe->setStage($stage);
        $wqe->setDateModified($cfg->now->datestamp);
        $wqe->setPriority($priority);
        $wqe->setAction($action);
        $wqe->setStage($stage);
        $wqe->setHostGroup($hostGroupName);

        $result = $wqe->save();
        return $result;

    }

    /**
     * @param $id
     * @param $action
     * @param $priority
     * @return bool
     */
    public static function addToWorkqueueById($id, $hostGroupName, $stage, $action, $priority)
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        $dao = Dao::getInstance();

        if (StatEntry::existsById($id)) {
            $wqe = new WorkqueueEntry();
            $wqe->setStatisticId($id);
            $wqe->setStage($stage);
            $wqe->setDateModified($cfg->now->datestamp);
            $wqe->setPriority($priority);
            $wqe->setAction($action);
            $wqe->setStage($stage);
            $wqe->setHostGroup($hostGroupName);

            $result = $wqe->save();
            return $result;
        } else {
            return false;
        }
    }


    /**
     * @param $directory
     * @param $sourcefile
     * @param $minDepth
     * @param string $action
     * @param string $retval
     * @return bool
     */
    public static function addNew($directory, $sourcefile, $minDepth, $hostGroupName = '', $stage = '', $action = 'none', $retval = 'unknown')
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        // we want only one file for given subdirectory of $minDepth
        // therefore check for matching subpath, not the exact file
        if (!StatEntry::pathMatches($directory, $minDepth)) {
            $entry = new StatEntry;
            $entry->filename = $directory;
            $entry->sourcefile = $sourcefile;
            $entry->retval = $retval;
            $entry->save();

            $wqe = new WorkqueueEntry();
            $wqe->setStatisticId($entry->getId()); // id has just been created
            $wqe->setStage($stage);
            $wqe->setDateModified($cfg->now->datestamp);
            $wqe->setPriority(0);
            $wqe->setAction($action);
            $wqe->setStage($stage);
            $wqe->setHostGroup($hostGroupName);
            $wqe->save();

            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $directory
     * @return StatEntry
     */
    public static function getByDir($directory)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                statistic
            WHERE
                filename = :directory";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':directory', $directory);

        $stmt->execute();

        $obj = null;
        if ($row = $stmt->fetch()) {
            $obj = self::fillEntry($row);
        }
        return $obj;
    }

    /**
     *
     * @param string $directory
     * @return StatEntry
     */
    public static function getById($id)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                statistic
            WHERE
                id = :id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        $stmt->execute();

        $obj = null;
        if ($row = $stmt->fetch()) {
            $obj = self::fillEntry($row);
        }
        return $obj;
    }

    /**
     * @param $directory
     * @return mixed
     */
    public static function getIdByDir($directory)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                id
            FROM
                statistic
            WHERE
                filename = :directory";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':directory', $directory);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['id'];
    }

    /**
     * @param $directory
     * @return mixed
     */
    public static function getIdsBySet($set)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                id
            FROM
                statistic
            WHERE
                `set` = :set";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':set', $set);

        $stmt->execute();

        $ids = [];
        while ($row = $stmt->fetch())
        {
            $ids[] = $row['id'];
        }
        return $ids;
    }

    /**
     * @param string $pattern
     * @return array
     */
    public static function getSets($pattern = '')
    {
        $dao = Dao::getInstance();

        if ($pattern != '') {
            $where = ' WHERE s.`set` LIKE :pattern ';
        } else {
            $where = '';
        }

        $query = "
            SELECT
                distinct s.`set`,
                std.sourcefile
            FROM
                statistic as s
            LEFT JOIN
                source_to_dir as std
            ON
                s.`set` = std.directory
            $where
            ORDER BY
                `set`";

        $stmt = $dao->prepare($query);
        if ($pattern != '') {
            $stmt->bindValue(':pattern', '%' . $pattern . '%', PDO::PARAM_STR);
        }
        $stmt->execute();

        $rows = array();
        while ($row = $stmt->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @param string $pattern
     * @return array
     */
    public static function getSetsCount()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                s.`set`,
                count(s.id) as num_documents,                            
                std.sourcefile
            FROM
                statistic as s
            LEFT JOIN
                source_to_dir as std
            ON
                s.`set` = std.directory
            GROUP BY
                `set`
            ORDER BY
                `set`";

        $stmt = $dao->prepare($query);
        $stmt->execute();

        $rows = array();
        while ($row = $stmt->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @param $directory
     * @return mixed
     */
    public static function getCountByDirPrefix($dirPrefix)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(*) as num
            FROM
                statistic
            WHERE
                filename LIKE :directory";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':directory', $dirPrefix .'%');

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['num'];
    }

    /**
     * get the statistic for the given result table
     *
     * @param string $joinTable
     * @param string $set
     * @return mixed
     */
    public static function getStats($joinTable, $set = '')
    {
        $dao = Dao::getInstance();

        if ($set != '') {
            $where = '
                WHERE
                    filename like :set ';
        } else {
            $where = '';
        }

        // do a LEFT JOIN, so we also find all files that have not
        // yet been processed.
        $query = "
            SELECT
                count(IFNULL(j.retval, 1)) as num,
                j.retval
            FROM
                statistic as s
            LEFT JOIN
                " . $joinTable . " as j
            ON
                s.id = j.id
            $where
            GROUP BY
                j.retval";

        $stmt = $dao->prepare($query);
        if ($set != '') {
            $stmt->bindValue(':set', $set . '%');
        }

        $stmt->execute();

        $rerun = array();

        $stat = array();
        while ($row = $stmt->fetch()) {
            // reduce retval
            if (is_null($row['retval'])) {
                $retval = 'unknown';
            } else {
                $retval = str_replace('rerun_', '', $row['retval']);
            }

            // but here check original, but assign to retval, it is easier later.
            if (strpos($row['retval'], 'rerun') !== false) {
                $rerun[$retval] = $row['num'];
            }

            if (isset($stat[$retval])) {
                $stat[$retval] += $row['num'];
            } else {
                $stat[$retval] = $row['num'];
            }

        }

        return array($stat, $rerun);
    }

    /**
     * @param $retval
     * @param string $joinTable
     * @param string $set
     * @return array
     */
    public static function getFileNamesByRetval($retval, $joinTable = '',  $set = '')
    {
        $dao = Dao::getInstance();

        if (empty($joinTable)) {
            $joinTable = 'retval_xml';
        }

        if ($set != '') {
            $where = '
                WHERE
                    s.filename like :set ';
        } else {
            $where = '
                WHERE 1 ';
        }

        $where .= ' AND j.retval = :retval';

        $query = "
        	SELECT
        		filename,
        		j.errmsg,
                j.retval
        	FROM
        		statistic as s
            LEFT JOIN
                " . $joinTable . " as j
            ON
                s.id = j.id
        	$where
	        ORDER BY
		        filename";

        $stmt = $dao->prepare($query);
        if ($set != '') {
            $stmt->bindValue(':set', $set . '%');
        }
        $stmt->bindValue(':retval', $retval);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param string $stage
     * @param string $joinTable
     * @return mixed
     */
    public static function getCountLastStat($stage, $joinTable)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(*) as numrows
            FROM
                statistic as s
            JOIN
                workqueue as wq
            ON
                s.id = wq.statistic_id  
                AND wq.stage = :stage    
            LEFT JOIN
                $joinTable as j
            ON
                s.id = j.id
            WHERE
                wq.prev_action is NOT NULL 
                AND wq.priority = 0";


        $stmt = $dao->prepare($query);
        $stmt->bindValue(':stage', $stage);
        $stmt->execute();

        $row = $stmt->fetch();

        $numrows = $row['numrows'];

        return $numrows;
    }

    /**
     * @param $orderBy
     * @param $sortBy
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getLastStat($orderBy, $sortBy, $min, $max_pp)
    {
        // due to long running sse script
        // here we might get "server has gone away message"
        // therefore explicitly check, whether connection is still there
        $dao = Dao::checkAndGetInstance();

        $query = "
            SELECT
                wq.date_modified as s_date_modified,
                s.id,
                s.sourcefile,
                s.filename,
                wq.prev_action as wq_prev_action,
                wq.stage as wq_stage
            FROM
                statistic as s
            JOIN
                workqueue as wq
            ON 
                s.id = wq.statistic_id
            WHERE
                wq.prev_action IS NOT NULL
                AND wq.priority = 0
            ORDER BY
                $orderBy $sortBy
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
