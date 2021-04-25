<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

/**
 * Class UtilManage
 *
 * manages entries
 *
 */
class UtilManage
{
    /**
     * deletes an Article from DB and filesystem, directories are traversed bottom-up. If no documents
     * remain in subDirectory, subDirectory is also removed.
     * Therefore, when last document in set is deleted, the set subDir is also deleted.
     */
    public static function deleteDocument(int $id): int
    {
        // delete entries in all retvaltables
        self::deleteFromAllRetvalTablesById($id);
        // delete entry in StatEntry
        $statEntry = StatEntry::getById($id);
        // delete the entry itself.
        if ($statEntry) {
            error_log("Deleting from DB: id: $id, " . $statEntry->getFilename());
            $success = (int) StatEntry::deleteById($id);
            if ($success) {
                error_log("$id deleted.");
                // delete file in filesystem
                // the name might actually be setname/subdir1/subdir2/Manuscript
                // walk up the tree, if no other document exists in subdir2, delete subdir2 as well
                $numSubdirs = substr_count($statEntry->getFilename(), '/');
                $dirs[] = $statEntry->getFilename();
                $newSubDir = $statEntry->getFilename();
                for ($i = 0; $i < $numSubdirs; $i++) {
                    $newSubDir = substr($statEntry->getFilename(), 0, strrpos($newSubDir, '/'));
                    $dirs[] = $newSubDir;
                }
                // dirs[0] = 'setname/subdir1/subdir2/Manuscript'
                // dirs[1] = 'setname/subdir1/subdir2'
                // dirs[2] = 'setname/subdir1'
                // dirs[3] = 'setname'
                foreach ($dirs as $subdir) {
                    // add /, so directories with same prefix are not considered.
                    $numDocuments = StatEntry::getCountByDirPrefix($subdir . '/');
                    if (!$numDocuments) {
                        UtilFile::deleteDirR(ARTICLEDIR . '/' . $subdir);
                        error_log("Deleting directory" . ARTICLEDIR . '/' . $subdir);
                    }
                }
            }
            return $success;
        } else {
            return 0;
        }
    }

    /**
     * deletes a complete set only from DB
     */
    public static function dropSet(string $set): int
    {
        // find all entries for set
        $ids = StatEntry::getIdsBySet($set);
        $count = 0;
        foreach ($ids as $id) {
            // delete entries in all retval tables
            self::deleteFromAllRetvalTablesById($id);
            // delete entry in StatEntry
            $result = StatEntry::deleteById($id);
            $count += (int)$result;
        }
        return $count;
    }
    /**
     * deletes a complete set from DB and filesystem
     */
    public static function deleteSet(string $set): int
    {
        $count = self::dropSet($set);
        // delete all files in set
        UtilFile::deleteDirR(ARTICLEDIR . '/' . $set);
        return $count;
    }

    /**
     * deletes all corresponding entries from RetvalTables
     */
    public static function deleteFromAllRetvalTablesById(int $id): bool
    {
        static $retvalTables = null;
        if ($retvalTables === null) {
            $retvalTables = self::getRetvalTables();
        }
        foreach ($retvalTables as $retvalTable) {
            RetvalDao::deleteById($retvalTable, $id);
        }
        return true;
    }

    /**
     * return all tables that are Retval-tables
     * might be mysql-specific
     *
     * @return string[]
     */
    public static function getRetvalTables(): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT 
                table_name
            FROM
                information_schema.tables
            WHERE
                table_type = 'BASE TABLE'
                AND table_schema = database()
                AND table_name LIKE 'retval_%' 
            ORDER BY
                table_name";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = $row['table_name'];
        }
        return $result;
    }
}
