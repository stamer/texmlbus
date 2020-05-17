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
	protected $sourcefile = '';
	protected $directory = '';

    /**
     *
     * @return string
     */
    public function setSourcefile($sourcefile)
    {
        $this->sourcefile = $sourcefile;
		return $this;
    }

    public function getSourcefile()
    {
        return $this->sourcefile;
    }

    /**
     *
     * @return this
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
		return $this;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function getSourcefilePrefix() {
        return preg_replace('/.tex$/', '', $this->sourcefile);
    }

    /**
     * Saves an entry to source_to_dir
     */
	public function save()
	{
        $cfg = Config::getConfig();

        $dao = DAO::getInstance();

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

        $stmt->execute();
	}

    /**
     * @param $row
     * @return SourceToDir
     */
    public static function fillEntry($row)
    {
        $std = new SourceToDir();
        if (isset($row['sourcefile'])) {
            $std->sourcefile = $row['sourcefile'];
        }
        if (isset($row['directory'])) {
    		$std->directory = $row['directory'];
        }

        return $std;
    }
}
