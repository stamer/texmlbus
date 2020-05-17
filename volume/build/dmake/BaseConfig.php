<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *  Configuration
 *
 */
namespace Dmake;

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
     * @param string|null $subobj
     * @param bool $useConfigHosts
     * @return StdClass|null
     */
    public static function getConfig($subobj = null)
    {
        if (self::$config === null) {
            $config = new \stdClass();
            require __DIR__ . '/../config/configGeneral.php';
            require __DIR__ . '/../config/configData.php';
            require __DIR__ . '/../config/configDb.php';
            self::$config = $config;
        }

        if (!is_null($subobj)) {
            if (!isset(self::$config->{$subobj})) {
                throw new \RangeException("Unknown Config part: $subobj");
            }
            return self::$config->{$subobj};
        } else {
            return self::$config;
        }
    }
}
