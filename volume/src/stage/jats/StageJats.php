<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle post log files
 *
 */

use Dmake\AbstractStage;
use Dmake\Config;
use Dmake\Dao;
use Dmake\StatEntry;
use Dmake\UtilFile;
use Dmake\UtilStage;

class StageJats extends AbstractStage
{
    public function __construct()
    {
        $this->config = static::register();
    }

    public static function register(): array
    {
        $cfg = Config::getConfig();

        $stage = 'jats';
        $config = [
            'stage' => $stage,
            'classname' => __CLASS__,
            'target' => $stage,
            'hostGroup' => 'worker',
            'command' => 'set -o pipefail; '
                . $cfg->app->make . ' -f Makefile',
            'parseXml' => true,
            'timeout' => 1200,
            'dbTable' => 'retval_' . $stage,
            'tableTitle' => 'plain jats',
            'toolTip' => 'Plain Jats conversion.',
            'destFile' => '%MAINFILEPREFIX%.jats.xml',
            'stdoutLog' => $stage . '.stdout.log', // this needs to match entry in Makefile
            'stderrLog' => $stage . '.stderr.log', // needs to match entry in Makefile
            'makeLog' => 'make_' . $stage . '.log',
            'dependentStages' => ['xml'], // which log files need to be parsed?
            'showRetval' => [
                'unknown' => false,
                'not_qualified' => false,
                'missing_errlog' => true,
                'fatal_error' => true,
                'timeout' => true,
                'error' => false,
                'missing_macros' => false,
                'missing_figure' => true,
                'missing_bib' => true,
                'missing_file' => true,
                'warning' => false,
                'no_problems' => true
            ],
            'showTopErrors' => [
                'error' => true,
                'fatal_error' => true,
                'missing_macros' => false,
            ],
            'showDetailErrors' => [
                'error' => false,
            ],
        ];

        return $config;
    }

    public function save(): bool
	{
        $cfg = Config::getConfig();
		$cfg->now->datestamp = date("Y-m-d H:i:s");

        $dao = Dao::getInstance();

		$query = /** @lang ignore */
            '
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
            $se->id             = $row['id'];
        }
        if (isset($row['date_created'])) {
    		$se->date_created   = $row['date_created'];
        }
        if (isset($row['date_modified'])) {
    		$se->date_created   = $row['date_modified'];
        }
        if (isset($row['retval'])) {
    		$se->retval         = $row['retval'];
        }
        if (isset($row['retval'])) {
    		$se->prevRetval     = $row['prev_retval'];
        }
        if (isset($row['timeout'])) {
    		$se->timeout        = $row['timeout'];
        }
        if (isset($row['num_warning'])) {
    		$se->num_warning    = $row['num_warning'];
        }
        if (isset($row['num_error'])) {
    		$se->num_error      = $row['num_error'];
        }
        if (isset($row['num_macro'])) {
    		$se->num_macro      = $row['num_macro'];
        }
        if (isset($row['missing_macros'])) {
            $se->missing_macros = $row['missing_macros'];
        }
        if (isset($row['warnmsg'])) {
    		$se->warnmsg        = $row['warnmsg'];
        }
        if (isset($row['errmsg'])) {
    		$se->errmsg         = $row['errmsg'];
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
        $texSourcefile = $sourceDir . '/' . $entry->getSourcefile();
        $stderrlog = $sourceDir . '/' . $res->config['stderrLog'];
        $makelog = $sourceDir . '/' . $res->config['makeLog'];

        if ($childAlarmed) {
            $res->retval = 'timeout';
            $res->timeout = $res->config->timeout;
        } elseif (!UtilFile::isFileTexfile($texSourcefile)) {
            $res->retval = 'not_qualified';
        } elseif (!is_file($stderrlog)) {
            // error status returned?
            if ($status) {
                $res->retval = 'fatal_error';
                $res->errmsg = static::parseMakelog($makelog);
            } else {
                $res->retval = 'missing_errlog';
            }
        } else {
            $content = file_get_contents($stderrlog);
            if ($status
                && ($content === ''
                    || strpos($content, 'processing finished') === false)
            ) {
                $res->retval = 'fatal_error';
                $res->errmsg = static::parseMakelog($makelog);
            } else {
                $matches = [];

                $fatal_pattern = '@(.*?)(^Fatal:)(\S*)\s+(.*)@m';
                preg_match($fatal_pattern, $content, $matches);
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    print_r($matches);
                }

                // this will only be set if we have found a fatal error
                if (isset($matches[2])) {
                    $res->retval = 'fatal_error';
                    $res->errmsg = $matches[4];
                }

                $warning_pattern = '@(.*?)(^Postprocessing complete: )(.*?)(\d*)(\s*)(warning)@m';
                preg_match($warning_pattern, $content, $matches);
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    print_r($matches);
                }

                if (isset($matches[6]) && $matches[6] == 'warning') {
                    $res->num_warning = $matches[4];
                    $res->retval = 'warning';
                }

                $error_pattern = '@(.*?)(^Postprocessing complete: )(.*?)(\d*)(\s*)(error)@m';
                preg_match($error_pattern, $content, $matches);
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    print_r($matches);
                }

                if (isset($matches[6]) && $matches[6] == 'error') {
                    $res->num_error = (int)$matches[4];
                    if ($res->num_error > 0) {
                        $res->retval = 'error';
                    } else {
                        $res->retval = 'no_problems';
                    }
                }

                $macro_pattern = '@(.*?)(^Postprocessing complete: )(.*?)(\d*)(\s*)(undefined macro)(s?)(.*)@m';
                preg_match($macro_pattern, $content, $matches);
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    print_r($matches);
                }

                if (isset($matches[6]) && $matches[6] == 'undefined macro') {
                    $res->num_macro = $matches[4];
                    $res->missing_macros = $matches[8];
                    $res->retval = 'missing_macros';
                }

                $success_pattern = '@(.*?)(^Postprocessing complete: No obvious problems)@m';
                preg_match($success_pattern, $content, $matches);
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    print_r($matches);
                }

                // this will only be set if
                if (isset($matches[2])) {
                    $res->retval = 'no_problems';
                }
            }
        }
        return $res->updateRetval();
    }
}
