function calc_qf($res, $bitrate, $fps = 23.976) {
    $res = explode('x', $res);
    $qf = ($bitrate * 1000) / ($res[0] * $res[1] * $fps);
    return sprintf('%.3f', round($qf, 3));
}

//echo calc_qf('1280x536', 3560);


function calc_max_ref($widthPx, $heightPx, $profileLevel) {
    $widthMbs = ($widthPx + ($widthPx % 16)) / 16;
    $heightMbs = ($heightPx + ($heightPx % 16)) / 16;
    $maxDPB = array(
        '1'   => 396,
        '1.1' => 900,
        '1.2' => 2376,
        '1.3' => 2376,
        '2'   => 2376,
        '2.1' => 4752,
        '2.2' => 8100,
        '3'   => 8100,
        '3.1' => 18000,
        '3.2' => 20480,
        '4'   => 32768,
        '4.1' => 32768,
        '4.2' => 34816,
        '5'   => 110400,
        '5.1' => 184320,
        '5.2' => 184320
    );
    $maxDPB = $maxDPB[$profileLevel];

    return min(floor($maxDPB / ($widthMbs * $heightMbs)), 16);
}

//echo calc_max_ref(2560, 1440, '5.1');