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

class IeeeconfClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'IEEEconf';

    /**
     * @inheritDoc
     */
    protected $publisher = 'IEEE Computer Society Press';

    /**
     * @inheritDoc
     */
    protected $url = 'http://mirrors.ctan.org/macros/latex/contrib/IEEEconf.zip';

    protected $files = ['ieeeconf.cls'];

    protected $comment = 'IEEE conference proceedings';
}

