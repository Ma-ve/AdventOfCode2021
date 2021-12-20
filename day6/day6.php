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
3,4,3,1,2
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day6-input.txt'),
];

$baseCount = [
    8 => 0,
    7 => 0,
    6 => 0,
    5 => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0,
    0 => 0,
];
$calculateFishAfterDays = function(int $days, array $fish) use ($baseCount): int {
    $countValues = array_count_values($fish);
    $sum = 0;
    for($i = 0; $i < $days; $i++) {
        $newCount = $baseCount;
        foreach($countValues as $value => $count) {
            if($value == 0) {
                $newCount[8] = $count;
                $value = 7;
            }
            $newCount[$value - 1] += $count;
        }
        $countValues = $newCount;
    }

    return array_sum($countValues);
};

foreach($inputs as $key => $input) {
    $fish = array_filter(array_map('trim', explode(",", str_replace("\r\n", "\n", $input))));

    echo str_pad("{$key}: after  18 days, there are ", 36) . $calculateFishAfterDays(18, $fish) . " fish\n";
    echo str_pad("{$key}: after  80 days, there are ", 36) . $calculateFishAfterDays(80, $fish) . " fish\n";
    echo str_pad("{$key}: after 256 days, there are ", 36) . $calculateFishAfterDays(256, $fish) . " fish\n";
}
