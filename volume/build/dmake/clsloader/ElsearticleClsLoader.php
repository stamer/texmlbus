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

class ElsearticleClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'elsarticle';

    /**
     * @inheritDoc
     */
    protected $publisher = 'Elsevier';

    /**
     * @inheritDoc
     */
    protected $url = 'http://mirrors.ctan.org/macros/latex/contrib/elsarticle.zip';

    protected $files = ['elsarticle.cls'];

    protected $comment = 'Elsevier journals';
}

