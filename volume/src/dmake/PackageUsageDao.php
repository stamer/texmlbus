<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle queries to RetvalTables.
 *
 */

namespace Dmake;

class PackageUsageDao
{
    /**
     * Get count by set.
     * @return int
     */
    public static function getCount(string $set): int
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' s.`set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
            SELECT
                count(pu.styfilename) as numrows
            FROM
                package_usage as pu
            JOIN
                statistic as s
            ON
                pu.filename = s.filename
            JOIN
                retval_xml as rx
            ON
                s.id = rx.id
            WHERE
                $where
            GROUP BY
                pu.styfilename";

        $stmt = $dao->prepare($query);
        if (!empty($set)) {
            $stmt->bindValue(':set', $set);
        }
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * @param $filename
     */
    public function getStyfilesByFilename($filename): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                package_usage as pu
            WHERE
                filename = :filename
            ORDER BY
                styfilename";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':filename', $filename);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get Correlation
     */
    public static function getStyCorrelation(
        string $set,
        string $order,
        int $min,
        int $max_pp): array
    {
        $dao = Dao::getInstance();

        if (!empty($set)) {
            $where = ' s.`set` = :set ';
        } else {
            $where = ' 1 = 1 ';
        }

        $query = "
            SELECT
                pu.styfilename,
                (count(if(rx.retval = 'warning' OR rx.retval = 'no_problems', 1, NULL))/count(rx.retval)) as success_rate,
                count(rx.retval) as total
            FROM
                package_usage as pu
            JOIN
                statistic as s
            ON
                pu.filename = s.filename
            JOIN
                retval_xml as rx
            ON
                s.id = rx.id
            WHERE
                $where
            GROUP BY
                pu.styfilename 
            ORDER BY
                $order
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
