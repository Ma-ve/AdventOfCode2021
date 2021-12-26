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

const LINE_SEPARATOR = "\n";

const TYPE_HORIZONTAL = 'horizontal';
const TYPE_VERTICAL = 'vertical';

set_error_handler("exception_error_handler");

$example = <<<TXT
0,9 -> 5,9
8,0 -> 0,8
9,4 -> 3,4
2,2 -> 2,1
7,0 -> 7,4
6,4 -> 2,0
0,9 -> 2,9
3,4 -> 1,4
0,0 -> 8,8
5,5 -> 8,2
TXT;

$echoBoard = function(string $board): string {
    $keys = [
        '0'            => '.',
        LINE_SEPARATOR => "\n",
    ];

    return str_replace(array_keys($keys), $keys, $board);;
};

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day5-input.txt'),
];

$updateBoard = function(string &$board, int $rowLength, int $var1, int $var2, int $base, string $type) {
    for($i = $var1; $i <= $var2; $i++) {
        // 0,9 -> 5,9 = final row, pos 1 through 5
        // = pos 80 through 85

        // y = 4: 44
        // y = 0: 0
        // y = 9: 99
        if($type === TYPE_VERTICAL) {
            $offsetForRowLength = ($base * $rowLength) + $base;
            $offsetForPosition = $i;
        } else {
            $offsetForRowLength = ($i * $rowLength) + $i;
            $offsetForPosition = $base;
        }

        $boardPos = $offsetForRowLength + $offsetForPosition;

        $value = (int)$board[$boardPos];
        if($value + 1 >= 10) {
            throw new Exception('Uh oh, cant have 2 digits in a cell');
        }

        $board[$boardPos] = $value + 1;
    }
};

foreach($inputs as $key => $input) {
    $instructions = array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", $input))));

    $x = [];
    $y = [];
    $parsed = array_map(static function(string $row) use (&$x, &$y) {
        $explode = explode(' -> ', $row);
        $one = explode(',', $explode[0]);
        $two = explode(',', $explode[1]);
        $x[] = $one[0];
        $x[] = $two[0];
        $y[] = $one[1];
        $y[] = $two[1];

        return [$one, $two];
    }, $instructions);

    $xMax = max($x) + 1;
    $yMax = max($y) + 1;
    $row = str_repeat('0', $xMax);
    $rowLength = strlen($row);

    $board = '';
    for($i = 0; $i < $yMax; $i++) {
        $board .= $row . LINE_SEPARATOR;
    }
    $board = rtrim($board, LINE_SEPARATOR);

    foreach($parsed as $index => $instruction) {
        [$one, $two] = $instruction;
        [$x1, $y1] = $one;
        [$x2, $y2] = $two;

        $isStraight = $x1 == $x2 || $y1 == $y2;
        if(!$isStraight) {
            continue;
        }

        if($x1 != $x2) {
            if($y1 != $y2) {
                throw new Exception(sprintf('y1 was not y2 (%s != %s)', $y1, $y2));
            }
            $updateBoard($board, $rowLength, min($x1, $x2), max($x1, $x2), $y1, TYPE_VERTICAL);
        }
        if($y1 != $y2) {
            if($x1 != $x2) {
                throw new Exception(sprintf('x1 was not x2 (%s != %s)', $x1, $x2));
            }
            $updateBoard($board, $rowLength, min($y1, $y2), max($y1, $y2), $x1, TYPE_HORIZONTAL);
        }
    }

    $mostDangerousSpots = str_replace([
        LINE_SEPARATOR,
        0,
        1,
    ], '', $board);

    echo str_pad("{$key}: ", 30) . (strlen($mostDangerousSpots)) . "\n";
}

