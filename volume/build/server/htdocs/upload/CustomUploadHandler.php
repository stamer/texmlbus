<?php
/**
 * MIT License
 *
 * (c) 2019 - 2020 Heinrich Stamerjohanns
 *
 */
namespace Server\Upload;

error_reporting(E_ALL | E_STRICT);
require_once('../../include/IncFiles.php');
require_once('UploadHandler.php');

use Dmake\PrepareFiles;
use Dmake\UtilFile;
use Dmake\UtilZipfile;

class CustomUploadHandler extends UploadHandler
{
    private $debug = true;

    /**
     * CustomUploadHandler constructor.
     * @param null $options
     * @param bool $initialize
     * @param null $error_messages
     */
    public function __construct($options = null, $initialize = true, $error_messages = null)
    {
        $options['import_type'] = 'POST';
        $options['upload_dir'] = UPLOADDIR . '/';
        $options['upload_url'] = '/files/upload/';
        parent::__construct($options, $initialize, $error_messages);
        $this->debugLog('UPLOADDIR: ' . UPLOADDIR);
    }

    /**
     *
     */
    protected function initialize()
    {
        parent::initialize();
    }

    protected function debugLog($str)
    {
        if ($this->debug) {
            error_log($str);
        }
    }
    /**
     * @param $file
     * @param $index
     */
    protected function handle_form_data($file, $index)
    {
        $file->title = $_REQUEST['title'][$index] ?? '';
        $file->description = $_REQUEST['description'][$index] ?? '';
    }

    /**
     * @param $uploaded_file
     * @param $name
     * @param $size
     * @param $type
     * @param $error
     * @param null $index
     * @param null $content_range
     * @return stdClass
     */
    protected function handle_file_upload(
        $uploaded_file,
        $name,
        $size,
        $type,
        $error,
        $index = null,
        $content_range = null
    ) {
        $file = parent::handle_file_upload(
            $uploaded_file,
            $name,
            $size,
            $type,
            $error,
            $index,
            $content_range
        );

        return $file;
    }

    /**
     * @param $file
     */
    protected function set_additional_file_properties($file)
    {
        parent::set_additional_file_properties($file);

        if (empty($file->error)) {
            $this->debugLog(print_r($file, 1));
            if (isset($file->type) && $file->type === 'application/zip') {
                $file->subDirs = UtilZipfile::listSubdirs(UPLOADDIR . '/' . $file->name);
                $file->num_files = count($file->subDirs);
            }

            $file->importUrl = $this->options['script_url']
                . $this->get_query_separator($this->options['script_url'])
                . $this->get_singular_param_name()
                . '=' . rawurlencode($file->name);
            $file->importType = $this->options['import_type'];
            if (true || $file->importType !== 'POST') {
                $file->importUrl .= '&_method=IMPORT';
            }
        }
    }

    /**
     * @param bool $print_response
     * @throws WriteException
     */
    public function import($print_response = true)
    {
        $destSet = $this->get_query_param('_destset');
        $response = array();
        $response['isImport'] = true;
        $response['destSet'] = $destSet;
        $response['files'] = [];
        $documentsImported = 0;

        if (empty($destSet)) {
            $response['message'] = "No set specified, please select a set where to import to.";
            return $this->generate_response($response, $print_response);
        }
        // upload: hard-coded restriction, we already use this directory for uploads.
        // overall: hard-coded restriction, we already use this to name all sets.
        if ($destSet === 'upload' || $destSet === 'overall') {
            $response['message'] = "The set name may not be named <em>" . $destSet . "</em>.";
            return $this->generate_response($response, $print_response);
        }

        if (strpos($destSet, '..') !== false) {
            $response['message'] = "The set name may not contain two dot characters: ..";
            return $this->generate_response($response, $print_response);
        }
        if (strpos($destSet, '/') !== false) {
            $response['message'] = "The set name may not contain a forward slash: /";
            return $this->generate_response($response, $print_response);
        }

        $destDir = ARTICLEDIR . '/' . $destSet;
        if (!is_dir($destDir)) {
            $result = mkdir($destDir);
            if (!$result) {
                error_log(__METHOD__ . ": Failed to create $destDir");
                $response['num_documents'] = 0;
                $response['message'] = "Failed to create $destDir";
                return $this->generate_response($response, $print_response);
            }
        }
        $fileNames = $this->get_file_names_params();
        if (empty($fileNames)) {
            $fileNames = array($this->get_file_name_param());
        }

        foreach ($fileNames as $fileName) {
            $this->debugLog("File: $fileName");
            $filePath = $this->get_upload_path($fileName);
            $fileFound = (strlen($fileName) > 0) && ($fileName[0] !== '.') && is_file($filePath);
            if ($fileFound) {
                $tmpUploadDir = $this->createTempDir();
                $this->debugLog('tmpUploadDir:' . $tmpUploadDir);
                if (UtilFile::isFileZipfile($filePath)) {
                    // use this if we have a single file or zip file that
                    // extracts to the same directory
                    $filePrefix = UtilFile::sanitizeFilename($fileName, true);
                    $this->debugLog('IsZipfile.. ' . $tmpUploadDir);
                    $result = UtilZipfile::extract($filePath, $tmpUploadDir);
                    if (!$result) {
                        $response['message'] = 'Failed to extract ' . $fileName . '.';
                    } else {
                        $this->debugLog("Succesfully extracted $fileName");
                    }

                    $pf = new PrepareFiles();
                    /*
                     * There several options now.
                     * The zip file could be consist of
                     * subdir/content
                     *  subdir/main.tex
                     *  subdir/images/1.png
                     *  subdir/images/2.png
                     *
                     * or the zip file extracts to the same directory
                     * main.tex
                     * images/1.png
                     * images/2.png
                     */
                    $subDirs = $pf->getSubdirs($tmpUploadDir);
                    $this->debugLog('SubDirs: ' . count($subDirs));
                    $filesInDir = $pf->getFilesInDir($tmpUploadDir);
                    $this->debugLog('filesInDir: ' . count($filesInDir));

                    $renameTo = '';

                    if (count($filesInDir)) {
                        // zipfile extracts to same dir
                        // just a dir that holds the subdirs?
                        $this->debugLog("Zipfile $fileName extracts into same directory.");
                        $pf = new PrepareFiles();
                        $files = $pf->findTexFileInDirectory($tmpUploadDir);
                        if (empty($files)) {
                            $subDirs = $pf->getSubdirs($tmpUploadDir);
                        } else {
                            $subDirs = ['.'];
                        }
                        // import will be renamed to name of zipfile.
                        $renameTo = $filePrefix;
                    } elseif (count($subDirs) == 1 && !count($filesInDir)) {
                        // just a dir that holds the subdirs?
                        $pf = new PrepareFiles();
                        $files = $pf->findTexFileInDirectory($tmpUploadDir . '/' . $subDirs[0]);
                        if (empty($files)) {
                            $subDirs = $pf->getSubdirs($tmpUploadDir . '/' . $subDirs[0]);
                        }
                    }
                    foreach ($subDirs as $subDir) {
                        $destDir = ARTICLEDIR . '/' . $destSet;
                        if ($subDir === '.') {
                            $safePrefix = UtilFile::sanitizeFilename($fileName, true);
                            $dirName = $tmpUploadDir . '/' . $safePrefix;
                            $this->createSubDir($dirName);
                            // extract again into subdir
                            // maybe we should extract into tmp/subdir right away...
                            $result = UtilZipfile::extract($filePath, $dirName);
                            $currentDir = $dirName;
                        } else {
                            $currentDir = $tmpUploadDir . '/' . $subDir;
                        }
                        $result = $pf->import(
                            $currentDir,
                            $destDir,
                            $destDir
                        );
                        if (is_string($result)) {
                            $documentsImported++;
                        }
                        $response['files'][$subDir] = $result;
                    }
                    $result = unlink($filePath);
                } else {
                    // we need a directory for single file
                    $safePrefix = UtilFile::sanitizeFilename($fileName, true);
                    $dirName = $tmpUploadDir . '/' . $safePrefix;
                    $this->createSubDir($dirName);
                    $newFilePath = $dirName . '/' . $fileName;
                    // need to implement own rename because of Windows
                    UtilFile::rename($filePath, $newFilePath);

                    try {
                        $pf = new PrepareFiles();
                        $result = $pf->import(
                            $dirName,
                            ARTICLEDIR . '/' . $destSet,
                            ARTICLEDIR . '/' . $destSet
                        );
                    } catch (Throwable $t) {
                        $response['success'] = false;
                        $response['message'] = $t->getMessage();
                        break;
                    }
                    if (is_string($result)) {
                        $documentsImported++;
                    }
                    $response['files'][$this->stripUploadTmpDir($tmpUploadDir, $dirName)] = $result;
                }

                $this->debugLog(__METHOD__ . ": Deleting tmpdir $tmpUploadDir");

                UtilFile::deleteDirR($tmpUploadDir);
            } else {
                $response['files'][$fileName] = PrepareFiles::FILENOTFOUND;
                // no unlink needed
            }
            if (!$result) {
                error_log("Failed to unlink $filePath");
            }
        }
        $response['documentsImported'] = $documentsImported;

        return $this->generate_response($response, $print_response);
    }

    /**
     * @param bool $print_response
     * @throws WriteException
     */
    public function post($print_response = true)
    {
        $this->debugLog("Importing...");
        if ($this->get_query_param('_method') === 'IMPORT') {
            return $this->import($print_response);
        }
        return parent::post($print_response);
    }

    /**
     * @param bool $print_response
     */
    public function delete($print_response = true)
    {
        $response = parent::delete(false);
        return $this->generate_response($response, $print_response);
    }

    /**
     * @param $tmpUploadDir
     * @param $directory
     * @return string|string[]|null
     */
    public function stripUploadTmpDir($tmpUploadDir, $directory)
    {
        return preg_replace('#^' . $tmpUploadDir . '/#', '', $directory);
    }

    public function createSubDir($dirName)
    {
        $result = mkdir($dirName);
        if (!$result) {
            throw new ErrorException("Failed to mkdir $dirName.");
        }
        $result = chmod($dirName, 0777);
        if (!$result) {
            error_log(__METHOD__ . "Failed to chmod $dirName");
        }
        return $dirName;
    }

    /**
     * @return string
     * @throws ErrorException
     */
    public function createTempDir()
    {
        $prefix = 'tmp';
        $uploadDir = $this->get_upload_path($prefix);
        if (!is_dir($uploadDir)) {
            error_log($uploadDir . " does not exist, creating directory");
            mkdir($uploadDir, 0777);
        }
        $dirName = tempnam($uploadDir, $prefix);
        $this->debugLog(__METHOD__ . " TempUploadDir is: $dirName");
        if ($dirName === false) {
            throw new ErrorException("Failed to tempnam $dirName.");
        }
        // has been created as file, we need a directory
        $result = unlink($dirName);
        if (!$result) {
            error_log(__METHOD__ . "Failed to unlink $dirName");
        }
        $dirName = $this->createSubDir($dirName);
        return $dirName;
    }
}


