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
[({(<(())[]>[[{[]{<()<>>
[(()[<>])]({[<{<<[]>>(
{([(<{}[<>[]}>{[]{[(<()>
(((({<>}<{<{<>}{[]{[]{}
[[<[([]))<([[{}[[()]]]
[{[{({}]{}}([{[{{{}}([]
{<[[]]>}<{[{[{[]{()[[[]
[<(<(<(<{}))><([]([]()
<{([([[(<>()){}]>(<<{{
<{([{{}}[<[[[<>{}]]]>[]]
TXT;

$corrupted = <<<TXT
{([(<{}[<>[]}>{[]{[(<()>
[[<[([]))<([[{}[[()]]]
[{[{({}]{}}([{[{{{}}([]
[<(<(<(<{}))><([]([]()
<{([([[(<>()){}]>(<<{{
TXT;

$ignored = <<<TXT
[(()[<>])]({[<{<<[]>>(
TXT;

$inputs = [
    'example'   => $example,
    'corrupted' => $corrupted,
    'ignored'   => $ignored,
    'actual'    => file_get_contents(__DIR__ . '/day10-input.txt'),
];

$characters = [
    '(' => ')',
    '[' => ']',
    '{' => '}',
    '<' => '>',
];
$opens = array_keys($characters);
$closes = array_values($characters);

$points = [
    ')' => 3,
    ']' => 57,
    '}' => 1197,
    '>' => 25137,
];

foreach($inputs as $key => $input) {
    $lines = array_map('trim', explode("\n", str_replace("\r\n", "\n", $input)));

    $illegal = [];
    foreach($lines as $line) {
        $openArray = [];

        for($i = 0; $i < strlen($line); $i++) {
            // Formats string like:
            //   [[[]<]]
            //      ^
            $debug = "\n\n" . $line . "\n" . str_repeat(' ', $i) . "^\n\n";

            $character = $line[$i];
            $characterIndex = in_array($character, $opens);
            $isOpen = false !== $characterIndex;
            if($isOpen) {
                $openArray[] = $character;
                continue;
            }
            $characterIndex = array_search($character, $closes);
            $openCharacter = $opens[$characterIndex];
            $lastOpenCharacter = end($openArray);
            if($lastOpenCharacter === $openCharacter) {
                array_pop($openArray);
                continue;
            };
            $lastOpenCharacterIndex = array_search($lastOpenCharacter, $opens);

            $illegal[] = [
                'found'    => $character,
                'expected' => $closes[$lastOpenCharacterIndex],
                'position' => $i,
                'debug'    => $debug,
            ];
            continue 2;
        }
    }

    $errorScore = 0;
    foreach(array_column($illegal, 'found') as $index => $item) {
        $errorScore += $points[$item];
    }

    echo str_pad("{$key}: syntax error score: ", 36) . $errorScore . "\n";
}
