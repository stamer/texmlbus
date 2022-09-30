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
    public const RESULT_OK = 0;
    public const TIMEOUT = 99;

    /**
     * Success of action
     * @var bool
     */
    protected bool $success = false;

    /**
     * Output of external program
     */
	protected array $output = [];

    /**
     * Return value of shell process
     */
	protected int $shellReturnVar = 0;

    /**
     *
     * @param bool $success
     * @param mixed $output
     * @param int $shellReturnVar
     */
	public function __construct(
        bool $success = true,
        mixed $output = '',
        int $shellReturnVar = 0)
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

    public function getShellReturnVar(): ?int
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
