<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function exception_error_handler($severity, $message, $file, $line) {
    if(!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler("exception_error_handler");

$example = <<<TXT
forward 5
down 5
forward 8
up 3
down 8
forward 2
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day2-input.txt'),
];

$log = function(int $id, string $action, int $units, int $depth, int $horizontal, int $aim) {
    $prefix = str_pad("{$id}  {$action} {$units}", 20);
    echo "{$prefix} | Depth: {$depth}, horizontal: {$horizontal}, aim: {$aim}\n";
};

foreach($inputs as $key => $input) {
    $explode = array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", $input))));

    $depth = 0;
    $horizontal = 0;
    $aim = 0;
    foreach($explode as $id => $value) {
        [$action, $units] = explode(' ', $value);
        $units = (int)$units;

        switch($action) {
            case 'forward':
                $horizontal += $units;
                $depth += ($aim * $units);
                break;
            case 'down':
//                $depth += $units;
                $aim += $units;
                break;
            case 'up':
//                $depth -= $units;
                $aim -= $units;
                break;
        }
//        $log($id, $action, $units, $depth, $horizontal, $aim);
    }

    $result = $horizontal * $depth;
    echo str_pad("{$key}: ", 30) . $result . "\n";
}

