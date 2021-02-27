<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
namespace Dmake;

use Server\Config;

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
            $path = dirname($cfg->server->app->latexml, 2) . '/lib/LaTeXML/Package';
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

    public function getClsFiles($useCache = true)
    {
        $bindingDir = UtilBindingFile::getBindingFilesDir();
        $clsPattern = "/\.cls\.ltxml$/";

        $latexmlClsFiles =
            array_fill_keys(
                UtilBindingFile::getBindingFiles($bindingDir, $useCache, 'latexmlClsFiles.tmp', $clsPattern),
                'latexml'
            );

        $buildClsFiles =
            array_fill_keys(
                UtilBindingFile::getBindingFiles(STYDIR, $useCache, 'buildClsFiles.tmp', $clsPattern),
                'build'
            );

        // find files that exist in both arrays
        $clsIntersectFiles = array_intersect_key($latexmlClsFiles, $buildClsFiles);
        foreach ($clsIntersectFiles as $key => &$val) {
            $val = 'build/latexml';
        }


        // last ones will overwrite previous ones
        $clsFiles = array_merge($latexmlClsFiles, $buildClsFiles, $clsIntersectFiles);

        ksort($clsFiles, SORT_ASC);
        return $clsFiles;
    }

    public function getStyFiles($useCache = true)
    {
        $bindingDir = UtilBindingFile::getBindingFilesDir();
        $styPattern = "/\.sty\.ltxml$/";

        $latexmlStyFiles =
            array_fill_keys(
                UtilBindingFile::getBindingFiles($bindingDir, $useCache, 'latexmlStyFiles.tmp', $styPattern),
                'latexml'
            );
        $buildStyFiles =
            array_fill_keys(
                UtilBindingFile::getBindingFiles(STYDIR, $useCache, 'buildStyFiles.tmp', $styPattern),
                'build'
            );
        // find files that exist in both arrays
        $styIntersectFiles = array_intersect_key($latexmlStyFiles, $buildStyFiles);
        foreach ($styIntersectFiles as $key => &$val) {
            $val = 'build/latexml';
        }
        // last ones will overwrite previous ones
        $styFiles = array_merge($latexmlStyFiles, $buildStyFiles, $styIntersectFiles);
        ksort($styFiles, SORT_ASC);
        return $styFiles;
    }

    /**
     * get current version of latexml for each HostGroup
     *
     * @return mixed|string
     *
     */
    public static function testStyClsSupport($filenames)
    {
        $cfg = Config::getConfig();

        //$hostGroups = self::getActiveHostGroups();
        $hostGroups = ['worker'];

        $parameter = [
            // usr/share/texmf-dist/tex is alpine-specific...
            'TEXINPUTS' => '.:/usr/share/texmf-dist/tex//:' . ARTICLESTYDIR . '//:' . STYDIR .'//',
            'filenames' => array_keys($filenames)
        ];

        foreach ($hostGroups as $hostGroupName) {
            // the string needs to be \''string'\' ...
            // the string is also base64_encoded, to circumvent encoding " problems
            $execstr = $cfg->app->ssh . ' dmake@' . $hostGroupName . ' php ' . BUILDDIR . '/script/php/testStyClsSupport.php '
                . "\\\\\''" . base64_encode(json_encode($parameter)) . "'\\\\\'";
            $retstr = shell_exec($execstr);
            $result = json_decode($retstr, true);
        }
        return $result;
    }
}
