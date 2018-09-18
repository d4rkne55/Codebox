<?php

class Dumper
{
    private $customFormat;
    private $highlighting;


    /**
     * Dumper constructor.
     * Dumps variables with highlighting
     *
     * @param mixed $var           variable(s) to dump
     * @param bool  $custom        decides whether custom dumping format or PHP's print_r
     * @param bool  $highlighting  colored styling or plain dump
     */
    public function __construct($var, $custom = true, $highlighting = true) {
        $this->customFormat = $custom;
        $this->highlighting = $highlighting;

        if (count(func_get_args()) > 0) {
            $containerStyles = self::arrayToInlineCss(array(
                'display' => 'table',
                'min-width' => '100%',
                'margin' => '0.3em 0',
                'padding' => '0.2em 0.35em 0.3em',
                'background' => 'rgba(0,0,0, 0.02)',
                'border' => '1px solid rgba(0,0,0, 0.1)',
                'border-width' => '1px 0',
                'box-sizing' => 'border-box'
            ));

            if ($custom) {
                $dumped = self::customDumping($var, $highlighting);
            } else {
                $dumped = print_r($var, true);
                $dumped = '<pre style="margin: 0">' .$dumped. '</pre>';
            }

            echo "<div style=\"$containerStyles\">$dumped</div>";
        }
    }

    /**
     * Custom dumping format and styling for better readability
     *
     * @param mixed $var
     * @param bool  $highlighting
     * @return string
     */
    public static function customDumping($var, $highlighting) {
        ob_start();

        include_once('templates/DumperCustom.css.html');

        $highlightClass = $highlighting ? 'highlighting' : '';

        if (is_array($var)) {
            ?>
            <table class="dumper <?= $highlightClass ?>">
                <tr>
                    <td colspan="4"><span class="var-type">Array</span> {</td>
                </tr>
                <?php
                foreach ($var as $varKey => $varValue) {
                    $valueType = self::getVarType($varValue);
                    $typeClass = self::getVarType($varValue, true);

                    if (is_array($varValue)) {
                        $value = self::customDumping($varValue, $highlighting);
                    } else {
                        $value = self::processVarValue($varValue);
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td><span class="object-index"><?= $varKey ?></span></td>
                        <td> => </td>
                        <td>
                            <?php
                            if (is_array($varValue)) {
                                echo $value;
                            } else { ?>
                                <span class="value <?= $typeClass ?>"><?= $value ?></span>
                            <?php } ?>

                            <?php if (!empty($valueType)) { ?>
                                <span class="value-type">(<?= $valueType ?>)</span>
                            <?php } ?>
                        </td>
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
            <div class="dumper <?= $highlightClass ?>">
                <span class="value <?= $typeClass ?>"><?= $value ?></span>

                <?php if (!empty($valueType)) { ?>
                    <span class="value-type">(<?= $valueType ?>)</span>
                <?php } ?>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Returns the variable's datatype
     * The text for being displayed when $cssClass is not set,
     * otherwise, if $cssClass is true, the class name for CSS
     *
     * @param mixed $var
     * @param bool  $cssClass
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
                $valueType = 'Resource: ' . get_resource_type($var);
                break;
        }

        if ($cssClass) {
            if ($valueType == 'bool') {
                $valueType = ($var === true) ? 'true' : 'false';
            } elseif (gettype($var) == 'resource') {
                $valueType = 'resource';
            } else {
                $valueType = strtolower($valueType);
            }
        } else {
            if (in_array($valueType, array('array', 'null'))) {
                $valueType = '';
            }
        }

        return $valueType;
    }

    /**
     * Processes values for dumping
     *
     * @param mixed $var
     * @return string|mixed
     */
    private static function processVarValue($var) {
        switch (strtolower(gettype($var))) {
            case 'string' :
                // shorten long strings
                if (mb_strlen($var) > 500) {
                    $var = mb_substr($var, 0, 500);
                    $ellipsis = true;
                } else {
                    $ellipsis = false;
                }

                $quoteType = '"';
                $value = str_replace($quoteType, "\\$quoteType", $var);

                // escaping of HTML tags
                $value = strtr($value, array(
                    '<' => '&lt;',
                    '>' => '&gt;'
                ));

                // escaping of special characters
                $value = strtr($value, array(
                    "\\$quoteType" => '<span class="escaped-char">\\' .$quoteType. '</span>',
                    "\r" => '<span class="escaped-char">\r</span>',
                    "\n" => '<span class="escaped-char">\n</span>',
                    "\t" => '<span class="escaped-char">\t</span>'
                ));

                // escaping of HTML entities
                $value = preg_replace_callback('/&(\w+|#x?\d+);/', function($matches) {
                    if (in_array($matches[1], ['lt', 'gt'])) {
                        return $matches[0];
                    }

                    return '<span class="escaped-char">&amp;' .$matches[1]. ';</span>';
                }, $value);

                if ($ellipsis) {
                    $value .= '<span class="string-shortened">&hellip;</span>';
                }

                $value = $quoteType .$value. $quoteType;
                break;
            case 'boolean' :
                $value = ($var === true) ? 'true' : 'false';
                break;
            case 'object' :
                $value = get_class($var);
                break;
            case 'null' :
                $value = 'null';
                break;
            default :
                $value = $var;
        }

        return $value;
    }

    /**
     * little helper function for converting an associative array to inline CSS format
     *
     * @param array $styleArr
     * @return string
     */
    private static function arrayToInlineCss($styleArr) {
        $css = array();
        foreach ($styleArr as $option => $value) {
            $css[] = $option. ':' .$value;
        }

        return implode(';', $css);
    }
}