<?php
/**
 * MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 * Class AbstractSharedResource
 *
 * A class to share resources between parent and child process.
 *
 * It is implicitly assumed that the only writer is the child and the only reader is the parent.
 * The parent reads when the child is terminated, so it can be safely assumed that the
 * data read is complete.
 *
 */

/**
 * File still needs to be 7.3 compatible, as it runs on worker.
 */
namespace Worker;

/**
 *
 *
 */
abstract class AbstractSharedResource
{
    private $resource;

    public abstract function __construct($key = null);

    public abstract function put($data) : bool;

    public abstract function get();

    public abstract function detach() : bool;

    public abstract function remove() : bool;

    public abstract function exists();
}