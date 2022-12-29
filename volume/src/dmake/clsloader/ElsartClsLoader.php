<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;

class ElsartClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('elsart');
        $this->setPublisher('Elsevier');
        $this->setUrl('https://arxiv.org/macros/elsart.cls');
        $this->setFiles(['elsart.cls']);
        $this->setComment('outdated, (support for existing publications), superseded by elsarticle.cls');
    }
}

