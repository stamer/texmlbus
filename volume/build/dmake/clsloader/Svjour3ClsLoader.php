<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;
use Dmake\UtilFile;
use Dmake\UtilStylefile;
use Dmake\UtilZipfile;

class Svjour3ClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'svjour3';

    /**
     * @inheritDoc
     */
    protected $publisher = 'SpringerNature';

    /**
     * @inheritDoc
     */
    protected $url = 'https://static.springer.com/sgw/documents/468198/application/zip/LaTeX_DL_468198.zip';

    protected $files = ['svjour3.cls'];
}

