<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 *  Configuration
 *
 */
namespace Worker;

use Dmake\BaseConfig;
use \RangeException;
use \stdClass;

class Config extends BaseConfig
{
	private static $config = null;

	private function __construct() {}
	private function __clone() {}

	public static function getConfig(
        ?string $subobj = null,
        bool $useConfigDb = true
    ): ?stdClass
    {
        if (self::$config === null)
        {
            $config = parent::getConfig(null, false);
			self::$config = $config;
		}
		if (!is_null($subobj)) {
			if (!isset(self::$config->{$subobj})) {
				throw new RangeException("Unknown Config part: $subobj");
			}
			return self::$config->{$subobj};
		} else {
			return self::$config;
		}
	}
}

