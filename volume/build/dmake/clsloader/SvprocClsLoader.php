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

class SvprocClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'svproc';

    /**
     * @inheritDoc
     */
    protected $publisher = 'SpringerNature';

    /**
     * @inheritDoc
     */
    protected $url = 'ftp://ftp.springernature.com/cs-proceeding/svproc/templates/ProcSci_TeX.zip';

    protected $files = ['svproc.cls'];

    protected $comment = 'Proceedings';
}

