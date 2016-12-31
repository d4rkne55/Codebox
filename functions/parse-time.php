function parseTime($string) {
    $units = array_reverse(explode(':', $string));
    $seconds = 0;

    foreach ($units as $pos => $unit) {
        $seconds += pow(60, $pos) * $unit;
    }

    return $seconds;
}

echo parseTime('4:00');