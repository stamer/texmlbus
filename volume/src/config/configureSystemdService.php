<?php
/**
 * MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 * Write out a texmlbus.service for systemd.
 * This only needed on system where systemd is being used.
 * The dockerized system does NOT use systemd.
 *
 * Enable service: sudo systemctl enable texmlbus
 *
 * Start service: sudo service  start
 * Stop service: sudo service texmlbus stop
 * Status: sudo service texmlbus status
 *
 * Logging: journalctl -f -u texmlbus
 *
 */

require_once('../dmake/UtilFile.php');

$php = '/usr/bin/php';

/**
 * Username under which texmlbus.php should run.
 * Converted files will be created under this user.
 */
$user = null;

/**
 * Group under which texmlbus.php should run.
 * Converted files will be created under this group.
 */
$group = null;

$baseDir = dirname(__FILE__, 2);

$texmlbus = $baseDir . '/dmake/texmlbus.php';

$texmlbusServiceFile = '/etc/systemd/system/texmlbus.service';
$input = fopen("php://stdin", 'r') or die("Cannot open stdin!" . PHP_EOL);

if (file_exists($texmlbusServiceFile)) {
    echo "$texmlbusServiceFile already exists. Continue [Y/n]? ";
    $yesOrNo = trim(fgets($input));
    if (!in_array($yesOrNo, array('', 'y', 'Y'))) {
        die("Exiting..." . PHP_EOL);
    }
}

if ($user === null) {
	$user = UtilFile::getFileOwner(__FILE__);
}
if ($group === null) {
	$group = UtilFile::getFileGroup(__FILE__);
}

echo "Service will run with" . PHP_EOL;
echo "User : $user" . PHP_EOL;
echo "Group: $group" . PHP_EOL;
echo PHP_EOL;

echo "Write configuration to $texmlbusServiceFile [Y/n]? ";

$yesOrNo = trim(fgets($input));
if (!in_array($yesOrNo, array('', 'y', 'Y'))) {
	die ("Exiting..." . PHP_EOL);
}

$fp = fopen($texmlbusServiceFile, 'w');
if (!$fp) {
	die("Cannot write to $texmlbusServiceFile, you might need to run this script as root." . PHP_EOL);
}

fprintf($fp, "[Unit]\n");
fprintf($fp, "Description=Texmlbus Build System\n");
fprintf($fp, "\n");
fprintf($fp, "[Service]\n");
fprintf($fp, "Type=simple\n");
fprintf($fp, "ExecStart=%s %s\n", $php, $texmlbus);
fprintf($fp, "WorkingDirectory=%s\n", dirname($texmlbus));
fprintf($fp, "User=%s\n", $user);
fprintf($fp, "Group=%s\n", $group);
fprintf($fp, "\n");
fprintf($fp, "[Install]\n");
fprintf($fp, "WantedBy=multi-user.target\n");
fprintf($fp, "\n");
fclose($fp);

echo "Servicefile written to $texmlbusServiceFile\n";
echo "Enable service by \"systemctl enable texmlbus\"\n";

