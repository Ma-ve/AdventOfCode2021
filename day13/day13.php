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
6,10
0,14
9,10
0,3
10,4
4,11
6,0
6,12
4,1
0,13
10,12
3,4
3,0
8,4
1,10
2,14
8,10
9,0

fold along y=7
fold along x=5
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day13-input.txt'),
];

function testHorizontal() {
    $input = [
        [
            'y'        => 3,
            'length'   => 5,
            'input'    => <<<TXT
...#.
..#..
##...
#####
..###
.#.#.
#####
TXT
            ,
            'foldline' => <<<TXT
...#.
..#..
##...
-----
..###
.#.#.
#####

TXT
            ,
            'folded'   => <<<TXT
#####
.###.
#####
TXT
            ,
        ],
        [
            'y'        => 7,
            'length'   => 11,
            'input'    => <<<TXT
...#..#..#.
....#......
...........
#..........
...#....#.#
...........
...........
...........
...........
...........
.#....#.##.
....#......
......#...#
#..........
#.#........
TXT
            ,
            'foldline' => <<<TXT
...#..#..#.
....#......
...........
#..........
...#....#.#
...........
...........
-----------
...........
...........
.#....#.##.
....#......
......#...#
#..........
#.#........
TXT
            ,
            'folded'   => <<<TXT
#.##..#..#.
#...#......
......#...#
#...#......
.#.#..#.###
...........
...........
TXT
            ,
        ],
    ];

    foreach($input as $item) {
        $sheet = str_replace(["\r\n", "\n"], '', $item['input']);
        $withFoldline = str_replace(["\r\n", "\n"], '', $item['foldline']);
        $folded = str_replace(["\r\n", "\n"], '', $item['folded']);

        $horizontalFold = foldHorizontal($sheet, $item['y'], $item['length'], true);
        if($horizontalFold !== $withFoldline) {
            throw new Exception('Did not find horizontal fold line');
        }
        $foldedHorizontally = foldHorizontal($sheet, $item['y'], $item['length']);
        if($foldedHorizontally !== $folded) {
            throw new Exception('Did not properly fold horizontally');
        }
    }
}

function testVertical() {
    $input = [
        [
            'x'        => 2,
            'length'   => 5,
            'input'    => <<<TXT
...#.
..#..
##...
#####
..###
.#.#.
#####
TXT
            ,
            'foldline' => <<<TXT
..|#.
..|..
##|..
##|##
..|##
.#|#.
##|##
TXT
            ,
            'folded'   => <<<TXT
.#
..
##
##
##
.#
##
TXT
            ,
        ],
        [
            'x'        => 5,
            'length'   => 11,
            'input'    => <<<TXT
#.##..#..#.
#...#......
......#...#
#...#......
.#.#..#.###
...........
...........
TXT
            ,
            'foldline' => <<<TXT
#.##.|#..#.
#...#|.....
.....|#...#
#...#|.....
.#.#.|#.###
.....|.....
.....|.....
TXT
            ,
            'folded'   => <<<TXT
#####
#...#
#...#
#...#
#####
.....
.....
TXT
            ,
        ],
    ];

    foreach($input as $item) {
        $sheet = str_replace(["\r\n", "\n"], '', $item['input']);
        $withFoldline = str_replace(["\r\n", "\n"], '', $item['foldline']);
        $folded = str_replace(["\r\n", "\n"], '', $item['folded']);

        $verticalFold = foldVertical($sheet, $item['x'], $item['length'], true);
        if($verticalFold !== $withFoldline) {
            throw new Exception('Did not find vertical fold line');
        }
        $foldedVertically = foldVertical($sheet, $item['x'], $item['length']);
        if($foldedVertically !== $folded) {
            throw new Exception('Did not properly fold vertically');
        }
    }
}

testHorizontal();
testVertical();

function foldHorizontal(string $sheet, int $y, int $length, bool $returnFoldline = false): string {
    // Fold line is to overwrite the line FOLLOWING y
    for($i = (($y) * $length); $i < (($y + 1) * $length); $i++) {
        $sheet[$i] = '-';
    }
    if($returnFoldline) {
        return $sheet;
    }

    $halves = explode(str_repeat('-', $length), $sheet);
    $sheet = $halves[0];

    $bottomHalf = implode('', array_reverse(str_split($halves[1], $length)));
    for($i = 0; $i < strlen($bottomHalf); $i++) {
        if($bottomHalf[$i] !== '#') {
            continue;
        }
        $sheet[$i] = '#';
    }

    return $sheet;
}

function foldVertical(string $sheet, int $x, int $length, bool $returnFoldline = false): string {
    // Fold line is to overwrite the line FOLLOWING x
    for($i = $x; $i <= strlen($sheet); $i += $length) {
        $sheet[$i] = '|';
    }
    if($returnFoldline) {
        return $sheet;
    }

//    echo chunk_split($sheet, $length) . "\n\n";
    $newSheet = [];
    foreach(str_split($sheet, $length) as $row) {
        $explode = explode('|', $row);
        $left = $explode[0];
        if(!isset($explode[1])) {
            continue;
        }
        $right = strrev($explode[1]);
        for($i = strlen($left) - 1; $i >= 0; $i--) {
            if(isset($right[$i]) && $right[$i] === '#') {
                $left[$i] = '#';
            }
        }
        $newSheet[] = $left;
    }

    $newSheet = implode('', $newSheet);

    return $newSheet;
}

foreach($inputs as $key => $input) {
    $instructions = array_filter(array_map('trim', explode("\n", $input)));
    $coordinates = array_filter($instructions, static function(string $input) {
        return strpos($input, 'fold') === false;
    });
    $folds = array_values(array_filter($instructions, static function(string $input) {
        return strpos($input, 'fold') !== false;
    }));

    $xCoordinates = [];
    $yCoordinates = [];
    foreach($coordinates as $coordinate) {
        [$__x, $__y] = explode(',', $coordinate);
        $xCoordinates[] = $__x;
        $yCoordinates[] = $__y;
    }

    $length = max($xCoordinates) + 1;
    $height = max($yCoordinates) + 1;

    echo "Sheet is to be {$length} (x) by {$height} (y):\n";

    $sheet = str_repeat(str_repeat('.', $length), $height);

    foreach($xCoordinates as $index => $x) {
        $y = $yCoordinates[$index];
        $stringPosition = $x + ($y * $length);

        $sheet[$stringPosition] = '#';
    }

    $countFolds = 1;
    foreach($folds as $fold) {
        $isHorizontalFold = strpos($fold, 'y=') !== false;
        $foldAlong = explode($isHorizontalFold ? 'y=' : 'x=', $fold)[1];

        if($isHorizontalFold) {
            $sheet = foldHorizontal($sheet, (int)$foldAlong, $length);
        } else {
            $sheet = foldVertical($sheet, (int)$foldAlong, $length);
        }

        echo str_pad("{$key}: after {$countFolds} fold(s):", 36) . substr_count($sheet, '#') . " ({$fold})\n";
        $countFolds++;
    }
}
