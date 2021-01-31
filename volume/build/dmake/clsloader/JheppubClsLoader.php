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

class JheppubClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('jheppub');
        $this->setPublisher('SpringerNature-Sissa');
        $this->setUrl('https://jhep.sissa.it/jhep/help/JHEP/TeXclass/DOCS/jheppub.sty');
        $this->setFiles(['jheppub.sty']);
        $this->setComment('');
    }
}

