<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
define("DBG_SLEEP", 1 << 0);
define("DBG_DIRECTORIES", 1 << 1);
define("DBG_EXEC", 1 << 2);
define("DBG_ALARM", 1 << 3);
define("DBG_SIGNAL", 1 << 4);
define("DBG_CHILD", 1 << 5);
define("DBG_CHILD_RETVAL", 1 << 6);
define("DBG_DELETE", 1 << 7);
define("DBG_PARSE_ERRLOG", 1 << 8);
define("DBG_PARSE_POST", 1 << 9);
define("DBG_HOSTS", 1 << 10);
define("DBG_MAKE", 1 << 11);
define("DBG_SETUP_FILES", 1 << 12);
define("DBG_CLS_LOADER", 1 << 13);

$dbgLevel = getenv('DBG_LEVEL');
if ($dbgLevel !== false && $dbgLevel != '') {
    define("DBG_LEVEL", (int) $dbgLevel);
} else {
    define("DBG_LEVEL", DBG_EXEC | DBG_CHILD | DBG_CHILD_RETVAL);
}

// not supported yet
define("STAT_IDLE", 1);
define("STAT_ACTIVE", 2);
define("STAT_DEACTIVATED", 3);

/**
 * Timeout
 * @var StdClass $config
 */
$config->timeout = new stdClass();
$timeoutSeconds = getenv('TIMEOUT_SECONDS');
if ($timeoutSeconds !== false) {
    $config->timeout->default = $timeoutSeconds;
} else {
    $config->timeout->default = 1200;
}

/**
 * Memory Limit
 * The available memory will be multiplied with this factor
 */
$config->memory = new stdClass();
$memlimitPercent = getenv('MEMLIMIT_PERCENT');
if ($memlimitPercent !== false && $memlimitPercent != '') {
    if ($memlimitPercent <= 100) {
        $config->memory->factor = $memlimitPercent / 100;
    } else {
        $config->memory->factor = 0.8;
        error_log('Invalid MEMLIMIT_PERCENT, setting MEMLIMIT_PERCENT to ' . ($config->memory->factor * 100));
    }
} else {
    $config->memory->factor = 0.8;
}

$memlimitAbsolute = getenv('MEMLIMIT_ABSOLUTE');

if ($memlimitAbsolute !== false && $memlimitAbsolute != '') {
    $config->memory->absolute = $memlimitAbsolute;
}

/**
 * Time 
 */
$config->now = new stdClass();
$config->now->ts = time();
$config->now->datestamp = date("Y-m-d H:i:s", $config->now->ts);

// controls authentication options
$config->auth = new stdClass();

// whether API-clients need to use tokens.
// as access is limited to localhost, this is not necessary.
$config->auth->useJwToken = false;

$config->linkSourceFiles = true;