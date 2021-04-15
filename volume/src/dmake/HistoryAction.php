<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

// Server\Config needed for ret_color
use Server\Config;

require_once "HistorySum.php";

class HistoryAction
{
    /**
     * Initialiaze a HistorySnapshot.
     */
    public static function initializeHistorySumEntries(): void
    {
        $cfg = Config::getConfig();
        $sets = Set::getSets();

        $stat = [];  // empty will save default (0) values;
        foreach ($sets as $set) {
            $stages = array_keys($cfg->stages);

            foreach ($stages as $stage) {
                self::saveStat($set, $stage, $stat);
            }
        }

    }

    /**
     * Create entries for all sets.
     */
    public static function createHistorySumEntries(): void
    {
        $sets = Set::getSets();
        foreach ($sets as $set) {
            self::createHistorySumEntry($set);
        }
    }

    /**
     * Create a history snapshot for a single set.
     */
    public static function createHistorySumEntry(Set $set): void
    {
        $cfg = Config::getConfig();
        $stages = array_keys($cfg->stages);

        foreach ($stages as $stage) {

            // just to get the right order..
            $stat = [];
            foreach ($cfg->ret_class as $class => $stclass) {
                $stat[$class] = 0;
            }

            $dbTable = $cfg->stages[$stage]->dbTable;

            list($stat, $rerun) = StatEntry::getStats($dbTable, $set->getName());

            self::saveStat($set, $stage, $stat);
        }
    }

    /**
     * Saves the entry.
     */
    public static function saveStat(Set $set, string $stage, array $stat): bool
    {
        $cfg = Config::getConfig();

        $hs = HistorySum::adaptFromStat($stat, $stage);
        $hs->setSet($set);
        $hs->setDateSnapshot($cfg->now->datestamp);

        return $hs->save();
    }
}
