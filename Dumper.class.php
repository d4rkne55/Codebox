<?php

class Dumper
{
    public $timeStart;
    public $timeEnd;


    /**
     * Dumper constructor.
     * Dumps variables with highlighting when passed, else only prepare for time measuring
     *
     * @param array $var  Array of variables to dump
     */
    public function __construct($vars = null) {
        if ($vars) {
            $dumped = print_r($vars, true);
            // add PHP-tags temporarily for hightlight_string() to work
            $code = '<?php ' .$dumped. ' ?>';
            $code = highlight_string($code, true);

            // remove PHP-tags from output again
            $dom = new \DOMDocument();
            $dom->loadHTML($code);
            $xPath = new \DOMXPath($dom);

            $nodes = $xPath->query('//code/span/span');
            $node1 = $nodes->item(0);
            $node2 = $nodes->item( $nodes->length - 1 );
            $node3 = $xPath->query('br[last()]', $node2->previousSibling)->item(0);
            $node1->parentNode->removeChild($node1);
            $node2->parentNode->removeChild($node2);
            if (isset($node3)) $node3->parentNode->removeChild($node3);

            $code = $dom->saveHTML();
            echo '<br><br>', $code, '<br>';
        }

        // start time measuring
        $this->timeStart = microtime(true);
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
}