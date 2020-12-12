<?php
/**
 * MIT License
 * (c) 2020 Heinrich Stamerjohanns
 *
 * The result of an Api Call for several Ids
 */

namespace Dmake;

class ApiResultArray extends ApiResult implements \JsonSerializable
{
    protected $successArray;

    public function addSuccess(int $id, bool $success): void
    {
        $this->successArray[$id] = $success;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'success' => true,
            'successArray' => $this->successArray,
            'output' => implode("\n", $this->output),
            'shellReturnVar' => $this->shellReturnVar
        ];
    }
}
