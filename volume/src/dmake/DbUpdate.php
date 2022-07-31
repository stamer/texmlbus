<?php
/**
 * MIT License
 * (c) 2020 Heinrich Stamerjohanns

 * DbUpdate
 *
 */

namespace Dmake;

use ValueError;

/**
 * Class DbUpdate
 *
 * A simple DbUpdate Handler.
 * The current dbversion is stored in dmake_status:dbversion
 * Upgrade files are stored in config/sql/dddd-upgrade.sql
 * If fileVersion is > dbVersion then the script is being applied.
 */
class DbUpdate
{
    public const UPGRADEDIR = SRCDIR . '/config/sql';

    public function execute(): void
    {
        $dbVersion = $this->getDbVersion();
        echo "Checking DB updates..." . PHP_EOL;
        echo "Current dbVersion is: $dbVersion" . PHP_EOL;

        $files = UtilFile::listDir(self::UPGRADEDIR, true, true, '/.\.sql/', true);

        foreach ($files as $filename) {
            // filename pattern should be dddd-upgrade.sql
            try {
                $result = explode('-', $filename, 2);
            } catch (ValueError $e) {
                echo "Skipping filename $filename, unknown pattern." . PHP_EOL;
                continue;
            }
            $fileVersion = (int) $result[0];
            if ($fileVersion <= $dbVersion) {
                echo "$filename: already applied" . PHP_EOL;
                continue;
            }

            echo "Applying $filename... ";
            $result = $this->importFile(self::UPGRADEDIR . '/' . $filename);
            if ($result) {
                echo "OK" . PHP_EOL;
                $this->setDbVersion($fileVersion);
            } else {
                echo "FAIL" . PHP_EOL;
                break;
            }
        }
    }

    /**
     * Checks if given columnName for tableName exists.
     */
    public function columnExists(string $tableName, string $columnName): bool
    {
        $db = Config::getConfig('db');
        $dao = Dao::getInstance();

        $query = "
            SELECT COUNT(*) AS num
                FROM information_schema.COLUMNS 
            WHERE 
                TABLE_SCHEMA = :dbName
                AND TABLE_NAME = :tableName
                AND COLUMN_NAME = :columnName
        ";
        $stmt = $dao->prepare($query);
        $stmt->bindValue(':dbName', $db->dbname);
        $stmt->bindValue(':tableName', $tableName);
        $stmt->bindValue(':columnName', $columnName);
        $stmt->execute();

        $row = $stmt->fetch();
        return ($row['num'] > 0);
    }

    public function getDbVersion(): int
    {
        if (!$this->columnExists('dbversion', 'dbversion')) {
            return 0;
        }

        $dao = Dao::getInstance();
        $query = "SELECT dbversion from dbversion";
        $stmt = $dao->prepare($query);
        $result = $stmt->execute();
        $row = $stmt->fetch();
        return $row['dbversion'];
    }

    /**
     * Sets current version of patches to dbversion.
     */
    public function setDbVersion(int $dbVersion): bool
    {
        $dao = Dao::getInstance();
        $query = "UPDATE dbversion SET dbversion = :dbVersion";
        $stmt = $dao->prepare($query);
        $stmt->bindValue(':dbVersion', $dbVersion);
        return $stmt->execute();
    }

    /**
     * Imports a dump file to mysql.
     */
    public function importFile(string $filename): bool
    {
        $db = Config::getConfig('db');
        $systemstr = sprintf('/usr/bin/mysql -u%s -p%s -h%s %s < %s',
                    $db->username, $db->password, $db->host, $db->dbname, $filename);
        // echo $systemstr . PHP_EOL;
        $output = [];
        $result = exec($systemstr, $output, $return_var);
        if ($return_var !== 0) {
            echo __METHOD__ . ': mysql failed...' . PHP_EOL;
            print_r($output);
            return false;
        }
        return true;
    }
}
