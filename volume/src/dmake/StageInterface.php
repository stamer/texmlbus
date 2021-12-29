<?php
/**
 * MIT License
 * (c) 2017 - 2021 Heinrich Stamerjohanns
 *
 * Stage Interface
 */

namespace Dmake;

interface StageInterface
{
    public static function register(): ConfigStage;

    public function save(): bool;

	public static function fillEntry(array $row): StatEntry;

	public function updateRetval(): bool;

	public static function parse(
	    string $hostGroup,
        StatEntry $entry,
        int $status,
        bool $childAlarmed
    ): bool;
}
