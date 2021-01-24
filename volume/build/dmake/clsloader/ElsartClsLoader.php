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

class ElsartClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'elsart';

    /**
     * @inheritDoc
     */
    protected $publisher = 'Elsevier';

    /**
     * @inheritDoc
     */
    protected $url = 'https://arxiv.org/macros/elsart.cls';

    protected $files = ['elsart.cls'];

    protected $comment = 'outdated, (support for existing publications), superseded by elsarticle.cls';
}

