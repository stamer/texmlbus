<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

/**
 * Abstract Class to handle stages
 */
abstract class AbstractStage implements StageInterface
{
    protected ConfigStage $config;

    public bool $debug = false;

    public int $id = 0;

	public string $date_created = '';

	public string $date_modified = '';

	public string $retval = 'unknown';

	public string $prevRetval = 'unknown';

	public int $timeout = -1;

	public int $num_warning = 0;

	public int $num_error = 0;

	public int $num_macro = 0;

	public string $missing_macros = '';

	public string $warnmsg = '';

	public string $errmsg = '';

	public function debug(string $message): void
    {
	    if ($this->debug) {
	        echo $message . PHP_EOL;
        }
    }
    abstract public static function register(): ConfigStage;

    abstract public function save(): bool;

    abstract public static function fillEntry(array $row): StatEntry;

    abstract public function updateRetval(): bool;

    abstract public static function parse(
        string $hostGroup,
        StatEntry $entry,
        int $status,
        bool $childAlarmed
    ): bool;

    public static function parseMakelog(
        string $filename
    ) : string {
        $content = file_get_contents($filename);
        if ($content === false) {
            return '';
        }
        // for now just return the full log.
        return $content;
    }
}

