<?php

namespace PHPCodebox;

/**
 * Created by PhpStorm.
 * User: d437586
 * Date: 22.11.16
 * Time: 08:34
 */
class Codebox
{
    public function __construct($errorHandling = false) {
        set_time_limit(3);

        if ($errorHandling) {
            // fix for not throwing 500 status code for fatal errors on webserver
            ini_set('display_errors', 1);

            $errMgr = new ErrorManager();
            set_error_handler(array($errMgr, 'handleError'));
            set_exception_handler(array($errMgr, 'handleException'));
            ErrorManager::handleFatal();
        }
    }

    public static function getCodeTemplate() {
        if (!empty($_GET['fn'])) {
            $fn = "functions/{$_GET['fn']}.php";
            if (file_exists($fn)) {
                return file_get_contents($fn);
            }
        }

        return false;
    }

    public function parseCode($code) {
        if (!empty($code)) {
            // if HTML tag or PHP start-tag at the beginning of the code,
            // parse code but also allow HTML
            if (preg_match('/^<[\w!]/', $code) || strpos($code, '<?php') === 0) {
                $code = '?>' . $code;
            }
            eval($code);
        }

        exit;
    }
}