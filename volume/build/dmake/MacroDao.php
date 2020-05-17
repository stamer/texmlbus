<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle queries to macro table.
 *
 */

namespace Dmake;

class MacroDao
{
    /**
     *
     * @return int
     */
    public static function getCountTopStylefiles()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct styfilename) as numrows
            FROM
                macro";
        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * @return array
     */
    public static function getTopStylefiles()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(styfilename) as num,
                sum(weight) as weight,
                styfilename
            FROM
                macro
            GROUP BY
                styfilename
            ORDER BY
                1 DESC";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param $field2
     * @param $column
     * @param $value
     * @return int
     */
    public static function getCountField2($field2, $column, $value)
    {
        $dao = Dao::getInstance();

        $query = "
	        SELECT
		        count(distinct $field2) as numrows
	        FROM
		        macro
	        WHERE
		        $column = :value";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':value', $value);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'];
    }

    /**
     * @param $field2
     * @param $column
     * @param $value
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getField2($field2, $column, $value, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        $query = "
	        SELECT
		        DISTINCT $field2
	        FROM
		        macro
	        WHERE
		        $column = :value
            ORDER BY
                $field2
	        LIMIT
		        $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':value', $value);

        $stmt->execute();

        return $stmt->fetchAll();
    }
}