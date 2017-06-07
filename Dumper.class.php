<?php

class Dumper
{
    public $timeStart;
    public $timeEnd;


    /**
     * Dumper constructor.
     * Dumps variables with highlighting when passed, else only prepare for time measuring
     *
     * @param mixed $var          variable(s) to dump
     * @param bool $custom        decides whether custom dumping format or PHP's print_r
     * @param bool $highlighting  colored styling or plain dump
     */
    public function __construct($var = null, $custom = true, $highlighting = true) {
        if (isset($var)) {
            $containerStyles = $this::arrayToInlineCss(array(
                'display' => 'table',
                'min-width' => '100%',
                'margin' => '0.3em 0',
                'padding' => '0.2em 0.35em 0.3em',
                'background' => 'rgba(0,0,0, 0.02)',
                'border' => '1px solid rgba(0,0,0, 0.1)',
                'border-width' => '1px 0',
                'box-sizing' => 'border-box'
            ));
            echo "<div style=\"$containerStyles\">";

            if ($custom) {
                $this->customDumping($var, $highlighting);
            } else {
                $dumped = print_r($var, true);
                $dumped = '<pre style="margin: 0">' .$dumped. '</pre>';

                echo $dumped;
            }

            echo '</div>';
        }

        // start time measuring
        $this->timeStart = microtime(true);
    }

    /**
     * Custom dumping format and styling for better readability
     *
     * @param mixed $var
     * @param bool $highlighting
     */
    public function customDumping($var, $highlighting) {
        include_once('templates/DumperCustom.css.html');

        if (is_array($var)) {
            ?>
            <table class="dumper <?php echo $highlighting ? 'highlighting' : '' ?>">
                <tr>
                    <td colspan="4"><span class="var-type">Array</span> {</td>
                </tr>
                <?php
                foreach ($var as $varKey => $varValue) {
                    $value = self::processVarValue($varValue);
                    $valueType = self::getVarType($varValue);
                    $typeClass = self::getVarType($varValue, true);
                    ?>
                    <tr>
                        <td></td>
                        <td><span class="object-index"><?= $varKey ?></span></td>
                        <td> => </td>
                        <td><span class="value <?= $typeClass ?>"><?= $value ?></span> <?= $valueType ?></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td>}</td>
                </tr>
            </table>
            <?php
        }
        else {
            $value = self::processVarValue($var);
            $valueType = self::getVarType($var);
            $typeClass = self::getVarType($var, true);
            ?>
            <div class="dumper highlighting">
                <span class="value <?= $typeClass ?>"><?= $value ?></span> <?= $valueType ?>
            </div>
            <?php
        }
    }

    /**
     * Returns the variable's datatype
     * Includes the markup for being displayed when $cssClass is not set
     *
     * @param mixed $var
     * @param bool $cssClass
     * @return string
     */
    private static function getVarType($var, $cssClass = false) {
        switch ($valueType = strtolower(gettype($var))) {
            case 'integer' :
                $valueType = 'int';
                break;
            case 'double' :
                $valueType = 'float';
                break;
            case 'boolean' :
                $valueType = 'bool';
                break;
            case 'object' :
                $valueType = 'Object';
                break;
            case 'resource' :
                $valueType = 'Resource';
                break;
        }

        if ($cssClass) {
            if ($valueType == 'bool') {
                $valueType = ($var === true) ? 'true' : 'false';
            } else {
                $valueType = strtolower($valueType);
            }
        } else {
            if (!in_array($valueType, array('array', 'null'))) {
                $valueType = '<span class="value-type">(' . $valueType . ')</span>';
            } else {
                $valueType = '';
            }
        }

        return $valueType;
    }

    /**
     * Processes values for dumping
     *
     * @param mixed $var
     * @return mixed|string
     */
    private static function processVarValue($var) {
        switch (strtolower(gettype($var))) {
            case 'string' :
                $quoteType = '"';
                $value = str_replace($quoteType, "\\$quoteType", $var);
                // escaping of HTML tags
                $value = strtr($value, array(
                    '<' => '&lt;',
                    '>' => '&gt;'
                ));
                // shorten long strings
                if (mb_strlen($value) > 500) {
                    $value = mb_substr($value, 0, 500) . '<span class="string-shortened">&hellip;</span>';
                }
                // escaping of special characters
                $value = strtr($value, array(
                    "\\$quoteType" => '<span class="escaped-char">\\' .$quoteType. '</span>',
                    "\r" => '<span class="escaped-char">\r</span>',
                    "\n" => '<span class="escaped-char">\n</span>',
                    "\t" => '<span class="escaped-char">\t</span>'
                ));
                $value = $quoteType .$value. $quoteType;
                break;
            case 'boolean' :
                $value = ($var === true) ? 'true' : 'false';
                break;
            case 'array' :
                $value = print_r($var, true);
                break;
            case 'object' :
                $value = get_class($var);
                break;
            default :
                $value = $var;
        }

        return $value;
    }

    /**
     * Display time of code execution between Class construction and this method
     *
     * @param int $precision
     */
    public function getTime($precision = 2) {
        $this->timeEnd = microtime(true);
        echo sprintf("%.{$precision}f", ($this->timeEnd - $this->timeStart) * 1000) . ' ms';
    }

    /**
     * little helper function for converting an associative array to inline CSS format
     */
    private static function arrayToInlineCss($styleArr) {
        $css = array();
        foreach ($styleArr as $option => $value) {
            $css[] = $option. ':' .$value;
        }

        return implode(';', $css);
    }
}