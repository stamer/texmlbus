<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle queries to RetvalTables.
 *
 */

namespace Dmake;

class RetvalDao
{
    /**
     * Delete entry from jointable by id.
     */
    public static function deleteById(string $joinTable, int $id): int
    {
        $dao = Dao::getInstance();

        $query = "
            DELETE
            FROM
                $joinTable
            WHERE
                id = :id";


        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Get number of entries from joinTable by set.
     */
    public static function getCount(string $joinTable, string $set): int
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(*) as numrows
            FROM
                statistic as s
            LEFT JOIN
                $joinTable as j
            ON
                s.id = j.id
            WHERE
                1";

        $ext_query = '';
        if ($set != '') {
            $ext_query = '
                AND s.`set` = :set';
        }
        $query .= $ext_query;

        $stmt = $dao->prepare($query);
        if ($set != '') {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();
        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * Get number of entries from table by matching errMsg.
     */
    public static function getCountByErrMsg(string $table, string $errMsg)
    {
        $dao = Dao::getInstance();

        $query = "
	        SELECT
		        count(*) as numrows
	        FROM
		        " . $table . "
	        WHERE
		        errmsg LIKE :errmsg";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errmsg', '%' . $errMsg . '%');

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * Get entries from joinTable by ErrMsg
     */
    public static function getByErrMsg(
        string $joinTable,
        string $errMsg,
        int $min = 0,
        int $max = 100)
    {
        $dao = Dao::getInstance();

        $query = "
	        SELECT
		        s.filename,
		        s.date_created,
		        j.errmsg
	        FROM
                statistic as s
            JOIN
		        " . $joinTable . " as j
            ON
                s.id = j.id
	        WHERE
		        j.errmsg LIKE :errmsg
	        ORDER BY
		        s.date_created DESC
	        LIMIT
		        $min, $max";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errmsg', '%' . $errMsg . '%');

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get entries by stage, joinTable and set.
     */
    public static function getEntries(
        string $stage,
        string $joinTable,
        string $set,
        string $orderBy,
        string $sortBy,
        int $min,
        int $max_pp): array
    {
        $dao = Dao::getInstance();

        $ext_query = '';
        if ($set != '') {
            $ext_query = '
                AND s.`set` = :set';
        }

        $query = "
            SELECT
                j.retval,
                j.prev_retval,
                j.date_modified,
                s.date_modified as s_date_modified,
                s.id,
                s.sourcefile,
                s.filename,
                wq.priority as wq_priority,
                wq.action as wq_action
            FROM
                statistic as s
            LEFT JOIN
                workqueue as wq
            ON s.id = wq.statistic_id   
               AND wq.stage = :stage
            LEFT JOIN
                $joinTable as j
            ON
                s.id = j.id
            WHERE
                1
                $ext_query
            ORDER BY
                $orderBy $sortBy
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':stage', $stage);

        if ($set != '') {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get entries by given ids and stage.
     */
    public static function getByIds(
        array $ids,
        string $stage,
        string $orderBy,
        string $sortBy,
        int $min,
        int $max): array
    {
        $dao = Dao::getInstance();

        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        $joinTable = 'retval_' . str_replace('clean', '', $stage);

        $query = "
            SELECT
                '". $stage . "' as stage,  
                j.retval,
                j.prev_retval,
                j.date_modified,
                s.date_modified as s_date_modified,
                unix_timestamp(s.date_modified) as tstamp,
                s.id,
                s.sourcefile,
                s.filename,
                wq.priority as wq_priority,
                wq.prev_action as wq_prev_action,
                wq.action as wq_action
           FROM
                statistic as s
           LEFT JOIN
                workqueue as wq
           ON
                s.id = wq.statistic_id
                AND wq.stage = :stage     
           LEFT JOIN
                $joinTable as j
           ON
                s.id = j.id
           WHERE
                j.id in (" . $ids . ")
           ORDER BY
                $orderBy $sortBy
           LIMIT
                $min, $max";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':stage', $stage);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get count by given macro.
     */
    public function getCountByMacro(string $macro, string $joinTable): int
    {
        $dao = Dao::getInstance();

        $query = "
        	SELECT
                count(*) as numrows
            FROM
                " . $joinTable . "
            WHERE
                missing_macros LIKE :macro";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':macro', '%' . $macro . '%');

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * Get entries by given macro.
     */
    public static function getByMacro(
        string $macro,
        string $joinTable,
        string $set,
        int $min,
        int $max_pp): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                s.filename,
                j.date_created,
                j.missing_macros
            FROM
                statistic as s
            JOIN
                " . $joinTable . " as j
            ON
                s.id = j.id
            WHERE
                j.missing_macros LIKE :macro
                AND s.set LIKE :set
            ORDER BY
                j.date_created DESC
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':macro', '%' . $macro . '%');
        $stmt->bindValue(':set', '%' . $set . '%');

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get missing macros.
     */
    public static function getMissingMacros(string $joinTable, string $set = ''): array
    {
        $dao = Dao::getInstance();

        if (empty($set)) {
            $query = "
                SELECT
                    j.missing_macros
                FROM
                    " . $joinTable . " as j
                WHERE
                    j.missing_macros != ''
                ORDER BY
                    j.date_modified";

            $stmt = $dao->prepare($query);
        } else {
            $query = "
                SELECT
                    j.missing_macros
                FROM
                    " . $joinTable . " as j
                JOIN
                    statistic as s
                ON
                    j.id = s.id
                WHERE
                    j.missing_macros != ''
                    AND s.`set` LIKE :set
                ORDER BY
                    j.date_modified";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Gets the count by given retval and stage.
     */
    public static function getCountByRetval(
        string $retval,
        string $stage,
        string $joinTable,
        string $set,
        string $detail): int
    {
        $dao = Dao::getInstance();

        if ($retval != 'unknown') {
            $join = "
            JOIN
                $joinTable as j
            ON
                s.id = j.id
            ";
            $joinWhere = '
                j.retval = :retval
            ';
        } else {
            $join = "
            LEFT JOIN
                $joinTable as j
            ON
                s.id = j.id
            ";
            $joinWhere = '
                j.id is NULL
            ';
        }

        $query = "
            SELECT
                count(*) as numrows
            FROM
                statistic as s
            LEFT JOIN
                workqueue as wq
            ON s.id = wq.statistic_id
               AND wq.stage = :stage
               AND wq.priority = 0    
            $join
            WHERE
                $joinWhere";

        $ext_query = '';
        if ($set != '') {
            $ext_query = '
                AND s.`set` = :set';
        }

        if ($detail != '') {
            switch ((string)$detail) {
                case 'num_complete':
                    $ext_query = '
                            AND j.num_error = 0
                            AND j.num_warning = 0';
                    break;
                case 'num_warning':
                    $ext_query = '
                            AND j.num_error = 0
                            AND j.num_warning > 0';
                    break;

                case 'num_error':
                    $ext_query = '
                            AND j.num_error > 0
                            AND j.num_warning = 0';
                    break;
                default:
                    echo "Unknown detail parameter value!";
                    exit;
            }
        }

        $query .= $ext_query;

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':stage', $stage);
        if ($retval != 'unknown') {
            $stmt->bindValue(':retval', $retval);
        }
        if ($set != '') {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * Get details by given retval and stage.
     */
    public static function getDetailsByRetval(
        string $retval,
        string $stage,
        string $joinTable,
        string $set,
        array $columns,
        string $orderBy,
        string $sortBy,
        int $min,
        int $max_pp): array
    {
        $dao = Dao::getInstance();

        $sqlstr = '';
        foreach ($columns as $field) {
            if (is_array($field['sql'])) {
                foreach ($field['sql'] as $fieldname) {
                    $sqlstr .= 'j.' . $fieldname . ",\n";
                }
            } else {
                $sqlstr .= 'j.' . $field['sql'] . ",\n";
            }
        }

        if ($retval != 'unknown') {
            $join = "
            JOIN
                $joinTable as j
            ON
                s.id = j.id
            ";
            $joinWhere = '
                AND j.retval = :retval
            ';
        } else {
            $join = "
            LEFT JOIN
                $joinTable as j
            ON
                s.id = j.id
            ";
            $joinWhere = '
                AND j.id is NULL
            ';
        }

        $ext_query = '';
        if ($set != '') {
            $ext_query = '
                AND s.`set` = :set';
        }

        $query = "
            SELECT
                $sqlstr
                j.date_modified,
                s.id,
                s.sourcefile,
                s.filename,
                wq.action as wq_action,
                wq.priority as wq_priority
            FROM
                statistic as s
            $join
            LEFT JOIN
                workqueue as wq
            ON s.id = wq.statistic_id
               AND wq.stage = :stage
            WHERE
                1 " .
                $joinWhere .
                $ext_query . "
            ORDER BY
                $orderBy $sortBy
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':stage', $stage);

        if ($retval != 'unknown') {
            $stmt->bindValue(':retval', $retval);
        }
        if ($set != '') {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $rows = [];
        while ($row = $stmt->fetch()) {
            $rows[$row['id']] = $row;
        }

        return $rows;
    }

    /**
     * Get details by id.
     */
    public static function getDetailsByIds(
        array $ids,
        string $stage,
        string $joinTable,
        array $columns
    ): array
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $dao = Dao::getInstance();

        $sqlstr = '';
        foreach ($columns as $field) {
            if (is_array($field['sql'])) {
                foreach ($field['sql'] as $fieldname) {
                    $sqlstr .= 'j.' . $fieldname . ",\n";
                }
            } else {
                $sqlstr .= 'j.' . $field['sql'] . ",\n";
            }
        }

        // Do not set any condition on retval, retval might change
        // and then the entry should be removed from stage.
        $join = "
        LEFT JOIN
            $joinTable as j
        ON
            s.id = j.id
        ";
        $joinWhere = '';

        $ext_query = ' AND s.id in (' . implode(',', $ids) . ') ';

        $query = "
            SELECT
                $sqlstr
                j.date_modified,
                j.retval,
                s.id,
                s.sourcefile,
                s.filename,
                wq.priority as wq_priority,   
                wq.action as wq_action
            FROM
                statistic as s
            LEFT JOIN
                workqueue as wq
            ON s.id = wq.statistic_id
               AND wq.stage = :stage
            $join
            WHERE
                1 " .
            $joinWhere .
            $ext_query;

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':stage', $stage);

        $stmt->execute();

        $rows = [];
        while ($row = $stmt->fetch()) {
            $rows[$row['id']] = $row;
        }

        return $rows;
    }

    /**
     * Get count by given retval.
     */
    public static function getCountErrMsgByRetval(
        string $retval,
        string $joinTable,
        string $set = ''): int
    {
        $dao = Dao::getInstance();

        if (empty($set)) {
            $query = "
                SELECT
                    count(j.errmsg) as numrows
                FROM
                    ".$joinTable." as j
                WHERE
                    j.retval = :retval";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':retval', $retval);
        } else {
            $query = "
                SELECT
                    count(j.errmsg) as numrows
                FROM
                    ".$joinTable." as j
                JOIN
                    statistic as s
                ON
                    j.id = s.id
                WHERE
                    j.retval = :retval
                    AND s.`set` like :set";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':retval', $retval);
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * Ger error messages by retval.
     */
    public static function getErrMsgByRetval(
        string $retval,
        string $joinTable,
        string $set = ''): array
    {
        $dao = Dao::getInstance();

        if (empty($set)) {
            $query = "
                SELECT
                    j.errmsg
                FROM
                    ".$joinTable." as j
                WHERE
                    j.retval = :retval";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':retval', $retval);
        } else {
            $query = "
                SELECT
                    j.errmsg
                FROM
                    ".$joinTable." as j
                JOIN
                    statistic as s
                ON
                    j.id = s.id
                WHERE
                    j.retval = :retval
                    AND s.`set` like :set";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':retval', $retval);
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }
}