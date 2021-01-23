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

class LlncsClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'llncs';

    /**
     * @inheritDoc
     */
    protected $publisher = 'SpringerNature';

    /**
     * @inheritDoc
     */
    protected $url = 'ftp://ftp.springernature.com/cs-proceeding/llncs/llncs2e.zip';

    protected $files = ['llncs.cls'];
}

