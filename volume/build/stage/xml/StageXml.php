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

class StageXml extends AbstractStage
{
	public $num_xmarg = 0;
	public $ok_xmarg = 0;
	public $num_xmath = 0;
	public $ok_xmath = 0;

    public function __construct()
    {
        $this->config = static::register();
        $this->debug = true;
    }

    public static function register(): array
    {
        $stage = 'xml';
        $config = [
            'stage' => $stage,
            'classname' => __CLASS__,
            'target' => $stage,
            'hostGroup' => 'worker',
            'dbTable' => 'retval_' . $stage,
            'tableTitle' => $stage,
            'toolTip' => 'Latexml XML intermediate format creation.',
            'parseXml' => true,
            'timeout' => 1200,
            'destFile' => '%MAINFILEPREFIX%.tex.xml',
            'stdoutLog' => 'stdout.log', // this needs to match entry in Makefile
            'stderrLog' => 'stderr.log', // needs to match entry in Makefile
            'makeLog' => 'make_' . $stage . '.log',
            'dependentStages' => [],
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
                'missing_macros' => [
                    ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                    ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                    ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                    ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                    ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                    ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                    ['sql' => 'missing_macros', 'html' => 'Missing macros', 'align' => 'left'],
                ],
                'warning' => [
                    ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                    ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                    ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                    ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                    ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                    ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                ],
                'error' => [
                    ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                    ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                    ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                    ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                    ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                    ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                ],
                'fatal_error' => [
                    ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                    ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                    ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                    ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                    ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                    ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                ],
                'no_problems' => [
                    ['sql' => 'num_warning', 'html' => 'num<br />warning', 'align' => 'right'],
                    ['sql' => 'num_error', 'html' => 'num<br />error', 'align' => 'right'],
                    ['sql' => 'num_xmarg', 'html' => 'num<br />xmarg', 'align' => 'right'],
                    ['sql' => 'ok_xmarg', 'html' => 'ok<br />xmarg', 'align' => 'right'],
                    ['sql' => 'num_xmath', 'html' => 'num<br />xmath', 'align' => 'right'],
                    ['sql' => 'ok_xmath', 'html' => 'ok<br />xmath', 'align' => 'right'],
                ],
            ],
            'showTopErrors' => [
                'error' => true,
                'fatal_error' => true,
                'missing_macros' => true,
            ],
            'showDetailErrors' => [
                'error' => true,
            ],
        ];

        return $config;
    }

	public function save(): bool
	{
        $cfg = Config::getConfig();
		$cfg->now->datestamp = date("Y-m-d H:i:s");

        $dao = Dao::getInstance();

		$query = '
			REPLACE	INTO
				'.$this->config['dbTable'].'
			SET
				id =            = :id,
				date_created	= :date_created,
				date_modified	= :date_modified,
				prev_retval     = retval,
				retval  		= :retval,
				timeout			= :timeout,
				num_xmarg		= :num_xmarg,
				ok_xmarg		= :ok_xmarg,
				num_xmath		= :num_xmath,
				ok_xmath		= :ok_xmath,
				num_warning		= :num_warning,
				num_error		= :num_error,
				num_macro		= :num_macro,
				missing_macros	= :missing_macros,
				warnmsg			= :warnmsg,
				errmsg			= :errmsg';

		$stmt = $dao->prepare($query);

		$stmt->bindValue('date_created', $cfg->now->datestamp);
		$stmt->bindValue('date_modified', $cfg->now->datestamp);
		$stmt->bindValue('retval', $this->retval);
		$stmt->bindValue('timeout',	$this->timeout);
		$stmt->bindValue('num_xmarg', $this->num_xmarg);
		$stmt->bindValue('ok_xmarg', $this->ok_xmarg);
		$stmt->bindValue('num_xmath', $this->num_xmath);
		$stmt->bindValue('ok_xmath', $this->ok_xmath);
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
            $se->date_modified  = $row['date_modified'];
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
        if (isset($row['num_xmarg'])) {
    		$se->num_xmarg      = $row['num_xmarg'];
        }
        if (isset($row['ok_xmarg'])) {
    		$se->ok_xmarg       = $row['ok_xmarg'];
        }
        if (isset($row['num_xmath'])) {
    		$se->num_xmath      = $row['num_xmath'];
        }
        if (isset($row['ok_xmath'])) {
    		$se->ok_xmath       = $row['ok_xmath'];
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

		$query = '
			INSERT INTO
				'.$this->config['dbTable'].'
			SET
				id              = :id,
				date_modified	= :i_date_modified,
				retval      	= :i_retval,
                num_xmarg       = :i_num_xmarg,
                ok_xmarg        = :i_ok_xmarg,
                num_xmath       = :i_num_xmath,
                ok_xmath        = :i_ok_xmath,
                num_warning     = :i_num_warning,
                num_error       = :i_num_error,
                num_macro       = :i_num_macro,
                missing_macros  = :i_missing_macros,
                warnmsg         = :i_warnmsg,
                errmsg          = :i_errmsg
            ON DUPLICATE KEY UPDATE
				date_modified	= :u_date_modified,
				prev_retval     = retval,
				retval      	= :u_retval,
                num_xmarg       = :u_num_xmarg,
                ok_xmarg        = :u_ok_xmarg,
                num_xmath       = :u_num_xmath,
                ok_xmath        = :u_ok_xmath,
                num_warning     = :u_num_warning,
                num_error       = :u_num_error,
                num_macro       = :u_num_macro,
                missing_macros  = :u_missing_macros,
                warnmsg         = :u_warnmsg,
                errmsg          = :u_errmsg';

        $stmt = $dao->prepare($query);
		$stmt->bindValue('id', $this->id);
		$stmt->bindValue('i_date_modified', $cfg->now->datestamp);
		$stmt->bindValue('u_date_modified', $cfg->now->datestamp);
        $stmt->bindValue('i_retval', $this->retval);
        $stmt->bindValue('u_retval', $this->retval);
		$stmt->bindValue('i_num_xmarg', $this->num_xmarg);
		$stmt->bindValue('u_num_xmarg', $this->num_xmarg);
		$stmt->bindValue('i_ok_xmarg', $this->ok_xmarg);
		$stmt->bindValue('u_ok_xmarg', $this->ok_xmarg);
		$stmt->bindValue('i_num_xmath', $this->num_xmath);
		$stmt->bindValue('u_num_xmath', $this->num_xmath);
		$stmt->bindValue('i_ok_xmath', $this->ok_xmath);
		$stmt->bindValue('u_ok_xmath', $this->ok_xmath);
		$stmt->bindValue('i_num_warning', $this->num_warning);
		$stmt->bindValue('u_num_warning', $this->num_warning);
		$stmt->bindValue('i_num_error', $this->num_error);
		$stmt->bindValue('u_num_error', $this->num_error);
		$stmt->bindValue('i_num_macro', $this->num_macro);
		$stmt->bindValue('u_num_macro', $this->num_macro);
        $stmt->bindValue('i_missing_macros', $this->missing_macros);
        $stmt->bindValue('u_missing_macros', $this->missing_macros);
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
            $res->timeout = $res->config['timeout'];
        } elseif (!UtilFile::isFileTexfile($texSourcefile)) {
            $res->retval = 'not_qualified';
        } elseif (!is_file($stderrlog)) {
            if ($status) {
                $res->retval = 'fatal_error';
                $res->errmsg = static::parseMakelog($makelog);
            } else {
                $res->retval = 'missing_errlog';
            }
        } else {
            $content = file_get_contents($stderrlog);
            if ($content === ''
                && $status
            ) {
                $res->retval = 'fatal_error';
                $res->errmsg = static::parseMakelog($makelog);
            } else {

                // matches[3] ==> num_xmarg
                // matches[4] ==> ok_xmarg
                $xmarg_pattern = '@(.*?)(^   XMArg: )(\d+)/(\d+)@m';
                $matches = [];
                preg_match($xmarg_pattern, $content, $matches);
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    print_r($matches);
                }

                if (isset($matches[3])) {
                    $res->num_xmarg = $matches[3];
                }
                if (isset($matches[4])) {
                    $res->ok_xmarg = $matches[4];
                }

                // matches[3] ==> num_xmath
                // matches[4] ==> ok_xmath
                $xmath_pattern = '@(.*?)(^   XMath: )(\d+)/(\d+)@m';
                preg_match($xmath_pattern, $content, $matches);

                if (isset($matches[3])) {
                    $res->num_xmath = $matches[3];
                }
                if (isset($matches[4])) {
                    $res->ok_xmath = $matches[4];
                }

                $fatal_pattern = '@(.*?)(^Fatal:)(\S*)\s+(.*)@m';
                preg_match($fatal_pattern, $content, $matches);
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    print_r($matches);
                }

                // this will only be set if we have found a fatal error
                if (isset($matches[2])) {
                     $res->retval = 'fatal_error';
                     $res->errmsg = $matches[4];
                } else {
                    $warning_pattern = '@(.*?)(^Conversion complete: )(.*?)(\d*)(\s*)(warning)@m';
                    preg_match($warning_pattern, $content, $matches);
                    if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                        print_r($matches);
                    }

                    if (isset($matches[6]) && $matches[6] == 'warning') {
                        $res->num_warning = $matches[4];
                        $res->retval = 'warning';
                    }

                    $error_pattern = '@(.*?)(^Conversion complete: )(.*?)(\d*)(\s*)(error)@m';
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

                    $macro_pattern = '@(.*?)(^Conversion complete: )(.*?)(\d*)(\s*)(undefined macro)(s?)(.*)@m';
                    preg_match($macro_pattern, $content, $matches);
                    if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                        print_r($matches);
                    }

                    if (isset($matches[6]) && $matches[6] == 'undefined macro') {
                        $res->num_macro = $matches[4];
                        $res->missing_macros = $matches[8];
                        $res->retval = 'missing_macros';
                    }

                    $success_pattern = '@(.*?)(^Conversion complete: No obvious problems)@m';
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
        }

        return $res->updateRetval();
    }

    public function parseDetail(
        string $hostGroup,
        StatEntry $entry): void
    {
        $directory = $entry->getFilename();
        $datestamp = date("Y-m-d H:i:s");

        $stderrlog = ARTICLEDIR.'/'.$directory.'/'.$this->config['stderrLog'];

        $this->debug($stderrlog);

        $content = file_get_contents($stderrlog);

        $err_pattern = '/^(Error|Warning):(.*?):(.*)$/m';
        if (preg_match_all($err_pattern, $content, $matches)) {
            // $matches[1] = errclass
            // $matches[2] = errtype
            // $matches[3] = errmsg

            print_r($matches);

            $num = count($matches[0]);
            for ($i = 0; $i <= $num; $i++) {
                $ede = new ErrDetEntry($entry->getId(), $this->config['target']);
                $ede->setPos($i);
                $ede->setDateCreated($datestamp);
                // ?? does not work here
                if (isset($matches[1][$i])) {
                    $ede->setErrClass($matches[1][$i]);
                } else {
                    $ede->setErrClass('');
                }
                if (isset($matches[2][$i])) {
                    $ede->setErrType($matches[2][$i]);
                } else {
                    $ede->setErrType('');
                }

                if (isset($matches[3][$i])) {
                    $ede->setErrMsg($matches[3][$i]);
                } else {
                    $ede->setErrMsg('');
                }

                $ede->setMd5ErrMsg(md5($ede->getErrMsg()));

                $ede->save();
            }
        } else {
            echo $stderrlog . ': Nothing found.' . PHP_EOL;
        }
    }
}
