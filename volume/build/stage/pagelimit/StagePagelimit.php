<?php

use Dmake\AbstractStage;
use Dmake\Config;
use Dmake\Dao;
use Dmake\StatEntry;
use Dmake\UtilFile;
use Dmake\UtilStage;

class StagePagelimit extends AbstractStage
{
	public $num_err_namespace = 0;
	public $num_err_parser = 0;
	public $num_err_validity = 0;

    public function __construct()
    {
        $this->config = static::register();
    }

    public static function register(): array
    {
        $stage = 'pagelimit';
        $config = [
            'stage' => $stage,
            'classname' => __CLASS__,
            'target' => $stage,
            'hostGroup' => 'worker',
            'dbTable' => 'retval_' . $stage,
            'tableTitle' => $stage,
            'toolTip' => $stage,
            'parseXml' => false,
            'timeout' => 1200,
            'destFile' => '%MAINFILEPREFIX%.pagelimit.html',
            'stdoutLog' => 'pagelimit.stdout.log', // this needs to match entry in Makefile
            'stderrLog' => 'pagelimit.stderr.log', // needs to match entry in Makefile
            'makeLog' => 'make_' . $stage . '.log',
            'dependentStages' => [], // which log files need to be parsed?
            /* retvals to be shown */
            'showRetval' => [
                'unknown' => true,
                'not_qualified' => true,
                'missing_errlog' => true,
                'fatal_error' => true,
                'timeout' => true,
                'error' => true,
                'missing_macros' => false,
                'missing_figure' => true,
                'missing_bib' => true,
                'missing_file' => true,
                'warning' => true,
                'no_problems' => true
            ],
            'showTopErrors' => [
                'error' => false,
                'fatal_error' => false,
                'missing_macros' => false,
            ],
            'showDetailErrors' => [
                'error' => false,
            ],
            /* column configuration for retval_detail.php */
            'retvalDetail' => [
                'error' => [
                    ['sql' => ['errmsg', 'warnmsg'], 'html' => 'Error message', 'align' => 'left']
                ],
            ],
        ];

        return $config;
    }

	public function save(): bool
	{
        $cfg = Config::getConfig();
		$cfg->now->datestamp = date("Y-m-d H:i:s");

        $dao = Dao::getInstance();

        $query = /** @lang ignore */ '
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

        return $stmt->execute();
	}

    public static function fillEntry($row): StatEntry
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

    public function updateRetval(): bool
    {
        $cfg = Config::getConfig();
        $cfg->now->datestamp = date("Y-m-d H:i:s");

        $dao = Dao::getInstance();

        $query = /** @lang ignore */ '
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

        return $stmt->execute();
    }

    public static function parse(
        string $hostGroup,
        StatEntry $entry,
        int $status,
        bool $childAlarmed): bool
    {
        $directory = $entry->filename;

        $res = new static();
        $res->id = $entry->id;

        $sourceDir = UtilStage::getSourceDir(ARTICLEDIR, $directory, $hostGroup);
        $texSourcefilePrefix = $sourceDir . '/' . $entry->getSourcefilePrefix();
        $texSourcefile = $sourceDir . '/' . $entry->getSourcefile();
        $stdErrlog = $sourceDir . '/' . $res->config['stderrLog'];
        $makelog = $sourceDir . '/' . $res->config['makeLog'];

        $destFile = str_replace('%MAINFILEPREFIX%', $texSourcefilePrefix, $res->config['destFile']);

        echo "parsing Logfile $stdErrlog ..." . PHP_EOL;

        if ($childAlarmed) {
            $res->retval = 'timeout';
            $res->timeout = $res->config->timeout;
        } elseif (!UtilFile::isFileTexfile($texSourcefile)) {
            $res->retval = 'not_qualified';
        } elseif (!is_file($stdErrlog)) {
            if ($status) {
                $res->retval = 'fatal_error';
                $res->errmsg = static::parseMakelog($makelog);
            } else {
                $res->retval = 'missing_errlog';
            }
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
        if ($content === ''
            && $status
        ) {
            $res->retval = 'fatal_error';
            $res->errmsg = static::parseMakelog($makelog);
        } else {
            $content = file_get_contents($stdErrlog);
            $res->errmsg = '';
            $res->warnmsg = '';

            $warnPattern = '@(.*?)(Warning:)(\S*)\s+(.*)@m';
            $matches = [];
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
            $matches = [];
            preg_match_all($errPattern, $content, $matches);
            $numErrors = count($matches[4]);
            if ($numErrors) {
                $res->errmsg .= 'Found ' . $numErrors . ' error' . ($numErrors == 1 ? '' : 's') . ".\n";
                $res->retval = 'error';
            }
            $res->errmsg .= implode("\n", $matches[4]);

            echo static::class . ": Setting retval to " . $res->retval . PHP_EOL;
        }
        return $res->updateRetval();
    }
}
