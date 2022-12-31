<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;

class Svjour3ClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('svjour3');
        $this->setPublisher('SpringerNature');
        $this->setUrl('https://static.springer.com/sgw/documents/468198/application/zip/LaTeX_DL_468198.zip');
        $this->setFiles(['svjour3.cls']);
        $this->setComment('Several Journals');
    }
}

