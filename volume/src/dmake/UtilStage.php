<?php
/**
 * MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
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
            file_put_contents(ACTIVESTAGESFILE, '[]');
            chmod(ACTIVESTAGESFILE, 0666);
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
    ): void
    {
        $cfg = Config::getConfig();

        $sourceDir = $articleDir . '/' . $directory;

        // Before files are copied to worker dir, make sure that no (old) result
        // files will be linked, otherwise make never actually does something.
        // This needs to be done in the original directory.
        $systemCmd = 'cd "' . $sourceDir . '" && ' . $cfg->app->make . ' allclean';
        if (DBG_LEVEL & DBG_MAKE) {
            echo "make allclean $directory...\n";
        }
        exec($systemCmd, $output, $result);
        if (DBG_LEVEL & DBG_MAKE) {
            print_r($output);
        }
        if ($result) {
            echo "Failed to make allclean in $sourceDir";
        }

        $destDir = $articleDir . '/' . rtrim($directory, '/') . '/' . $cfg->server->workerPrefix . $hostGroupName;

        /*
         * It is not necessary to recreate worker directories any more.
         * Any file changes via remote updates are automatically synchronized to worker dirs.
         * Local updates are also handled in linkR(), only missing piece is currently
         * local deletes.
         */
        if (DBG_LEVEL & DBG_SETUP_FILES) {
            echo "DestDirectory is: $destDir" . PHP_EOL;
        }
        UtilFile::linkR($sourceDir, $destDir, '/' . $cfg->server->workerPrefix . '/', '/\\.bbl$|Makefile$/');
        UtilFile::adjustMakefilePrefix($destDir, 1);
    }

    public static function getSourceDir(
        string $articleDir,
        string $directory,
        string $hostGroup
    ): string {
        $cfg = Config::getConfig();

        if ($cfg->linkSourceFiles) {
            $sourceDir = $articleDir . '/' . rtrim($directory, '/') . '/' . $cfg->server->workerPrefix . $hostGroup;
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
