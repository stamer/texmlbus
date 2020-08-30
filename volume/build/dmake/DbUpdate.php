<?php
/**
 * MIT License
 * (c) 2020 Heinrich Stamerjohanns

 * DbUpdate
 *
 */

namespace Dmake;

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
    const UPGRADEDIR = BUILDDIR . '/config/sql';

    public function execute()
    {
        $dbVersion = $this->getDbVersion();
        echo "Checking DB updates..." . PHP_EOL;
        echo "Current dbVersion is: $dbVersion" . PHP_EOL;

        $files = UtilFile::listDir(self::UPGRADEDIR, true, true, '/.\.sql/', true);

        foreach ($files as $filename) {
            // filename pattern should be dddd-upgrade.sql
            $result = preg_split('/-/', $filename, 2);
            if ($result === false
                || !isset($result[0])
            ) {
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
     * @param $tableName
     * @param $columnName
     * @return bool
     */
    public function columnExists($tableName, $columnName)
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

    public function getDbVersion()
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

    public function setDbVersion($dbVersion)
    {
        $dao = Dao::getInstance();
        $query = "UPDATE dbversion SET dbversion = :dbVersion";
        $stmt = $dao->prepare($query);
        $stmt->bindValue(':dbVersion', $dbVersion);
        $result = $stmt->execute();
        return $result;
    }

    public function importFile($filename)
    {
        $db = Config::getConfig('db');
        $systemstr = sprintf('/usr/bin/mysql -u%s -p%s -h%s %s < %s',
                    $db->username, $db->password, $db->host, $db->dbname, $filename);
        // echo $systemstr . PHP_EOL;
        $output = [];
        $result = exec($systemstr, $output, $return_var);
        if ($return_var != 0) {
            echo __METHOD__ . ': mysql failed...' . PHP_EOL;
            print_r($output);
            return false;
        }
        return true;
    }
}
