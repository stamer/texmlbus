<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

class UtilStage
{
    /**
     * Determines all hostGroups that are configured in the current active stages
     * @return array
     */
    public static function getHostGroups()
    {
        $cfg = Config::getConfig();
        $hostGroups = [];
        foreach ($cfg->stages as $stage) {
            if (!in_array($stage->hostGroup, $hostGroups)) {
                $hostGroups[] = $stage->hostGroup;
            }
        }
        return $hostGroups;
    }

    /**
     * Determines all hostGroups that are configured in the current active stages
     * @return array
     */
    public static function getHostGroupByStage($stage)
    {
        $cfg = Config::getConfig();
        return $cfg->stages[$stage]->hostGroup;
    }

    public static function getPossibleTargets()
    {
        $cfg = Config::getConfig();
        $targets = [];
        foreach ($cfg->stages as $stage) {
            if (!in_array($stage->target, $targets)) {
                $targets[] = $stage->target;
            }
        }
        return $targets;
    }

    public static function setupFiles($articleDir, $directory, $hostGroupName)
    {
        $sourceDir = $articleDir . '/' . $directory;
        $destDir = $articleDir . '/' . $directory . '/__texmlbus_' . $hostGroupName;
        if (!file_exists($destDir)) {
            echo "DestDirectory is: $destDir" . PHP_EOL;
            UtilFile::linkR($sourceDir, $destDir, '/__texmlbus_' . '/', '/\\.bbl$|Makefile$/');
            UtilFile::adjustMakefilePrefix($destDir, 1);
        }
    }

    public static function getSourceDir($articleDir, $directory, $hostGroup)
    {
        $cfg = Config::getConfig();

        if ($cfg->linkSourceFiles) {
            $sourceDir = $articleDir . '/' . $directory . '/__texmlbus_' . $hostGroup;
        } else {
            $sourceDir = $articleDir . '/' . $directory;
        }
        return $sourceDir;
    }
}
