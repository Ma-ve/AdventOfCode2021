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

const DEBUG = false;

$example = <<<TXT
5483143223
2745854711
5264556173
6141336146
6357385478
4167524645
2176841721
6882881134
4846848554
5283751526
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day11-input.txt'),
];

/**
 * @see https://stackoverflow.com/a/27333242/1255033
 */
function strpos_all(string $haystack, string $needle): array {
    $offset = 0;
    $allpos = [];
    /** @noinspection PhpAssignmentInConditionInspection */
    while(($pos = strpos($haystack, $needle, $offset)) !== false) {
        $offset = $pos + 1;
        $allpos[] = $pos;
    }

    return $allpos;
}

$testsStrposAll = [
    [
        <<<TXT
22222
20aa2
2a2a2
2aaa2
22222
TXT
        ,
        [7, 8, 11, 13, 16, 17, 18],
    ],
    [
        <<<TXT
2aaa2
20222
22222
23332
a222a
TXT
        ,
        [1, 2, 3, 20, 24],
    ],
];

foreach($testsStrposAll as $item) {
    [$input, $expected] = $item;
    $actual = strpos_all(str_replace("\n", '', $input), 'a');
    if($expected !== $actual) {
        throw new Exception('In tests str pos all, expected did not match actual');
    }
}

function debug(string $grid, bool $exit = true) {
    global $rowLength;
    if(!DEBUG) {
        return;
    }

    echo "\n";
    echo chunk_split($grid, $rowLength, "\n");
    echo "\n";
    $exit && exit;
}

function goFlash(string &$grid, int $position, array &$flashed) {
    global $step;

    if(isset($flashed[$position])) {
        return;
    }
    updateGridValue($grid, $position, 0);

    foreach(getPositions($position) as $name => $adjacentPosition) {
        if($adjacentPosition < 0) {
            continue;
        }
        if(!isset($grid[$adjacentPosition])) {
            continue;
        }
        if(isset($flashed[$adjacentPosition])) {
            continue;
        }
        updateGridValue($grid, $adjacentPosition);
    }
    $flashed[$position] = true;

    $neededFlashingAgain = strpos_all($grid, 'a');
    if(!empty($neededFlashingAgain)) {
        if(DEBUG) echo sprintf("Step {$step}, re-flashing needed octopi... (%s)\n", implode(', ', $neededFlashingAgain));
        foreach($neededFlashingAgain as $flashAgain) {
            goFlash($grid, $flashAgain, $flashed);
        }
    }

    foreach(array_keys($flashed) as $alreadyFlashedPosition) {
        updateGridValue($grid, $alreadyFlashedPosition, 0);
    }
}

function getPositions(int $i): array {
    global $rowLength;

    $isTopLeft = $i === 0;

    if($isTopLeft) {
        return [
            'right'       => 1,
            'bottom'      => $rowLength,
            'bottomright' => $rowLength + 1,
        ];
    }

    $top = $i - $rowLength;
    $bottom = $i + $rowLength;
    $bottomLeft = $bottom - 1;
    $bottomRight = $bottom + 1;

    $isTopRight = $i === $rowLength - 1;
    if($isTopRight) {
        return [
            'left'       => $i - 1,
            'bottomleft' => $i + $rowLength - 1,
            'bottom'     => $bottom,
        ];
    }

    $tops = [
        'topleft'  => $top - 1,
        'top'      => $top,
        'topright' => $top + 1,
    ];
    $bottoms = [
        'bottomleft'  => $bottomLeft,
        'bottom'      => $bottom,
        'bottomright' => $bottomRight,
    ];

    $lefts = [
        'left'       => $i - 1,
        'topleft'    => $tops['topleft'],
        'bottomleft' => $bottoms['bottomleft'],
    ];

    $rights = [
        'right'       => $i + 1,
        'topright'    => $tops['topright'],
        'bottomright' => $bottoms['bottomright'],
    ];
    if($i < $rowLength) {
        $tops = [];
        unset($lefts['topleft']);
        unset($rights['topright']);
    }

    if(($i + 1) % ($rowLength) === 0) {
        $rights = [];
        unset($tops['topright']);
        unset($bottoms['bottomright']);
    }
    if($i >= ($rowLength * ($rowLength - 1))) {
        $bottoms = [];
        unset($lefts['bottomleft']);
        unset($rights['bottomright']);
    }

    if($i % $rowLength === 0) {
        $lefts = [];
        unset($tops['topleft']);
        unset($bottoms['bottomleft']);
    }

    return $tops + $rights + $bottoms + $lefts;
}

$testGrid = str_replace(["\r\n", "\n"], '', <<<TXT
012
345
678
TXT
);

$testGetPositions = [
    0 => [1, 3, 4],
    1 => [0, 2, 3, 4, 5],
    2 => [1, 4, 5],
    3 => [0, 1, 4, 6, 7],
    4 => [0, 1, 2, 3, 5, 6, 7, 8],
    5 => [1, 2, 4, 7, 8],
    6 => [3, 4, 7],
    7 => [3, 4, 5, 6, 8],
    8 => [4, 5, 7],
];

$rowLength = 3;
foreach($testGetPositions as $testPosition => $expected) {
    $actual = getPositions($testPosition);
    $filteredActual = array_values(array_filter($actual, static function(int $i) {
        return $i >= 0;
    }));
    sort($filteredActual);
    if($expected !== $filteredActual) {
        debug($testGrid, false);
        var_dump($testPosition, $expected, $actual);
        echo "</pre>";
        exit;
    }
}

function updateGridValue(&$grid, int $position, int $newValue = null): void {
    if(null === $newValue) {
        $value = $grid[$position];
        $input = is_numeric($value) ? $value : dechex($value);
        $newValue = dechex(min($input + 1, 10));
    }
    $grid[$position] = $newValue;
}

foreach($inputs as $key => $input) {
    foreach([2, 5, 10, 100, 1000] as $steps) {
        $countFlashes = 0;

        $grid = str_replace("\r\n", "\n", $input);
        $rowLength = strlen(trim(explode("\n", $grid)[0]));

        $grid = str_replace("\n", '', $grid);

        $allFlashedAtStep = false;

        for($step = 1; $step <= $steps; $step++) {
            if(DEBUG) echo "Step {$step}, updating all values... Current grid:\n";
            debug($grid, false);

            for($j = 0; $j < strlen($grid); $j++) {
                updateGridValue($grid, $j);
            }
            if(DEBUG) echo "Step {$step}, updated all values!\n";
            debug($grid, false);

            $flashed = [];
            if(DEBUG) echo "Step {$step}, flashing all octopi...\n";
            foreach(strpos_all($grid, 'a') as $pos) {
                goFlash($grid, $pos, $flashed);
            }

            $countFlashes += count($flashed);

            if(!$allFlashedAtStep && count($flashed) === strlen($grid)) {
                $allFlashedAtStep = $step;
            }

            if(DEBUG) echo "Step {$step} done!\n";
            debug($grid, false);
        }

        echo str_pad("{$key}: count flashes after {$steps} steps: ", 36) . $countFlashes . ". They all flashed at step {$allFlashedAtStep}.\n";
    }
}
