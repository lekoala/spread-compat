<?php

use LeKoala\SpreadCompat\Csv\League;

require './vendor/autoload.php';

$league = new League();
$data = $league->read(__DIR__ . '/tests/data/sample-basic.csv');
foreach ($data as $row) {
    var_dump($row);
}
