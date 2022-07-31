<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * DmakeStatus
 *
 */

namespace Dmake;

class DmakeStatus
{
    public int $id = 0;
	public string $started = '';
	public string $directory = '';
	public string $num_files = '';
	public string $num_hosts = '';
	public string $hostnames = '';
	public int $timeout = -1;
	public string $errmsg = '';

    /**
     * saves the current status
     *
     * @param bool $updateStarted
     */
	public function save($updateStarted = TRUE): bool
	{
		$dao = Dao::getInstance();

		$this->started = date("Y-m-d H:i:s");

		$query = "
			INSERT INTO
				dmake_status
                (id, started, directory, num_files, num_hosts, hostnames, timeout, errmsg)
			VALUES(0, :started, :directory, :num_files, :num_hosts, :hostnames, :timeout, :errmsg)
			ON DUPLICATE KEY
			UPDATE ";

			if ($updateStarted) {
				$query .= "started = VALUES(started), ";
			}
			$query .= "
				directory = VALUES(directory),
				num_files = VALUES(num_files),
				num_hosts = VALUES(num_hosts),
				hostnames = VALUES(hostnames),
				timeout = VALUES(timeout),
				errmsg = VALUES(errmsg)";

		$stmt = $dao->prepare($query);
        $stmt->bindValue(':started', $this->started);
        $stmt->bindValue(':directory', $this->directory);
        $stmt->bindValue(':num_files', $this->num_files);
        $stmt->bindValue(':num_hosts', $this->num_hosts);
        $stmt->bindValue(':hostnames', $this->hostnames);
        $stmt->bindValue(':timeout', $this->timeout);
        $stmt->bindValue(':errmsg', $this->errmsg);

        return $stmt->execute();
	}

    /**
     * Gets and sets the current status.
     */
	public function get(): self
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
		foreach ($row as $key => $val) {
			$this->$key = $val;
		}
		return $this;
	}
}
