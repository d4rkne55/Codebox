<?php

class Timer
{
    private static $timeStart = null;

    /**
     * Start or reset Timer
     */
    public static function start() {
        self::$timeStart = microtime(true);
    }

    /**
     * Display time passed between start() call and this method
     *
     * @param bool $output     time in ms gets echo'ed when true
     * @param int  $precision
     * @return null|string
     */
    public static function getTime($output = true, $precision = 2) {
        if (self::$timeStart === null) {
            throw new LogicException('You need to call Timer::start() before this method.');
        }

        $timeMs = (microtime(true) - self::$timeStart) * 1000;
        $timeMs = sprintf("%.{$precision}f", $timeMs);

        if ($output) {
            echo "$timeMs ms";
            return null;
        } else {
            return $timeMs;
        }
    }
}