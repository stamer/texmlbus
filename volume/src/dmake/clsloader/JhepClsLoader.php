<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;

class JhepClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('JHEP');
        $this->setPublisher('SpringerNature');
        $this->setUrl('http://mirrors.ctan.org/macros/latex/contrib/jhep/JHEP.cls');
        $this->setFiles(['JHEP.cls']);
        $this->setComment('Journal of High Energy Physics');
    }

}

