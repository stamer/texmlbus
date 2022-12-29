<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * queues jobs for conversion.
 */

namespace Dmake;

ini_set("display_errors", 1);
ini_set("memory_limit", "512M");

/*
possible commands:

simple make
	php workqueue.php default

simple make in cond-mat
	php workqueue.php default -d cond-mat

//make postprocessing in cond-mat (status is status of postprocess)
//	php workqueue.php post -d all

remake in cond-mat but only for fatal_errors
	php workqueue.php default -d cond-mat -v fatal_error xhtml

remake all fatal_errors
	php workqueue.php default -d cond-mat -v fatal_error xhtml

remake all fatal_errors and set high priority
so this is done first
	php workqueue.php default -d cond-mat -v fatal_error xhtml -p 99

simple clean only in cond-mat
 	php workqueue.php clean -d cond-mat

cleanup only missing_errlog in cond-mat
 	php workqueue.php clean -d cond-mat -v missing_errlog xhtml

cleanup only fatal_error in cond-mat
 	php workqueue.php clean -d cond-mat -v fatal_error xhtml

cleanup only files that use aa.cls in cond-mat
 	php workqueue.php clean -d cond-mat -s aa.cls

cleanup only fatal_error everywhere
 	php workqueue.php clean -d all -v fatal_error xhtml

cleanup only missing_macros where this macro was missing in cond-mat
 	php workqueue.php clean -d cond-mat -v missing_macros xml -m onlinecite


this does not make sense:
	php workqueue.php default -d cond-mat -v fatal_error xhtml
you need to do a
	php workqueue.php clean -d cond-mat -v fatal_error pdf
and then a
	php workqueue.php default -d cond-mat -v fatal_error pdf

*/

require_once "IncFiles.php";

/**
 * Main program
 */

$cfg = Config::getConfig();

$inotify = new InotifyHandler();

$possibleCleanActions = ['clean'];

foreach ($cfg->stages as $stage => $value) {
    $possibleActions[] = $stage;
    $possibleCleanActions[] = $stage.'clean';
}

if (!isset($argv[1])) {
	echo 'Unknown action!'.PHP_EOL;
	exit;
} else {
    if (in_array($argv[1], $possibleActions)
        || in_array($argv[1], $possibleCleanActions))
    {
		$action = $argv[1];
    } else {
        echo "Unknown action: ".$argv[1].PHP_EOL;
        echo "Possible actions are: ".implode(', ', $possibleActions).PHP_EOL;
        echo "Possible clean actions are: ".implode(', ', $possibleCleanActions).PHP_EOL;
        exit(2);
	}

	$priority = 10;
	$restrict['dir']            = '';
	$restrict['id']             = '';
	$restrict['retval']         = '';
	$restrict['retval_target']  = '';
	$restrict['macro']          = '';
	$restrict['stylefile']      = '';
	$restrict['time_after']     = '';
	$restrict['time_before']	= '';
    $restrictDirSet = false;

	foreach ($argv as $pos => $option) {
		// -p set priority
		if ($option === '-p' && isset($argv[$pos+1])) {
			$priority = $argv[$pos + 1];
			echo "Setting priority to $priority...\n";
		}
		// -d set directory
		// determine directory, special value 'all' will not set $restrict['dir']
		if ($option === '-d' && isset($argv[$pos+1])) {
			$restrict['dir'] = $argv[$pos + 1];
            $restrictDirSet = true;
			echo "Restricting make to set/path ".$restrict['dir']."...\n";
		}

		// -v set restrict_retval
		if ($option === '-v' && isset($argv[$pos+1]) && isset($argv[$pos+2])) {
			$restrict['retval'] = $argv[$pos + 1];
			$restrict['retval_target'] = $argv[$pos + 2];
			echo "Restricting retval to ".$restrict['retval']." of target ".$restrict['retval_target']."...\n";
		}

		// -m set restrict_macros
		// make clean cond_mat missing_macros onlinecite
		if ($option === '-m' && isset($argv[$pos+1])) {
			$restrict['macro'] = $argv[$pos + 1];
			echo "Restricting macros to ".$restrict['macro']."...\n";
		}

		// -s set restrict_styfile
		if ($option === '-s' && isset($argv[$pos+1])) {
			$restrict['stylefile'] = $argv[$pos + 1];
			echo "Restricting stylefile to ".$restrict['stylefile']."...\n";
		}

		// -tb set
		if ($option === '-tb' && isset($argv[$pos+1])) {
			$restrict['time_before'] = $argv[$pos + 1];
			echo "Restricting to entries before ".$restrict['time_before']."...\n";
		}
		// -ta set
		if ($option === '-ta' && isset($argv[$pos+1])) {
			$restrict['time_after'] = $argv[$pos + 1];
			echo "Restricting to entries after ".$restrict['time_after']."...\n";
		}
		if ($option === '-id' && isset($argv[$pos+1])) {
			$restrict['id'] = $argv[$pos + 1];
            if (!preg_match('/^\d{7}$/', $restrict['id'])) {
                echo "id must be a 7-digit number.".PHP_EOL;
                exit(2);
            }
            $restrictDirSet = true;
			echo "Restricting to entries with ".$restrict['id']."...\n";
		}
	}
}

if ($restrict['macro'] !== '' && $restrict['retval'] !== 'missing_macros') {
	echo "Incompatible options!\n";
	echo "Set restrict['retval'] to missing_macros!\n";
	exit(2);
}

if (!$restrictDirSet) {
    if (empty($restrict['dir'])) {
        echo "Unset mandatory option!\n";
        echo "Please specifiy either -d directory (set) or -id 1234567\n";
        echo "It is also possible to specify -d all\n";
        exit(2);
    } else {
        echo "Setting default restriction to set ".$restrict['dir']."\n";
    }
}

// for get_directories;
$depth = 0;
$flc = 0;

$dirs = [];

if (in_array($action, $possibleActions))
{
		if ($restrict['retval'] == '') {
			// just scan filesystem
			// UtilFile::getDirectoriesR($dirs, $restrict['dir']);
			$dirs = StatEntry::getFilenamesByRestriction($action, $restrict);
		} else {
			// get files from DB
			// retval should come form rerun_* values otherwise it does not make sense
			$dirs = StatEntry::getFilenamesByRestriction($action, $restrict);
		}
		print_r($dirs);

		/**
		 * loop through all given directories
		 */
		foreach ($dirs as $directory) {

			if (DBG_LEVEL & DBG_DIRECTORIES) echo $directory."...\n";

			$entry_done = FALSE;
			// only try to make if these conditions are met
			if (!$entry_done || $action == StatEntry::WQ_ACTION_FORCE) {
				StatEntry::addToWorkqueue(
                    $directory,
                    'worker',
                    'xhtml',
                    $action,
                    $priority);
                $inotify->trigger('worker', InotifyHandler::wqTrigger);
			} elseif (DBG_LEVEL & DBG_DIRECTORIES) {
			    echo "Skipping $directory, entry exists...\n";
			}
		}
}
elseif (in_array($action, $possibleCleanActions))
{
    if ($action === 'clean' && !empty($restrict['retval'])) {
        echo "Incompatible options: clean and restriction to retval is not possible.";
        exit(2);
    } else {
        $normalaction = str_replace('clean', '', $action);
    }
    if (DBG_LEVEL & DBG_MAKE) {
        echo "Cleaning up...\n";
    }
    $dirs = StatEntry::getFilenamesByRestriction($normalaction, $restrict);

    foreach ($dirs as $directory) {
        if (TRUE) {
            // new
            StatEntry::addToWorkqueue($directory, 'worker', 'xhtml', $action, $priority);
            // trigger at end of foreach...
        } else {
            // old

            if (DBG_LEVEL & DBG_MAKE) {
                echo "Dir: $directory\n";
            }
            // ARTICLEDIR./.$directory need quotes!
            $systemCmd = 'cd "'.ARTICLEDIR.'/'.$directory.'" && /usr/bin/make '.$action;
            if (DBG_LEVEL & DBG_MAKE) {
                echo "Make $action $directory...\n";
            }
            $output = [];
            exec($systemCmd, $output, $result_code);
            if (DBG_LEVEL & DBG_MAKE) {
                print_r($output);
            }
            if ($result_code) {
                echo "$systemCmd failed." . PHP_EOL;
            }

            //UtilFile::cleanupDir($directory, $action);
        }
    }

    $inotify->trigger('worker', InotifyHandler::doneTrigger);
}
