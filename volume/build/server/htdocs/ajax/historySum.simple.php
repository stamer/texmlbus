<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
header('Content-Type: application/json');
$data[] = array('playerid' => '1', 'score' => '10');
$data[] = array('playerid' => '2', 'score' => '40');
$data[] = array('playerid' => '3', 'score' => '20');
$data[] = array('playerid' => '4', 'score' => '9');
$data[] = array('playerid' => '5', 'score' => '20');
echo json_encode($data);
exit;

