<?php
/**
 * MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
 *
 */
namespace Dmake;

class UtilFile
{
    public static $flc = 0;

    public static function getFileOwner(string $filename, bool $clearCache = false): string
    {
        if ($clearCache) {
            clearstatcache();
        }
        return posix_getpwuid(fileowner($filename))['name'];
    }

    public static function getFileGroup(string $filename, bool $clearCache = false): array
    {
        if ($clearCache) {
            clearstatcache();
        }
        return posix_getgrgid(filegroup($filename))['name'];
    }

    /**
     */
    public static function updateNumber(string $number, bool $first = false): void
    {
        if (!$first) {
            printf("%c%c%c%c%c%c%c%c", 8, 8, 8, 8, 8, 8, 8, 8);
        }
        printf("\n\n%8d\n\n", $number);
    }

    /**
     * get the the directories where we want to run make in
     * @param &$dirs
     * @param $restrict_dir
     */
    public static function getDirectoriesR(&$dirs, string $restrict_dir)
    {
        //$output = `cd $makedir; ls -d /papers/*`;
        if ($restrict_dir != '') {
            $pattern = '|' . ARTICLEDIR . '/' . $restrict_dir . '.*/Manuscript$|';
        } else {
            $pattern = null;
        }

        $result_dirs = [];
        $current_depth = 0;
        self::updateNumber(0, true);
        self::listDirR(ARTICLEDIR, $result_dirs, $current_depth, false, $only_dirs = true, $pattern, 2);
        sort($result_dirs, SORT_STRING);
        // we want to remove the constant path
        foreach ($result_dirs as $files) {
            $dirs[] = str_replace(ARTICLEDIR . '/', '', $files);
        }

        if (DBG_LEVEL & 2) {
            $output = explode("\n", $dirs);
            print_r($output);
        }
    }

    /**
     * @param &$dirs
     * @param $restrict_dir
     */
    public static function getDirectories(&$dirs, $restrict_dir)
    {
        if ($restrict_dir != '') {
            $pattern = ARTICLEDIR . '/' . $restrict_dir . '/*';
        } else {
            $pattern = ARTICLEDIR . '/*/*';
        }
        echo "PATTERN $pattern" . PHP_EOL;

        $dirs = [];
        foreach (glob($pattern, GLOB_ONLYDIR) as $filename) {
            $dirs[] = str_replace(ARTICLEDIR . '/', '', $filename);
        }
        sort($dirs, SORT_STRING);
    }

    /* list contents of directory (non-recursive)
     * and return files as array.
     * returns empty array on error
     */
    /**
     * @param string $dir
     * @param bool $ignore_dot ignore dot files
     * @param bool $sort_asc
     * @param null $pattern restrict to pattern
     * @param bool $only_files
     * @param bool $only_dirs
     * @return array
     */
    public static function listDir(
        $dir = './',
        $ignore_dot = true,
        $sort_asc = true,
        $pattern = null,
        $only_files = false,
        $only_dirs = false
    )
    {
        $files = [];
        $cdir = @opendir($dir);
        if (!$cdir) {
            return $files;
        }

        while (($file = readdir($cdir)) != false) {
            if (
                strcmp($file, "..") != 0
                && strcmp($file, ".") != 0
            ) {
                if (
                    !$ignore_dot
                    || $file[0] != '.'
                ) {
                    if ($only_files && is_dir($dir . '/' . $file)) {
                        continue;
                    } elseif ($only_dirs && !is_dir($dir . '/' . $file)) {
                        continue;
                    }
                    if (!empty($pattern) && !preg_match($pattern, $file)) {
                        continue;
                    }
                    $files[] = $file;
                }
            }
        }
        closedir($cdir);

        if ($sort_asc) {
            sort($files, SORT_STRING);
        } else {
            rsort($files, SORT_STRING);
        }

        return $files;
    }

    /**
     * list directories recursively
     */
    public static function listDirR(
        string $dir,
        array &$result_dirs,
        int &$current_depth = 0,
        bool $ignore_error = true,
        bool $only_dirs = true,
        ?string $pattern = null,
        ?bool $only_depth = null
    ): bool
    {
        $current_depth++;
        if (DBG_LEVEL & DBG_DIRECTORIES) {
            echo "Opening $dir...\n";
        }

        $cdir = @opendir($dir);
        if (!$cdir) {
            if ($ignore_error) {
                return false;
            } else {
                echo "Unable to open $cdir";
            }
        }
        while (($file = readdir($cdir)) != false) {
            if (
                strcmp($file, "..") != 0
                && strcmp($file, ".") != 0
            ) {
                $filename = $dir . "/" . $file;
                if (empty($pattern)) {
                    $add = !($only_dirs && (!is_dir($filename)));
                    if ($add) {
                        if (!$only_depth || $only_depth == $current_depth) {
                            if (is_dir($filename)) {
                                $result_dirs[] = $filename . '/';
                            } else {
                                $result_dirs[] = $filename;
                            }
                            if (DBG_LEVEL & DBG_DIRECTORIES) echo "$current_depth: Adding $filename...\n";
                            if (DBG_LEVEL & DBG_DIRECTORIES) {
                                self::$flc++;
                                if (self::$flc % 1000 == 0) self::updateNumber(self::$flc);
                            }
                        }
                    }
                    if (is_dir($filename)) {
                        self::listDirR($filename, $result_dirs, $current_depth, $ignore_error, $only_dirs, $pattern, $only_depth);
                    }
                } else {
                    $res = preg_match($pattern, $filename);
                    if ($res) {
                        if (DBG_LEVEL & DBG_DIRECTORIES) echo "FILE: $filename\n";
                    }
                    if ($res || ($current_depth < 3)) {
                        $add = !($only_dirs && (!is_dir($filename)));
                        if ($res && $add) {
                            if (!$only_depth || $only_depth == $current_depth) {
                                if (is_dir($filename)) {
                                    $result_dirs[] = $filename . '/';
                                } else {
                                    $result_dirs[] = $filename;
                                }
                                if (DBG_LEVEL & DBG_DIRECTORIES) echo "Adding $filename...\n";
                                if (DBG_LEVEL & DBG_DIRECTORIES) {
                                    self::$flc++;
                                    if (self::$flc % 1000 == 0) self::updateNumber(self::$flc);
                                }
                            }
                        }
                        if (is_dir($filename)) {
                            self::listDirR($filename, $result_dirs, $current_depth, $ignore_error, $only_dirs, $pattern, $only_depth);
                        }
                    }
                }
            }
        }
        closedir($cdir);

        $current_depth--;

        return true;
    }


    /**
     * recursively delete given directory
     */
    public static function deleteDirR(
        string $dir,
        bool $ignore_error = true): bool
    {
        if (is_file($dir)) {
            $result = unlink($dir);
            return $result;
        }
        $cdir = @opendir($dir);
        if (!$cdir) {
            if ($ignore_error) {
                return false;
            } else {
                echo __FILE__ . ', ' . __LINE__ . ', Unable to open ' . $cdir . PHP_EOL;
                exit(1);
            }
        }
        while (($file = readdir($cdir)) != false) {
            if (
                strcmp($file, "..") != 0
                && strcmp($file, ".") != 0
            ) {
                $filename = $dir . "/" . $file;
                if (is_dir($filename)) {
                    self::deleteDirR($filename);
                } else {
                    unlink($dir . "/" . $file);
                }
            }
        }
        closedir($cdir);
        $result = rmdir($dir);
        return $result;
    }

    /**
     * recursively copy directory (or file)
     * @param $src
     * @param $dest
     * @return bool
     */
    public static function copyR(string $src, string $dest): bool
    {
        if (is_dir($src)) {
            $success = mkdir($dest);
            if (!$success) {
                error_log(__METHOD__ . ": Failed to create $dest");
                return false;
            }
            $files = scandir($src);
            foreach ($files as $file)
                if ($file != "."
                    && $file != ".."
                ) {
                    self::copyR("$src/$file", "$dest/$file");
                }
        } elseif (file_exists($src)) {
            // avoid "The second argument to copy() function cannot be a directory"
            if (is_dir($dest)) {
                $result = copy($src, $dest . '/' . basename($src));
            } else {
                $destDir = pathinfo($dest, PATHINFO_DIRNAME);
                if (!is_dir($destDir)) {
                    // do not return on error, as is_dir might be cached...
                    mkdir($destDir, 0777, true);
                }
                $result = copy($src, $dest);
            }
            return $result;
        }
        return true;
    }

    /**
     * Copies file $src to $dest, even if the destDir does not yet exist.
     */
    public static function copy(string $src, string $dest, int $permissions = 0777): bool
    {
        $destDir = dirname($dest);
        if (!is_dir($destDir)) {
            $success = mkdir($destDir, $permissions, true);
            if (!$success) {
                return $success;
            }
        }
        $success = copy($src, $dest);
        if (!$success) {
            return $success;
        }
        $success = chmod($dest, $permissions);
        return $success;
    }

    /**
     * On Windows it is still not possible to rename across file-system boundaries. :(
     * Therefore everything is done manually.
     */
    public static function rename(string $src, string $dest): bool
    {
        $result = false;
        // rename does not work with directories
        if (!is_dir($src) && !is_dir($dest)) {
            $result = rename($src, $dest);
        }
        if (!$result) {
            $result = self::copyR($src, $dest);
            if (!$result) {
                return false;
            }
            $result = self::deleteDirR($src);
        }
        return $result;
    }

    /**
     * recursively hardlink (or copy) directories and files
     */
    public static function linkR(
        string $src,
        string $dest,
        string $ignorePattern, // pattern of directories/files to ignore
        string $copyPattern // pattern of files to copy
    ): bool
    {
        if (is_dir($src)) {
            if ($ignorePattern !== '' && preg_match($ignorePattern, $src)) {
                if (DBG_LEVEL & DBG_SETUP_FILES) {
                        echo "Ignoring $src..." . PHP_EOL;
                    }
                return true;
            }
            if (!is_dir($dest)) {
                if (is_file($dest)) {
                    unlink($dest);
                }
                $success = mkdir($dest);
                if (!$success) {
                    error_log(__METHOD__ . ": Failed to create $dest");
                    return false;
                }
            }
            $files = scandir($src);
            foreach ($files as $file)
                if ($file != "."
                    && $file != ".."
                ) {
                    self::linkR("$src/$file", "$dest/$file", $ignorePattern, $copyPattern);
                }
        } elseif (file_exists($src)) {
            if ($ignorePattern !== '' && preg_match($ignorePattern, $src)) {
                if (DBG_LEVEL & DBG_SETUP_FILES) {
                    echo "Ignoring $src..." . PHP_EOL;
                }
                return true;
            }
            if ($copyPattern !== '' && preg_match($copyPattern, $src)) {
                if (DBG_LEVEL & DBG_SETUP_FILES) {
                    echo "Copying $src -> $dest" . PHP_EOL;
                }
                $result = self::copy($src, $dest);
            } else {
                $result = self::updateRegularFileLink($src, $dest);
            }
            return $result;
        }
        return true;
    }

    /*
     * Create or update hard link, if $src is newer.
     * The directory of $dest does not need to exist.
     */
    public static function updateRegularFileLink($src, $dest): bool
    {
        $result = true;
        if (!file_exists($dest)) {
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                $result = mkdir($destDir, 0777, true);
                if (!$result) {
                    return $result;
                }
            }
            $result = link($src, $dest);
        } else {
            $srcStat = stat($src);
            $destStat = stat($dest);
            // if ino is the same, there is an existing hard link
            if ($srcStat['ino'] != $destStat['ino']) {
                if ($srcStat['mtime'] > $destStat['mtime']) {
                    unlink($dest);
                    $result = link($src, $dest);
                }
            } else {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Write a file atomically
     */
    public static function filePutContentsAtomic(
        string $filename,
        string $data,
        int $flags = 0): bool
    {
        if (file_put_contents($filename . "~", $data, $flags) === strlen($data)) {
            return rename($filename . "~", $filename);
        } else {
            unlink($filename . "~");
        }
        return false;
    }

    /**
     *  this is the old way, used to read TARGET.base from the Makefile.
     */
    public static function getSourcefileInDirViaMake(string $directory): string
    {
        // we need to get the base from Makefile
        if (!($contents = @file_get_contents($directory . '/Makefile'))) {
            return '';
        }

        $matches = array();
        preg_match('/TARGET.base = (\S+)/m', $contents, $matches);
        // matches[1] is the base file.
        if (!isset($matches[1])) {
            return '';
        }

        $checkfile = $directory . '/' . $matches[1] . '.tex';
        return $checkfile;
    }

    /**
     * Rewrites the PREFIX of a given Makefile. It adds $addLevel ../ subdirectories
     * to PREFIX (because of hardlinked creation of subdirectories).
     * @return int|false
     */
    public static function adjustMakefilePrefix(string $directory, int $addLevel)
    {
        $filename = $directory . '/Makefile';
        // we need to get the base from Makefile
        if (!($contents = @file_get_contents($filename))) {
            return '';
        }

        $addDir = str_repeat('../', $addLevel);
        $replaced = preg_replace('/^PREFIX = (\S+)/m', 'PREFIX = ' . $addDir . '\\1', $contents);

        $result = file_put_contents($filename, $replaced);

        if (!$result) {
            error_log(__METHOD__ . ': Failed to rewrite ' . $filename . '!');
        }
        return $result;
    }

    /**
     * this function expects a directory like
     * '/arXMLiv/tars_untarred/arxiv/papers/00001/hep-th.0001081'
     * or
     * '/00001/hep-th.0001081'
     * and constructs the appropriate filename for the texfile.
     */
    public static function getSourcefileInDir(string $dir, bool $with_suffix = true): string
    {
        $subdirs = explode('/', $dir);

        $c = count($subdirs);

        $filename = $dir . '/' . $subdirs[$c - 1];
        if ($with_suffix) {
            $filename .= '.tex';
        }

        return $filename;
    }

    /**
     * Return suffix of an e.g. filename.
     */
    public static function getSuffix(string $str, bool $withDot = true): string
    {
        if ($withDot) {
            return strrchr($str, ".");
        }
        return substr(strrchr($str, "."), 1);
    }

    /**
     * Return prefix of filename.
     * basename() is not used, as it is locale dependent and you need to
     * specify the suffix to be removed as well.
     */
    public static function getPrefix(string $filename): string
    {
        $pos = strpos($filename, '.');
        if ($pos === false) {
            return $filename;
        }
        return substr($filename, 0, $pos);
    }

    /**
     *
     * parse makefile to find out whether current is actually a tex file
     *
     */
    public static function isFileTexfile(string $checkfile): bool
    {
        $cfg = Config::getConfig();
        $file = $cfg->app->file;

        if ($checkfile == '') {
            return false;
        }

        $command = "$file -Li '$checkfile'";
        if (DBG_LEVEL & DBG_EXEC) {
            error_log(__METHOD__ . ": Executing $command");
        }

        $retstr = `$command`;

        if (
            strpos($retstr, 'text/') !== false
            || strpos($retstr, 'application/octet-stream') !== false
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $checkfile
     * @return bool
     */
    public static function isFileZipfile(string $checkfile): bool
    {
        $cfg = Config::getConfig();
        $file = $cfg->app->file;

        if ($checkfile == '') {
            return false;
        }

        $command = "$file -Li '$checkfile'";
        if (DBG_LEVEL & DBG_EXEC) {
            error_log(__METHOD__ . ": Executing $command");
        }

        $retstr = `$command`;

        if (strpos($retstr, 'application/zip') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function createSubDir(string $dirname, $permissions = 0777): bool
    {
        $result = mkdir($dirname);
        if (!$result) {
            throw new ErrorException("Failed to mkdir $dirname.");
        }
        $result = chmod($dirname, $permissions);
        if (!$result) {
            error_log(__METHOD__ . "Failed to chmod $dirname");
        }
        return $result;
    }

    /**
     * creates a temporary directory in $dirname
     * @return string
     * @throws ErrorException
     */
    public static function createTempDir(string $dirname = '', $permissions = 0777): string
    {
        if ($dirname === '') {
            $dirname = sys_get_temp_dir();
        }
        $prefix = 'tmp';
        if (!is_dir($dirname)) {
            error_log($dirname . " does not exist, creating directory");
            $result = mkdir($dirname, $permissions);
            if (!$result) {
                throw new ErrorException("Failed to mkdir $dirname.");
            }
        }
        $tmpDirname = tempnam($dirname, $prefix);
        if ($tmpDirname === false) {
            throw new ErrorException("Failed to tempnam $tmpDirname.");
        }
        // has been created as file, we need a directory
        $result = unlink($tmpDirname);
        if (!$result) {
            error_log(__METHOD__ . "Failed to unlink $tmpDirname");
        }
        $result = self::createSubDir($tmpDirname);
        return $tmpDirname;
    }


    /**
     * parse given file to find out whether current is actually a latex file
     */
    public static function isFileLatexfile(string $checkfile): bool
    {
        if (!($contents = @file_get_contents($checkfile))) {
            false;
        }

        // Avoid comments
        $pattern = '/^\s*(?!%)\s*\\\\document(style|class)/mi';

        if (preg_match($pattern, $contents)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Ensures that a directory exists. If it does not exist, create
     * directory.
     */
    public static function ensureDirExists(string $dir): bool
    {
        if (!is_dir($dir)) {
            $success = mkdir($dir, 0777, true);
            return $success;
        } else {
            return true;
        }
    }

    public static function getSubDirs(string $directory): array
    {
        $resultDirs = self::listDir($directory, true, true, null, false, true);
        return $resultDirs;
    }

    /**
     * Removes files in directory
     */
    public static function cleanupDir(string $directory, string $action): void
    {
        $cfg = Config::getConfig();
        if (DBG_LEVEL & DBG_DIRECTORIES) {
            echo "$directory\n";
        }
        chdir(ARTICLEDIR . '/' . $directory);

        $possibleCleanActions = array('clean');

        foreach ($cfg->stages as $stage => $value) {
            $possibleActions[] = $stage;
            $possibleCleanActions[] = $stage.'clean';
        }

        // Cleanup via make.
        if (in_array($action, $possibleCleanActions)) {
            echo "Cleaning up...\n";
            echo "Dir: " . $directory . "\n";
            // ARTICLEDIR./.$directory need quotes!
            $systemCmd = 'cd "' . ARTICLEDIR . '/' . $directory . '" && /usr/bin/make ' . $action;
            if (DBG_LEVEL & DBG_MAKE) {
                echo "Make $action $directory...\n";
            }
            exec($systemCmd, $output, $result_code);
            if (DBG_LEVEL & DBG_MAKE) {
                print_r($output);
            }
            if ($result_code) {
                echo "Command failed: $systemCmd" . PHP_EOL;
            }
        }
    }

    public static function sanitizeFilename(string $fileName, $removeSuffix = false): string
    {
        // does not work on Alpine, needs 
        // $asciiName = iconv('UTF-8', 'ASCII//TRANSLIT', $fileName);
        $asciiName = iconv('UTF-8', 'ASCII', $fileName);
        if ($removeSuffix) {
            $suffix = self::getSuffix($asciiName);
            $asciiName = substr($asciiName, 0, -strlen($suffix));
        }
        $safeName = preg_replace('/[^A-Za-z0-9\.\-]/', '_', $asciiName);
        error_log('SafeName:' . $safeName);
        return $safeName;
    }

    public static function makeDirWritable(string $directory)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $item) {
            if (is_dir($item)) {
                chmod($item, 0777);
            } else {
                chmod($item, 0666);
            }
        }
    }

    /**
     * Makes a file world-writable
     */
    public static function makeFileWritable(string $filename): bool
    {
        $result = chmod($filename, 0666);
        return $result;
    }

    public static function downloadUrl(string $url, string $destFile)
    {
        $fp = fopen($destFile, 'w');
        if (!$fp) {
            throw new \ErrorException("Unable to create $destFile!");
        }
        $options = array(
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 600,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true
        );

        $ch = curl_init();
        if (!$ch) {
            throw new \ErrorException("Failed to init curl.");
        }
        curl_setopt_array($ch, $options);
        $success = curl_exec($ch);
        if (!$success) {
            throw new \ErrorException("Download of $url failed.");
        }
        curl_close($ch);
    }

    /**
     * Find current worker directories that need to be updated.
     */
    public static function findWorkerDirectories(string $directory): array
    {
        $cfg = Config::getConfig();
        $workerDirectories = [];
        $currentDir = getcwd();
        chdir($directory);
        $dirs = glob($cfg->server->workerPrefix . '*');

        foreach ($dirs as $key => $dir) {
            if (is_dir($dir)) {
                $workerDirectories[] = $dir;
            }
        }
        chdir($currentDir);
        return $workerDirectories;
    }

    public static function isFileNewerThan($file, $thanfile) 
    {
        return filemtime($file) > filemtime($thanfile);
    }
}
