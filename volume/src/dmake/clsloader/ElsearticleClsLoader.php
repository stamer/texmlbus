<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake\Clsloader;

use Dmake\AbstractClsLoader;
use Dmake\ApiWorkerRequest;
use Dmake\UtilFile;
use Dmake\UtilHost;
use Dmake\UtilStylefile;
use Dmake\UtilZipfile;

class ElsearticleClsLoader extends AbstractClsLoader
{
    public function __construct()
    {
        parent::__construct();
        $this->setName('elsarticle');
        $this->setPublisher('Elsevier');
        $this->setUrl('http://mirrors.ctan.org/macros/latex/contrib/elsarticle.zip');
        $this->setFiles(['elsarticle.cls']);
        $this->setComment('Elsevier journals');
    }

    public function install() : string
    {
        // returns full path
        $destDir = parent::install();

        $awr = new ApiWorkerRequest();
        $awr->setWorker('worker')
            ->setCommand('pdftex')
            ->setDirectory($destDir . '/elsarticle')
            ->setParameter('elsarticle.ins');

        $result = $awr->sendRequest();
        return $destDir;
    }
}

