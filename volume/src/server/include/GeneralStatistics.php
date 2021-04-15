<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

namespace Server;

use Dmake\Dao;
use Dmake\WorkqueueEntry;

class GeneralStatistics
{
    public static function getCurrentState()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(date_modified) as num
            FROM
                statistic
            WHERE
                date_modified > subdate(now(), interval 3 minute)";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['num'];
    }

    public static function getNumCompiledFiles()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(*) as num
            FROM
                statistic";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();

        return $row['num'];
    }

    public static function getNumLast24()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(date_modified) as num
            FROM
                statistic
            WHERE
                date_modified > subdate(now(), interval 1 day)
                AND wq_priority = 0";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['num'];
    }

    public static function getNumLastHour()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(date_modified) as num
            FROM
                statistic
            WHERE
                date_modified > subdate(now(), interval 1 hour)
                AND wq_priority = 0";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();
        return $row['num'];
    }

    public static function getDmakeStatus()
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                *
            FROM
                dmake_status";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $row = $stmt->fetch();

        return $row;
    }
}
