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
    protected $config = [];

    /**
     * @var bool
     */
    public $debug = false;
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
	public $date_created = '';

    /**
     * @var string
     */
	public $date_modified = '';

    /**
     * @var string
     */
	public $retval = 'unknown';

    /**
     * @var string
     */
	public $prevRetval = 'unknown';

    /**
     * @var int
     */
	public $timeout = -1;

    /**
     * @var int
     */
	public $num_warning = 0;

    /**
     * @var int
     */
	public $num_error = 0;

    /**
     * @var int
     */
	public $num_macro = 0;

    /**
     * @var string
     */
	public $missing_macros = '';

    /**
     * @var string
     */
	public $warnmsg = '';

    /**
     * @var string
     */
	public $errmsg = '';

    /**
     * @param string $message
     */
	public function debug(string $message): void
    {
	    if ($this->debug) {
	        echo $message . PHP_EOL;
        }
    }
    abstract public static function register(): array;

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

