<?php

namespace momentphp\interfaces;

/**
 * ViewEngineInterface
 */
interface ViewEngineInterface
{
    /**
     * Render template content
     *
     * @param  string $template
     * @param  array $data
     * @param  string|null $bundle
     * @return string
     */
    public function render($template, $data = [], $bundle = null);

    /**
     * Check if given template exists
     *
     * @param  string $template
     * @param  string|null $bundle
     * @return boolean
     */
    public function exists($template, $bundle = null);
}
