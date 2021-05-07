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
     * @param string $template
     * @param array $data
     * @param string|null $bundle
     * @return string
     */
    public function render(string $template, array $data = [], string $bundle = null): string;

    /**
     * Check if given template exists
     *
     * @param string $template
     * @param string|null $bundle
     * @return boolean
     */
    public function exists(string $template, string $bundle = null): bool;
}
