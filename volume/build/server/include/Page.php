<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
namespace Server;

use Dmake\JwToken;
use Dmake\Set;

class Page
{
	private $maintenance = false;
	private $title = 'TexmlBus';
	private $addScript = '';
	private $addCss = '';

	private $request = null;

	private $headerShown = false;

	public function __construct($title = null)
	{
		if (!empty($title)) {
			$this->title = $title;
		}
        $this->request = RequestFactory::create();
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return ServerRequest
     */
    public function getRequest(): ServerRequest
    {
        return $this->request;
    }

    /**
     * @param ServerRequest $request
     */
    public function setRequest(ServerRequest $request): void
    {
        $this->request = $request;
    }


    /**
     * adds a script to current page
     *
     * @param string $scriptPath
     */
    public function addScript($scriptPath)
    {
        /* avoid dumb mistakes */
        if ($this->headerShown) {
            echo '<script>alert("' . $scriptPath . ' added after header is displayed.")</script>';
        }
        $str = '<script src="' . $scriptPath . '"></script>' . PHP_EOL;
	    $this->addScript .= $str;
    }

    /**
     * adds css to current page
     *
     * @param string $css
     */
    public function addCss($css)
    {
        /* avoid dumb mistakes */
        if ($this->headerShown) {
            echo '<script>alert(\'css added after header is displayed.\')<script>';
        }
        $this->addCss .= $css;
    }

    /**
     * creates an info button with given id
     *
     * @param $id
     * @param string $scale
     * @param string $translateY
     * @param string $color
     * @return string
     */
    public function info($id, $scale = '0.7', $translateY = '-10px', $color = 'rgba(23, 162, 184, 0.3)')
    {
        $str = '<span style = "transform: translateY(' . $translateY . ') scale(' . $scale . ',' . $scale . ');'
            . ' color: ' . $color . '" class="fas fa-info-circle infolink" data="' . $id . '"></span>';
        return $str;
    }

    /**
     * @param null $activeLeft
     * @param null $activeRight
     */
	public function showHeader($activeLeft = null, $activeRight = null)
    {
        $cfg = Config::getConfig();
        $sets = Set::getSets();

        $currentSet = $this->getRequest()->getQueryParam('set', '');
        $detail = (int) $this->getRequest()->getQueryParam('detail', 0);

        if ($cfg->auth->useJwToken) {
            $jwToken = $this->getRequest()->getCookieParam('jwToken');

            if (empty($jwToken)
                || !JwToken::validate($jwToken)
            ) {
                $jwToken = JwToken::create();
                setcookie('jwToken', $jwToken, ['samesite' => 'Strict']);
            }
        }

        header('Cache-control: no-cache');
        header('Cache-control: no-store');
        header('Pragma: no-cache');
        header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?=htmlspecialchars('texmlbus - ' . $this->title); ?>
    </title>

    <!-- Bootstrap core CSS -->
    <link href="/css/general.css" rel="stylesheet" type="text/css">
    <link href="/css/jquery-ui-1.12.1.min.css" rel="stylesheet" type="text/css">
    <style class="anchorjs"></style><link href="/css/bootstrap.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="/css/all.min.css" rel="stylesheet">

    <!-- Documentation extras -->
    <link href="/css/docs.css" rel="stylesheet">
    <link href="/css/local.css" rel="stylesheet">
    <style>

    </style>
    <?php
    echo $this->addCss;
    ?>

    <link rel="icon" href="favicon.ico">

    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/jquery-ui-1.12.1.min.js"></script>
    <script src="/js/popper.js"></script>
    <script src="/js/bootstrap.js"></script>
    <script src="/js/docs.js"></script>
    <script src="/js/local.js"></script>
    <?php
    echo $this->addScript;
    ?>
</head>
<body class="bd-docs" data-spy="scroll" data-target=".bd-sidenav-active" cz-shortcut-listen="true">
    <a id="skippy" class="sr-only sr-only-focusable" href="#content">
      <div class="container">
        <span class="skiplink-text">Skip to main content</span>
      </div>
    </a>

    <header class="navbar navbar-expand navbar-dark flex-column flex-md-row bd-navbar">
        <a class="navbar-brand mr-0 mr-md-2" href="/" aria-label="texmlbus">texmlbus
            <small><b>Tex</b> to X<b>ML</b><b>BU</b>ild <b>S</b>ystem</small></a>

        <div class="navbar-nav-scroll">
            <ul class="navbar-nav bd-navbar-nav flex-row">
                <li class="nav-item">
                    <a class="nav-link <?=($activeLeft == 'import' ? 'active' : '') ?>" href="/upload.php">Import articles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?=(($activeLeft == 'retval_abc' && $currentSet == 'samples') ? 'active' : '') ?>" href="/createSamples.php">Create Sample Set</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?=(($activeLeft == 'retval_abc' && $currentSet == 'samples') ? 'active' : '') ?>" href="/retval_abc.php?set=samples">Sample set</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?=($activeLeft == 'documentation' ? 'active' : '') ?>" href="/doc/">Documentation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="http://dlmf.nist.gov/LaTeXML/" target="latexml">LaTeXML</a>
                </li>
            </ul>
        </div>
    	<div style="position:absolute; background-color:#fddfdf; margin: 50px; display:none; width:600px" id="msgbox"></div>
    </header>

    <!-- Left -->
    <div class="container-fluid">
      <div class="row flex-xl-nowrap">

        <div class="col-12 col-md-3 col-xl-2 bd-sidebar">
            <nav class="collapse bd-links" id="bd-docs-nav">
                <div class="bd-toc-item <?=($activeLeft == 'general' ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/generalStat.php">
                        General
                    </a>
<?php
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
					echo '  <li class=""><a href="/lastStat.php">current</a></li>'.PHP_EOL;
					echo '</ul>' . PHP_EOL;
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
                    echo '  <li class=""><a href="/manageQueue.php">manage queue</a></li>'.PHP_EOL;
                    echo '</ul>' . PHP_EOL;
?>
                </div>
                <div class="bd-toc-item <?=($activeLeft == 'import' ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/upload.php">
                        Import / Manage
                    </a>
                    <?php
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
                    echo '  <li class=""`><a href="/upload.php">Upload texfiles and import</a></li>'.PHP_EOL;
                    echo '  <li class=""><a href="/scan.php">Scan directory for documents</a></li>'.PHP_EOL;
                    echo '  <li class=""><a href="/manageSets.php">Manage sets</a></li>'.PHP_EOL;
                    echo '  <li class="mt-3"><a href="/uploadSty.php">Upload class and sty for global use</a></li>'.PHP_EOL;
                    echo '  <li class=""><a href="/manageSty.php">Manage class and sty files</a></li>'.PHP_EOL;
                    echo '  <li class=""><a href="/installSty.php">Install class and sty files</a></li>'.PHP_EOL;
                    echo '  <li class="mt-3"><a href="/createSamples.php">Create sample set</a></li>'.PHP_EOL;
                    echo '  <li class="""><a href="/createLatexmlTest.php">Create latexml test-cases set</a></li>'.PHP_EOL;
                    echo '</ul>' . PHP_EOL;
                    ?>
                </div>
                <div class="bd-toc-item <?=($activeLeft == 'index' ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/index.php?set=<?=htmlspecialchars(urlencode($currentSet)) ?>">
                        Statistics
                    </a>
<?php
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
					echo '  <li class="'.(empty($currentSet) ? 'active' : '') .'"><a href="/index.php">overall</a></li>'.PHP_EOL;
                    if (count($sets)) {
                        foreach ($sets as $set) {
                            echo '<li class="'.($currentSet == $set->getName() ? 'active' : '').'">'.PHP_EOL;
                            echo '  <a href="/index.php?set=' . htmlspecialchars(urlencode($set->getName())) . '" title="'
                                    . htmlspecialchars($set->getSourcefile()).'">';
                            echo '    '.htmlspecialchars($set->getName());
                            echo '  </a>'.PHP_EOL;
                            echo '</li>'.PHP_EOL;
                        }
                    }
                    echo '</ul>';
?>
                </div>
                <div class="bd-toc-item <?=($activeLeft == 'retval_abc' ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/retval_abc.php?set=<?=htmlspecialchars(urlencode($currentSet)) ?>">
                        Documents alphabetically
                    </a>
<?php
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
					echo '  <li class="'.(empty($currentSet) ? 'active' : '') .'"><a href="/retval_abc.php">overall</a></li>'.PHP_EOL;
                    if (count($sets)) {
                        foreach ($sets as $set) {
                            echo '<li class="'.($currentSet == $set->getName() ? 'active' : '').'">'.PHP_EOL;
                            echo '  <a href="/retval_abc.php?set=' . htmlspecialchars(urlencode($set->getName())) . '" title="'
                                    . htmlspecialchars($set->getSourcefile()).'">';
                            echo '    '.htmlspecialchars($set->getName());
                            echo '  </a>'.PHP_EOL;
                            echo '</li>'.PHP_EOL;
                        }
                    }
                    echo '</ul>';
?>
                </div>

<?php if ($cfg->show->experimental) { ?>
                <div class="bd-toc-item <?=(($activeLeft == 'stylefiles') ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/support_needed.php?set=<?=htmlspecialchars(urlencode($currentSet)); ?>">
                        Style Files
                    </a>
                    <ul class="nav bd-sidenav">
                        <li class="">
                            <a href="/support_needed.php?set=<?=htmlspecialchars(urlencode($currentSet)) ?>">
                            Macros and style files that need most support
                            </a>
                        </li>
<?php if ($cfg->show->evenMoreExperimental) { ?>
                        <li class="">
                            <a href="/sty_sim.php">
                            Style files similarities (ordered by filename)
                            </a>
                        </li>
                        <li class="">
                            <a href="/sty_sim.php?sort=1">
                            Style files similarities (ordered by similarity)
                            </a>
                        </li>
                        <li class="">
                            <a href="/package_usage.php">
                            Most prominent style files that are used in the articles
                            </a>
                        </li>
                    </ul>
                </div>
    <?php } ?>
<?php } ?>
<?php if ($cfg->show->experimental) { ?>
                <div class="bd-toc-item <?=($activeLeft == 'package_usage' ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/package_usage.php?set=<?=htmlspecialchars(urlencode($currentSet)) ?>">
                        Package usage and success correlation
                    </a>
<?php
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
					echo '  <li class="'.(empty($currentSet) ? 'active' : '') .'"><a href="/package_usage.php">overall</a></li>'.PHP_EOL;
                    if (count($sets)) {
                        foreach ($sets as $set) {
                            echo '<li class="'.($currentSet == $set->getName() ? 'active' : '').'">'.PHP_EOL;
                            echo '  <a href="/package_usage.php?set=' . htmlspecialchars(urlencode($set->getName())) . '" title="'
                                    . htmlspecialchars($set->getSourcefile()).'">';
                            echo '    '.htmlspecialchars($set->getName());
                            echo '  </a>'.PHP_EOL;
                            echo '</li>'.PHP_EOL;
                        }
                    }
                    echo '</ul>';
?>
                </div>
<?php } ?>
                <div class="bd-toc-item <?=(($activeLeft == 'history' && empty($detail)) ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/history.php?set=<?=htmlspecialchars(urlencode($currentSet)) ?>">
                        History
                    </a>
<?php
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
					echo '  <li class="' . (empty($currentSet) ? 'active' : '') . '"><a href="/history.php">overall</a></li>'.PHP_EOL;
                    if (count($sets)) {
                        foreach ($sets as $set) {
                            echo '<li class="'.($currentSet == $set->getName() ? 'active' : '').'">'.PHP_EOL;
                            echo '  <a href="/history.php?set=' . htmlspecialchars(urlencode($set->getName())) . '" title="'
                                    . htmlspecialchars($set->getSourcefile()).'">';
                            echo '    '.htmlspecialchars($set->getName());
                            echo '  </a>'.PHP_EOL;
                            echo '</li>'.PHP_EOL;
                        }
                    }
                    echo '</ul>';
?>
                </div>
                <div class="bd-toc-item <?=(($activeLeft == 'history' && !empty($detail)) ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/history.php?detail=1&amp;set=<?=htmlspecialchars(urlencode($currentSet)) ?>">
                        Detailed History
                    </a>
<?php
                    echo '<ul class="nav bd-sidenav">'.PHP_EOL;
					echo '  <li class="'.(empty($currentSet) ? 'active' : '') .'"><a href="/history.php?detail=1">overall</a></li>'.PHP_EOL;
                    if (count($sets)) {
                        foreach ($sets as $set) {
                            echo '<li class="'.($currentSet == $set->getName() ? 'active' : '').'">'.PHP_EOL;
                            echo '  <a href="/history.php?detail=1&amp;set=' . htmlspecialchars(urlencode($set->getName())) . '" title="'
                                    . htmlspecialchars($set->getSourcefile()).'">';
                            echo '    '.htmlspecialchars($set->getName());
                            echo '  </a>'.PHP_EOL;
                            echo '</li>'.PHP_EOL;
                        }
                    }
                    echo '</ul>';
?>
                </div>
                <div class="bd-toc-item <?=($activeLeft == 'documentation' ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/doc">
                        Documentation
                    </a>
                    <ul class="nav bd-sidenav">
                        <li class="">
                            <a href="/doc">
                            Workqueue usage
                            </a>
                        </li>
                    </ul>
                    <ul class="nav bd-sidenav">
                        <li class="">
                            <a href="/doc/packages-license.php">
                            Licenses
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="bd-toc-item <?=($activeLeft == 'supported' ? 'active' : '') ?>">
                    <a class="bd-toc-link" href="/supported">
                        Supported classes and packages
                    </a>
                    <ul class="nav bd-sidenav">
                        <li class="">
                            <a href="/supported#class">
                            Classes
                            </a>
                        </li>
                        <li class="">
                            <a href="/supported#package">
                            Packages
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Right -->
        <div class="d-none d-xl-block col-xl-2 bd-toc">
<?php
        if ($activeRight == 'documentation' && false) {
?>
            <ul class="section-nav">
                <li class="toc-entry toc-h2"><a href="#approach">Approach</a></li>
                <li class="toc-entry toc-h2"><a href="#page-defaults">Page defaults</a></li>
                <li class="toc-entry toc-h2"><a href="#misc-elements">Misc elements</a>
                    <ul>
                    <li class="toc-entry toc-h3"><a href="#address">Address</a></li>
                    <li class="toc-entry toc-h3"><a href="#blockquote">Blockquote</a></li>
                    <li class="toc-entry toc-h3"><a href="#inline-elements">Inline elements</a></li>
                    </ul>
                </li>
                <li class="toc-entry toc-h2"><a href="#html5-hidden-attribute">HTML5 [hidden] attribute</a>
            </ul>
<?php
        }
?>
        </div>

        <main class="col-12 col-md-9 col-xl-8 py-md-3 pl-md-5 bd-content" role="main">
<?php
        $this->headerShown = true;
		if ($this->maintenance) {
			echo '<h3 class="p-sm-5">';
			echo "Build System is under maintenance until Monday, Sep. 18th.<br />";
			echo "Please come back then.";
			echo "</h3>";
			echo "</body>";
			echo "</html>";
			exit;
		}
	}

    /**
     * shows the footer
     *
     * @param array $deferJs
     */
	public function showFooter($deferJs = array()) {
?>
       <!-- Footer -->
        </main>
      </div>
    </div>


<script type="text/javascript">
$(document).ready(function() {
    $('input[type="radio"][name="tab-group-1"]').click(function() {
        if (this.checked == true) {
            setCookie('statsTab', this.id, 30);
        }
    });
    $('.infolink').bind('click', function (e) {
        if ($(this).attr('data')) {
            openHelp(($(this)).attr('data'));
        } else {
            alert('Cannot load help page, data attribute is missing.');
        }
    });
<?php
    if (is_array($deferJs)) {
        foreach ($deferJs as $line) {
            echo $line.';'.PHP_EOL;
        }
    }
?>
});
</script>

<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<!-- <script src="/js/ie10-viewport-bug-workaround.js"></script> -->
<!-- <script src="/js/ie-emulation-modes-warning.js"></script> -->

<script>
Holder.addTheme('gray', {
  bg: '#777',
  fg: 'rgba(255,255,255,.75)',
  font: 'Helvetica',
  fontweight: 'normal'
})
</script>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>
<?php
	}
}
