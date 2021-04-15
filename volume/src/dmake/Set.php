<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle entries in the errlog_detail database.
 *
 */

namespace Dmake;

use \PDO;

/**
 * Documents are orgnanized in sets.
 *
 * Class Set
 */
class Set
{
    /**
     * @var ?string
     */
	protected $sourcefile;

    /**
     * @var ?string
     */
	protected $name;

    /**
     * @var ?int
     */
    protected $numDocuments;

    public function getSourcefile(): ?string
    {
        return $this->sourcefile;
    }

    public function setSourcefile(?string $sourcefile): void
    {
        $this->sourcefile = $sourcefile;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getNumDocuments(): ?int
    {
        return $this->numDocuments;
    }

    /**
     * @param int $numDocuments
     */
    public function setNumDocuments(?int $numDocuments): void
    {
        $this->numDocuments = $numDocuments;
    }

    public static function fillSet(array $row): self
    {
        $set = new self();
        if (isset($row['set'])) {
            $set->setName($row['set']);
        }
        if (isset($row['sourcefile'])) {
            $set->setSourcefile($row['sourcefile']);
        }
        if (isset($row['numDocuments'])) {
            $set->setNumDocuments($row['numDocuments']);
        }

        return $set;
    }

    /**
     * @param string $pattern
     * @return Set[]
     */
    public static function getSets(string $pattern = ''): array
    {
        $dao = Dao::getInstance();

        if ($pattern != '') {
            $where = ' WHERE s.`set` LIKE :pattern ';
        } else {
            $where = '';
        }

        $query = "
            SELECT
                distinct s.`set`,
                std.sourcefile
            FROM
                statistic as s
            LEFT JOIN
                source_to_dir as std
            ON
                s.`set` = std.directory
            $where
            ORDER BY
                `set`";

        $stmt = $dao->prepare($query);
        if ($pattern != '') {
            $stmt->bindValue(':pattern', '%' . $pattern . '%', PDO::PARAM_STR);
        }
        $stmt->execute();

        $sets = [];
        while ($row = $stmt->fetch()) {
            $sets[] = self::fillSet($row);
        }
        return $sets;

    }

    /**
     * @return Sets[]
     */
    public static function getSetsCount(): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                s.`set`,
                count(s.id) as numDocuments,
                std.sourcefile
            FROM
                statistic as s
            LEFT JOIN
                source_to_dir as std
            ON
                s.`set` = std.directory
            GROUP BY
                `set`
            ORDER BY
                `set`";

        $stmt = $dao->prepare($query);
        $stmt->execute();

        $sets = [];
        while ($row = $stmt->fetch()) {
            $sets[] = self::fillSet($row);
        }
        return $sets;
    }
}
