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

class AaClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('aa');
        $this->setPublisher('EDP Sciences');
        $this->setUrl('http://ftp.edpsciences.org/pub/aa/aa-package.zip');
        $this->setFiles(['aastex.cls']);
        $this->setComment('Astronomy & Astrophysics');
    }
}

