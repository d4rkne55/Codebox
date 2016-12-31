<?php

namespace PHPCodebox;

class ErrorManager
{
    /**
     * map error codes
     *
     * @link http://php.net/manual/en/errorfunc.constants.php
     */
    private static $errType = array(
        E_ERROR => 'Fatal error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse error',
        E_NOTICE => 'Notice',
        E_USER_ERROR => 'Fatal error <i>(user-generated)</i>',
        E_USER_WARNING => 'Warning <i>(user-generated)</i>',
        E_USER_NOTICE => 'Notice <i>(user-generated)</i>',
        E_STRICT => 'Strict notice',
        E_RECOVERABLE_ERROR => 'Fatal error <i>(catchable)</i>',
        E_DEPRECATED => 'Strict notice',
        E_USER_DEPRECATED => 'Strict notice <i>(user-generated)</i>'
    );


    public static function handleError($errNum, $errStr, $errFile, $errLine) {
        try {
            // when error is not covered by this handler, fallback to PHP handling
            // would throw an undefined error otherwise
            // also ignore @-suppressed errors (error level 0)
            if (!array_key_exists($errNum, self::$errType) || error_reporting() === 0) {
                return false;
            }

            // determines if the error is fatal and the script execution should stop
            $fatal = ($errNum === E_ERROR || $errNum === E_USER_ERROR);

            $errType = self::$errType[$errNum];
            $noticeClass = (stripos($errType, 'notice') !== false) ? 'notice' : '';
            $note = $fatal ? '<br>Script execution has stopped.' : '';

            $errStr = self::formatErrorMessage($errStr);

            $view = new \View();
            $view->render('ErrorManagerTemplate.php', array(
                'noticeClass' => $noticeClass,
                'errType' => $errType,
                'errStr' => $errStr,
                'errLine' => $errLine,
                'note' => $note
            ));

            // if fatal error, stop script execution, just like PHP's original handling
            if ($fatal) {
                die();
            }

            return true;
        }
        catch (\Exception $e) {
            $format = '<span style="color: gray"><b>Codebox Error</b>: %s (%s:%d)</span><br>';
            printf($format, $e->getMessage(), basename($e->getFile()), $e->getLine());
            return false;
        }
    }

    public static function handleException(\Exception $e) {
        // defaults
        $errNum = ($e->getCode() === 0) ? E_ERROR : $e->getCode();
        $errStr = ($e->getMessage() == '') ? 'Uncaught Exception' : $e->getMessage();

        self::handleError($errNum, $errStr, $e->getFile(), $e->getLine());
    }

    /**
     * workaround for handling fatal errors, set_error_handler() doesn't
     *
     * @link http://php.net/manual/de/function.set-error-handler.php#112291
     */
    public static function handleFatal() {
        // don't display fatal errors
        error_reporting(E_ALL & ~E_ERROR);

        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error['type'] == E_ERROR) {
                ErrorManager::handleError($error['type'], $error['message'], $error['file'], $error['line']);
            }
        });
    }

    private static function formatErrorMessage($str) {
        // prefix variables with $
        $str = preg_replace('/variable: (\w+)/', 'variable: \$$1', $str);

        // make undefined stuff italic and slightly grey, except offsets (beginning with number)
        $str = preg_replace('/(undefined [a-z]+:?) ([a-z$][^\s]+)/i', '$1 <i>$2</i>', $str);

        // link to php manual for functions and methods, unless undefined
        if (stripos($str, 'undefined') === false) {
            $str = preg_replace_callback('/(?:([^\s]+)::)?(\w+\(\))/', function($matches) {
                $funcParent = empty($matches[1]) ? 'function' : $matches[1];

                // remove function parenthesis and replace underscores with dash for URL
                $func = strtr($matches[2], array(
                    "()" => "",
                    "_" => "-"
                ));

                $page = strtolower($funcParent . '.' . $func);

                return '<a href="http://php.net/manual/en/' .$page. '.php" target="_blank">' .$matches[0]. '</a>';
            }, $str);
        }

        return $str;
    }
}