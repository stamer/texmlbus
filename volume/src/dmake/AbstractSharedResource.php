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

    abstract public function __construct(int $key = null, int $size = 10000);

    abstract public function put(?string $data) :bool;

    abstract public function has() :bool;

    abstract public function get() :?string;

    abstract public function detach() :bool;

    abstract public function remove() :bool;

    abstract public function exists() :bool;
}
