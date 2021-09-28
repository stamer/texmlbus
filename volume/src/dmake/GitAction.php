<?php
/**
 * MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

/**
 * Parses the output of git pull and updates the files in the worker directories.
 */
class GitAction
{
    private $baseDirectory;
    private $workerDirectories;

    private $changedFiles;
    private $deletedFiles;
    private $createdFiles;
    private $renamedFiles;

    public function __construct($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
        $this->workerDirectories = UtilFile::findWorkerDirectories($this->baseDirectory);
    }

    /**
     * Parses output and update worker directories.
     */
    public function updateWorkerDirectories($output): bool
    {
        $this->parsePullOutput($output);
        $this->updateChanged();
        $this->updateDeleted();
        $this->updateCreated();
        $this->updateRenamed();
        return true;
    }

    /**
     * @param array|string $output
     */
    public function parsePullOutput($output): void
    {
        if (is_array($output)) {
            $output = implode("\n", $output);
        }

        $matches = [];
        // main.tex | 1 -
        preg_match_all('/^\s(.*)\s\|\s\d+.*$/m', $output, $matches, PREG_SET_ORDER);
        $this->changedFiles = [];
        foreach ($matches as $match) {
            $this->changedFiles[] = ['filename' => $match[1]];
        }

        $matches = [];
        // delete mode 100644 main.tex
        preg_match_all('/^ (delete)\s+mode\s+(\d{6})\s+(.*)$/m', $output, $matches, PREG_SET_ORDER);
        $this->deletedFiles = [];
        foreach ($matches as $match) {
            $this->deletedFiles[] = ['mode' => $match[2], 'filename' => $match[3]];
        }
        $matches = [];
        // create mode 100644 main.tex
        preg_match_all('/^ (create)\s+mode\s+(\d{6})\s+(.*)$/m', $output, $matches, PREG_SET_ORDER);
        $this->createdFiles = [];
        foreach ($matches as $match) {
            $this->createdFiles[] = ['mode' => $match[2], 'filename' => $match[3]];
        }

        // rename test/{main.tex => blib.tex}
        // rename main.tex => blib.tex
        preg_match('/^ (rename.*)$/m', $output, $matches);
        $this->renamedFiles = [];
        foreach ($matches as $match) {
            // change with directories
            if (strpos($match[2], '{') !== false) {
                // A file in a subdirectory has changed.
                preg_match('/(.*)\{(.*)\s=\>\s(.*)\}/', $match[2], $rmatch);
                $this->renamedFiles[] = ['directory' => $rmatch[1], 'from' => $rmatch[2], 'to' => $rmatch[3]];
            } else {
                // Simple file has changed.
                preg_match('/(.*)\s=\>\s(.*)/', $match[2], $rmatch);
                $this->renamedFiles[] = ['directory' => '.', 'from' => $rmatch[1], 'to' => $rmatch[2]];
            }
        }
    }

    /**
     * Updates all changed files in worker directories.
     */
    public function updateChanged()
    {
        $currentDir = getcwd();
        chdir($this->baseDirectory);
        foreach ($this->workerDirectories as $workerDirectory) {
            foreach ($this->changedFiles as $file) {
                $success = UtilFile::linkR(
                    $file['filename'],
                    $workerDirectory . '/' . $file['filename'],
                    '',
                    '/\\.bbl$|Makefile$/'
                );
            }
        }
        chdir($currentDir);
    }

    /**
     * Updates all deleted files in worker directories.
     */
    public function updateDeleted()
    {
        $currentDir = getcwd();
        chdir($this->baseDirectory);
        foreach ($this->workerDirectories as $workerDirectory) {
            foreach ($this->deletedFiles as $file) {
                $success = unlink($workerDirectory . '/' . $file['filename']);
            }
        }
        chdir($currentDir);
    }

    /**
     * Updates all created files in worker directories.
     */
    public function updateCreated()
    {
        $currentDir = getcwd();
        chdir($this->baseDirectory);
        foreach ($this->workerDirectories as $workerDirectory) {
            foreach ($this->createdFiles as $file) {
                $success = UtilFile::linkR($file['filename'], $workerDirectory . '/' . $file['filename']);
            }
        }
        chdir($currentDir);
    }

    /**
     * Updates all renamed files in worker directories.
     */
    public function updateRenamed()
    {
        $currentDir = getcwd();
        chdir($this->baseDirectory);
        foreach ($this->workerDirectories as $workerDirectory) {
            chdir($this->baseDirectory . '/' . $workerDirectory);
            foreach ($this->renamedFiles as $file) {
                $success = UtilFile::rename(
                    $file['directory'] . '/' . $file['from'],
                    $file['directory'] . '/' . $file['to']
                );
            }
        }
        chdir($currentDir);
    }
}
