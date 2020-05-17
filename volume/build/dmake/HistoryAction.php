<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

require_once __DIR__ . '/../config/configServer.php';
require_once "HistorySum.php";

use Dmake\Config;
use Dmake\StatEntry;
use Dmake\HistorySum;

class HistoryAction
{
    /**
     * Initialiaze a HistorySnapshot.
     */
    public static function initializeHistorySumEntries()
    {
        $cfg = Config::getConfig();
        $sets = StatEntry::getSets();

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
    public static function createHistorySumEntries()
    {
        $sets = StatEntry::getSets();

        foreach ($sets as $set) {
            self::createHistorySumEntry($set);
        }
    }

    /**
     * Create a history snapshot for a single set.
     * @param mixed $set
     */
    public static function createHistorySumEntry($set)
    {
        $cfg = Config::getConfig();
        $stages = array_keys($cfg->stages);

        foreach ($stages as $stage) {

            // just to get the right order..
            $stat = array();
            foreach ($cfg->ret_class as $class => $stclass) {
                $stat[$class] = 0;
            }

            // just to get the right order..
            $stat_class = array();
            foreach ($cfg->ret_color as $class => $color) {
                $stat_class[$class] = 0;
            }

            $dbTable = $cfg->stages[$stage]->dbTable;

            list($stat, $rerun) = StatEntry::getStats($dbTable, $set['set']);

            self::saveStat($set, $stage, $stat);
        }
    }

    /**
     * Saves the entry.
     * @param $set
     * @param $stage
     * @param $stat
     */
    public static function saveStat($set, $stage, $stat)
    {
        $cfg = Config::getConfig();

        $hs = HistorySum::adaptFromStat($stat, $stage);
        $hs->setSet($set);
        $hs->setDateSnapshot($cfg->now->datestamp);

        $hs->save();
    }
}
