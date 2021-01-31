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

class LlncsClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('llncs');
        $this->setPublisher('SpringerNature');
        $this->setUrl('ftp://ftp.springernature.com/cs-proceeding/llncs/llncs2e.zip');
        $this->setFiles(['llncs.cls']);
        $this->setComment('Lecture Notes in Computer Science');
    }
}

