<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *  Configuration
 *
 */
namespace Dmake;

use Dmake\BaseConfig;

class Config extends BaseConfig
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
     * @return stdClass|null
     */
    public static function getConfig($subobj = null, $useConfigHosts = false)
    {
        if (self::$config === null) {
            $config = BaseConfig::getConfig();
            self::$config = $config;
        }
        if ($useConfigHosts) {
            $config = self::$config;
            require __DIR__ . '/../config/configHosts.php';
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
