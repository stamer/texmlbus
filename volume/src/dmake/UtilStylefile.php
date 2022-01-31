<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

require_once 'StatEntry.php';


class UtilStylefile
{
    public static $fullPathStylefile = [];

    public static function getStylefiles(string $checkfile): array
    {
        $stylefiles = array();

        if (!($contents = file_get_contents($checkfile))) {
            $stylefiles[0] = "NOFILE";
            return $stylefiles;
        }

        // see also http://www.techfak.uni-bielefeld.de/rechner/latex/classes.html

        /*
        Latex 2.09
        \documentstyle[11pt,german,twoside]{article}
        loads article.sty
        while the stuff inside [] might describe options (11pt, twoside)
        as well as additional style files (german).

        Latex 2e
        \documentclass[a4paper,11pt]{article} loads article.cls
        \usepackage{german} loads german.sty
        \usepackage[dvips]{graphics} loads graphics.sty

        be aware that pattern needs to support
        \usepackage[german]{babel,minitoc} where babel.sty and minitoc.sty are loaded

        Results are not exact, since commented out documentclass are still looked at, but
        that should not really be a problem

        */

        $stylefiles = array();
        $pattern = '/documentclass/i';

        if (preg_match($pattern, $contents)) {
            // find the .cls file
            preg_match_all('/^ *(?<!\%) *\\\\documentclass(\[.*?\]){0,1}\{(.+?)\}/m', $contents, $matches);
            //print_r($matches);
            echo $matches[2][0] . "\n";

            if (isset($matches[2][0])) {
                $stylefiles[] = $matches[2][0] . ".cls";
            } else {
                $stylefiles[0] = "NODOCUMENTCLASS";
            }

            // find the packages
            preg_match_all('/^ *(?<!\%) *\\\\usepackage(\[.*?\]){0,1}\{(.+?)\}/m', $contents, $matches);
            print_r($matches);

            // be aware each entry in $matches[2] might still contain multiple packages;
            if (isset($matches[2][0])) {
                foreach ($matches[2] as $entry) {
                    $files = explode(',', $entry);
                    foreach ($files as $file) {
                        $stylefiles[] = trim($file) . ".sty";
                    }
                }
            }
        } else {
            // Latex 2.09?
            preg_match_all('/^ *(?<!\%) *\\\\documentstyle(\[.*?\]){0,1}\{(.+?)\}/m', $contents, $matches);
            print_r($matches);


            if (isset($matches[2][0])) {
                $stylefiles[] = $matches[2][0] . ".sty";
            } else {
                $stylefiles[0] = "NODOCUMENTSTYLE";
            }

            // be aware each entry in $matches[1] might still contain multiple packages;
            if (isset($matches[1][0])) {
                foreach ($matches[1] as $entry) {
                    $files = explode(',', $entry);
                    foreach ($files as $file) {
                        $realname = trim($file, '[ ]');
                        if ($realname != '') {
                            $stylefiles[] = $realname . ".sty";
                        }
                    }
                }
            }
        }

        return $stylefiles;
    }

    public function saveStylefiles(
        string $set,
        string $filename,
        array $stylefilesArr
    ): void {
        $dao = DAO::getInstance();

        $styfiles = '';

        foreach ($stylefilesArr as $stylefile) {
            if ($stylefile == 'A4.sty' || $stylefile == '12pt.sty' || $stylefile == '11pt.sty' || $stylefile == 'twoside.sty') {
                continue;
            }
            $styfiles .= $stylefile . ' ';
        }

        // cleanup first, this could be expanded to compute diffs
        $query = "
            DELETE FROM
                package_usage
            WHERE
                `set`           = :set
                AND filename    = :filename
            ";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':set', $set);
        $stmt->bindValue(':filename', $filename);

        $stmt->execute();

        foreach ($stylefilesArr as $styfilename) {
            $styfilename = trim($styfilename);
            if (!strlen($styfilename)) {
                continue;
            }
            // echo $filename."\n";

            $query = "
                INSERT INTO
                    package_usage
                SET
                    id = 0,
                    `set`       = :set,
                    filename    = :filename,
                    styfilename = :styfilename
                ";

            $stmt = $dao->prepare($query);
            $stmt->bindValue(':set', $set);
            $stmt->bindValue(':filename', $filename);
            $stmt->bindValue(':styfilename', $styfilename);

            $stmt->execute();
        }
    }

    public function mmFindMacroInStylefiles(
        string $set,
        string $filename,
        string $macro,
        array $stylefilesArr
    ): void {
        $dao = DAO::getInstance();

        $styfiles = '';
        foreach ($stylefilesArr as $stylefile) {
            if ($stylefile == 'A4.sty'
                || $stylefile == '12pt.sty'
                || $stylefile == '11pt.sty'
                || $stylefile == 'twoside.sty') {
                continue;
            }
            if (!isset(self::$fullPathStylefile[$stylefile])) {
                $execStr = 'kpsewhich ' . $stylefile;
                $retstr = shell_exec($execStr);
                $retstr = trim($retstr);
                self::$fullPathStylefile[$stylefile] = $retstr;
            }
            if (self::$fullPathStylefile[$stylefile] !== '') {
                $styfiles .= self::$fullPathStylefile[$stylefile] . ' ';
            }
        }

        $arr = [];
        if ($styfiles) {
            $execStr = '/bin/egrep -l \'\\\\((future)?let|newcommand|(g|e|x)?def)[^\\\\]*[^a-zA-Z0-9_]*' . $macro . '[^a-zA-Z0-9_]\' ' . $styfiles;
            echo $execStr . "\n";

            $retstr = shell_exec($execStr);

            $retstr = trim($retstr);

            $arr = explode("\n", $retstr);

            $arr = array_unique($arr);
        }

        $filename = str_replace(ARTICLEDIR, '', $filename);
        // cleanup old entries
        $query = "
            DELETE FROM
                mmfile
            WHERE
                filename = :filename
                AND macro    = :macro";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':filename', $filename);
        $stmt->bindValue(':macro', $macro);

        $stmt->execute();

        foreach ($arr as $styfilename) {
            $styfilename = trim($styfilename);
            $styfilename = str_replace('/usr/share/texmf-dist/tex/latex/', '', $styfilename);
            if (!strlen($styfilename)) {
                continue;
            }
            // echo $filename."\n";

            $query = "
                INSERT INTO
                    mmfile
                SET
                    id = 0,
                    `set`       = :set,
                    filename    = :filename,
                    macro       = :macro,
                    styfilename = :styfilename,
                    num         = 0
                ";

            echo $query . "\n";
            $stmt = $dao->prepare($query);
            $stmt->bindValue(':set', $set);
            $stmt->bindValue(':filename', $filename);
            $stmt->bindValue(':macro', $macro);
            $stmt->bindValue(':styfilename', $styfilename);

            $stmt->execute();
        }
    }

    /**
     * Returns the installed cls/sty files as
     * [name => path] Array.
     * If checkfiles is empty, it checks for any .cls/.sty files,
     * otherwise it checks for specific filenames.
     */
    public static function getInstalledClsStyFiles(
        string $directory,
        array $checkFiles = []): array
    {
        if (empty($checkFiles)) {
            $pattern = '/\.cls|\.sty|\.tex/';
        } else {
            $pattern = null;
        }

        $currentDepth = -5;
        $result_dirs = [];
        UtilFile::listDirR(
            $directory,
            $result_dirs,
            $currentDepth,
            true,
            false,
            $pattern,
        );

        $installedFiles = [];
        foreach ($result_dirs as $filename) {
            $basename = basename($filename);
            if (empty($checkFiles)
                || in_array($basename, $checkFiles)
            ) {
                $installedFiles[basename($filename)] = $filename;
            }
        }
        return $installedFiles;
    }
}