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
     */
    public static function getHostGroups(): array
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

    public static function disableStagesByHostGroup(string $hostGroup): array
    {
        $cfg = Config::getConfig();
        $disabled = [];
        foreach ($cfg->stages as $name => $stage) {
            if ($stage->hostGroup === $hostGroup) {
                $disabled[] = $name;
                unset($cfg->stages[$name]);
            }
        }
        return $disabled;
    }

    public static function saveActiveStages(): bool
    {
        $cfg = Config::getConfig();
        $activeStages = [];
        foreach ($cfg->stages as $name => $stage) {
            $activeStages[] = $name;
        }
        $result = file_put_contents(ACTIVESTAGESFILE, json_encode($activeStages, true));
        if ($result === false) {
            echo "Failed to save current active stages to " . ACTIVESTAGESFILE;
            return false;
        }
        return true;
    }

    public static function loadActiveStages(): array
    {
        $json = file_get_contents(ACTIVESTAGESFILE);
        if ($json === false) {
            echo "Failed to load current active stages to " . ACTIVESTAGESFILE;
            return [];
        }
        $activeStages = json_decode($json, true);
        return $activeStages;
    }

    public static function determineActiveStages(): void
    {
        $cfg = Config::getConfig();
        $activeStages = self::loadActiveStages();

        foreach ($cfg->stages as $name => $stage) {
            if (!in_array($name, $activeStages)) {
                unset($cfg->stages[$name]);
            }
        }
    }

    /**
     * Determines the hostGroup that is configured for given stage
     */
    public static function getHostGroupByStage(string $stage): string
    {
        $cfg = Config::getConfig();
        return $cfg->stages[$stage]->hostGroup;
    }

    public static function getPossibleTargets(): array
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

    public static function setupFiles(
        string $articleDir,
        string $directory,
        string $hostGroupName
    ): void {
        $sourceDir = $articleDir . '/' . $directory;
        $destDir = $articleDir . '/' . $directory . '/__texmlbus_' . $hostGroupName;
        if (!file_exists($destDir)) {
            echo "DestDirectory is: $destDir" . PHP_EOL;
            UtilFile::linkR($sourceDir, $destDir, '/__texmlbus_' . '/', '/\\.bbl$|Makefile$/');
            UtilFile::adjustMakefilePrefix($destDir, 1);
        }
    }

    public static function getSourceDir(
        string $articleDir,
        string $directory,
        string $hostGroup
    ): string {
        $cfg = Config::getConfig();

        if ($cfg->linkSourceFiles) {
            $sourceDir = $articleDir . '/' . $directory . '/__texmlbus_' . $hostGroup;
        } else {
            $sourceDir = $articleDir . '/' . $directory;
        }
        return $sourceDir;
    }

    /**
     * Determine the makeCommand
     * @return string
     */
    public static function getMakeCommand(
        array $host,
        string $action,
        string $makeLog
    ) : string {
        $makeAction = 'make_' . $action;

        // if there is a defined command like MAKE_PDF, use that otherwise just "make action"
        if (isset($host[$makeAction])) {
            $makeCommand = $host[$makeAction];
        } else {
            $makeCommand = $host['make_default'] . ' ' . $action;
        }
        $makeCommand .= ' ' . str_replace('__MAKELOG__', $makeLog, $host['make_output']);

        return $makeCommand;
    }
}