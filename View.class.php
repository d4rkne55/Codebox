<?php

/**
 * Basic MVC class
 */
class View
{
    protected $templateDir;
    private $vars = array();


    public function __construct($templateDir = 'templates/') {
        if (substr($templateDir, -1) != '/') {
            $templateDir .= '/';
        }
        $this->templateDir = __DIR__ . "/$templateDir";

        if (!file_exists($this->templateDir)) {
            throw new Exception("Path doesn't exist.");
        }
    }

    /**
     * Renders template with passed variables
     *
     * @param string $template  filename of the template to render
     * @param array $vars       variables to pass to the template, optional
     * @throws Exception        ..when template not found
     */
    public function render($template, $vars = array()) {
        $this->vars = $vars;

        if (file_exists($this->templateDir . $template)) {
            include($this->templateDir . $template);
        } else {
            throw new Exception('Template not found!');
        }
    }

    /*public function __set($var, $value) {
        $this->vars[$var] = $value;
    }*/

    public function __get($var) {
        if (isset($this->vars[$var])) {
            return $this->vars[$var];
        }
        return null;
    }
}