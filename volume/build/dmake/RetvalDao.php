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
    public static function deleteById($joinTable, $id)
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
     * @param $joinTable
     * @param $set
     * @return int
     */
    public static function getCount($joinTable, $set)
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
     * @param $table
     * @param $errMsg
     * @return int
     */
    public static function getCountByErrMsg($table, $errMsg)
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
     * @param $joinTable
     * @param $errMsg
     * @param int $min
     * @param int $max
     * @return array
     */
    public static function getByErrMsg($joinTable, $errMsg, $min = 0, $max = 100)
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
     * @param $joinTable
     * @param $set
     * @param $orderBy
     * @param $sortBy
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getEntries($joinTable, $set, $orderBy, $sortBy, $min, $max_pp)
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
                s.wq_priority,
                s.wq_action
           FROM
                statistic as s
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

        if ($set != '') {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param string|array $ids
     * @param $stage
     * @param $orderBy
     * @param $sortBy
     * @param $min
     * @param $max
     * @return array
     */
    public function getByIds($ids, $stage, $orderBy, $sortBy, $min, $max)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        $joinTable = 'retval_' . str_replace('clean', '', $stage);

        $dao = Dao::getInstance();

        $query = "
            SELECT
                '". $stage . "' as stage,  
                j.retval,
                j.prev_retval,
                j.date_modified,
                s.date_modified as s_date_modified,
                unix_timestamp(s.date_modified) as tstamp,
                wq_prev_action as type,
                s.id,
                s.sourcefile,
                s.filename,
                s.wq_priority,   
                s.wq_action
           FROM
                statistic as s
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
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param $macro
     * @param $joinTable
     * @return int
     */
    public function getCountByMacro($macro, $joinTable)
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
     * @param $macro
     * @param $joinTable
     * @param $set
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getByMacro($macro, $joinTable, $set, $min, $max_pp)
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
     * @param $joinTable
     * @param string $set
     * @return array
     */
    public static function getMissingMacros($joinTable, $set = '')
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
     * @param $retval
     * @param $joinTable
     * @param $set
     * @param $detail
     * @return int
     */
    public static function getCountByRetval($retval, $joinTable, $set, $detail)
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

        $query = "
            SELECT
                count(*) as numrows
            FROM
                statistic as s
            $join
            WHERE
                s.wq_priority = 0
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
     * @param $retval
     * @param $joinTable
     * @param $set
     * @param $columns
     * @param $orderBy
     * @param $sortBy
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getDetailsByRetval($retval, $joinTable, $set, $columns, $orderBy, $sortBy, $min, $max_pp)
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
                s.wq_priority,   
                s.wq_action
            FROM
                statistic as s
            $join
            WHERE
                1 " .
                $joinWhere .
                $ext_query . "
            ORDER BY
                $orderBy $sortBy
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
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
     * @param array $ids
     * @param $retval
     * @param $joinTable
     * @param $columns
     * @return array
     */
    public static function getDetailsByIdsAndRetval($ids, $retval, $joinTable, $columns)
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

        $ext_query = ' AND s.id in (' . implode(',', $ids) . ') ';

        $query = "
            SELECT
                $sqlstr
                j.date_modified,
                s.id,
                s.sourcefile,
                s.filename,
                s.wq_priority,   
                s.wq_action
            FROM
                statistic as s
            $join
            WHERE
                1 " .
            $joinWhere .
            $ext_query;

        $stmt = $dao->prepare($query);
        if ($retval != 'unknown') {
            $stmt->bindValue(':retval', $retval);
        }

        $stmt->execute();

        $rows = [];
        while ($row = $stmt->fetch()) {
            $rows[$row['id']] = $row;
        }

        return $rows;
    }

    /**
     * @param $retval
     * @param $joinTable
     * @param string $set
     * @return int
     */
    public static function getCountErrMsgByRetval($retval, $joinTable, $set = '')
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
     * @param $retval
     * @param $joinTable
     * @param string $set
     * @return array
     */
    public static function getErrMsgByRetval($retval, $joinTable, $set = '')
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