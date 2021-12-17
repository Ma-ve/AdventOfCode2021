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
7,4,9,5,11,17,23,2,0,14,21,24,10,16,13,6,15,25,12,22,18,20,8,19,3,26,1

22 13 17 11  0
 8  2 23  4 24
21  9 14 16  7
 6 10  3 18  5
 1 12 20 15 19

 3 15  0  2 22
 9 18 13 17  5
19  8  7 25 23
20 11 10 24  4
14 21 16 12  6

14 21 17 24  4
10 16 15  9 19
18  8 23 26 20
22 11 13  6  5
 2  0 12  3  7
TXT;

$exampleThirdBoard = <<<TXT
7,4,9,5,11,17,23,2,0,14,21,24,10,16,13,6,15,25,12,22,18,20,8,19,3,26,1

14 21 17 24  4
10 16 15  9 19
18  8 23 26 20
22 11 13  6  5
 2  0 12  3  7
TXT;

$inputs = [
    'example-third-board' => $exampleThirdBoard,
    'example'             => $example,
    'actual'              => file_get_contents(__DIR__ . '/day4-input.txt'),
];

$checkIsBoardWinner = static function(string $board): string {
    $rows = array_filter(explode("\n", str_replace("\r\n", "\n", trim($board))));
    $itemsPerRow = 5; // can be dynamic?
    $itemLength = 5;
    $columns = [];
    foreach($rows as $row) {
        if(substr_count($row, '*') === $itemsPerRow * 2) {
            return 'Row . ' . $row . ' wins';
        }

        for($i = 0; $i < ($itemLength * $itemsPerRow); $i += $itemLength) {
            if(!isset($columns[$i])) {
                $columns[$i] = '';
            }
            $substring = substr($row, $i, $itemLength);
            $columns[$i] .= $substring;
        }
    }

    foreach($columns as $column) {
        if(substr_count($column, '*') === $itemsPerRow * 2) {
            return 'Column . ' . $column . ' wins';
        }
    }

    return '';
};

$calculateBoardScore = function(int $winningNumber, string $board): int {
    preg_match_all("/\s(\d+)\s/", $board, $matches);

    return array_sum($matches[1]) * $winningNumber;
};

foreach($inputs as $key => $input) {
    $winningBoardScore = -1;
    $boards = array_filter(array_map('trim', explode("\n\n", str_replace("\r\n", "\n", $input))));
    $numbersToDraw = array_map('intval', explode(',', array_shift($boards)));

    $boards = array_map(static function(string $board): string {
        $newBoard = ' ';
        for($i = 0; $i < strlen($board); $i += 3) {
            $newBoard .= sprintf(' %s ', substr($board, $i, 3));
        }

        return str_replace("\n", " \n", $newBoard);
    }, $boards);

    foreach($numbersToDraw as $numbersDrawn => $number) {
        foreach($boards as $id => &$board) {
            // Two leading spaces = one space, with a *
            // Trailing space = no trailing space, but *
            $board = str_replace(sprintf(' %d ', $number), sprintf('*%d*', $number), $board);

            $isBoardWinner = $checkIsBoardWinner($board);
//            echo "Drawn {$number}, board:\n{$board}\n\n";
            if($isBoardWinner) {
                $winningBoardScore = $calculateBoardScore($number, $board);
                break 2;
            }
        }
    }

    echo str_pad("{$key}: ", 30) . ($winningBoardScore) . "\n";
}

