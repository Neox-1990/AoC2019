<?php
$pstart = microtime(true);

$range_start = 158126;
$range_end = 624574;

$potiential_passwords = [];
$potiential_strict_passwords = [];

for ($password = $range_start; $password <= $range_end; $password++) {
    if (validatePassword($password)) {
        $potiential_passwords[] = $password;
        if (checkStrictDouble($password)) {
            $potiential_strict_passwords[] = $password;
        }
    }
}

$pend1 = microtime(true);

echo "There are ".count($potiential_passwords)." potential passwords\r\n";
echo "There are ".count($potiential_strict_passwords)." potential strict passwords\r\n";

$ptime1 = round(($pend1 - $pstart)*100, 3);
echo "\r\nPerformance: ".$ptime1."ms\r\n";

//Debug
file_put_contents('debug.txt', print_r($potiential_strict_passwords, true));

//Helpers
function validatePassword(int $password):bool
{
    return checkSixDigits($password) && checkAscending($password) && checkDouble($password);
}

function checkSixDigits(int $number):bool
{
    return $number >= 100000 && $number <= 999999;
}

function checkAscending(int $number):bool
{
    $number = str_split(strval($number));
    $valid = true;
    for ($index = 0; $index < count($number)-1; $index++) {
        $valid = $valid && intval($number[$index]) <= intval($number[$index+1]);
    }
    return $valid;
}

function checkDouble(int $number):bool
{
    $number = str_split(strval($number));
    $valid = false;
    for ($index = 0; $index < count($number)-1; $index++) {
        $valid = $valid || intval($number[$index]) == intval($number[$index+1]);
    }
    return $valid;
}

function checkStrictDouble(int $number)
{
    $number = str_split(strval($number));
    //find positions of doubles
    $doublesIndex = [];
    for ($index = 0; $index < count($number)-1; $index++) {
        if (intval($number[$index]) == intval($number[$index+1])) {
            $doublesIndex[] = $index;
        }
    }
    $strictDoublesIndex = [];
    //check if the doubles are part of bigger groups and keep the ones, that aren't
    foreach ($doublesIndex as $index) {
        $prev = $number[$index-1] ?? null;
        $next = $number[$index+2] ?? null;
        if ((is_null($prev) || intval($prev) != intval($number[$index])) && (is_null($next) || intval($next) != intval($number[$index]))) {
            $strictDoublesIndex[] = $index;
        }
    }
    return !empty($strictDoublesIndex);
}
