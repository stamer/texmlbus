<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
namespace Dmake;

require_once 'StatEntry.php';
require_once 'UtilHost.php';
require_once 'UtilStage.php';

/**
 * Class UtilBindingFile
 *
 * This is a support class to determine the current ltxml dir of the latexml release
 * or to get the current list of binding files.
 */
class UtilBindingFile
{
    /**
     * removes the .ltxml sufffix
     *
     * @param $str
     * @return string|string[]|null
     */
    public static function removeLtxmlSuffix($str)
    {
        return preg_replace('/\.ltxml$/', '', $str);
    }

    /**
     * returns the directory location of the binding file directory of the
     * currently used latexml
     */
    public static function getBindingFilesDir(): string
    {
        $cfg = Config::getConfig();

        // in docker context, the server cannot execute latexml directly
        if (!empty(getenv('DOCKERIZED'))) {
            $path = dirname($cfg->server->app->latexml, 2) . '/blib/lib/LaTeXML/Package';
            return $path;
        }

        // determine current ltxml dir of latexml_release
        $command = "echo '\\documentclass{article}\\begin{document}Hello\\end{document}' | " . $cfg->app->latexml . " - 2>&1";
        exec($command, $output, $return_var);

        $path = '';
        foreach ($output as $line) {
            if (strpos($line, 'TeX.pool.ltxml') !== FALSE) {
                if (preg_match('/Loading (.*)TeX.pool.ltxml/', $line, $matches)) {
                    $path = $matches[1];
                }
                break;
            }
        }

        if (empty($path)) {
            echo "Latex .ltxml dir could not be determined!";
        }

        return $path;
    }

    /**
     * returns all binding files of a specific directory
     */
    public static function getBindingFiles(
        string $bindingDir,
        bool $useCache,
        string $cacheFile,
        string $pattern): array
    {
        $cacheFile = sys_get_temp_dir() . '/' . $cacheFile;
        $cacheLife = 300; //caching time, in seconds

        $bindingFiles = [];
        $filemtime = @filemtime($cacheFile);
        if (
            !$useCache
            || !$filemtime
            || (time() - $filemtime >= $cacheLife)
        ) {
            $bindingFiles = array_map('self::removeLtxmlSuffix', UtilFile::listDir($bindingDir, true, true, $pattern));
            UtilFile::filePutContentsAtomic($cacheFile, serialize($bindingFiles));
        } else {
            $bindingFiles = unserialize(file_get_contents($cacheFile));
        }
        return $bindingFiles;
    }
}
