<?php
/**
 * MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 * Class SharedMem
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

class SharedMem extends AbstractSharedResource
{
    private $resource;

    public function __construct($key = null, $size = 100000)
    {
        if ($key === null) {
            $key = getmypid();
        }
        $this->resource = shm_attach($key, $size);
        if (!$this->resource) {
            error_log(__METHOD__ . ': Failed to create shared memory.');
        }
    }

    public function put($data) : bool
    {
        $success = shm_put_var($this->resource, 1, $data);
        if (!$success) {
            error_log(__METHOD__ . ': Failed to put var.');
        }
        return $success;
    }

    public function get()
    {
        $data = shm_get_var($this->resource, 1);
        return $data;
    }

    public function detach() : bool
    {
        $success = shm_detach($this->resource);
        return $success;
    }

    public function remove() : bool
    {
        $success = shm_remove($this->resource);
        if ($success) {
            $this->resource = null;
        }
        return $success;
    }

    public function exists() : bool
    {
        if ($this->resource) {
            return true;
        } else {
            return false;
        }
    }
}