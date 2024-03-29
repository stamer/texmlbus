<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;

class AastexClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('aastex');
        $this->setPublisher('AAS');
        $this->setUrl('http://mirrors.ctan.org/macros/latex/contrib/aastex.zip');
        $this->setFiles(['aastex.cls']);
        $this->setComment('AAS Journals');
    }

    public function install() : string
    {
        $destDir = parent::install();
        $execDir = $destDir . '/aastex';
        $execStr = "cd $execDir && cp aastex*.cls aastex.cls";

        exec($execStr, $output, $result_code);
        if ($result_code) {
            error_log("Command failed: $execStr");
        }
        return $destDir;
    }

}

