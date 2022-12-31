<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;

class SvprocClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('svproc');
        $this->setPublisher('SpringerNature');
        $this->setUrl('ftp://ftp.springernature.com/cs-proceeding/svproc/templates/ProcSci_TeX.zip');
        $this->setFiles(['svproc.cls']);
        $this->setComment('Proceedings');
    }
}

