<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle queries to help table.
 *
 */

namespace Dmake;

class HelpDao
{
    /**
     * Placeholders that may be used in helptexts.
     * @var array
     */
    public static $placeholder =  [
        '__BASEDIR__' => BASEDIR,
        '__MAKEDIR__' => MAKEDIR,
        '__SRCDIR__' =>  SRCDIR,
        '__ARTICLEDIR__' => ARTICLEDIR,
        '__UPLOADDIR__' => UPLOADDIR,
        '__STYDIR__' =>  STYDIR,
        '__BINDIR__' => BINDIR,
        '__STYARXMLIVDIR__' => STYARXMLIVDIR,
        '__SERVERDIR__' => SERVERDIR,
        '__HTDOCS__' => HTDOCS
    ];

    /**
     * Gets a help message by given id.
     */
    public static function getHelpById(string $id, bool $replacePlaceholder = true): false|array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                id, 
                title,
                html   
            FROM
                help
            WHERE
                id = :id";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        $stmt->execute();

        $row = $stmt->fetch();
        if (!empty($row['html'])
            && $replacePlaceholder
        ) {
            $row['html'] = str_replace(array_keys(self::$placeholder), array_values(self::$placeholder), $row['html']);
        }
        return $row;
    }

    /**
     * Gets all Ids stored in help table.
     */
    public static function getAllIds(): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                id
            FROM
                help
            ORDER BY id
        ";

        $stmt = $dao->prepare($query);

        $stmt->execute();

        $rows = [];
        while ($row = $stmt->fetch()) {
            $rows[] = $row['id'];
        }
        return $rows;
    }

    /**
     * Saves the entry.
     */
    public static function save(string $id, string $title, string $html): bool
    {
        $dao = Dao::getInstance();

        $query = "
            REPLACE INTO help
            SET 
                id = :id,
                title = :title,
                html = :html
        ";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':html', $html);

        return $stmt->execute();
    }

    /**
     * Deletes the entry by given id.
     */
    public static function deleteById(string $id): bool
    {
        $dao = Dao::getInstance();

        $query = "
            DELETE FROM help
            WHERE
                id = :id
        ";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}