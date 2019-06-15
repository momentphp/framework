<?php

namespace momentphp;

/**
 * SmartyViewEngine
 */
class SmartyViewEngine implements \momentphp\interfaces\ViewEngineInterface
{
    /**
     * Smarty
     *
     * @var \Smarty
     */
    public $smarty;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->smarty = new \Smarty;
        $this->smarty->error_reporting = E_ALL & ~E_NOTICE;
        $this->smarty->escape_html = isset($options['autoescape']) ? $options['autoescape'] : false;

        $debug = isset($options['debug']) ? $options['debug'] : false;

        if ($debug) {
            $this->smarty->force_compile = true;
        } else {
            $this->smarty->compile_check = false;
        }

        if (!file_exists($options['compile'])) {
            mkdir($options['compile'], 0777, true);
        }

        $this->smarty->setTemplateDir($options['templates']);
        $this->smarty->setCompileDir($options['compile']);
    }

    /**
     * Render template content
     *
     * @param  string $template
     * @param  array $data
     * @param  null|string $bundle
     * @return string
     */
    public function render($template, $data = [], $bundle = null)
    {
        foreach ($data as $k => $v) {
            $this->smarty->assign($k, $v);
        }
        return $this->smarty->fetch($this->template($template, $bundle));
    }

    /**
     * Check if given template exists
     *
     * @param  string $template
     * @param  null|string $bundle
     * @return boolean
     */
    public function exists($template, $bundle = null)
    {
        return $this->smarty->templateExists($this->template($template, $bundle));
    }

    /**
     * Return template path
     *
     * @param  string $template
     * @param  null|string $bundle
     * @return string
     */
    protected function template($template, $bundle = null)
    {
        if ($bundle !== null) {
            $template = sprintf('[%s]', $bundle) . $template;
        }
        return $template . '.tpl';
    }
}
