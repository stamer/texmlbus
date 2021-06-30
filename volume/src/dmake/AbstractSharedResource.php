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

namespace Dmake;

/**
 *
 *
 */
abstract class AbstractSharedResource
{
    private $resource;

    public abstract function __construct(int $key = null, int $size = 10000);

    public abstract function put(?string $data) :bool;

    public abstract function has() :bool;

    public abstract function get() :?string;

    public abstract function detach() :bool;

    public abstract function remove() :bool;

    public abstract function exists() :bool;
}
