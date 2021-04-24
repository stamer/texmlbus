<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * include all needed files
 */

namespace Dmake;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Config.php';

require_once 'exception/WriteException.php';

require_once __DIR__ . '/../config/registerStages.php';

