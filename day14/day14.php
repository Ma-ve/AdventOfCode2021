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
NNCB

CH -> B
HH -> N
CB -> H
NH -> C
HB -> C
HC -> B
HN -> C
NN -> C
BH -> H
NC -> B
NB -> B
BN -> B
BB -> N
BC -> B
CC -> N
CN -> C
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day14-input.txt'),
];

function updateString(string $string): string {
    global $instructions;

    $newString = '';
    for($i = 0; $i < strlen($string); $i++) {
        $newString .= $string[$i];
        if(!isset($string[$i + 1])) {
            continue;
        }
        $currentPair = $string[$i] . $string[$i + 1];
        if(isset($instructions[$currentPair])) {
            $newString .= $instructions[$currentPair];
        }
    }

    return $newString;
}

foreach($inputs as $key => $input) {
    $instructions = array_map(static function(string $input) {
        return explode(' -> ', $input);
    }, array_filter(array_map('trim', explode("\n", $input))));
    $template = array_shift($instructions)[0];

    $instructions = array_combine(array_column($instructions, 0), array_column($instructions, 1));

    $string = $template;
    for($i = 1; $i <= 10; $i++) {
        $string = updateString($string);
        $length = strlen($string);
        $split = str_split($string);

        $countValues = array_count_values($split);
        $max = max($countValues);
        $min = min($countValues);

        echo sprintf(
            "[%s] step %s (length: %d, most: %s, least: %s. Most - least = %d)\n",
            $key,
            str_pad("{$i}:", 5, ' ', STR_PAD_LEFT),
            $length,
            $max,
            $min,
            $max - $min
        );
    }
    echo "\n";
}
