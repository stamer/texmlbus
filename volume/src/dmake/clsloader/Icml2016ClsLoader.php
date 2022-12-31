<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;

class Icml2016ClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('icml2016');
        $this->setPublisher('ICML');
        $this->setUrl('https://icml.cc/2016/wp-content/uploads/icml2016.tar.gz');
        $this->setFiles(['icml2016.sty']);
        $this->setComment('International Conference on Machine Learning 2016');
    }
}

