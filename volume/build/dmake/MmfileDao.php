<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle queries to RetvalTables.
 *
 */

namespace Dmake;

class MmfileDao
{
    /**
     * Gets the number of entries for given parameters.
     * @param $set
     * @param $macro
     * @param $styfilename
     * @return int
     */
    public function getCount($set, $macro, $styfilename)
    {
        $dao = Dao::getInstance();

        $query = "
	        SELECT
		        count(distinct filename) as numrows
	        FROM
		        mmfile
	    WHERE
            1 ";

        if (!empty($macro)) {
            $query .= "
		        AND macro       = :macro";
        }

        if (!empty($styfilename)) {
            $query .= "
 		        AND styfilename = :styfilename";
        }

        if (!empty($set)) {
            $query .= "
 		        AND `set` = :set";
        }

        $ext_query = '';

        $query .= $ext_query;

        $stmt = $dao->prepare($query);
        if (!empty($macro)) {
            $stmt->bindValue(':macro', $macro);
        }
        if (!empty($sty)) {
            $stmt->bindValue(':styfilename', $styfilename);
        }
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * @param $set
     * @param $macro
     * @param $styfilename
     * @param $min
     * @param $max_pp
     * @return array
     */
    public function getFilenames($set, $macro, $styfilename, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                DISTINCT filename
            FROM
                mmfile
            WHERE
                1 ";

        if (!empty($macro)) {
            $query .= "
                AND macro           = :macro";
        }

        if (!empty($sty)) {
            $query .= "
                AND styfilename = :styfilename";
        }
        if (!empty($set)) {
            $query .= "
                AND `set` = :set";
        }

        $query .= "
            ORDER BY
                filename
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        if (!empty($macro)) {
            $stmt->bindValue(':macro', $macro);
        }
        if (!empty($sty)) {
            $stmt->bindValue(':styfilename', $styfilename);
        }
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param $set
     * @return int
     */
    public static function getCountA($set)
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' `set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
		SELECT
			count(*) as numrows
		FROM
			mmfile
        WHERE
            $where
		GROUP BY
			macro, styfilename";

        $stmt = $dao->prepare($query);
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * @param $set
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getA($set, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' `set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
            SELECT
                count(*) as num,
                macro,
                styfilename
            FROM
                mmfile
            WHERE
                $where
            GROUP BY
                macro, styfilename
            ORDER BY
                num DESC
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $set
     * @return int
     */
    public static function getCountM($set)
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' `set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
		SELECT
			count(distinct filename) as numrows
		FROM
			mmfile
        WHERE
            $where
		GROUP BY
			macro";

        $stmt = $dao->prepare($query);
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * @param $set
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getM($set, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' `set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
            SELECT
                count(*) as num,
                count(distinct filename) as numdoc,
                macro
            FROM
                mmfile
            WHERE
                $where
            GROUP BY
                macro
            ORDER BY
                numdoc DESC
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }
        try {
            $stmt->execute();
        } catch(Exception $e) {
            $stmt->debugDumpParams();
        }
        return $stmt->fetchAll();
    }

    /**
     * @param $set
     * @return int
     */
    public static function getCountS($set)
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' `set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
            SELECT
                count(distinct filename) as numrows
            FROM
                mmfile
            WHERE
                $where
            GROUP BY
                styfilename";

        $stmt = $dao->prepare($query);
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        $row = $stmt->fetch();
        return  $row['numrows'];
    }

    /**
     * @param $set
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getS($set, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' `set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
		SELECT
			count(*) as num,
			count(distinct filename) as numdoc,
			styfilename
		FROM
			mmfile
        WHERE
            $where
		GROUP BY
			styfilename
		ORDER BY
			num DESC
		LIMIT
			$min, $max_pp";

        $stmt = $dao->prepare($query);
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
