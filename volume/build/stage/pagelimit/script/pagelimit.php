<?php
/**
 * Released under MIT License
 * (c) 2020 Heinrich Stamerjohanns
 *
 */

/**
 * Class PageLimit
 *
 * Reads a given (.tex)-file and colors some commands that are known to typically
 * tweak pagelimits that are imposed on a paper.
 *
 * The commands are taken from "How to Cheat the Page Limit: the Version
 * Adhering to the Guidelines" by Wouter Duivesteijn, Sibylle Hess, Xin Du,
 * In WIREs Data Mining and Knowledge Discovery
 * https://doi.org/10.1002/widm.1361
 * http://wwwis.win.tue.nl/~wouter/Publ/htctpl.pdf
 *
 */
class PageLimit
{
    var $sourcefile = '';
    var $destfile = '';
    var $fp = null;
    var $fpErr = null;

    public const warningPatterns = [
        // 1.1 Alternative Fonts
        [
            'pattern' => '@\\\\usepackage\{.*?times.*?\}@',
            'tooltip' => 'Loading times package is discouraged.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{.*?helvet.*?\}@',
            'tooltip' => 'Loading helvet package is discouraged.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{.*?courier.*?\}@',
            'tooltip' => 'Loading courier package is discouraged.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{.*?newtxtext.*?\}@',
            'tooltip' => 'Loading newtxtext package is discouraged.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{.*?newtxmath.*?\}@',
            'tooltip' => 'Loading newtxmath package is discouraged.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{.*?stix.*?\}@',
            'tooltip' => 'Loading stix package is discouraged.',
            'break' => true,
        ],
        // 1.2 Reduce Font Size
        [
            'pattern' => '@\\\\small@',
            'tooltip' => 'Do not reduce font size via \\small.',
        ],
        [
            'pattern' => '@\\\\tiny@',
            'tooltip' => 'Do not reduce font size via \\tiny.',
        ],
        [
            'pattern' => '@\\\\footnotesize@',
            'tooltip' => 'Do not reduce font size via \\footnotesize.',
        ],
        [
            'pattern' => '@\\\\scriptsize@',
            'tooltip' => 'Do not reduce font size via \\scriptsize.',
        ],
        [
            'pattern' => '@\\\\fontsize\{\s*?\S*?\s*?\}@',
            'tooltip' => 'Changing the fontsize is discouraged.',
        ],
        [
            'pattern' => '@\\\\SetAlFnt@',
            'tooltip' => 'Do not use \\SetAlFnt',
        ],
        [
            'pattern' => '@\\\\SetCommentSty@',
            'tooltip' => 'Do not use \\SetCommentSty',
        ],
        // 1.3 Reduce Vertical Spacing
        [
            'pattern' => '@\\\\vspace\{\s*?\-\S*?\s*?\}@',
            'tooltip' => 'Negative vertical spacing is discouraged.',
        ],
        [
            'pattern' => '@\\\\vskip\s*?\-\S*@',
            'tooltip' => 'Negative vertical spacing is discouraged.',
        ],
        [
            'pattern' => '@\\\\\\\\\[\s*\-.*?\]@',
            'tooltip' => 'Negative vertical spacing is discouraged.',
        ],
        // ... Around Equations
        [
            'pattern' => '@\\\\abovedisplayskip@',
            'tooltip' => 'Changing the distance between equations is discouraged.',
        ],
        [
            'pattern' => '@\\\\belowdisplayskip@',
            'tooltip' => 'Changing the distance below equations/algorithms is discouraged.',
        ],
        // ... Around Algorithms
        // ... in and Around the Bibliography
        [
            'pattern' => '@\\\\bibsep@',
            'tooltip' => 'Changing the distance between bibliographic items is discouraged.',
        ],
        [
            'pattern' => '@\\\\bibpreample@',
            'tooltip' => 'The preample of the biblography should not be changed.',
        ],
        // ... Between Items
        [
            'pattern' => '@\\\\itemsep@',
            'tooltip' => 'The space between items is not supposed to be changed.',
        ],
        // ... Throughout the Document
        [
            'pattern' => '@\\\\linespread\{.*?\}@',
            'tooltip' => 'The layout of document is not supposed to be changed.',
        ],
        [
            'pattern' => '@\\\\baselinestretch\{.*?\}@',
            'tooltip' => 'The layout of the document is not supposed to be changed.',
        ],
        [
            'pattern' => '@\\\\baselineskip\{.*?\}@',
            'tooltip' => 'The distance between lines is not supposed to be changed.',
        ],
        // 1.4 Reduce Spacing Around Figures
        [
            'pattern' => '@\\\\textfloatsep@',
            'tooltip' => 'The spacing around figures should not be changed.',
        ],
        [
            'pattern' => '@\\\\floatsep@',
            'tooltip' => 'The spacing around figures should not be changed.',
        ],
        [
            'pattern' => '@\\\\intextsep@',
            'tooltip' => 'The spacing around figures should not be changed.',
        ],
        // wrapfig is in unclear patterns
        // 1.5 Reduce Tablespace
        [
            'pattern' => '@\\\\resizebox@',
            'tooltip' => 'Tablespace is not supposed to be reduced.',
        ],
        [
            'pattern' => '@\\\\scalebox@',
            'tooltip' => 'Tablespace is not supposed to be reduced.',
        ],
        [
            'pattern' => '@\\\\adjustbox@',
            'tooltip' => 'Tablespace is not supposed to be reduced.',
        ],
        [
            'pattern' => '@\\\\arraystretch@',
            'tooltip' => 'Tablespace is not supposed to be reduced.',
        ],
        [
            'pattern' => '@\\\\tabcolsep@',
            'tooltip' => 'Column distance are not supposed to be changed.',
        ],
        // 1.6 Reduce Margins
        [
            'pattern' => '@\\\\usepackage\[\s*margin\s*=.*?\]\{.*?geometry.*?\}@',
            'tooltip' => 'Document margins should not be changed.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{.*?fullpage.*?\}@',
            'tooltip' => 'The layout of the document should not be changed.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{.*?a4wide.*?\}@',
            'tooltip' => 'The layout of the document should not be changed.',
            'break' => true,
        ],
        [
            'pattern' => '@\\\\usepackage\{wrapfig\}@',
            'tooltip' => 'It is unclear whether wrapfig can be used.',
        ],
        //
        //[
        //    'pattern' => '@\\\\enlargethispage\{.*?\}@',
        //    'tooltip' => 'tiny problem',
        //],
        //
    ];

    public const errorPatterns = [
        // 1.6 Reduce Margins
        [
            'pattern' => '@\\\\enlargethispage\{.*?\}@',
            'tooltip' => '\\enlargepage may not be used.'],
    ];

    public function __construct(string $sourcefile, string $destfile = 'php://stdout')
    {
        $this->sourcefile = $sourcefile;
        $this->destfile = $destfile;

        $fp = fopen($this->destfile, 'w');
        if (!$fp) {
            echo "Cannot open " . $this->destfile . " for writing." . PHP_EOL;
            exit(1);
        }
        $this->fp = $fp;

        $fpErr = fopen('php://stderr', 'w+');
        if (!$fpErr) {
            echo "Cannot open php://stderr for writing." . PHP_EOL;
            exit(1);
        }
        $this->fpErr = $fpErr;
    }

    public function writeHeader($headline)
    {
        $str = '
<html>
<head>
  <title>Pagelimit</title>
  <style>
    .white { background-color: #fefefe; } 
    pre .error {
        color: red;
        font-weight: bold;
    }
    pre .number {
        color: #999;
    }
    pre .warning {
        color: orange;
        font-weight: bold;
    }
    pre {
        line-height: 1.2em;
        font-size: 13px;
    }
    
 .tooltip {
    position: relative;
    color: #3983ab;
    text-decoration: underline;
}

.tooltip::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 130%;
    left: 20%;
    background: rgba(230, 230, 230, 0.95);
    padding: 0.4em 0.6em;
    color: #111111;
    border-radius: 0.3em;
    opacity: 0;
    transition: all 0.3s;
    min-width: 10em;
    max-width: 35em;
}

.tooltip::before {
    content: "";
    position: absolute;
    width: 0;
    height: 0;
    border-top: 1em solid rgba(130, 130, 130, 0.95)
    border-left: 1em solid transparent;
    border-right: 1em solid transparent;
    transition: all 0.3s;
    opacity: 0;
    left: 30%;
    bottom: 90%;
}
.tooltip:hover::after,
.tooltip:focus::after {
    opacity: 1;
    bottom: 100%;
}

.tooltip:hover::before,
.tooltip:focus::after {
    opacity: 1;
    bottom: 70%;
}
  </style>
</head>
<body class="white">
<h2>' .  htmlspecialchars($headline) . '</h2>
<pre>
';
        fwrite($this->fp, $str);
    }

    public function writeFooter()
    {
        $str = '
</pre>
</body>
</html>
';
        fwrite($this->fp, $str);
        fclose($this->fp);
    }

    public function execute()
    {
        if (!file_exists($this->sourcefile)) {
            fprintf($this->fpErr, "%s" . PHP_EOL, "File " . $this->sourcefile . " does not exist.");
            exit(1);
        }
        if (!is_readable($this->sourcefile)) {
            fprintf($this->fpErr, "%s" . PHP_EOL, "Unable to access " . $this->sourcefile . ".");
            exit(1);
        }
        $lines = file($this->sourcefile);
        foreach ($lines as $lineno => &$line) {
            $line = htmlspecialchars($line);
        }
        unset($line);

        /**
         * Please note the pattern matching is actually done on the
         * html escaped file (it is more difficult to escape later on..).
         * Therefore a pattern that wants to match < actually needs to match &lt;
         * Therefore a pattern that wants to match > actually needs to match &gte;
         * Therefore a pattern that wants to match " actually needs to match &quot;
         */
        foreach ($lines as $lineno => &$line) {
            foreach (self::errorPatterns as $errorPattern) {
                if (preg_match($errorPattern['pattern'], $line, $matches)) {
                    fprintf($this->fpErr, "Error: Found %s on input line %d." . PHP_EOL, $matches[0], $lineno);
                    $line = str_replace(
                        $matches[0],
                        '<span class="error tooltip" tabindex="0" data-tooltip="'
                        . $errorPattern['tooltip'] . '">' . $matches[0] . '</span>',
                        $line
                    );
                    if (!empty($errorPattern['break'])) {
                        break;
                    }
                }
            }
            foreach (self::warningPatterns as $warningPattern) {
                if (preg_match($warningPattern['pattern'], $line, $matches)) {
                    fprintf($this->fpErr, "Warning: Found %s on input line %d." . PHP_EOL, $matches[0], $lineno);
                    $line = str_replace(
                        $matches[0],
                        '<span class="warning tooltip" tabindex="0" data-tooltip="'
                        . $warningPattern['tooltip'] . '">' . $matches[0] . '</span>',
                        $line
                    );
                    if (!empty($warningPattern['break'])) {
                        break;
                    }
                }
            }
        }
        // always unset reference...
        unset($line);

        $filename = basename($this->sourcefile);

        $this->writeHeader($filename);
        // compute width of number for given document
        $width = strlen((string)count($lines)) + 1;
        $format = '<span class="number">%0' . $width . 'd</span>  %s';
        $depth = 0;
        $lineCount[$depth] = 0;

        foreach ($lines as $line) {
            if (substr($line, 0, 15) === '% start include') {
                fprintf($this->fp, "%s %s", str_repeat('X', $width), $line);
                $depth++;
                $lineCount[$depth] = 0;
                continue;
            }
            if (substr($line, 0, 14) === ' % end include') {
                fprintf($this->fp, "%s %s", str_repeat('X', $width), $line);
                $depth--;
                continue;
            }
            fprintf($this->fp, $format, ++$lineCount[$depth], $line);
        }

        $this->writeFooter();
    }
}

/* main */
if (empty($argv[1])) {
    echo "Please provide a source filename." . PHP_EOL;
    exit(1);
}
$sourcefile = $argv[1];

if (!empty($argv[2])) {
    $destfile = $argv[2];
} else {
    $destfile = 'php://stdout';
}

$pagelimit = new PageLimit($sourcefile, $destfile);

$pagelimit->execute();

