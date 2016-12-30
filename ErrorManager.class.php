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
        E_ERROR => "Fatal error",
        E_WARNING => "Warning",
        E_PARSE => "Parse error",
        E_NOTICE => "Notice",
        E_USER_ERROR => "Fatal error <i>(user-generated)</i>",
        E_USER_WARNING => "Warning <i>(user-generated)</i>",
        E_USER_NOTICE => "Notice <i>(user-generated)</i>",
        E_STRICT => "Strict notice",
        E_RECOVERABLE_ERROR => "Fatal error <i>(catchable)</i>",
        E_DEPRECATED => "Strict notice",
        E_USER_DEPRECATED => "Strict notice <i>(user-generated)</i>"
    );

    const styleFile = 'ErrorManager.css.html';


    public static function handleError($errNum, $errStr, $errFile, $errLine) {
        // when error is not covered by this handler, fallback to PHP handling
        // would throw an undefined error otherwise
        // also ignore @-suppressed errors (error level 0)
        if (!array_key_exists($errNum, self::$errType) || error_reporting() === 0) {
            return false;
        }

        // determines if the error is fatal and the script execution should stop
        $fatal = ($errNum === E_ERROR || $errNum === E_USER_ERROR);

        $errType = self::$errType[$errNum];
        $noticeClass = (stripos($errType, "notice") !== false) ? "notice" : "";
        $note = $fatal ? '<br>Script execution has stopped.' : "";

        $errStr = self::formatErrorMessage($errStr);

        if (self::styleFile) include_once(self::styleFile);

        ?>
        <div class="code-error <?= $noticeClass ?>">
            <h3><?= $errType ?></h3>
            <p>
                <span class="error-msg"><?= $errStr ?></span>&ensp;on line <b class="error-line"><?= $errLine ?></b>
                <?= $note ?>
            </p>
        </div>
        <?php

        // if fatal error, stop script execution, just like PHP's original handling
        if ($fatal) {
            die();
        }

        return true;
    }

    public static function handleException(\Exception $e) {
        $errNum = ($e->getCode() === 0) ? E_ERROR : $e->getCode();
        $errStr = ($e->getMessage() == "") ? "Uncaught Exception" : $e->getMessage();

        self::handleError($errNum, $errStr, $e->getFile(), $e->getLine());
    }

    private static function formatErrorMessage($str) {
        // prefix variables with $
        $str = preg_replace('/variable: (\w+)/', 'variable: \$$1', $str);

        // link to php manual for functions, unless function is undefined
        if (stripos($str, 'undefined function') === false) {
            $str = preg_replace_callback('/\w+\(\)/', function($matches) {
                // remove function parenthesis and replace underscores with dash for URL
                $func = strtr($matches[0], array(
                    "()" => "",
                    "_" => "-"
                ));

                return '<a href="//php.net/manual/en/function.' .$func. '.php" target="_blank">' .$matches[0]. '</a>';
            }, $str);
        }

        // make undefined stuff italic and slightly grey, except offsets (beginning with number)
        $str = preg_replace('/(undefined [a-z]+:?) ([a-z$][\w()]+)/i', '$1 <i>$2</i>', $str);

        return $str;
    }
}