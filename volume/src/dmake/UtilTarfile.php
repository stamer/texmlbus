<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

use PharData;
use Exception;

class UtilTarfile
{
    public static bool $debug = true;

    public static function extract(string $tarfile, string $destDir): bool
    {
        try {
            $suffix = UtilFile::getSuffix($tarfile);
            if ($suffix === '.gz'
                || $suffix === '.tgz'
            ) {
                // decompress from gz
                $p = new PharData($tarfile);
                $phar = $p->decompress();
            } else {
                $phar = new PharData($tarfile);
            }
            $phar->extractTo($destDir);
        } catch (Exception $e) {
            error_log(__METHOD__ . ": extract failed. " . $e->getMessage());
            return false;
        }
        return true;
    }
}
