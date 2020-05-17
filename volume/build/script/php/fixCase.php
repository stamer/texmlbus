<?php
# try to find files because of case-insensitive file system

if (empty($argv[1])) {
	echo "Name of texfile is missing, giving up".PHP_EOL;
	exit(1);
}
if (empty($argv[2])) {
	echo "Name of logfile is missing, giving up".PHP_EOL;
	exit(1);
}

/**
 * @param string name of tex file
 * @return array 
 */
function determineGraphicsPath($texFile) {
    $texContent = file_get_contents($texFile);
    $gpPattern = '@^\s*\\\\graphicspath\{\{(.*?)\}\}@m';
    preg_match_all($gpPattern, $texContent, $matches);
    if (isset($matches[1][0])) {
        $paths = explode('}{', $matches[1][0]);
    } else {
        $paths = array();
    }
    return $paths;
}

function getInsensitiveGlob($pattern) {
	$func = function($c) {
		if (ctype_alpha($c)) {
			return sprintf("[%s%s]", strtoupper($c), strtolower($c));
		} else {
			return $c;
		}
	};
	return implode('', array_map($func, str_split($pattern)));
}

function createSymlink($target, $link) {
    echo 'Creating symlink '.$link.' --> '.$target.PHP_EOL;
    $dirnameTarget = pathinfo($target, PATHINFO_DIRNAME);
    $dirnameLink = pathinfo($link, PATHINFO_DIRNAME);
    if ($dirnameTarget != $dirnameLink) {
        echo "Link found in different directory!".PHP_EOL;
        echo "Dir Target: $dirnameTarget".PHP_EOL;
        echo "Dir Link: $dirnameLink".PHP_EOL;
        echo "Skipping...".PHP_EOL;
        return false;
    } 
    if ($dirnameTarget == '.') {
        // we can just symlink
        $result = symlink($target, $link);
    } else {
        // if the files lie in subdirectories, we need to move into
        // the subdir and create the link there, otherwise it will 
        // just not work.
        $currentDir = getcwd();
        chdir($dirnameTarget);
        $tpath = pathinfo($target);
        $lpath = pathinfo($link);
        $target = $tpath['filename'].(!empty($tpath['extension']) ? '.'.$tpath['extension'] : '');
        $link = $lpath['filename'].(!empty($lpath['extension']) ? '.'.$tpath['extension'] : '');
        $result = symlink($target, $link);
        chdir($currentDir);
    }
    if (!$result) {
        echo "Symlink creation failed!".PHP_EOL;
    }
}

$texfile = $argv[1];
$logfile = $argv[2];

$graphicPaths = determineGraphicsPath($texfile);
if (empty($graphicPaths)) {
    $graphicPaths[] = '';
}

$content = file_get_contents($logfile);
$errPattern = '@(.*?)(Warning:|Error:)(\S*)\s+File `(.*)\' not found@m';
$matches = array();
preg_match_all($errPattern, $content, $matches);
// $matches[4] is an array of all the files that could not be found.
if (count($matches[4])) {
	foreach ($matches[4] as $missingFile) {
        $success = false;
        foreach ($graphicPaths as $gPath) {
            $missingFullFile = $gPath . $missingFile;
            $globPattern = getInsensitiveGlob($missingFullFile);	
            $suffixMissingFile = pathinfo($missingFullFile, PATHINFO_EXTENSION);
            if (empty($suffixMissingFile)) {
                $globPattern = $globPattern.'.[pP][dDnN][fFgG]';
            }
            $foundFiles = glob($globPattern);
            if (count($foundFiles) == 1) {
                $suffix = pathinfo($foundFiles[0], PATHINFO_EXTENSION);
                // hat missingFile keine Extension?
                if (!empty($suffixMissingFile)) {
                    $link = $missingFullFile;
                } else {
                    $link = $missingFullFile.'.'.$suffix;
                }
                createSymlink($foundFiles[0], $link);
                $success = true;
                break;
            } elseif (count($foundFiles) > 1) {
                echo "Several matching files found\n";
                $success = true;
                break;
            }
		}
		if (!$success) {
            echo "No matching files found for $missingFile\n";
            echo "Pattern was $globPattern\n";
            echo "graphicspath is ".implode(', ', $graphicsPath);
        }
		
	}
} else {
	echo "No match?\n";
}
