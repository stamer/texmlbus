<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
#header('Content-Type: application/json');
$labels[] = '2017-10-01';
$labels[] = '2017-10-05';
$labels[] = '2017-10-08';
$labels[] = '2017-10-09';
$labels[] = '2017-10-10';

$y[] = 10;
$y[] = 40;
$y[] = 20;
$y[] = 9;
$y[] = 20;
$datasets[0] = new StdClass;
$datasets[0]->data = $y;
$datasets[0]->label = 'Set 1';
$datasets[0]->backgroundColor = 'rgba(300, 200, 200, 0.75)';
$datasets[0]->borderColor =  'rgba(300, 200, 200, 0.75)';
$datasets[0]->hoverBackgroundColor = 'rgba(200, 200, 200, 1)';
$datasets[0]->hoverBorderColor = 'rgba(200, 200, 200, 1)';


$y = array();
$y[] = '20';
$y[] = '19';
$y[] = '15';
$y[] = '9';
$y[] = '2';
$datasets[1] = new StdClass;
$datasets[1]->data = $y;
$datasets[1]->label = 'Set 2';
$datasets[1]->backgroundColor = 'rgba(200, 500, 200, 0.75)';
$datasets[1]->borderColor =  'rgba(200, 500, 200, 0.75)';
$datasets[1]->hoverBackgroundColor = 'rgba(200, 200, 200, 1)';
$datasets[1]->hoverBorderColor = 'rgba(200, 200, 200, 1)';

$data['labels'] = $labels;
$data['datasets'] = $datasets;

$response->json($data);

