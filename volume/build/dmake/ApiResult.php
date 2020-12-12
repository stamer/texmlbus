<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * The result of an Api Call
 *
 */

namespace Dmake;

class ApiResult implements \JsonSerializable
{
    /**
     * Success of action
     * @var bool
     */
    protected $success;

    /**
     * Output of external program
     * @var array
     */
	protected $output = array();

    /**
     * Return value of shell process
     * @var int
     */
	protected $shellReturnVar = 0;

    /**
     *
     * @param bool $success
     * @param mixed $output
     * @param int $shellReturnVar
     */
	public function __construct($success = true, $output = '', $shellReturnVar = 0)
    {
        $this->success = $success;
		$this->shellReturnVar = $shellReturnVar;
        if (is_array($output)) {
            $this->output = $output;
        } else {
            $this->output = array($output);
        }
	}

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return int
     */
    public function getShellReturnVar()
    {
        return $this->shellReturnVar;
    }

    /**
     * @param $success
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * @param $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param $shellReturnVar
     */
    public function setShellReturnVar($shellReturnVar)
    {
        $this->shellReturnVar = $shellReturnVar;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'success' => $this->success,
            'output' => implode("\n", $this->output),
            'shellReturnVar' => $this->shellReturnVar
        ];
    }
}
