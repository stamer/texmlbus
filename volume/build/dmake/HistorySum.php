<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

class HistorySum
{
    public $id = 0;
    public $set = '';
    public $dateSnapshot = '';
    public $showEntry = 1;
    public $stage = '';
    public $retvalUnknown = 0;
    public $retvalNotQualified = 0;
    public $retvalMissingErrlog = 0;
    public $retvalTimeout = 0;
    public $retvalFatalError = 0;
    public $retvalMissingMacros = 0;
    public $retvalMissingFigure = 0;
    public $retvalMissingBib = 0;
    public $retvalMissingFile = 0;
    public $retvalError = 0;
    public $retvalWarning = 0;
    public $retvalNoProblems = 0;
    public $sumWarning = 0;
    public $sumError = 0;
    public $sumMacro = 0;
    public $timeout = 0;
    public $comment = '';

    function getId()
    {
        return $this->id;
    }

    function getSet()
    {
        return $this->set;
    }

    function getDateSnapshot()
    {
        return $this->dateSnapshot;
    }

    function getShowEntry()
    {
        return $this->showEntry;
    }

    function getStage()
    {
        return $this->stage;
    }

    function getRetvalUnknown()
    {
        return $this->retvalUnknown;
    }

    function getRetvalNotQualified()
    {
        return $this->retvalNotQualified;
    }

    function getRetvalMissingErrlog()
    {
        return $this->retvalMissingErrlog;
    }

    function getRetvalTimeout()
    {
        return $this->retvalTimeout;
    }

    function getRetvalFatalError()
    {
        return $this->retvalFatalError;
    }

    function getRetvalMissingMacros()
    {
        return $this->retvalMissingMacros;
    }

    function getRetvalMissingFigure()
    {
        return $this->retvalMissingFigure;
    }

    function getRetvalMissingBib()
    {
        return $this->retvalMissingBib;
    }

    function getRetvalMissingFile()
    {
        return $this->retvalMissingFile;
    }

    function getRetvalError()
    {
        return $this->retvalError;
    }

    function getRetvalWarning()
    {
        return $this->retvalWarning;
    }

    function getRetvalNoProblems()
    {
        return $this->retvalNoProblems;
    }

    function getSumWarning()
    {
        return $this->sumWarning;
    }

    function getSumError()
    {
        return $this->sumError;
    }

    function getSumMacro()
    {
        return $this->sumMacro;
    }

    function getTimeout()
    {
        return $this->timeout;
    }

    function getComment()
    {
        return $this->comment;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setSet($set)
    {
        $this->set = $set;
    }

    function setDateSnapshot($dateSnapshot)
    {
        $this->dateSnapshot = $dateSnapshot;
    }

    function setShowEntry($showEntry)
    {
        $this->showEntry = $showEntry;
    }

    function setStage($stage)
    {
        $this->stage = $stage;
    }

    function setRetvalUnknown($retvalUnknown)
    {
        $this->retvalUnknown = $retvalUnknown;
    }

    function setRetvalNotQualified($retvalNotQualified)
    {
        $this->retvalNotQualified = $retvalNotQualified;
    }

    function setRetvalMissingErrlog($retvalMissingErrlog)
    {
        $this->retvalMissingErrlog = $retvalMissingErrlog;
    }

    function setRetvalTimeout($retvalTimeout)
    {
        $this->retvalTimeout = $retvalTimeout;
    }

    function setRetvalFatalError($retvalFatalError)
    {
        $this->retvalFatalError = $retvalFatalError;
    }

    function setRetvalMissingMacros($retvalMissingMacros)
    {
        $this->retvalMissingMacros = $retvalMissingMacros;
    }

    function setRetvalMissingFigure($retvalMissingFigure)
    {
        $this->retvalMissingFigure = $retvalMissingFigure;
    }

    function setRetvalMissingBib($retvalMissingBib)
    {
        $this->retvalMissingBib = $retvalMissingBib;
    }

    function setRetvalMissingFile($retvalMissingFile)
    {
        $this->retvalMissingFile = $retvalMissingFile;
    }

    function setRetvalError($retvalError)
    {
        $this->retvalError = $retvalError;
    }

    function setRetvalWarning($retvalWarning)
    {
        $this->retvalWarning = $retvalWarning;
    }

    function setRetvalNoProblems($retvalNoProblems)
    {
        $this->retvalNoProblems = $retvalNoProblems;
    }

    function setSumWarning($sumWarning)
    {
        $this->sumWarning = $sumWarning;
    }

    function setSumError($sumError)
    {
        $this->sumError = $sumError;
    }

    function setSumMacro($sumMacro)
    {
        $this->sumMacro = $sumMacro;
    }

    function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function save()
    {
        $cfg = Config::getConfig();
        $dao = Dao::getInstance();

        $query = '
			INSERT INTO
				history_sum
			SET
				id                      = 0,
				`set`                   = :i_set,
				date_snapshot           = :i_date_snapshot,
				show_entry              = :i_show_entry,
				target                  = :i_target,
				retval_unknown          = :i_retval_unknown,
				retval_not_qualified    = :i_retval_not_qualified,
				retval_missing_errlog   = :i_retval_missing_errlog,
				retval_timeout          = :i_retval_timeout,
				retval_fatal_error      = :i_retval_fatal_error,
				retval_missing_macros   = :i_retval_missing_macros,
				retval_missing_figure   = :i_retval_missing_figure,
				retval_missing_bib      = :i_retval_missing_bib,
				retval_missing_file     = :i_retval_missing_file,
				retval_error            = :i_retval_error,
				retval_warning          = :i_retval_warning,
				retval_no_problems      = :i_retval_no_problems,
                sum_warning             = :i_sum_warning,
				sum_error               = :i_sum_error,
                sum_macro               = :i_sum_macro,
                comment                 = :i_comment
            ON DUPLICATE KEY UPDATE
				`set`                   = :u_set,
				date_snapshot           = :u_date_snapshot,
				show_entry              = :u_show_entry,
				target                  = :u_target,
				retval_unknown          = :u_retval_unknown,
				retval_not_qualified    = :u_retval_not_qualified,
				retval_missing_errlog   = :u_retval_missing_errlog,
				retval_timeout          = :u_retval_timeout,
				retval_fatal_error      = :u_retval_fatal_error,
				retval_missing_macros   = :u_retval_missing_macros,
				retval_missing_figure   = :u_retval_missing_figure,
				retval_missing_bib      = :u_retval_missing_bib,
				retval_missing_file     = :u_retval_missing_file,
				retval_error            = :u_retval_error,
				retval_warning          = :u_retval_warning,
				retval_no_problems      = :u_retval_no_problems,
                sum_warning             = :u_sum_warning,
				sum_error               = :u_sum_error,
                sum_macro               = :u_sum_macro,
                comment                 = :u_comment';

        $stmt = $dao->prepare($query);


        $stmt->bindValue(':i_set', $this->getSet()['set']);
        $stmt->bindValue(':i_date_snapshot', $this->getDateSnapshot());
        $stmt->bindValue(':i_show_entry', $this->getShowEntry());
        $stmt->bindValue(':i_target', $this->getStage());
        $stmt->bindValue(':i_retval_unknown', $this->getRetvalUnknown());
        $stmt->bindValue(':i_retval_not_qualified', $this->getRetvalNotQualified());
        $stmt->bindValue(':i_retval_missing_errlog', $this->getRetvalMissingErrlog());
        $stmt->bindValue(':i_retval_timeout', $this->getRetvalTimeout());
        $stmt->bindValue(':i_retval_fatal_error', $this->getRetvalFatalError());
        $stmt->bindValue(':i_retval_missing_macros', $this->getRetvalMissingMacros());
        $stmt->bindValue(':i_retval_missing_figure', $this->getRetvalMissingFigure());
        $stmt->bindValue(':i_retval_missing_bib', $this->getRetvalMissingBib());
        $stmt->bindValue(':i_retval_missing_file', $this->getRetvalMissingFile());
        $stmt->bindValue(':i_retval_error', $this->getRetvalError());
        $stmt->bindValue(':i_retval_warning', $this->getRetvalWarning());
        $stmt->bindValue(':i_retval_no_problems', $this->getRetvalNoProblems());
        $stmt->bindValue(':i_sum_warning', $this->getSumWarning());
        $stmt->bindValue(':i_sum_error', $this->getSumError());
        $stmt->bindValue(':i_sum_macro', $this->getSumMacro());
        $stmt->bindValue(':i_comment', $this->getComment());
        $stmt->bindValue(':u_set', $this->getSet()['set']);
        $stmt->bindValue(':u_date_snapshot', $this->getDateSnapshot());
        $stmt->bindValue(':u_show_entry', $this->getShowEntry());
        $stmt->bindValue(':u_target', $this->getStage());
        $stmt->bindValue(':u_retval_unknown', $this->getRetvalUnknown());
        $stmt->bindValue(':u_retval_not_qualified', $this->getRetvalNotQualified());
        $stmt->bindValue(':u_retval_missing_errlog', $this->getRetvalMissingErrlog());
        $stmt->bindValue(':u_retval_timeout', $this->getRetvalTimeout());
        $stmt->bindValue(':u_retval_fatal_error', $this->getRetvalFatalError());
        $stmt->bindValue(':u_retval_missing_macros', $this->getRetvalMissingMacros());
        $stmt->bindValue(':u_retval_missing_figure', $this->getRetvalMissingFigure());
        $stmt->bindValue(':u_retval_missing_bib', $this->getRetvalMissingBib());
        $stmt->bindValue(':u_retval_missing_file', $this->getRetvalMissingFile());
        $stmt->bindValue(':u_retval_error', $this->getRetvalError());
        $stmt->bindValue(':u_retval_warning', $this->getRetvalWarning());
        $stmt->bindValue(':u_retval_no_problems', $this->getRetvalNoProblems());
        $stmt->bindValue(':u_sum_warning', $this->getSumWarning());
        $stmt->bindValue(':u_sum_error', $this->getSumError());
        $stmt->bindValue(':u_sum_macro', $this->getSumMacro());
        $stmt->bindValue(':u_comment', $this->getComment());

        $stmt->execute();
    }

    public static function fillEntry($row)
    {
        $hs = new HistorySum();
        if (isset($row['id'])) {
            $hs->setId($row['id']);
        }
        if (isset($row['date_snapshot'])) {
            $hs->setDateSnapshot($row['date_snapshot']);
        }
        if (isset($row['show_entry'])) {
            $hs->setShowEntry($row['show_entry']);
        }
        if (isset($row['target'])) {
            $hs->setStage($row['target']);
        }
        if (isset($row['retval_unknown'])) {
            $hs->setRetvalUnknown($row['retval_unknown']);
        }
        if (isset($row['retval_not_qualified'])) {
            $hs->setRetvalNotQualified($row['retval_not_qualified']);
        }
        if (isset($row['retval_missing_errlog'])) {
            $hs->setRetvalMissingErrlog($row['retval_missing_errlog']);
        }
        if (isset($row['retval_timeout'])) {
            $hs->setRetvalTimeout($row['retval_timeout']);
        }
        if (isset($row['retval_fatal_error'])) {
            $hs->setRetvalFatalError($row['retval_fatal_error']);
        }
        if (isset($row['retval_missing_macros'])) {
            $hs->setRetvalMissingMacros($row['retval_missing_macros']);
        }
        if (isset($row['retval_missing_figure'])) {
            $hs->setRetvalMissingFigure($row['retval_missing_figure']);
        }
        if (isset($row['retval_missing_bib'])) {
            $hs->setRetvalMissingBib($row['retval_missing_bib']);
        }
        if (isset($row['retval_missing_file'])) {
            $hs->setRetvalMissingFile($row['retval_missing_file']);
        }
        if (isset($row['retval_error'])) {
            $hs->setRetvalError($row['retval_error']);
        }
        if (isset($row['retval_warning'])) {
            $hs->setRetvalWarning($row['retval_warning']);
        }
        if (isset($row['retval_no_problems'])) {
            $hs->setRetvalNoProblems($row['retval_no_problems']);
        }
        if (isset($row['sum_warning'])) {
            $hs->setSumError($row['sum_warning']);
        }
        if (isset($row['sum_error'])) {
            $hs->setSumMacro($row['sum_error']);
        }
        if (isset($row['sum_macro'])) {
            $hs->setSumMacro($row['sum_macro']);
        }
        if (isset($row['comment'])) {
            $hs->setComment($row['comment']);
        }
        return $hs;
    }

    /**
     * @param $stat
     * @param $stage
     * @return HistorySum
     */
    public static function adaptFromStat($stat, $stage)
    {
        $hs = new HistorySum();
        $hs->setId(0);

        $hs->setDateSnapshot('current');
        $hs->setShowEntry(1);
        $hs->setStage($stage);
        if (isset($stat['unknown'])) {
            $hs->setRetvalUnknown($stat['unknown']);
        }
        if (isset($stat['not_qualified'])) {
            $hs->setRetvalNotQualified($stat['not_qualified']);
        }
        if (isset($stat['missing_errlog'])) {
            $hs->setRetvalMissingErrlog($stat['missing_errlog']);
        }
        if (isset($stat['timeout'])) {
            $hs->setRetvalTimeout($stat['timeout']);
        }
        if (isset($stat['fatal_error'])) {
            $hs->setRetvalFatalError($stat['fatal_error']);
        }
        if (isset($stat['missing_macros'])) {
            $hs->setRetvalMissingMacros($stat['missing_macros']);
        }
        if (isset($stat['missing_figure'])) {
            $hs->setRetvalMissingFigure($stat['missing_figure']);
        }
        if (isset($stat['missing_bib'])) {
            $hs->setRetvalMissingBib($stat['missing_bib']);
        }
        if (isset($stat['missing_file'])) {
            $hs->setRetvalMissingFile($stat['missing_file']);
        }
        if (isset($stat['error'])) {
            $hs->setRetvalError($stat['error']);
        }
        if (isset($stat['warning'])) {
            $hs->setRetvalWarning($stat['warning']);
        }
        if (isset($stat['no_problems'])) {
            $hs->setRetvalNoProblems($stat['no_problems']);
        }
        $hs->setSumError(0);
        $hs->setSumMacro(0);
        $hs->setSumMacro(0);
        $hs->setComment('');

        return $hs;
    }

    /**
     *
     * @param string $set
     * @param string $stage
     * @return StatEntry
     */
    public static function getBySetStage($set, $stage)
    {
        $dao = Dao::getInstance();

        if ($set != '') {
            $query = "
                SELECT
                    *
                FROM
                    history_sum
                WHERE
                    `set` = :set
                    AND target = :target
                    AND show_entry = 1
                ORDER BY
                    date_snapshot";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':set', $set);
            $stmt->bindValue(':target', $stage);

            $stmt->execute();
        } else {
            $query = "
                SELECT
                    '' as `set`,
                    date_snapshot,
                    show_entry,
                    target,
                    sum(retval_unknown) as retval_unknown,
                    sum(retval_not_qualified) as retval_not_qualified,
                    sum(retval_missing_errlog) as retval_missing_errlog,
                    sum(retval_timeout) as retval_timeout,
                    sum(retval_fatal_error) as retval_fatal_error,
                    sum(retval_missing_macros) as retval_missing_macros,
                    sum(retval_missing_figure) as retval_missing_figure,
                    sum(retval_missing_bib) as retval_missing_bib,
                    sum(retval_missing_file) as retval_missing_file,
                    sum(retval_error) as retval_error,
                    sum(retval_warning) as retval_warning,
                    sum(retval_no_problems) as retval_no_problems,
                    sum(sum_warning) as sum_warning,
                    sum(sum_error) as sum_error,
                    sum(sum_macro) as sum_macro,
                    '' as comment
                FROM
                    history_sum
                WHERE
                    target = :target
                    AND show_entry = 1
                GROUP BY
                    date(date_snapshot)
                ORDER BY
                    date_snapshot";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':target', $stage);

            $stmt->execute();
        }

        $obj = array();
        while ($row = $stmt->fetch()) {
            $obj[] = self::fillEntry($row);
        }
        return $obj;
    }
}
