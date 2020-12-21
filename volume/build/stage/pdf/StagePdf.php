<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

use Dmake\AbstractStage;
use Dmake\Config;
use Dmake\Dao;
use Dmake\ErrDetEntry;
use Dmake\StatEntry;
use Dmake\UtilFile;
use Dmake\UtilStage;

 /**
  * Class StagePdf
  */
class StagePdf extends AbstractStage
{
    /**
     * @var bool
     */
    public $debug = true;

    /**
     * @var array
     */
    public $config;

    /**
     * StagePdf constructor.
     */
    public function __construct()
    {
        $this->config = static::register();
    }

    public const FOUND_MISSING_MACROS = 1 << 3;
    public const FOUND_MISSING_FIGURE = 1 << 2;
    public const FOUND_MISSING_BIB    = 1 << 1;
    public const FOUND_MISSING_FILE   = 1 << 0;

    /**
     * @return array|mixed
     */
    public static function register(): array
    {
        $config = [
            // the name of the stage
            'stage' => 'pdf',
            // the name of the class
            'classname' => __CLASS__,
            // the target of makefile, most times same as stage, but
            // pdf and pdf_edge have the same target pdf
            'target' => 'pdf',
            // the hostGroup
            // pdf has worker as hostGroup, pdf_edge has worker_edge
            // therefore two different pdf environments can be used
            'hostGroup' => 'worker',
            // the name of the table where target specific results are stored
            'dbTable' => 'retval_pdf',
            // titles on statistic page
            'tableTitle' => 'pdf',
            'toolTip' => 'PDF creation.',
            // whether xml needs to be parsed
            'parseXml' => false,
            // the timeout in seconds
            'timeout' => 240,
            /* use %MAINFILEPREFIX%, if the logfile use same prefix as the main tex file */
            'destFile' => '%MAINFILEPREFIX%.pdf',
            'stdoutLog' => '%MAINFILEPREFIX%.log', // this needs to match entry in Makefile
            'stderrLog' => '%MAINFILEPREFIX%.log', // needs to match entry in Makefile
            'dependentStages' => [], // which log files need to be parsed?
            'showRetval' => [
                'unknown' => true,
                'not_qualified' => true,
                'missing_errlog' => true,
                'fatal_error' => true,
                'timeout' => true,
                'error' => true,
                'missing_macros' => true,
                'missing_figure' => true,
                'missing_bib' => true,
                'missing_file' => true,
                'warning' => true,
                'no_problems' => true
            ],
            'retvalDetail' => [
                'missing_figures' => [
                     ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'missing_bib' => [
                     ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'missing_file' => [
                     ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'missing_macros' => [
                     ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
                'error' => [
                     ['sql' => 'errmsg', 'html' => 'Error message', 'align' => 'left']
                ],
            ],
            'showTopErrors' => [
                'error' => true,
                'fatal_error' => false,
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

    public static function fillEntry(array $row): StatEntry
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
        if (isset($row['prev_retval'])) {
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
    		$se->warnmsg         = $row['warnmsg'];
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

        echo "Updating " . $this->config['dbTable'] . PHP_EOL;
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

    /**
     * @param $hostname
     * @param $entry
     * @param $childAlarmed
     * @return mixed|void
     */
    public static function parse(
        string $hostGroup,
        StatEntry $entry,
        bool $childAlarmed): bool
    {
        $directory = $entry->filename;

        $res = new static();
        $res->id = $entry->id;

        $sourceDir = UtilStage::getSourceDir(ARTICLEDIR, $directory, $hostGroup);
        $texSourcefilePrefix = $sourceDir . '/' . $entry->getSourcefilePrefix();
        $texSourcefile = $sourceDir . '/' . $entry->getSourcefile();
        $logfile = $texSourcefilePrefix.'.log';
        echo "parsing Logfile $logfile ..." . PHP_EOL;

        $resultfile = $texSourcefilePrefix.'.pdf';

        if ($childAlarmed) {
            $res->retval = 'timeout';
            $res->timeout = $res->config['timeout'];
        } elseif (!UtilFile::isFileTexfile($texSourcefile)) {
            $res->retval = 'not_qualified';
        } elseif (!is_file($logfile)) {
            $res->retval = 'missing_errlog';
        // have we created a pdf?
        } elseif (is_file($resultfile)) {
            $res->retval = 'no_problems';
        } else {
            $res->retval = 'fatal_error';
        }

		$content = file_get_contents($logfile);

		$warnPattern = '@(.*?)(Warning:)(\S*)\s+(.*)@m';
        $matches = [];
		preg_match_all($warnPattern, $content, $matches);
		if (count($matches[4])) {
            $res->retval = 'warning';
        }
		$res->warnmsg = implode("\n", $matches[4]);

		$errPattern = '@(.*?)(Error:)(\S*)\s+(.*)@m';
        $matches = [];
		preg_match_all($errPattern, $content, $matches);
		if (count($matches[4])) {
            $res->retval = 'error';
        }
		$res->errmsg = implode("\n", $matches[4]);

        // Citation undefined considered errors
		$errPattern = '@(.*?)(Warning:)(\S*)\s+(Citation.{0,100}undefined)@m';
        $matches = [];
		preg_match_all($errPattern, $content, $matches);
		if (count($matches[4])) {
            $res->retval = 'error';
        }
		$res->errmsg .= implode("\n", $matches[4]);

		// try to classify in more detail missing files
        if ($res->retval === 'error') {
            $macroSuffixes = ['sty', 'cls'];
            $figureSuffixes = ['eps', 'jpg', 'jpeg', 'png', 'pdf'];
            $bibSuffixes = ['bib'];

            $errPattern = '@(.*?)(Error: File\s)\S(.*?)\S\s(not found)@m';
            $matches = [];
            preg_match_all($errPattern, $content, $matches);
            if (count($matches[4])) {
                $filenames = $matches[3];
                $result = 0;
                foreach ($filenames as $filename) {
                    $suffix = strtolower(UtilFile::getSuffix($filename, false));
                    echo $suffix . PHP_EOL;
                    if (in_array($suffix, $macroSuffixes)) {
                        $result |= self::FOUND_MISSING_MACROS;
                    } elseif (
                        in_array($suffix, $figureSuffixes)
                        || preg_match('/figure|image/i', $filename) !== false
                    ) {
                        $result |= self::FOUND_MISSING_FIGURE;
                    } elseif (in_array($suffix, $bibSuffixes)) {
                        $result |= self::FOUND_MISSING_BIB;
                    } else {
                        $result |= self::FOUND_MISSING_FILE;
                    }
                }
                if ($result & self::FOUND_MISSING_MACROS) {
                    $res->retval = 'missing_macros';
                } elseif ($result & self::FOUND_MISSING_FIGURE) {
                    $res->retval = 'missing_figure';
                } elseif ($result & self::FOUND_MISSING_BIB) {
                    $res->retval = 'missing_bib';
                } else {
                    $res->retval = 'missing_file';
                }
            }
        }

        echo static::class . ": Setting retval to " . $res->retval . PHP_EOL;

        return $res->updateRetval();
    }

    /**
     * @param StatEntry $entry
     */
    public function parseDetail($hostGroup, StatEntry $entry): void
    {
        $directory = $entry->getFilename();
        $datestamp = date("Y-m-d H:i:s");

        $sourceDir = UtilStage::getSourceDir(ARTICLEDIR, $directory, $hostGroup);
        $texSourcefilePrefix = $sourceDir . '/' . $entry->getSourcefilePrefix();
        $texSourcefile = $sourceDir . '/' . $entry->getSourcefile();
        $logfile = $texSourcefilePrefix.'.log';

        $this->debug('Logfile: ' . $logfile);

        $content = file_get_contents($logfile);

        $err_pattern = '@^!(.*?)(Warning|Error):\s+(.*)@m';
        if (preg_match_all($err_pattern, $content, $matches)) {
            // $matches[1] = errclass
            // $matches[2] = errtype (Warning, Error)
            // $matches[3] = errmsg

            print_r($matches);

            $num = count($matches[0]);
            $this->debug($num . ' matches');

            for ($i = 0; $i < $num; $i++) {
                $ede = new ErrDetEntry($entry->getId(), $this->config['target']);
                $ede->setPos($i);
                $ede->setDateCreated($datestamp);
                // ?? does not work here
                if (isset($matches[1][$i])) {
                    $ede->setErrClass(trim($matches[1][$i]));
                } else {
                    $ede->setErrClass('');
                }
                if (isset($matches[2][$i])) {
                    $ede->setErrType($matches[2][$i]);
                } else {
                    $ede->setErrType('');
                }

                if (isset($matches[3][$i])) {
                    // try to set specific classes for special error messages
                    if (preg_match('/^File(.*)not found/', $matches[3][$i], $m)) {
                        $ede->setErrClass('missing_file');
                        $filename = trim($m[1], '\'` ');
                        $ede->setErrObject($filename);
                    }
                    $ede->setErrMsg($matches[3][$i]);
                } else {
                    $ede->setErrMsg('');
                }

                $ede->setMd5ErrMsg(md5($ede->getErrMsg()));

                $ede->save();
            }
        } else {
            echo $logfile . ': Nothing found.' . PHP_EOL;
        }
    }
}
