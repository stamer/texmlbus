<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

/**
 * Class SourceToDir
 *
 * Maps zipfiles to specific directories.
 */

class SourceToDir
{
	protected string $sourcefile = '';
	protected string $directory = '';

    /**
     */
    public function setSourcefile(?string $sourcefile): self
    {
        $this->sourcefile = $sourcefile;
		return $this;
    }

    public function getSourcefile(): string
    {
        return $this->sourcefile;
    }

    /**
     */
    public function setDirectory(?string $directory): self
    {
        $this->directory = $directory;
		return $this;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function getSourcefilePrefix(): ?string
    {
        return preg_replace('/.tex$/', '', $this->sourcefile);
    }

    /**
     * Saves an entry to source_to_dir
     */
	public function save(): bool
	{
        $dao = Dao::getInstance();

		$query = '
			INSERT INTO
				source_to_dir
			SET
				sourcefile  = :i_sourcefile,
				directory	= :directory
            ON DUPLICATE KEY UPDATE
				sourcefile  = :u_sourcefile';

		$stmt = $dao->prepare($query);

		$stmt->bindValue(':directory', $this->directory);
		$stmt->bindValue(':i_sourcefile', $this->sourcefile);
		$stmt->bindValue(':u_sourcefile', $this->sourcefile);

        return $stmt->execute();
	}

    public static function fillEntry(array $row): self
    {
        $std = new self();
        if (isset($row['sourcefile'])) {
            $std->sourcefile = $row['sourcefile'];
        }
        if (isset($row['directory'])) {
    		$std->directory = $row['directory'];
        }

        return $std;
    }
}
