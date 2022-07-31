<?php
/**
 * MIT License
 * (c) 2020 Heinrich Stamerjohanns
 *
 * The result of an Api Call for several Ids
 */

namespace Dmake;

/**
 * For Api calls within Buildsystem.
 *
 * Class Api
 *
 */
class ApiResultArray extends ApiResult
{
    protected array $successArray;

    public function addSuccess(int $id, bool $success): void
    {
        $this->successArray[$id] = $success;
    }

    /**
     */
    public function jsonSerialize(): array
    {
        return [
            'success' => true,
            'successArray' => $this->successArray,
            'output' => implode("\n", $this->output),
            'shellReturnVar' => $this->shellReturnVar
        ];
    }
}
