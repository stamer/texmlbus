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

class JheppubClsLoader extends AbstractClsLoader
{
    /**
     * @inheritdoc
     */
    protected $name = 'jheppub';

    /**
     * @inheritDoc
     */
    protected $publisher = 'SpringerNature / Sissa';

    /**
     * @inheritDoc
     */
    protected $url = 'https://jhep.sissa.it/jhep/help/JHEP/TeXclass/DOCS/jheppub.sty';

    protected $files = ['jheppub.sty'];

    protected $comment = '';
}

