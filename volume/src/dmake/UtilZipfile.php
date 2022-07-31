<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

use ZipArchive;

class UtilZipfile
{
    public static bool $debug = true;

    public static function extract(string $zipfile, string $destDir): bool
    {
        $zip = new ZipArchive();
        if (self::$debug) {
            error_log(__METHOD__ . ': ' . $zipfile);
            error_log(__METHOD__ . ': ' . $destDir);
        }
        if ($zip->open($zipfile) === true) {
            if (!is_dir($destDir)) {
                error_log(__METHOD__ . ": extract failed. $destDir is not a directory!");
                return false;
            }
            if (!is_writable($destDir)) {
                error_log(__METHOD__ . ": extract failed. $destDir is not writable!");
                return false;
            }
            $result = $zip->extractTo($destDir);
            if (!$result) {
                error_log(__METHOD__ . ": extract failed.");
                return false;
            }
            $zip->close();
            return true;
        } else {
            error_log(__METHOD__ . ": Failed to open '$zipfile'.");
            return false;
        }
    }

    public static function listSubDirs(string $zipfile) : array
    {
        $za = new ZipArchive();
        $za->open($zipfile);

        if (self::$debug) {
            error_log("status: " . $za->status);
            error_log("statusSys: " . $za->statusSys);
            error_log("filename: " . $za->filename);
            error_log("comment: " . $za->comment);
        }

        $subDirs = [];
        for ($i = 0; $i < $za->numFiles; $i++) {
            $filenameInZip = $za->statIndex($i)['name'];
            if (strpos($filenameInZip, '/') !== false) {
                $subDir = strstr($filenameInZip, '/', true);
                if (!in_array($subDir, $subDirs)) {
                    $subDirs[] = $subDir;
                }
            }
            //error_log(print_r($za->statIndex($i), 1));
        }
        return $subDirs;
    }
}
