<?php
/**
 * MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 * This program is being run on the worker.
 * This program tests the existence of specific sty and cls files. 
 * It expects an base64_encode(json_encode(string)) 
 * which contains an array with
 * TEXINPUTS: a TEXINPUTS path 
 * filenames: filenames that are to be tested
 * 
 * It returns a json-encoded string with
 * 'filename' => bool for each filename.
 */

// the string is also base64_encodeid to avoid " encoding problems.
if (isset($argv[1])) {
    $param = json_decode(base64_decode($argv[1]), true);
} else {
    $param['TEXINPUTS'] = '.:/usr/share/texmf-dist/tex//:/srv/texmlbus/articles/sty//';
    $param['filenames'] = ['svjour3.cls'];
}

$supported = [];
foreach ($param['filenames'] as $file) {
    $systemstr = 'TEXINPUTS="' . $param['TEXINPUTS'] . '"';
    $systemstr .= ' /usr/bin/kpsewhich ' . $file . '>/dev/null 2>&1';
    system($systemstr, $returnVar);`
    // 1: error => false
    // 0: found => true
    $supported[$file] = ($returnVar ? false : true);
}

echo json_encode($supported);

