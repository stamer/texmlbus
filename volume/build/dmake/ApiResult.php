<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * The result of an Api Call
 *
 */

namespace Dmake;

use JsonSerializable;

/**
 * For Api calls within Buildsystem.
 *
 * Class Api
 *
 */
class ApiResult implements JsonSerializable
{
    /**
     * Success of action
     * @var bool
     */
    protected $success = false;

    /**
     * Output of external program
     * @var array
     */
	protected $output = [];

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
            $this->output = [$output];
        }
	}

    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string[]
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    public function getShellReturnVar(): int
    {
        return $this->shellReturnVar;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function setOutput(array $output): void
    {
        $this->output = $output;
    }

    public function setShellReturnVar(int $shellReturnVar): void
    {
        $this->shellReturnVar = $shellReturnVar;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'output' => implode("\n", $this->output),
            'shellReturnVar' => $this->shellReturnVar
        ];
    }
}
