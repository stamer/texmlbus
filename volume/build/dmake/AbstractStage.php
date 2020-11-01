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
abstract class AbstractStage
{
    protected $config = array();

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
}

