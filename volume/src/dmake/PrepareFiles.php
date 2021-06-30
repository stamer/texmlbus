<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

use Dmake\Exception\WriteException;

require_once "UtilFile.php";
require_once "exception/WriteException.php";

/**
 * Class PrepareFiles
 * Prepares directories, detects tex files and imports articles
 * to database.
 */
class PrepareFiles
{
    public const FILENOTFOUND = 0;
    public const NOTEXFILEFOUND = 1;
    public const TEXFILEEXISTS = 2;
    // instead answer will be string of added file.
    public const TEXFILEADDED = 3;
    public const DIRECTORYEXISTS = 4;
    public const MOVEDIRERROR = 5;

    private $debug = true;

    // directories that will be removed when found in the document tree
    // these directories are often inside archived files and should not
    // be inside the document tree.
    public static $removeDirs =
        [
            '.svn',
            '.git',
            '__MACOSX',
            '__macosx',
        ];

    public $removeDirsPattern = '';

    /**
     * whether directories that match pattern are actually removed.
     * As default: removing such directories is disabled.
     * @var bool
     */
    public static $removeDirsEnabled = false;

    public static $ignoreDirs =
        [
            '.svn',
            '.git',
            '__MACOSX',
            '__macosx',
        ];

    public $ignoreDirsPattern = '';

    /**
     * files with these prefixes should be ignored for mainfile detection
     */
    public static $ignorePrefixes =
        [
            '__flat',
            'bib.',
            'stand.',
        ];

    /**
     *  will be created for $ignorePrefixes
     */
    public $ignorePrefixesPattern = '';

    /**
     * files with these suffixes should not be used for .tex for TeX detection
     * it may be possible that the main tex file may not have a suffix at all.
     *
     * use ignoreSuffixes, as it also include .tex files
     */

    public static $ignoreSuffixesTexDetection =
        [
            'Makefile',
            'changelog.txt.tex',
            'IEEEtran',
            'IEEEtran0',
            '.aux',
            '.bib',
            '.blg',
            '.cache',
            '.cls',
            '.css',
            '.doc',
            '.docx',
            '.dtd',
            '.eps',
            '.fff',
            '.gz',
            '.html',
            '.jpg',
            '.js',
            '.lof',
            '.log',
            '.lot',
            '.png',
            '.pdf',
            '.rar',
            '.sty',
            '.tif',
            '.txt',  // changelog.txt...
            '.xml',
            '.xls',
            '.xlsx',
            '.xz',
            '.zip'
        ];

    /**
     * will be created from array above
     */
    public $ignoreSuffixesTexDetectionPattern;

    /**
     * array of suffixes of compressed filetypes. The mimeType is
     * determined and an action according to $uncompressMap is called.
     */
    public static $compressedArchives =
        [
            '.gz',
            '.rar',
            '.xz',
            '.zip'
        ];

    /**
     * will be created in the constructor for pattern matching.
     */
    public $compressedArchivesPattern;

    const ModeInteractive = 'interactive';
    const ModeOverwriteOn = 'overwriteOn';
    const ModeOverwriteOff = 'overwriteOff';

    const DefaultUncompressionMode = self::ModeOverwriteOn;

    /**
     * maps mimeTypes to uncompress actions
     * It uses config variables, therefore it is created in
     * the constructor and not here.
     */
    public $uncompressMap = [];

    /**
     * an array of actions for default conversion of graphic files
     * that are done when the source files are imported.
     */
    public $conversionMap = [];

    /**
     * will be created for pattern matching
     */
    public $conversionMapPattern;

    /**
     * PrepareFiles constructor.
     */
    public function __construct()
    {
        $this->cfg = Config::getConfig();

        $this->conversionMap =
            [
                // .eps to .pdf conversion
                '.eps' => [
                    'app' => $this->cfg->app->epstopdf.' --outfile=__DEST__ __FILE__',
                    'destfile' => '__PREFIX__-eps-converted-to.pdf',
                ],
            ];
        $this->removeDirsPattern = '/('.implode('|', array_map('preg_quote', self::$removeDirs)).')/';
        $this->ignoreDirsPattern = '/('.implode('|', array_map('preg_quote', self::$ignoreDirs)).')/';
        $this->ignorePrefixesPattern = '/^('.implode('|', array_map('preg_quote', self::$ignorePrefixes)).')/';
        $this->ignoreSuffixesTexDetectionPattern = '/('.implode('|',
                array_map('preg_quote', self::$ignoreSuffixesTexDetection)).')$/';
        // echo $this->ignoreSuffixesTexDetectionPattern.PHP_EOL;
        $this->compressedArchivesPattern = '/('.implode('|', array_map('preg_quote', self::$compressedArchives)).')$/';
        //echo $this->compressedArchivesPattern.PHP_EOL;
        $this->conversionMapPattern = '/('.implode('|', array_map('preg_quote', array_keys($this->conversionMap))).')$/';
    }

    protected function debugLog(string $str): void
    {
        if ($this->debug) {
            error_log($str);
        }
    }

    /**
     * Simple map to handle certain archives.
     */
    public function setUncompressMap(): void
    {
        $this->uncompressMap = [
            'application/rar' => $this->cfg->app->unrar.' '.$this->cfg->uncompress->unrar->{$this->uncompressionMode}.' __FILE__ __DIR__',
            'application/x-rar' => $this->cfg->app->unrar.' '.$this->cfg->uncompress->unrar->{$this->uncompressionMode}.' __FILE__ __DIR__',
            'application/zip' => $this->cfg->app->unzip.' '.$this->cfg->uncompress->unzip->{$this->uncompressionMode}.' -d __DIR__ __FILE__',
            'application/x-zip' => $this->cfg->app->unzip.' '.$this->cfg->uncompress->unzip->{$this->uncompressionMode}.' -d __DIR__ __FILE__',
            //'application/gzip' => $this->cfg->app->gunzip.' __FILE__',
            //'application/x-gzip' => $this->cfg->app->gunzip.' __FILE__',
        ];
    }

    /**
     * Determines the uncompressionMode.
     * Interactive in CLI mode, otherwise DefaultUncompressionMode.
     */
    public function chooseUncompressionMode(): array
    {
        if (php_sapi_name() == 'cli') {
            stream_set_blocking(STDIN, 1);
            echo "Choose uncompression mode for archives:" . PHP_EOL;
            echo " 1) interactive" . PHP_EOL;
            echo " 2) always overwrite" . PHP_EOL;
            echo " 3) never overwrite" . PHP_EOL;
            $choice = fgetc(STDIN);

            switch ($choice) {
                case 1:
                default:
                    $this->uncompressionMode = self::ModeInteractive;
                    echo "Ask for existing files." . PHP_EOL;
                    break;
                case 2:
                    $this->uncompressionMode = self::ModeOverwriteOn;
                    echo "Always overwrite existing files." . PHP_EOL;
                    break;
                case 3:
                    $this->uncompressionMode = self::ModeOverwriteOff;
                    echo "Never overwrite existing files." . PHP_EOL;
                    break;
            }
        } else {
            $this->uncompressionMode = self::DefaultUncompressionMode;
        }
        $this->setUncompressMap();
    }

    /**
     * Determines whether a given file is a tex file.
     */
    public function isFileTexfile(string $checkfile): bool
    {
        $file = $this->cfg->app->file;

        if ($checkfile == '') {
            return false;
        }

        $retstr = `$file -Li '$checkfile'`;

        if (strpos($retstr, 'text/') !== FALSE
            || strpos($retstr, 'application/octet-stream') !== FALSE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determines whether a given file is a main tex file.
     *
     * If it is a main tex file, $texEngine is filled with either
     * default TexEngine or detected $texEngine.
     */
    public function isFileMainTexfile(string $checkfile, &$texEngine): bool
    {
        $cfg = Config::getConfig();
        if (!($contents = @file_get_contents($checkfile))) {
            return false;
        }
        $texEngine = $cfg->defaultTexEngine;

        // Avoid comments
        $pattern = '/^\s*(?!%)\s*\\\\document(style|class)/mi';

        if (preg_match($pattern, $contents)) {
            // try to find hint for TexEngine
            // recognize LaTeXML hint: %%% TeX-engine: xelatex
            // recognize llmk hint: % latex = "xelatex"
            if (preg_match('/^%%%\s*tex-engine:\s*"*(\w+)"*/mi', $contents, $matches)
                || preg_match('/^%\s*latex\s*=\s*"*(\w+)"*/m', $contents, $matches)
            ) {
                if (in_array($matches[1], array_keys($cfg->validPdfTexEngines))) {
                    $texEngine = $matches[1];
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /*
     * Uncompresses a given file.
     * Directory or upload files may contain archives within.
     */
    public function uncompressFile(string $archiveFile): bool
    {
        $dir = dirname($archiveFile);
        $file = basename($archiveFile);
        $result = false;

        $fileProgram = $this->cfg->app->file;

        if ($archiveFile == '') {
            return false;
        }

        $systemstr = "$fileProgram -Lbi ".escapeshellarg($archiveFile);
        // echo "SYSTEMSTR: ".$systemstr.PHP_EOL;

        $retstr = exec($systemstr);

        $this->debugLog("Checking $archiveFile $retstr");
        if (preg_match('/^(.*?)[;$]/', $retstr, $matches)) {
            $mimeType = $matches[1];

            $this->debugLog("Mimetype of compressed file is $mimeType");

            if (isset($this->uncompressMap[$mimeType])) {
                $systemstr = $this->uncompressMap[$mimeType];
                $systemstr = str_replace('__DIR__', escapeshellarg($dir), $systemstr);
                $systemstr = str_replace('__FILE__', escapeshellarg($dir.'/'.$file), $systemstr);
                // echo "Systemstr: $systemstr\n";
            } else {
                return false;
            }

            $this->debugLog("Unpacking ".escapeshellarg($archiveFile)."...");

            $result = exec($systemstr, $output, $return_var);

            if ($return_var) {
                $this->debugLog("Unpacking of ".escapeshellarg($archiveFile)." failed!");
            }
        }
        return $result;
    }

    /**
     * Converts a given file according to this->conversionMap.
     * E.g. eps -> pdf
     */
    public function convertFile(string $fullfile): bool
    {
        $dir = dirname($fullfile);
        $file = basename($fullfile);

        // abc.tex.pdf --> prefix: abc.tex  suffix --> .pdf
        $splitpos = mb_strrpos($file, '.');
        $prefix = mb_substr($file, 0, $splitpos);
        $suffix = mb_substr($file, $splitpos);

        if (isset($this->conversionMap[$suffix]['app'])) {
            // create name of destfile
            $destfile = str_replace('__PREFIX__', $prefix, $this->conversionMap[$suffix]['destfile']);
            $destfile = str_replace('__SUFFIX__', $suffix, $destfile);

            // change to dir, so we can run repstopdf, which makes sure that we do not
            // escape the directory.
            $saveDir = getcwd();
            chdir($dir);

            // only convert if destFile does not yet exist
            if (!file_exists($destfile)) {
                $systemstr = $this->conversionMap[$suffix]['app'];
                $systemstr = str_replace('__FILE__', escapeshellarg($file), $systemstr);
                $systemstr = str_replace('__DEST__', escapeshellarg($destfile), $systemstr);

                $retval = system($systemstr);
            } else {
                $this->debugLog("Destfile $destfile already exists...");
            }

            chdir($saveDir);
        }
        return true;
    }

    /**
     * Tries to find tex files in given directory.
     */
    public function findTexFileInDirectory(string $dir): array
    {
        if (self::$removeDirsEnabled
            && preg_match($this->removeDirsPattern, $dir)
        ) {
            UtilFile::deleteDirR($dir);
            $this->debugLog("$dir removed.");
            return [];
        }

        if (preg_match($this->ignoreDirsPattern, $dir)) {
            $this->debugLog("Ignoring $dir ...");
            return [];
        }

        $texFiles = [];
        $files = UtilFile::listDir($dir, true, false, null, true);
        foreach ($files as $file) {
            if (preg_match($this->ignoreSuffixesTexDetectionPattern, $file)
                || preg_match($this->ignorePrefixesPattern, $file)) {
                $this->debugLog("$file ignored ...");
                continue;
            }

            $fullfilename = $dir . '/' . $file;
            if ($this->isFileTexfile($fullfilename)) {
                $this->debugLog($file . ': possible Tex...');
                // the detected $texEngine is not needed here
                if ($this->isFileMainTexfile($fullfilename, $texEngine)) {
                    $this->debugLog($file .': Mainfile!');
                    $texFiles[] = $fullfilename;
                }
            }
        }
        return $texFiles;
    }

    /**
     * Tries to find and uncompress archive files.
     * Uncompresses files in the given directory.
     */
    public function findAndUncompressFiles(string $directory): void
    {
        // try to find compressed files. They need to be uncompressed, as they
        // may contain images
        // This also needs go among all directories
        $result_dirs = array($directory);
        $current_depth = 0;
        UtilFile::listDirR($directory, $result_dirs, $current_depth, true, true, '');
        //print_r($result_dirs);

        foreach ($result_dirs as $dir) {
            if (preg_match($this->ignoreDirsPattern, $dir)) {
                $this->debugLog("Ignoring $dir ...");
                continue;
            }

            $files = UtilFile::listDir($dir);
            $this->debugLog("DIR: $dir");
            foreach ($files as $file) {
                $this->debugLog($file . '... ');
                // echo $pf->compressedArchivesPattern.PHP_EOL;
                if (preg_match($this->compressedArchivesPattern, $file)) {
                    $this->uncompressFile($dir.'/'.$file);
                }
            }
        }
    }

    /**
     * Tries to return the content of Makefile.template in given directory.
     * If it does not exist or rewrite is set, create default Makefile.template.
     * @return bool|string
     */
    public function getMakefileContent(string $directory, bool $rewrite = true)
    {
        $template = $directory . '/Makefile.template';

        // possibly create makefile
        if (!is_file($template) || $rewrite) {
            // determine the number of ../ to add.
            $numBaseDir = substr_count($this->stripArticleDir($directory), '/');
            $this->debugLog("Directory: " . $directory);
            $this->debugLog("stripped: " . $this->stripArticleDir($directory));
            $this->debugLog(__METHOD__ . ": numBaseDir: " . $numBaseDir);
            $prefix = str_repeat('../', $numBaseDir) . '../../src';
            $this->debugLog(__METHOD__ . ": prefix: " . $prefix);

            $fp = fopen($template, 'w');
            if (!$fp) {
                error_log("Cannot write to file $template");
                return false;
            }
            fwrite($fp, 'PREFIX = ' . $prefix . PHP_EOL);
            fwrite($fp, 'include $(PREFIX)/script/make/Makefile.paper.vars' . PHP_EOL);
            fwrite($fp, 'TARGET.base = #TARGET#' . PHP_EOL);
            fwrite($fp, 'STY.base = #STY#' . PHP_EOL);
            fwrite($fp, 'CLS.base = #CLS#' . PHP_EOL);
            fwrite($fp, 'TEXENGINEOPT = #TEXENGINEOPT#' . PHP_EOL);
            fwrite($fp, 'include $(PREFIX)/script/make/Makefile.paper.in' . PHP_EOL);
            fclose($fp);
            $this->debugLog("$template created.");
        }

        $Makefile = file_get_contents($template);
        return $Makefile;
    }

    /**
     * Code has been moved from build to src.
     * Makefiles will still work inside docker, but not outside.
     * Adapt Makefiles accordingly.
     * @return int
     */
    public function fixMakefile(string $directory)
    {
        $files = ['Makefile', 'Makefile.template'];
        $totalCount = 0;
        foreach ($files as $file) {
            $filename = $directory . '/' . $file;
            error_log($filename);
            if (!is_file($filename)) {
                continue;
            }
            $count = 0;
            $content = file_get_contents($filename);
            $content = preg_replace('/^PREFIX =(.*)build$/m', 'PREFIX = ${1}src', $content, -1, $count);

            if ($count) {
                $result = file_put_contents($filename, $content);
                if (!$result) {
                    error_log("Failed to write to $filename!");
                    $count = 0;
                }
            }
            $totalCount += $count;
        }

        return $totalCount;
    }

    /**
     * Tries to import a tex file in the given directory.
     * If $destDir is not empty, it will be moved to $destDir.
     * It also implicitly assumes that file has been uploaded to UPLOADDIR/tmp ...
     *
     * @return int|string // int = errorcode, string = filename
     */
    public function importTex(
            string $currentDir,
            string $makeFileDir = '',
            string $destDir = '',
            bool $rewrite = false,
            string $projectId = '',
            string $projectSrc = ''
    ) {
        $cfg = Config::getConfig();
        $this->debugLog(__METHOD__ . ": currentDir is $currentDir");
        $this->debugLog(__METHOD__ . ": destDir is $destDir");

        if (empty($makeFileDir)) {
            if ($destDir != '') {
                $makeFileDir = $destDir;
            } else {
                $makeFileDir = $currentDir;
            }
        }

        $Makefile = $this->getMakefileContent($makeFileDir, $rewrite);

        $this->findAndUncompressFiles($currentDir);

        // list directories again, we might have new ones now
        // also look for files in the current directory
        $result_dirs = [$currentDir];
        $current_depth = 0;
        UtilFile::listDirR($currentDir, $result_dirs, $current_depth, true, true, '//');

        // error_log(print_r($result_dirs, 1));

        foreach ($result_dirs as $dir) {
            if (self::$removeDirsEnabled
                && preg_match($this->removeDirsPattern, $dir)
            ) {
                $this->debugLog("Deleting directory $dir");
                UtilFile::deleteDirR($dir);
                continue;
            }

            if (preg_match($this->ignoreDirsPattern, $dir)) {
                $this->debugLog("Ignoring $dir ...");
                continue;
            }

            $files = UtilFile::listDir($dir);
            $this->debugLog("DIR: $dir");

            // first uncompress files
            foreach ($files as $file) {
                $this->debugLog($file . '... ');
                if (preg_match($this->conversionMapPattern, $file)) {
                    $this->convertFile($dir . '/' . $file);
                    $this->debugLog("Converting " . $file . '... ');
                }
            }

            // compute list again, tex file might have been in compressed file.
            $files = UtilFile::listDir($dir);
            // prefer main.tex, mainfile.tex
            $files = UtilSort::sortPreferValues($files, ['main.tex', 'mainfile.tex']);

            foreach ($files as $file) {
                if (preg_match($this->ignoreSuffixesTexDetectionPattern, $file)
                    || preg_match($this->ignorePrefixesPattern, $file)
                ) {
                    $this->debugLog("$file ignored...");
                    continue;
                }

                if ($this->isFileTexfile($dir . '/' . $file)) {
                    $this->debugLog($file . ': possible Tex...');
                    if ($this->isFileMainTexfile($dir . '/' . $file, $texEngine)) {
                        $this->debugLog($file . ': Mainfile!');
                        $saveDir = getcwd();
                        chdir($dir);
                        $maintexfile = $dir . '/.maintexfile';
                        $fp = @fopen($maintexfile, 'w');
                        if (!$fp) {
                            // this can happen, if an uncompressed folder has wrong permissions.
                            // Since the created directory is unknown in advance, it is easier to
                            // just change permissions
                            // make $dir user writeable
                            $perms = fileperms($dir);
                            $newperms = $perms |= 0x0080;
                            if ($newperms != $perms) {
                                chmod($dir, $perms);
                            }
                            // now it should work...
                            $fp = @fopen($maintexfile, 'w');
                            if (!$fp) {
                                throw new WriteException("Cannot write to: $maintexfile");
                            }
                        }

                        // make does not like filenames with spaces.
                        // for simplicity just link to a file with __ instead,
                        // and work with that file.
                        if (preg_match('/ /', $file)) {
                            $newfile = str_replace(' ', '__', $file);
                            $this->debugLog("Symlink $file --> $newfile");
                            if (!file_exists($newfile)) {
                                $success = symlink($file, $newfile);
                                if (!$success) {
                                    throw new WriteException("Cannot symlink $file -> $newfile");
                                }
                            }
                            $file = $newfile;
                        }
                        fputs($fp, $file);
                        fclose($fp);

                        if (!preg_match('/\.tex$/', $file)) {
                            // $file without $dir, since it symlinks to file in same dir
                            $newfile = $file . '.tex';
                            $this->debugLog("Symlink $file --> $newfile");
                            if (!file_exists($newfile)) {
                                $success = symlink($file, $newfile);
                                if (!$success) {
                                    throw new WriteException("Cannot symlink $file -> $newfile");
                                }
                            }
                        }

                        $generatedMakefile = $Makefile;
                        // write Makefile
                        // if we are in subdirectories below Manuscript
                        if ($destDir === '') {
                            $stripped = $this->stripArticleDir($dir);
                        } else {
                            $newDir = str_replace(dirname($currentDir), $destDir, $currentDir);
                            $stripped = $this->stripArticleDir($newDir);
                        }
                        // directory might contain trailing slash
                        $stripped = rtrim($stripped, '/');
                        $this->debugLog("Current: " . $currentDir);
                        $this->debugLog("Dir: " . $dir);
                        $this->debugLog("Stripped: " . $stripped);
                        $depth = substr_count($stripped, '/');
                        $this->debugLog("Depth: $depth");
                        if ($depth > 0) {
                            $addDotDir = 'PREFIX = ' . str_repeat('../', $depth + 2);
                            $this->debugLog($addDotDir);
                            $generatedMakefile = str_replace('PREFIX = ../../', $addDotDir, $generatedMakefile);
                        }
                        $targetBase = preg_replace('/\.tex$/', '', $file);
                        $generatedMakefile = str_replace('#TARGET#', $targetBase, $generatedMakefile);
                        // texEngine needs to be translated into option
                        $texEngineOpt = $cfg->validPdfTexEngines[$texEngine] ?? '-pdf';
                        $generatedMakefile = str_replace('#TEXENGINEOPT#', $texEngineOpt, $generatedMakefile);
                        file_put_contents($dir . '/Makefile', $generatedMakefile);
                        chdir($saveDir);

                        /*
                         * on import destDir is not empty, on scan destDir is empty
                         */
                        if ($destDir !== '') {
                            $newDir = str_replace(dirname($currentDir), $destDir, $currentDir);
                            $addDir = str_replace(dirname($currentDir), $destDir, $dir);
                            if (file_exists($newDir)) {
                                $this->debugLog("Skipping $newDir: directory already exists");
                                $retval = self::DIRECTORYEXISTS;
                                continue;
                            }
                            // need to implement own rename because of Windows
                            $result = UtilFile::rename($currentDir, $newDir);
                            if (!$result) {
                                $this->debugLog(__METHOD__ . ": Failed to move $currentDir to $newDir");
                                return self::MOVEDIRERROR;
                            }
                            UtilFile::makeDirWritable($newDir);
                            $this->debugLog("DIR: $dir");
                            $this->debugLog("NEWDIR: $newDir");
                            $this->debugLog("ADDDIR: $addDir");

                            $toAdd = preg_replace('#^' . ARTICLEDIR . '/#', '', $addDir);
                        } else {
                            UtilFile::makeDirWritable($dir);
                            $toAdd = preg_replace('#^' . ARTICLEDIR . '/#', '', $dir);
                        }
                        $this->debugLog("Checking $toAdd, $file");
                        if (StatEntry::addNew(
                            $toAdd,
                            $file,
                            1,
                            'none',
                            'unknown',
                            'none',
                            $projectId,
                            $projectSrc)
                        ) {
                            $this->debugLog("Added $file.");
                            // Add only one file per subdir
                            return $file;
                        } else {
                            $this->debugLog("Exists $file.");
                            // Try to add only one file per subdir
                            return self::TEXFILEEXISTS;
                        }
                    }
                }
            }
        }
        return $retval ?? self::NOTEXFILEFOUND;
    }

    /**
     * Tries to import a cls/sty file in the given directory.
     * If $destDir is not empty, it will be moved to $destDir.
     * It also implicitly assumes that file has been uploaded to UPLOADDIR/tmp ...
     *
     * @return int|string[] // int errorcode, [] name of filenames
     */
    public function importClsSty(
        string $currentDir,
        string $destDir = '',
        bool $isSingleFile
    )
    {
        $this->debugLog(__METHOD__ . ": currentDir is $currentDir");
        $this->debugLog(__METHOD__ . ": destDir is $destDir");

        if ($isSingleFile) {
            $files = UtilFile::listDir($currentDir);

            // should be only one...
            foreach ($files as $file) {
                // need to implement own rename because of Windows
                $currentFile = $currentDir . '/' . $file;
                $newFile = $destDir . '/' . $file;
                $result = UtilFile::rename($currentFile, $newFile);
                if (!$result) {
                    $this->debugLog(__METHOD__ . ": Failed to move $currentFile to $newFile");
                    return self::MOVEDIRERROR;
                }
                $this->debugLog("NEWFILE: $newFile");
                UtilFile::makeFileWritable($newFile);

                return [$file];
            }
        } else {
            $this->findAndUncompressFiles($currentDir);

            $result_dirs = [$currentDir];
            $current_depth = 0;
            UtilFile::listDirR($currentDir, $result_dirs, $current_depth, true, true, '//');

            foreach ($result_dirs as $dir) {
                $files = UtilFile::listDir($dir);
                $this->debugLog("DIR: $dir");

                $files = UtilFile::listDir($dir);
                /*
                 * on import destDir is not empty, on scan destDir is empty
                 */
                if ($destDir !== '') {
                    $newDir = str_replace(dirname($currentDir), $destDir, $currentDir);
                    $addDir = str_replace(dirname($currentDir), $destDir, $dir);
                    if (file_exists($newDir)) {
                        $this->debugLog("Skipping $newDir: directory already exists");
                        $retval = self::DIRECTORYEXISTS;
                        continue;
                    }
                    // need to implement own rename because of Windows
                    $result = UtilFile::rename($currentDir, $newDir);
                    if (!$result) {
                        $this->debugLog(__METHOD__ . ": Failed to move $currentDir to $newDir");
                        return self::MOVEDIRERROR;
                    }
                    UtilFile::makeDirWritable($newDir);
                    $this->debugLog("DIR: $dir");
                    $this->debugLog("NEWDIR: $newDir");

                    return $files;
                }
            }
        }
        return $retval ?? self::FILENOTFOUND;
    }

    /**
     * Returns all children of given directory.
     */
    public function getAllSubDirs(string $directory): array
    {
        $result_dirs = array($directory);
        $current_depth = 0;
        UtilFile::listDirR($directory, $result_dirs, $current_depth, true, true, '');
        return $result_dirs;
    }

    /**
     * Returns all direct children of given directory.
     */
    public function getSubDirs(string $directory): array
    {
        $current_depth = 0;
        $resultDirs = UtilFile::listDir($directory, true, true, null, false, true);
        return $resultDirs;
    }

    public function getFilesInDir(string $directory): array
    {
        $current_depth = 0;
        $resultFiles = UtilFile::listDir($directory, true, true, null, true, false);
        return $resultFiles;
    }

    /**
     * @param $directory
     * @return string|string[]|null
     */
    public function stripArticleDir(string $directory)
    {
        return preg_replace('#^' . ARTICLEDIR . '/#', '', $directory);
    }
}
