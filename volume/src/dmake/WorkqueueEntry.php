<?php
/**
 * MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 * A class that handles entries in the workqueue table.
 *
 */
namespace Dmake;

class WorkqueueEntry
{
    public const WQ_ACTION_NONE = 'none';
    public const WQ_ACTION_DEFAULT = 'default';
    public const WQ_ACTION_FORCE = 'force';
    public const WQ_ENTRY_DISABLED = 0;

    protected ?int $id = 0;
    protected ?int $statisticId = 0;
    protected ?int $pid = null;
    protected ?string $dateCreated = null;
    protected ?string $dateModified = null;
    protected ?int $priority = self::WQ_ENTRY_DISABLED; // if > 0 entry is part of workqueue
    protected ?string $prevAction = self::WQ_ACTION_NONE;
    protected ?string $action = self::WQ_ACTION_NONE;
    protected ?string $stage = '';
    protected ?string $hostGroup = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getStatisticId(): ?int
    {
        return $this->statisticId;
    }

    public function setStatisticId(?int $statisticId): void
    {
        $this->statisticId = $statisticId;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(?int $pid): void
    {
        $this->pid = $pid;
    }

    public function getDateCreated(): ?string
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?string $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDateModified(): ?string
    {
        return $this->dateModified;
    }

    public function setDateModified(?string $dateModified): void
    {
        $this->dateModified = $dateModified;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPrevAction(): ?string
    {
        return $this->prevAction;
    }

    public function setPrevAction(?string $prevAction): void
    {
        $this->prevAction = $prevAction;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function setStage(?string $stage): void
    {
        $this->stage = $stage;
    }

    public function getHostGroup(): ?string
    {
        return $this->hostGroup;
    }

    public function setHostGroup(?string $hostGroup): void
    {
        $this->hostGroup = $hostGroup;
    }

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
                pid = :i_pid,
                date_created = :date_created,
                date_modified = :i_date_modified,
                priority = :i_priority,
                prev_action = NULL,
                action = :i_action,
                stage = :i_stage,
                hostgroup = :i_hostgroup
            ON DUPLICATE KEY UPDATE
                statistic_id = :u_statistic_id,
                pid = :u_pid,                    
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
        $stmt->bindValue(':i_pid', $this->pid);
        $stmt->bindValue(':u_pid', $this->pid);
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

        $success = $stmt->execute();

        if ($this->id == 0) {
            $this->id = $dao->lastInsertId();
        }
        return $success;
    }

    public static function fillEntry(array $row): self
    {
        $we = new static();
        $we->setId($row['id'] ?? 0);
        $we->setStatisticId($row['statistic_id'] ?? 0);
        $we->setPid($row['pid'] ?? null);
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
                pid = :pid,
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
        $stmt->bindValue(':pid', $this->getPid());
        $stmt->bindValue(':priority', $this->getPriority());
        $stmt->bindValue(':action', $this->getAction());
        $stmt->bindValue(':hostgroup', $this->getHostGroup());
        $stmt->bindValue(':date_modified', $this->getDateModified());
        $stmt->bindValue(':statistic_id', $this->getStatisticId());
        $stmt->bindValue(':stage', $this->getStage());

        return  $stmt->execute();
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
                pid = :pid,
                priority = :priority,
                prev_action = action,
                action = :action,
                date_modified = :date_modified
            WHERE
                statistic_id = :statistic_id
                AND stage = :stage
            ';

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':pid', $this->getPid());
        $stmt->bindValue(':priority', $this->getPriority());
        $stmt->bindValue(':action', $this->getAction());
        $stmt->bindValue(':date_modified', $this->getDateModified());
        $stmt->bindValue(':statistic_id', $this->getStatisticId());
        $stmt->bindValue(':stage', $this->getStage());

        return $stmt->execute();
    }

    public function updateAndStat(): bool
    {
        $cfg = Config::getConfig();

        $this->dateModified = $cfg->now->datestamp;
        if ($this->getId() == 0) {
            $this->save();
        } else {
            $this->update();
        }

        $dao = Dao::getInstance();

        $query = '
            UPDATE
                statistic
            SET
                date_modified = :date_modified
            WHERE
                id = :id';

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':date_modified', $this->dateModified);
        $stmt->bindValue(':id', $this->getStatisticId());

        return $stmt->execute();
    }

    public static function resetPriority(int $statisticId, string $stage): bool
    {
        $dao = Dao::getInstance();

        $query = "
            UPDATE
                workqueue
            SET
                priority = :priority
            WHERE
                statistic_id = :statistic_id
                AND stage = :stage
        ";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':priority', StatEntry::WQ_ENTRY_DISABLED);
        $stmt->bindValue(':statistic_id', $statisticId);
        $stmt->bindValue(':stage', $stage);

        return $stmt->execute();
    }

    public static function disableEntry(int $statisticId, string $stage): bool
    {
        $dao = Dao::getInstance();

        $query = "
            UPDATE
                workqueue
            SET
                pid = null, 
                priority = :priority,
                action = :action
            WHERE
                statistic_id = :statistic_id
                AND stage = :stage
        ";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':priority', StatEntry::WQ_ENTRY_DISABLED);
        $stmt->bindValue(':action', StatEntry::WQ_ACTION_NONE);
        $stmt->bindValue(':statistic_id', $statisticId);
        $stmt->bindValue(':stage', $stage);

        return $stmt->execute();
    }

    public static function deleteById(int $id): bool
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

    public static function deleteByStatisticId(int $statisticId): bool
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

    public static function getById(int $id): ?self
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

        if ($row = $stmt->fetch()) {
            return self::fillEntry($row);
        }
        return null;
    }

    public static function getByPid(int $pid): ?self
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                workqueue
            WHERE
                pid = :pid";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':pid', $pid);

        $stmt->execute();

        if ($row = $stmt->fetch()) {
            return self::fillEntry($row);
        }
        return null;
    }

    /**
     * @param int $statisticId
     * @return WorkqueueEntry[]
     */
    public static function getByStatisticId(int $statisticId): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                workqueue
            WHERE
                statistic_id = :statistic_id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':statistic_id', $statisticId);

        $stmt->execute();

        $rows = [];
        while ($row = $stmt->fetch()) {
            $rows[] = self::fillEntry($row);
        }
        return $rows;
    }

    /**
     */
    public static function getByStatisticIdAndStage(int $statisticId, string $stage): ?self
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                workqueue
            WHERE
                statistic_id = :statistic_id
                AND stage = :stage";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':statistic_id', $statisticId);
        $stmt->bindValue(':stage', $stage);

        $stmt->execute();

        if ($row = $stmt->fetch()) {
            return self::fillEntry($row);
        }
        return null;
    }

    public static function getNumQueuedEntries($includeCurrentEntries = false): int
    {
        $dao = Dao::getInstance();

        if ($includeCurrentEntries) {
            $where = "
                priority > 0
                OR (priority = 0 AND action != 'none')";
        } else {
            $where = "priority > 0";
        }

        $query = "
            SELECT
                count(*) as num
            FROM
                workqueue
            WHERE
                $where
        ";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['num'];
    }

    /**
     * On startup, there may be left over entries, which have not completely finished.
     * Just requeue these entries.
     * @return int
     */
    public static function requeueLeftoverRunningEntries(): int
    {
        $dao = Dao::getInstance();

        $query = "
            UPDATE
                workqueue
            SET 
                priority = 1 
            WHERE
                priority = 0 AND action != 'none';
        ";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        return $stmt->rowCount();
    }


}
