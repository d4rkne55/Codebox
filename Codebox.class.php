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
    /**
     * Path to the style file for the errorHandler
     *
     * @var string|null
     */
    private $errorHandlingStyle;


    public function __construct($errorHandling = false, $errorHandlingStyle = null) {
        $this->errorHandlingStyle = $errorHandlingStyle;

        if ($errorHandling) {
            $errMgr = new ErrorManager();
            set_error_handler(array($errMgr, 'handleError'));
            set_exception_handler(array($errMgr, 'handleException'));
        }
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