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

class SvjourClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('svjour');
        $this->setPublisher('SpringerNature');
        $this->setUrl('http://mirrors.ctan.org/macros/latex/contrib/springer/svjour.zip');
        $this->setFiles(['svjour.cls']);
        $this->setComment('Several Journals (outdated)');
    }
}

