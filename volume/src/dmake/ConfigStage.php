<?php
/**
 * MIT License
 * (c) 2017 - 2019 Heinrich Stamerjohanns
 *
 * Stage Interface
 */

namespace Dmake;

class ConfigStage
{
    /**
     * the name of the stage
     * @var string
     */
    protected $stage;
    //'stage' => 'pdf',

    /**
     * the name of the class
     * @var string
     */
    protected $classname;
    //'classname' => __CLASS__,

    /**
     * the target of makefile, most times same as stage, but
     * pdf and pdf_edge have the same target pdf
     * @var string
     */
    protected $target;
    //'target' => 'pdf',

    /**
     * the hostGroup
     * pdf has worker as hostGroup, pdf_edge has worker_edge
     * therefore two different pdf environments can be used
     * @var string
     */
    protected $hostGroup;
    //'hostGroup' => 'worker',
    // the name of the table where target specific results are stored

    /**
     * @var string
     */
    protected $command;
    /**
     * dbTable
     * @var string
     */
    protected $dbTable;
    //'dbTable' => 'retval_pdf',

    /**
     * titles on statistic page
     * @var string
     */
    protected $tableTitle;
    // 'tableTitle' => 'pdf',

    /**
     * @var string
     */
    protected $toolTip;
    //'toolTip' => 'PDF creation.',

    /**
     * whether xml needs to be parsed
     * @var string
     */
    protected $parseXml;
    //'parseXml' => false,

    /**
     * the timeout in seconds
     * @var int
     */
    protected $timeout;
    //'timeout' => 240,

    /**
     * use %MAINFILEPREFIX%, if the logfile use same prefix as the main tex file
     * @var string
     */
    protected $destFile;
    //'destFile' => '%MAINFILEPREFIX%.pdf',

    /**
     * @var string
     */
    protected $stdOutLog;
    //'stdoutLog' => '%MAINFILEPREFIX%.log', // this needs to match entry in Makefile

    /**
     * @var string
     */
    protected $stdErrLog;
    //'stdErrLog' => '%MAINFILEPREFIX%.log', // needs to match entry in Makefile

    /**
     * @var string
     */
    protected $makeLog;

    /**
     * @var array
     */
    protected $dependentStages = []; // which log files need to be parsed?

    /**
     * @var array
     */
    protected $showRetval;
    /*
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
    */

    /**
     * @var array
     */
    protected $retvalDetail;
    /*
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
    */

    /**
     * @var array
     */
    protected $showTopErrors;
    /**
     'showTopErrors' => [
        'error' => true,
        'fatal_error' => false,
        'missing_macros' => false,
    ],
    */

    /**
     * @var array
     */
    protected $showDetailErrors;

    /**
     * @return string
     */
    public function getStage(): string
    {
        return $this->stage;
    }

    /**
     * @param string $stage
     * @return ConfigStage
     */
    public function setStage(string $stage): ConfigStage
    {
        $this->stage = $stage;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassname(): string
    {
        return $this->classname;
    }

    /**
     * @param string $classname
     * @return ConfigStage
     */
    public function setClassname(string $classname): ConfigStage
    {
        $this->classname = $classname;
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     * @return ConfigStage
     */
    public function setTarget(string $target): ConfigStage
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return string
     */
    public function getHostGroup(): string
    {
        return $this->hostGroup;
    }

    /**
     * @param string $hostGroup
     * @return ConfigStage
     */
    public function setHostGroup(string $hostGroup): ConfigStage
    {
        $this->hostGroup = $hostGroup;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return ConfigStage
     */
    public function setCommand(string $command): ConfigStage
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return string
     */
    public function getDbTable(): string
    {
        return $this->dbTable;
    }

    /**
     * @param string $dbTable
     * @return ConfigStage
     */
    public function setDbTable(string $dbTable): ConfigStage
    {
        $this->dbTable = $dbTable;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableTitle(): string
    {
        return $this->tableTitle;
    }

    /**
     * @param string $tableTitle
     * @return ConfigStage
     */
    public function setTableTitle(string $tableTitle): ConfigStage
    {
        $this->tableTitle = $tableTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getToolTip(): string
    {
        return $this->toolTip;
    }

    /**
     * @param string $toolTip
     * @return ConfigStage
     */
    public function setToolTip(string $toolTip): ConfigStage
    {
        $this->toolTip = $toolTip;
        return $this;
    }

    /**
     * @return string
     */
    public function getParseXml(): string
    {
        return $this->parseXml;
    }

    /**
     * @param string $parseXml
     * @return ConfigStage
     */
    public function setParseXml(string $parseXml): ConfigStage
    {
        $this->parseXml = $parseXml;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return ConfigStage
     */
    public function setTimeout(int $timeout): ConfigStage
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestFile(): string
    {
        return $this->destFile;
    }

    /**
     * @param string $destFile
     * @return ConfigStage
     */
    public function setDestFile(string $destFile): ConfigStage
    {
        $this->destFile = $destFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getStdOutLog(): string
    {
        return $this->stdOutLog;
    }

    /**
     * @param string $stdOutLog
     * @return ConfigStage
     */
    public function setStdOutLog(string $stdOutLog): ConfigStage
    {
        $this->stdOutLog = $stdOutLog;
        return $this;
    }

    /**
     * @return string
     */
    public function getStdErrLog(): string
    {
        return $this->stdErrLog;
    }

    /**
     * @param string $stdErrLog
     * @return ConfigStage
     */
    public function setStdErrLog(string $stdErrLog): ConfigStage
    {
        $this->stdErrLog = $stdErrLog;
        return $this;
    }

    /**
     * @return string
     */
    public function getMakeLog(): string
    {
        return $this->makeLog;
    }

    /**
     * @param string $makeLog
     * @return ConfigStage
     */
    public function setMakeLog(string $makeLog): ConfigStage
    {
        $this->makeLog = $makeLog;
        return $this;
    }

    /**
     * @return array
     */
    public function getDependentStages(): array
    {
        return $this->dependentStages;
    }

    /**
     * @param array $dependentStages
     * @return ConfigStage
     */
    public function setDependentStages(array $dependentStages): ConfigStage
    {
        $this->dependentStages = $dependentStages;
        return $this;
    }

    /**
     * @return array
     */
    public function getShowRetval(): array
    {
        return $this->showRetval;
    }

    /**
     * @param array $showRetval
     * @return ConfigStage
     */
    public function setShowRetval(array $showRetval): ConfigStage
    {
        $this->showRetval = $showRetval;
        return $this;
    }

    /**
     * @return array
     */
    public function getRetvalDetail(): array
    {
        return $this->retvalDetail;
    }

    /**
     * @param array $retvalDetail
     * @return ConfigStage
     */
    public function setRetvalDetail(array $retvalDetail): ConfigStage
    {
        $this->retvalDetail = $retvalDetail;
        return $this;
    }

    /**
     * @return array
     */
    public function getShowTopErrors(): array
    {
        return $this->showTopErrors;
    }

    /**
     * @param array $showTopErrors
     * @return ConfigStage
     */
    public function setShowTopErrors(array $showTopErrors): ConfigStage
    {
        $this->showTopErrors = $showTopErrors;
        return $this;
    }

    /**
     * @return array
     */
    public function getShowDetailErrors(): array
    {
        return $this->showDetailErrors;
    }

    /**
     * @param array $showDetailErrors
     * @return ConfigStage
     */
    public function setShowDetailErrors(array $showDetailErrors): ConfigStage
    {
        $this->showDetailErrors = $showDetailErrors;
        return $this;
    }
    /**
    'showDetailErrors' => [
        'error' => false,
    ],
    */
    public function toArray(){
        return call_user_func('get_object_vars', $this);
    }
}
