<?php

namespace momentphp;

use momentphp\interfaces\ViewEngineInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * TwigViewEngine
 */
class TwigViewEngine implements ViewEngineInterface
{
    /**
     * Twig
     *
     * @var Environment
     */
    public $twig;

    /**
     * Constructor
     *
     * @param array $options
     * @throws LoaderError
     */
    public function __construct(array $options = [])
    {
        if (!file_exists($options['compile'])) {
            mkdir($options['compile'], 0777, true);
        }

        $loader = new FilesystemLoader;
        // register paths inside main namespace
        foreach ($options['templates'] as $namespace => $path) {
            if (file_exists($path)) {
                $loader->addPath($path);
            }
        }
        // register paths inside bundles namespaces
        foreach ($options['templates'] as $namespace => $path) {
            if (file_exists($path)) {
                $loader->addPath($path, $namespace);
            }
        }

        $debug = $options['debug'] ?? false;
        $options['autoescape'] = $options['autoescape'] ?? false;

        $environmentOptions = [
            'cache' => $options['compile'],
            'autoescape' => $options['autoescape'],
            'debug' => $debug,
        ];

        $this->twig = new Environment($loader, $environmentOptions);
    }

    /**
     * Render template content
     *
     * @param string $template
     * @param array $data
     * @param string|null $bundle
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(string $template, array $data = [], string $bundle = null): string
    {
        return $this->twig->render($this->template($template, $bundle), $data);
    }

    /**
     * Check if given template exists
     *
     * @param string $template
     * @param string|null $bundle
     * @return boolean
     */
    public function exists(string $template, string $bundle = null): bool
    {
        return $this->twig->getLoader()->exists($this->template($template, $bundle));
    }

    /**
     * Return template path
     *
     * @param string $template
     * @param string|null $bundle
     * @return string
     */
    protected function template(string $template, string $bundle = null): string
    {
        if ($bundle !== null) {
            $template = '@' . $bundle . '/' . $template;
        }
        return $template . '.twig';
    }
}
