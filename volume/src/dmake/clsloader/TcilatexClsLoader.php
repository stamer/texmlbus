<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;

class TcilatexClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('tcilatex');
        $this->setPublisher('TCI Software Research, Inc');
        $this->setUrl('ftp://ftp.mackichan.com/swandswp30/updates/tcitex/tex/latex/tci/tcilatex.tex');
        $this->setFiles(['tcilatex.tex']);
        $this->setComment('tcilatex.tex needed for latexml test');
    }
}

