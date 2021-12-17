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
00100
11110
10110
10111
10101
01111
00111
11100
10000
11001
00010
01010
TXT;

// First column (of 12 rows):
//   0 => 5 occurences
//   1 => 7 occurences
// Most common:  1
// Least common: 0

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day3-input.txt'),
];

//$log = function(int $id, string $action, int $units, int $depth, int $horizontal, int $aim) {
//    $prefix = str_pad("{$id}  {$action} {$units}", 20);
//    echo "{$prefix} | Depth: {$depth}, horizontal: {$horizontal}, aim: {$aim}\n";
//};

$gamma = '';
$epsilon = '';

$binaryToDecimal = function(string $binary): int {
    return bindec($binary);
};

$calculateValues = static function (array $data) {
    $gamma = '';
    $epsilon = '';

    for($i = 0; $i < strlen($data[0]); $i++) {
        $values = array_map(static function (string $item) use ($i) {
            return $item[$i];
        }, $data);

        $countValues = array_count_values($values);
        $mostCommon = $countValues[0] > $countValues[1]
            ? 0
            : 1;
        $leastCommon = (int)!$mostCommon;

        $gamma .= (string)$mostCommon;
        $epsilon .= (string)$leastCommon;
    };

    return [$gamma, $epsilon];
};

foreach($inputs as $key => $input) {
    $newInput = [];
    $gamma = '';
    $epsilon = '';

    $explode = array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", $input))));


    [$gamma, $epsilon] = $calculateValues($explode);


    echo str_pad("{$key}: ", 30) . ($binaryToDecimal($gamma) * $binaryToDecimal($epsilon)) . "\n";
}

