<?php
/**
 * MIT License
 * (c) 2007 - 2022 Heinrich Stamerjohanns
 *
 * A class to handle entries in the statistic database.
 *
 */
namespace Dmake;

use \Dmake\WorkqueueEntry;
use \PDO;

class StatEntry
{
    public const WQ_ACTION_NONE = 'none';
    public const WQ_ACTION_DEFAULT = 'default';
    public const WQ_ACTION_FORCE = 'force';
    public const WQ_ENTRY_DISABLED = 0;

    public const ENUM_COMMENT_STATUS = [
        'none' => 'white',
        'todo' => 'blue',
        'working' => 'lightgreen',
        'revisit' => 'orange',
        'cannot fix' => 'red',
        'ok' => 'green'
    ];

    public $id = 0;
    public $date_created = '';
    public $date_modified = '';
    public $set = '';
    public $filename = '';
    public $sourcefile = '';
    public $hostgroup = '';
    public $timeout = -1;
    public $errmsg = '';
    protected $project_id = ''; // id of project in cloud
    protected $project_src = ''; // cloud provider
    protected $comment = '';
    protected $comment_status = 'none';
    protected $comment_date;

    public $wq_id = 0;
    public $wq_priority = self::WQ_ENTRY_DISABLED; // if > 0 entry is part of workqueue
    public $wq_action = self::WQ_ACTION_NONE;
    public $wq_stage = '';
    public $wq_date_created = '';
    public $wq_date_modified = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDateCreated(): ?string
    {
        return $this->date_created;
    }

    public function setDateCreated(string $date_created): void
    {
        $this->date_created = $date_created;
    }

    public function getDateModified(): ?string
    {
        return $this->date_modified;
    }

    public function setDateModified(string $date_modified): void
    {
        $this->date_modified = $date_modified;
    }

    public function getWqId(): ?int
    {
        return $this->wq_id;
    }

    public function setWqId(int $wq_id): void
    {
        $this->wq_id = $wq_id;
    }

    public function getWqPriority(): ?int
    {
        return $this->wq_priority;
    }

    public function setWqPriority(int $wq_priority): void
    {
        $this->wq_priority = $wq_priority;
    }

    public function getWqAction(): ?string
    {
        return $this->wq_action;
    }

    public function setWqAction(?string $wq_action): void
    {
        $this->wq_action = $wq_action;
    }

    public function getWqPrevAction(): ?string
    {
        return $this->wq_prev_action;
    }

    public function setWqPrevAction(?string $wq_prev_action): void
    {
        $this->wq_prev_action = $wq_prev_action;
    }

    public function getWqStage(): ?string
    {
        return $this->wq_stage;
    }

    public function setWqStage(?string $wq_stage): void
    {
        $this->wq_stage = $wq_stage;
    }

    public function getWqDateCreated(): ?string
    {
        return $this->wq_date_created;
    }

    public function setWqDateCreated(?string $wq_date_created): void
    {
        $this->wq_date_created = $wq_date_created;
    }

    public function getWqDateModified(): ?string
    {
        return $this->wq_date_modified;
    }

    public function setWqDateModified(string $wq_date_modified): void
    {
        $this->wq_date_modified = $wq_date_modified;
    }

    public function getSet(): ?string
    {
        return $this->set;
    }

    public function setSet(?string $set): void
    {
        $this->set = $set;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getWqHostGroup(): ?string
    {
        return $this->wq_hostgroup;
    }

    public function setWqHostgroup(string $hostGroup): void
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
    public function setErrmsg($errmsg): void
    {
        $this->errmsg = $errmsg;
    }

    /**
     * Returns raw filename (possibly with or without .tex).
     */
    public function getSourcefile(): ?string
    {
        return $this->sourcefile;
    }

    /**
     * Sets raw filename (possibly with or without .tex).
     */
    public function setSourcefile(string $sourcefile): void
    {
        $this->sourcefile = $sourcefile;
    }

    public function getProjectId(): string
    {
        return $this->project_id;
    }

    public function setProjectId(string $project_id): void
    {
        $this->project_id = $project_id;
    }

    public function getProjectSrc(): string
    {
        return $this->project_src;
    }

    public function setProjectSrc(string $project_src): void
    {
        $this->project_src = $project_src;
    }

    /**
     * Returns the TeX source (it is guaranteed to have a .tex suffix).
     */
    public function getSourcefileTex(): ?string
    {
        if (!preg_match('/\.tex$/', $this->sourcefile)) {
            return $this->sourcefile . '.tex';
        }

        return $this->sourcefile;
    }

    /**
     * Returns the the prefix of the sourcefile (without .tex).
     */
    public function getSourcefilePrefix(): ?string
    {
        return preg_replace('/\.tex$/', '', $this->sourcefile);
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCommentStatus(): ?string
    {
        return $this->comment_status;
    }

    public function setCommentStatus(?string $comment_status): void
    {
        $this->comment_status = $comment_status;
    }

    public function getCommentDate()
    {
        return $this->comment_date;
    }

    public function setCommentDate($comment_date)
    {
        $this->comment_date = $comment_date;
        return $this;
    }

    /**
     * Saves entry to DB.
     */
    public function save(): bool
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
                errmsg          = :i_errmsg,
                project_id      = :i_project_id,
                project_src     = :i_project_src,
                comment         = :i_comment,
                comment_status  = :i_comment_status,
                comment_date    = :i_comment_date
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
                errmsg          = :u_errmsg,
                project_id      = :u_project_id,
                project_src     = :u_project_src,
                comment         = :u_comment,
                comment_status  = :u_comment_status,
                comment_date    = :u_comment_date
            ';
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
        $stmt->bindValue(':i_project_id', $this->project_id);
        $stmt->bindValue(':u_project_id', $this->project_id);
        $stmt->bindValue(':i_project_src', $this->project_src);
        $stmt->bindValue(':u_project_src', $this->project_src);
        $stmt->bindValue(':i_comment', $this->comment);
        $stmt->bindValue(':u_comment', $this->comment);
        $stmt->bindValue(':i_comment_status', $this->comment_status);
        $stmt->bindValue(':u_comment_status', $this->comment_status);
        $stmt->bindValue(':i_comment_date', $this->comment_date);
        $stmt->bindValue(':u_comment_date', $this->comment_date);

        $success = $stmt->execute();

        if ($this->id == 0) {
            $this->id = $dao->lastInsertId();
        }
        return $success;
    }

    /*
     * Database row to StatEntry.
     */
    public static function fillEntry(array $row): self
    {
        $se = new self();
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
        if (isset($row['project_id'])) {
            $se->setProjectId($row['project_id']);
        }
        if (isset($row['project_src'])) {
            $se->setProjectSrc($row['project_src']);
        }
        if (isset($row['comment'])) {
            $se->setComment($row['comment']);
        }
        if (isset($row['comment_status'])) {
            $se->setCommentStatus($row['comment_status']);
        }
        if (isset($row['comment_date'])) {
            $se->setCommentDate($row['comment_date']);
        }

        return $se;
    }

    /**
     * Determine if entry exists by filename.
     */
    public static function exists(string $filename): bool
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

    public static function existsById(int $id): bool
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
     * We only want one file for given subdirectory of depth $mindepth.
     */
    public static function pathMatches(string $filename, int $minDepth): bool
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

    public static function alreadyDone(string $action, int $id): bool
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
        }

        return true;
    }

    public static function getRetval(string $filename, string $stage, string $action): ?bool
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
        }

        return null;
    }

    public static function getFilenamesByRestriction(string $action, array $restrict): array
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

        $filenames = [];
        while ($row = $stmt->fetch()) {
            $filenames[$row['id']] = $row['filename'];
        }

        return $filenames;
    }

    public static function getFilenamesByRestrictionXml(array $restrict): array
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

        $filenames = [];
        while ($row = $stmt->fetch()) {
            $filenames[$row['id']] = $row['filename'];
        }

        return $filenames;
    }

    /**
     * Delete by given directory.
     */
    public static function deleteByDirectory(string $directory): bool
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
     * Delete by given id.
     */
    public static function deleteById(int $id): bool
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

    public static function markRerun(
        string $filename,
        string $stage,
        string $action): void
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

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
    public static function wqGetNextEntries(
        string $hostGroupName = '',
        int $limit = 10,
        bool $toStdout = true)
    {
        // Here we might get "server has gone away message".
        // Therefore explicitly check, whether connection is still there.
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
                wq.date_modified,
                s.filename
            LIMIT :limit";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':limit', $limit);
        if ($hostGroupName !== '') {
            $stmt->bindValue(':hostGroupName', $hostGroupName);
        }

        $stmt->execute();

        $dbEntries = [];
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
     * Get next entries
     * @return ?StatEntry[]
     */
    public static function wqGetEntries(
        string $hostGroupName = '',
        int $limit = 10)
    {
        // Here we might get "server has gone away message".
        // Therefore explicitly check, whether connection is still there.
        $dao = Dao::checkAndGetInstance();

        $where = "
            wq.priority > 0
            OR (wq.priority = 0 AND wq.action != 'none')
        ";

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
                wq.priority,
                wq.date_modified
            LIMIT :limit";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':limit', $limit);
        if ($hostGroupName !== '') {
            $stmt->bindValue(':hostGroupName', $hostGroupName);
        }

        $stmt->execute();

        $dbEntries = [];
        while ($row = $stmt->fetch()) {
            $dbEntries[$row['wq_id']] = self::fillEntry($row);
        }

        return $dbEntries;
    }

    /**
     * @return bool|void
     */
    public static function addToWorkqueue(
        string $directory,
        string $hostGroupName,
        string $stage,
        string $action,
        int $priority
    ) {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        $dao = Dao::getInstance();

        $entry = StatEntry::getByDir($directory);
        if ($entry) {
            if ($action == StatEntry::WQ_ACTION_FORCE) {
                UtilFile::cleanupDir($directory, $action);
            }
        } else {
            $entry = new StatEntry;
            $entry->filename = $directory;
            $success = $entry->save();
        }

        $wqe = new WorkqueueEntry();
        $wqe->setStatisticId($entry->getId());
        $wqe->setStage($stage);
        $wqe->setDateModified($cfg->now->datestamp);
        $wqe->setPriority($priority);
        $wqe->setAction($action);
        $wqe->setStage($stage);
        $wqe->setHostGroup($hostGroupName);

        $success = $wqe->save();
        return $success;

    }

    public static function addToWorkqueueById(
        int $id,
        string $hostGroupName,
        string $stage,
        string $action,
        int $priority): bool
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

            return $wqe->save();
        } else {
            return false;
        }
    }

    /**
     * Adds new entry.
     */
    public static function addNew(
        string $directory,
        string $sourcefile,
        int $minDepth,
        string $hostGroupName = '',
        string $stage = '',
        string $action = 'none',
        ?string $projectId = null,
        ?string $projectSrc = null): bool
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        // We want only one file for given subdirectory of $minDepth.
        // Therefore check for matching subpath, not the exact file.
        if (!StatEntry::pathMatches($directory, $minDepth)) {
            $entry = new StatEntry;
            $entry->setFilename($directory);
            $entry->setSourcefile($sourcefile);
            $entry->setProjectId($projectId);
            $entry->setProjectSrc($projectSrc);
            $entry->save();

            $wqe = new WorkqueueEntry();
            $wqe->setStatisticId($entry->getId()); // Id has just been created.
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

    public static function getByDir(string $directory): ?StatEntry
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
     * @return StatEntry[]
     */
    public static function getBySet(string $set): ?array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                statistic
            WHERE
                `set` = :set
            ORDER BY
                filename";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':set', $set);

        $stmt->execute();

        $objs = [];
        while ($row = $stmt->fetch()) {
            $objs[] = self::fillEntry($row);
        }
        return $objs;
    }

    public static function getByIds(array $ids): array
    {
        if (!count($ids)) {
            return [];
        }

        $dao = Dao::getInstance();

        $idsStr = implode(',', $ids);
        $query = "
            SELECT
                *
            FROM
                statistic
            WHERE
                id in (" . $idsStr .")";

        $stmt = $dao->prepare($query);
        $stmt->execute();

        $objs = [];
        while ($row = $stmt->fetch()) {
            $objs[] = self::fillEntry($row);
        }
        return $objs;
    }

    public static function getById(int $id): ?StatEntry
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
     * @return mixed
     */
    public static function getIdByDir(string $directory)
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

    public static function getIdsBySet(string $set): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                id
            FROM
                statistic
            WHERE
                `set` = :set
            ORDER BY
                filename";

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


    public static function getCountByDirPrefix(string $dirPrefix): int
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
     * Get the statistic for the given result table.
     */
    public static function getStats(string $joinTable, string $set = ''): array
    {
        $dao = Dao::getInstance();

        if ($set != '') {
            $where = '
                WHERE
                    `set` = :set ';
        } else {
            $where = '';
        }

        // Do a LEFT JOIN, so we also find all files that have not
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
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $rerun = [];

        $stat = [];
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

        return [$stat, $rerun];
    }

    public static function getFileNamesByRetval(
        string $retval,
        string $joinTable = '',
        string $set = ''): array
    {
        $dao = Dao::getInstance();

        if (empty($joinTable)) {
            $joinTable = 'retval_xml';
        }

        if ($set != '') {
            $where = '
                WHERE
                    s.`set` = :set ';
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
            $stmt->bindValue(':set', $set);
        }
        $stmt->bindValue(':retval', $retval);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getCountLastStat(
        string $stage,
        string $joinTable): int
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

    public static function getLastStat(
        string $orderBy,
        string $sortBy,
        int $min,
        int $max_pp): array
    {
        // Due to long running sse script
        // here we might get "server has gone away message"
        // therefore explicitly check, whether connection is still there.
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
