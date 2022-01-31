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
     */
    public function getCount(string $set, string $macro, string $styfilename): int
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
        if (!empty($styfilename)) {
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
     * Get filenames for given set, macro and styfilename.
     */
    public function getFilenames(string $set, string $macro, string $styfilename, int $min, int $max_pp): array
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

        if (!empty($styfilename)) {
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
        if (!empty($styfilename)) {
            $stmt->bindValue(':styfilename', $styfilename);
        }
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get count for given set.
     */
    public static function getCountA(string $set): int
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
        return $row['numrows'] ?? 0;
    }

    /**
     * Get macro and styfilename for given set.
     */
    public static function getA(string $set, int $min, int $max_pp): array
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
     * Get count of mmfile entries for given set.
     */
    public static function getCountM(string $set): int
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
        return $row['numrows'] ?? 0;
    }

    /**
     * Get count mmfiles entries grouped by macro entries for given set.
     */
    public static function getM(string $set, int $min, int $max_pp): array
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
     * Get count grouped by styfilename for given set.
     */
    public static function getCountS(string $set): int
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
        return  $row['numrows'] ?? 0;
    }

    /**
     * Get entries grouped by styfilename.
     */
    public static function getS(string $set, int $min, int $max_pp): array
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
