<?php
/**
 * MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 * A class that handles entries in the workqueue table.
 *
 */
namespace Dmake;

use \PDO;

class WorkqueueEntry
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

    protected $id = 0;
    protected $statisticId = 0;
    protected $dateCreated = null;
    protected $dateModified = null;
    protected $priority = self::WQ_ENTRY_DISABLED; // if > 0 entry is part of workqueue
    protected $prevAction = self::WQ_ENTRY_DISABLED;
    protected $action = self::WQ_ACTION_NONE;
    protected $stage = '';
    protected $hostGroup = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getStatisticId(): int
    {
        return $this->statisticId;
    }

    /**
     * @param int $statisticId
     */
    public function setStatisticId(int $statisticId): void
    {
        $this->statisticId = $statisticId;
    }

    /**
     * @return null
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param null $dateCreated
     */
    public function setDateCreated($dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return null
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @param null $dateModified
     */
    public function setDateModified($dateModified): void
    {
        $this->dateModified = $dateModified;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getPrevAction(): int
    {
        return $this->prevAction;
    }

    /**
     * @param int $prevAction
     */
    public function setPrevAction(int $prevAction): void
    {
        $this->prevAction = $prevAction;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return $this->stage;
    }

    /**
     * @param string $stage
     */
    public function setStage(string $stage): void
    {
        $this->stage = $stage;
    }

    /**
     * @return string
     */
    public function getHostGroup(): string
    {
        return $this->hostGroup;
    }

    /**
     * @param string $hostGroup
     */
    public function setHostGroup(string $hostGroup): void
    {
        $this->hostGroup = $hostGroup;
    }

    /**
     */
    public function save(): bool
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date('Y-m-d H:i:s');

        $dao = Dao::getInstance();

        $query = '
            INSERT INTO
                workqueue
            SET
                id = 0,
                statistic_id = :i_statistic_id,
                date_created = :date_created,
                date_modified = :i_date_modified,
                priority = :i_priority,
                prev_action = NULL,
                action = :i_action,
                stage = :i_stage,
                hostgroup = :i_hostgroup
            ON DUPLICATE KEY UPDATE
                statistic_id = :u_statistic_id,
                date_modified = :u_date_modified,
                priority = :u_priority,
                prev_action = action,
                action = :u_action,
                stage = :u_stage,
                hostgroup = :u_hostgroup
            ';

        $stmt = $dao->prepare($query);

        $stmt->bindValue(':i_statistic_id', $this->statisticId);
        $stmt->bindValue(':u_statistic_id', $this->statisticId);
        $stmt->bindValue(':date_created', $cfg->now->datestamp);
        $stmt->bindValue(':i_date_modified', $cfg->now->datestamp);
        $stmt->bindValue(':u_date_modified', $cfg->now->datestamp);
        $stmt->bindValue(':i_priority', $this->priority);
        $stmt->bindValue(':u_priority', $this->priority);
        $stmt->bindValue(':i_action', $this->action);
        $stmt->bindValue(':u_action', $this->action);
        $stmt->bindValue(':i_stage', $this->stage);
        $stmt->bindValue(':u_stage', $this->stage);
        $stmt->bindValue(':i_hostgroup', $this->hostGroup);
        $stmt->bindValue(':u_hostgroup', $this->hostGroup);

        $result = $stmt->execute();

        if ($this->id == 0) {
            $this->id = $dao->lastInsertId();
        }
        return $result;
    }

    /**
     * @param $row
     * @return StatEntry
     */
    public static function fillEntry($row): ?self
    {
        $we = new static();
        $we->setId($row['id'] ?? 0);
        $we->setStatisticId($row['statistic_id'] ?? 0);
        $we->setDateCreated($row['date_created'] ?? null);
        $we->setDateModified($row['date_modified'] ?? null);
        $we->setPriority($row['priority'] ?? 0);
        $we->setPrevAction($row['prev_action'] ?? '') ;
        $we->setAction($row['action'] ?? '');
        $we->setStage($row['stage'] ?? '');
        $we->setHostGroup($row['hostgroup'] ?? '');

        return $we;
    }

    public function update(): bool
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s");

        $this->setDateModified($cfg->now->datestamp);
        $dao = Dao::getInstance();

        $query = '
            UPDATE
                workqueue
            SET
                priority = :priority,
                prev_action = action,
                action = :action,
                hostgroup = :hostgroup,
                date_modified = :date_modified
            WHERE
                statistic_id = :statistic_id
                AND stage = :stage
            ';

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':priority', $this->getPriority());
        $stmt->bindValue(':action', $this->getAction());
        $stmt->bindValue(':hostgroup', $this->getHostGroup());
        $stmt->bindValue(':date_modified', $this->getDateModified());
        $stmt->bindValue(':statistic_id', $this->getStatisticId());
        $stmt->bindValue(':stage', $this->getStage());

        $result = $stmt->execute();
        return $result;
    }

    public function updateButHostgroup(): bool
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s");

        $this->setDateModified($cfg->now->datestamp);
        $dao = Dao::getInstance();

        $query = '
            UPDATE
                workqueue
            SET
                priority = :priority,
                prev_action = action,
                action = :action,
                date_modified = :date_modified
            WHERE
                statistic_id = :statistic_id
                AND stage = :stage
            ';

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':priority', $this->getPriority());
        $stmt->bindValue(':action', $this->getAction());
        $stmt->bindValue(':date_modified', $this->getDateModified());
        $stmt->bindValue(':statistic_id', $this->getStatisticId());
        $stmt->bindValue(':stage', $this->getStage());

        $result = $stmt->execute();
        return $result;
    }

    public function updateAndStat()
    {
        $cfg = Config::getConfig();

        $this->update();

        $dao = Dao::getInstance();

        $query = '
            UPDATE
                statistic
            SET
                date_modified = :date_modified
            WHERE
                id = :id';

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':date_modified', $this->getDateModified());
        $stmt->bindValue(':id', $this->getStatisticId());

        $stmt->execute();
    }

    /**
     * @param $id
     */
    public static function disableEntry($statisticId, $stage)
    {
        $dao = Dao::getInstance();

        $query = "
            UPDATE
                workqueue
            SET
                priority = " . StatEntry::WQ_ENTRY_DISABLED . "
            WHERE
                statistic_id = :statistic_id
                AND stage = :stage
        ";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':statistic_id', $statisticId);
        $stmt->bindValue(':stage', $stage);

        $stmt->execute();
    }

    /**
     * @param $id
     * @return bool
     */
    public static function deleteById($id): bool
    {
        $dao = Dao::getInstance();

        $query = "
            DELETE FROM
                workqueue
            WHERE
                id = :id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        $result = $stmt->execute();
        return $result;
    }

    /**
     * @param $statisticId
     * @return bool
     */
    public static function deleteByStatisticId($statisticId): bool
    {
        $dao = Dao::getInstance();

        $query = "
            DELETE FROM
                workqueue
            WHERE
                statistic_id = :statistic_id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':statistic_id', $statisticId);

        $result = $stmt->execute();
        return $result;
    }

    /**
     * @param string $id
     * @return StatEntry
     */
    public static function getById($id): ?self
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                workqueue
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
    public static function getQueuedEntries()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(*) as num
            FROM
                workqueue
            WHERE
                priority > 0
        ";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['num'];
    }

}
