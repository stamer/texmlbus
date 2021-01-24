<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

use Dmake\UtilFile;
use Dmake\UtilZipfile;

/**
 * Abstract Class to load .cls and .sty files from somewhere
 */
abstract class AbstractClsLoader
{
    /**
     * @var string $name name of the loader
     */
    protected $name;

    /**
     * @var string $publisher name of the publisher
     * Used for grouping on installSty.
     */
    protected $publisher;

    /**
     * @var string url of the zip file
     * Where to download the sources from.
     */
    protected $url;

    /**
     * @var string[] cls/sty files of package
     * Files that are checked for existence, to determine whether
     * package is installed or not.
     */
    protected $files = [];

    /**
     * @var string some comment
     */
    protected $comment = '';

    /**
     * @var string[] installed cls/sty files of package
     */
    protected $installedFiles = [];

    /**
     * @var string name of localFilename
     */
    protected $localFilename;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPublisher(): string
    {
        return $this->publisher;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param string[] $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * @return string[]
     */
    public function getInstalledFiles(): array
    {
        return $this->installedFiles;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @param string[] $installedFiles
     */
    public function setInstalledFiles(array $installedFiles): void
    {
        $this->installedFiles = $installedFiles;
    }

    public function getLocalFilename(): string
    {
        return $this->localFilename;
    }

    public function setLocalFilename(string $localFilename): void
    {
        $this->localFilename = $localFilename;
    }

    /**
     * installs the cls/sty files
     * @return bool
     */
    public function download(string $url): string
    {
        $tmpDir = UtilFile::createTempDir();
        $suffix = UtilFile::getSuffix($url);
        $tmpFile = $tmpDir . '/destfile' . $suffix;
        $this->localFilename = UtilFile::downloadUrl($url, $tmpFile);
        return $tmpFile;
    }

    /**
     * installs the cls/sty files
     * @return bool
     */
    public function install(): bool
    {
        $this->localFilename = $this->download($this->url);
        if (!$this->localFilename) {
            return false;
        }
        $tmpDestDir = UtilFile::createTempDir();
        $suffix = UtilFile::getSuffix($this->localFilename);
        if ($suffix === '.zip') {
            UtilZipfile::extract($this->localFilename, $tmpDestDir);
        } else {
            UtilFile::rename($this->localFilename, $tmpDestDir . '/' . basename($this->url));
        }
        // remove tmpDir of downloaded file
        UtilFile::deleteDirR(dirname($this->localFilename));

        $publisherDir = ARTICLESTYDIR . '/' . $this->getPublisher();
        UtilFile::ensureDirExists($publisherDir);
        $destDir = $publisherDir . '/' . $this->getName();
        UtilFile::deleteDirR($destDir);
        UtilFile::rename($tmpDestDir, $destDir);
        $this->installedFiles = UtilStylefile::getInstalledClsStyFiles($destDir);

        // remove tmpDir of extracted files / file.
        UtilFile::deleteDirR($tmpDestDir);
        return true;
    }
}

