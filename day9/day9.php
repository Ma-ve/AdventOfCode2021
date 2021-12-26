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
2199943210
3987894921
9856789892
8767896789
9899965678
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day9-input.txt'),
];

foreach($inputs as $key => $input) {
    $heightmap = str_replace("\r\n", "\n", $input);
    $rowLength = strlen(explode("\n", $input)[0]);

    $getPositions = static function(int $i) use ($rowLength) {
        return [
            'previous' => $i - 1,
            'next'     => $i + 1,
            'above'    => $i - $rowLength,
            'under'    => $i + $rowLength,
        ];
    };
    $lowestPoints = [];

    $parsedHeightmap = str_replace("\n", '', $heightmap);
    for($i = 0; $i < strlen($parsedHeightmap); $i++) {
        $currentValue = $parsedHeightmap[$i];
        $isLowerThanAnyAdjacent = true;

        $debug = [];
        foreach($getPositions($i) as $positionName => $position) {
            $positionValue = false;
            if($position >= 0 && isset($parsedHeightmap[$position])) {
                $positionValue = $parsedHeightmap[$position];
            }

            $debug[] = "Board position {$i} (value = {$currentValue}), has '{$positionName}' ({$position}) of value {$positionValue}";
            if($positionValue !== false && $currentValue >= $positionValue) {
                $isLowerThanAnyAdjacent = false;
                break;
            }
        }

        if(true === $isLowerThanAnyAdjacent) {
            $lowestPoints[] = [
                'position' => $i,
                'value'    => (int)$currentValue,
                'debug'    => $debug,
            ];
        }
    }

    $riskLevels = array_map(
        static function(int $i) {
            return $i + 1;
        },
        array_column($lowestPoints, 'value')
    );

    echo str_pad("{$key}: risk level: ", 36) . array_sum($riskLevels) . "\n";
}
