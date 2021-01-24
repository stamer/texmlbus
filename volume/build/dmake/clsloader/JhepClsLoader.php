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

class JhepClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'JHEP';

    /**
     * @inheritDoc
     */
    protected $publisher = 'SpringerNature';

    /**
     * @inheritDoc
     */
    protected $url = 'http://mirrors.ctan.org/macros/latex/contrib/jhep/JHEP.cls';

    protected $files = ['JHEP.cls'];

    protected $comment = 'Journal of High Energy Physics';
}

