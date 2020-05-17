<?php
/**
 * MIT License
 * (c) 2017 - 2019 Heinrich Stamerjohanns
 *
 * Stage Interface
 */

namespace Dmake;

interface StageInterface
{
    /**
     * @return mixed
     */
    public static function register();

    /**
     * @return mixed
     */
    public function save();

    /**
     * @param $row
     * @return mixed
     */
	public static function fillEntry($row);

    /**
     * @return mixed
     */
	public function updateRetval();

    /**
     * @param $hostname
     * @param $entry
     * @param $childAlarmed
     * @return mixed
     */
	public static function parse($hostname, $entry, $childAlarmed);
}
