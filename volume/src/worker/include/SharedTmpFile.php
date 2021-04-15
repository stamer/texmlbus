<?php
/**
 * MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 * Class SharedTmpFile
 *
 * This class can be used to share resources between a child and and a parent process.
 * No locking is implemented as it is assumed that the only writer is the child and
 * the only reader is the parent process.
 * The parent reads when the child is terminated, so it can be safely assumed that the
 * data read is complete.
 *
 */

/**
 * File still needs to be 7.3 compatible, as it runs on worker.
 */
namespace Worker;

class SharedTmpFile extends AbstractSharedResource
{
    // Here it is actually just the filename.
    private $resource;

    public function __construct($key = null, $size = 100000)
    {
        if ($key === null) {
            $key = getmypid();
        }
        $tmpFilename = sys_get_temp_dir() . '/shared_' . $key . '.obj';
        $this->resource = $tmpFilename;
    }

    public function put($data) : bool
    {
        $success = file_put_contents($this->resource, serialize($data));
        if (!$success) {
            error_log(__METHOD__ . ': Failed to put var.');
        }
        return ($success !== false);
    }

    public function get()
    {
        $data = file_get_contents($this->resource);
        return unserialize($data);
    }

    public function detach() : bool
    {
        return true;
    }

    public function remove() : bool
    {
        $success = unlink($this->resource);
        if ($success) {
            $this->resource = null;
        }
        return $success;
    }

    public function exists() : bool
    {
        if ($this->resource
            && file_exists($this->resource)
        ) {
            return true;
        } else {
            return false;
        }
    }
}