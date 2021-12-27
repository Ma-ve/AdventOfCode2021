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
start-A
start-b
A-c
A-b
b-d
A-end
b-end
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day12-input.txt'),
];

function isSmallCave(string $cave): bool {
    return strtolower($cave) === $cave;
}

function through(string $start, array $linkedCaves, array &$alreadyPassedThrough, int $attempt = 0): int {
    $path = $alreadyPassedThrough[$attempt] ?? [];
    $path[] = $start;
    if($start === 'end') {
        $alreadyPassedThrough[$attempt] = $path;

        return $attempt + 1;
    }
    foreach($linkedCaves[$start] as $cave) {
        if(
            $cave === 'start' ||
            wasVisitedTwice($cave, $path)
        ) {
            continue;
        }
        $alreadyPassedThrough[$attempt] = $path;
        $attempt = through($cave, $linkedCaves, $alreadyPassedThrough, $attempt);
    }
    unset($alreadyPassedThrough[$attempt]);

    return $attempt;
}

function wasVisitedTwice($cave, $path): bool {
    if(!isSmallCave($cave)) {
        return false;
    }

    if(!in_array($cave, $path)) {
        return false;
    }

    $filtered = array_filter($path, 'isSmallCave');

    return count(array_unique($filtered)) < count($filtered);
}

foreach($inputs as $key => $input) {
    $instructions = array_filter(array_map('trim', explode("\n", $input)));

    $linkedCaves = [];
    foreach($instructions as $instruction) {
        [$caveA, $caveB] = explode('-', $instruction);
        $linkedCaves[$caveA][] = $caveB;
        $linkedCaves[$caveB][] = $caveA;
    }

    $alreadyPassedThrough = [];
    $availableRoutes = through('start', $linkedCaves, $alreadyPassedThrough);

    echo str_pad("{$key}: available routes:", 36) . $availableRoutes . "\n";
}
