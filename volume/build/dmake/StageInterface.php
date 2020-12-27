<?php
/**
 * MIT License
 * (c) 2017 - 2019 Heinrich Stamerjohanns
 *
 * Stage Interface
 */

namespace Dmake;

interface StageInterface
{
    public static function register(): array;

    public function save(): bool;

	public static function fillEntry(array $row): StatEntry;

	public function updateRetval(): bool;

	public static function parse(
	    string $hostGroup,
        StatEntry $entry,
        bool $childAlarmed
    ): bool;
}
