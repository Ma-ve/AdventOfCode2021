<?php

$example = <<<TXT
199
200
208
210
200
207
240
269
260
263
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day1-input.txt'),
];

foreach($inputs as $name => $input) {
    $explode = array_map('intval', array_map('trim', explode("\n", str_replace("\r\n", "\n", $input))));

    $prev = null;
    $increments = 0;
    foreach($explode as $key => $currentValue) {
        $value = $currentValue;
        if(isset($explode[$key+1])) {
            $value += $explode[$key+1];
        }
        if(isset($explode[$key+2])) {
            $value += $explode[$key+2];
        }
        if(null !== $prev && $value > $prev) {
            $increments++;
        }
        $prev = $value;
    }

    echo str_pad("{$name}: ", 30) . $increments . "\n";
}

