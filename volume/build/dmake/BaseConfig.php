<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *  Configuration
 *
 */
namespace Dmake;

use RangeException;
use stdClass;

class BaseConfig
{
    private static $config = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Gets the config object, or the subobject of config.
     */
    public static function getConfig(
        ?string $subobj = null,
        bool $useConfigDb = true
    ): ?stdClass
    {
        if (self::$config === null) {
            $config = new stdClass();
            require __DIR__ . '/../config/configGeneral.php';
            require __DIR__ . '/../config/configData.php';
            if ($useConfigDb) {
                require __DIR__ . '/../config/configDb.php';
            }
            self::$config = $config;
        }

        if (!is_null($subobj)) {
            if (!isset(self::$config->{$subobj})) {
                throw new RangeException("Unknown Config part: $subobj");
            }
            return self::$config->{$subobj};
        }

        return self::$config;
    }
}
