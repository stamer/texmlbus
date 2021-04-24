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
     * Get count of top stylefiles.
     */
    public static function getCountTopStylefiles(): int
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
     * Get the top Stylefiles.
     */
    public static function getTopStylefiles(): array
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
     * Gets the count for dynamic field.
     */
    public static function getCountField2(string $field2, string $column, $value): int
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
     * Gets dynamic fields.
     */
    public static function getField2(string $field2, string $column, $value, int $min, int $max_pp): array
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