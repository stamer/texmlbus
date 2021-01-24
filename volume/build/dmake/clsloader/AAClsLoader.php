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

class AaClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'aa';

    /**
     * @inheritDoc
     */
    protected $publisher = 'EDP Sciences';

    /**
     * @inheritDoc
     */
    protected $url = 'http://ftp.edpsciences.org/pub/aa/aa-package.zip';

    protected $files = ['aa.cls'];

    protected $comment = 'Astronomy & Astrophysics';
}

