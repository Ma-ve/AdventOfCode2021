<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('memory_limit', '4G');

function exception_error_handler($severity, $message, $file, $line) {
    if(!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler("exception_error_handler");

$example = <<<TXT
16,1,2,0,4,2,7,1,2,14
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day7-input.txt'),
];

foreach($inputs as $key => $input) {
    $horizontalPositions = array_map('trim', explode(",", str_replace("\r\n", "\n", $input)));

    $min = min($horizontalPositions);
    $max = max($horizontalPositions);

    $fuelUsedByPosition = [];
    for($i = $min; $i <= $max; $i++) {
        $fuelUsedByPosition[$i] = 0;
    }

    foreach($horizontalPositions as $horizontalPosition) {
        for($i = $min; $i <= $max; $i++) {
            $fuelUsedByPosition[$i] += abs($horizontalPosition - $i);
        }
    }

    $minimumFuelUsed = min($fuelUsedByPosition);
    $bestPosition = array_search($minimumFuelUsed, $fuelUsedByPosition);

    echo str_pad("{$key}: best position (least fuel) is", 48) . "{$bestPosition} (fuel used = {$minimumFuelUsed}) \n";
}
