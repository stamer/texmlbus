<?php

use Dmake\AbstractStage;
use Dmake\Config;
use Dmake\Dao;
use Dmake\StageInterface;
use Dmake\StatEntry;
use Dmake\UtilFile;

class StagePagelimit extends AbstractStage implements StageInterface
{
	public $num_err_namespace = 0;
	public $num_err_parser = 0;
	public $num_err_validity = 0;

    public function __construct()
    {
        $this->config = self::register();
    }

    public static function register()
    {
        $config = array(
            'stage' => 'pagelimit',
            'classname' => __CLASS__,
            'parseXml' => false,
            'timeout' => 1200,
            'dbTable' => 'retval_pagelimit',
            'destFile' => '%MAINFILEPREFIX%.pagelimit.html',
            'stdoutLog' => 'pagelimit.stdout.log', // this needs to match entry in Makefile
            'stderrLog' => 'pagelimit.stderr.log', // needs to match entry in Makefile
            'dependentTargets' => array(), // which log files need to be parsed?
            /* retvals to be shown */
            'showRetval' =>
                array(
                    'unknown'           => true,
                    'not_qualified'     => true,
                    'missing_errlog'    => true,
                    'fatal_error'       => true,
                    'timeout'           => true,
                    'error'             => true,
                    'missing_macros'    => false,
                    'missing_figure'    => true,
                    'missing_bib'       => true,
                    'missing_file'      => true,
                    'warning'           => true,
                    'no_problems'       => true
                ),
            'showTopErrors' =>
                array(
                    'error'             => false,
                    'fatal_error'       => false,
                    'missing_macros'    => false,
                ),
            'showDetailErrors' =>
                array(
                    'error'             => false,
                ),
            /* column configuration for retval_detail.php */
            'retvalDetail' => array(
                'error' =>
                    array(0 =>
                        array('sql' => ['errmsg', 'warnmsg'], 'html' => 'Error message', 'align' => 'left')
                    ),
            ),
            'tableTitle' => 'pagelimit',
            'toolTip' => 'pagelimit'

        );

        return $config;
    }

	public function save()
	{
        $cfg = Config::getConfig();
		$cfg->now->datestamp = date("Y-m-d H:i:s", time());

        $dao = DAO::getInstance();

        $query = '
            REPLACE	INTO
				'.$this->config['dbTable'].'
			SET
				id              = :id,
				date_created	= :date_created,
				date_modified	= :date_modified,
				prev_retval     = retval,
				retval      	= :retval,
				timeout			= :timeout,
				num_warning		= :num_warning,
				num_error		= :num_error,
				num_macro		= :num_macro,
				missing_macros	= :missing_macros,
				warnmsg			= :warnmsg,
				errmsg			= :errmsg';

		$stmt = $dao->prepare($query);

		$stmt->bindValue('id', $this->id);
		$stmt->bindValue('date_created', $cfg->now->datestamp);
		$stmt->bindValue('date_modified', $cfg->now->datestamp);
		$stmt->bindValue('retval', $this->retval);
		$stmt->bindValue('timeout',	$this->timeout);
		$stmt->bindValue('num_warning', $this->num_warning);
		$stmt->bindValue('num_error', $this->num_error);
		$stmt->bindValue('num_macro', $this->num_macro);
        $stmt->bindValue('missing_macros', $this->missing_macros);
		$stmt->bindValue('warnmsg', $this->warnmsg);
		$stmt->bindValue('errmsg', $this->errmsg);

        $stmt->execute();
	}

    public static function fillEntry($row)
    {
        $se = new StatEntry();
        if (isset($row['id'])) {
            $se->id = $row['id'];
        }
        if (isset($row['date_created'])) {
    		$se->date_created = $row['date_created'];
        }
        if (isset($row['date_modified'])) {
            $se->date_modified = $row['date_modified'];
        }
        if (isset($row['retval'])) {
    		$se->retval = $row['retval'];
        }
        if (isset($row['prev_retval'])) {
    		$se->prevRetval = $row['prev_retval'];
        }
        if (isset($row['timeout'])) {
    		$se->timeout = $row['timeout'];
        }
        if (isset($row['num_err_namespace'])) {
    		$se->num_err_namespace = $row['num_err_namespace'];
        }
        if (isset($row['num_err_parser'])) {
    		$se->num_err_parser = $row['num_err_parser'];
        }
        if (isset($row['num_err_validity'])) {
    		$se->num_err_validity = $row['num_err_validity'];
        }
        if (isset($row['warnmsg'])) {
    		$se->warnmsg = $row['warnmsg'];
        }
        if (isset($row['errmsg'])) {
    		$se->errmsg = $row['errmsg'];
        }

        return $se;
    }

    /**
     * @return mixed|void
     */
    public function updateRetval()
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s", time());

        $dao = DAO::getInstance();

        $query = '
			INSERT INTO
				'.$this->config['dbTable'].'
			SET
				id              = :id,
				date_modified	= :i_date_modified,
				retval          = :i_retval,
                warnmsg         = :i_warnmsg,
				errmsg          = :i_errmsg
            ON DUPLICATE KEY UPDATE
				date_modified	= :u_date_modified,
				prev_retval     = retval,
				retval          = :u_retval,
                warnmsg         = :u_warnmsg,
				errmsg          = :u_errmsg';

        $stmt = $dao->prepare($query);
        $stmt->bindValue('id', $this->id);
        $stmt->bindValue('i_date_modified', $cfg->now->datestamp);
        $stmt->bindValue('u_date_modified', $cfg->now->datestamp);
        $stmt->bindValue('i_retval', $this->retval);
        $stmt->bindValue('u_retval', $this->retval);
        $stmt->bindValue('i_warnmsg', $this->warnmsg);
        $stmt->bindValue('u_warnmsg', $this->warnmsg);
        $stmt->bindValue('i_errmsg', $this->errmsg);
        $stmt->bindValue('u_errmsg', $this->errmsg);

        $stmt->execute();
    }

    public static function parse($hostname, $entry, $childAlarmed)
    {
        $directory = $entry->filename;

        $res = new self;
        $res->id = $entry->id;

        $stdErrlog = ARTICLEDIR.'/'.$directory.'/'.$res->config['stderrLog'];

        $texSourcefilePrefix = ARTICLEDIR.'/'.$directory.'/'.$entry->getSourcefilePrefix();
        $texSourcefile = ARTICLEDIR.'/'.$directory.'/'.$entry->getSourcefile();

        $destFile = str_replace('%MAINFILEPREFIX%', $texSourcefilePrefix, $res->config['destFile']);

        echo "parsing Logfile $stdErrlog ..." . PHP_EOL;

        if ($childAlarmed) {
            $res->retval = 'timeout';
            $res->timeout = $res->config->timeout;
        } elseif (!UtilFile::isFileTexfile($texSourcefile)) {
            $res->retval = 'not_qualified';
        } elseif (!is_file($stdErrlog)) {
            $res->retval = 'missing_errlog';
        } else {
            // have we created a destFile?
            echo "check whether destfile $destFile exists... ";
            if (is_file($destFile)) {
                echo "yes\n";
                $res->retval = 'no_problems';
            } else {
                echo "no\n";
                $res->retval = 'fatal_error';
            }
        }

        $content = file_get_contents($stdErrlog);
        $res->errmsg = '';
        $res->warnmsg = '';

        $warnPattern = '@(.*?)(Warning:)(\S*)\s+(.*)@m';
        $matches = array();
        preg_match_all($warnPattern, $content, $matches);

        $numWarnings = count($matches[4]);
        if ($numWarnings) {
            $res->warnmsg .= 'Found ' . $numWarnings . ' warning' . ($numWarnings == 1 ? '' : 's') . ".\n";
            $res->retval = 'warning';
            $errLimit = 10;
            if ($numWarnings > $errLimit) {
                $res->retval = 'error';
                $res->errmsg .= "More than $errLimit warnings, considering paper as error.\n";
            }
        }
        $res->warnmsg .= implode("\n", $matches[4]);

        $errPattern = '@(.*?)(Error:)(\S*)\s+(.*)@m';
        $matches = array();
        preg_match_all($errPattern, $content, $matches);
        $numErrors = count($matches[4]);
        if ($numErrors) {
            $res->errmsg .= 'Found ' . $numErrors . ' error' . ($numErrors == 1 ? '' : 's') . ".\n";
            $res->retval = 'error';
        }
        $res->errmsg .= implode("\n", $matches[4]);

        echo __CLASS__ . ": Setting retval to " . $res->retval . PHP_EOL;

        $res->updateRetval();
    }
}
