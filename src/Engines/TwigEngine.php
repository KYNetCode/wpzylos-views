<?php

declare(strict_types=1);

namespace WPZylos\Framework\Views\Engines;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WPZylos\Framework\Views\EngineInterface;

/**
 * Twig template engine adapter.
 *
 * Wraps Twig with autoescape enabled by default.
 * Twig is an optional dependency.
 *
 * @package WPZylos\Framework\Views
 */
class TwigEngine implements EngineInterface
{
    /**
     * @var Environment Twig environment
     */
    private Environment $twig;

    /**
     * @var string Base view path
     */
    private string $basePath;

    /**
     * Create Twig engine.
     *
     * @param string $basePath View base path
     * @param string|null $cachePath Cache directory (null for no cache)
     * @param bool $debug Enable debug mode
     */
    public function __construct(string $basePath, ?string $cachePath = null, bool $debug = false)
    {
        $this->basePath = rtrim($basePath, '/\\');

        $loader = new FilesystemLoader($this->basePath);

        $this->twig = new Environment($loader, [
            'cache' => $cachePath ?? false,
            'debug' => $debug,
            'autoescape' => 'html', // Autoescape enabled by default
            'strict_variables' => $debug,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $path, array $data = []): string
    {
        // Convert absolute path to relative for Twig
        $relativePath = $this->toRelative($path);
        return $this->twig->render($relativePath, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function canRender(string $path): bool
    {
        return str_ends_with($path, '.twig') || str_ends_with($path, '.html.twig');
    }

    /**
     * Get Twig environment for customization.
     *
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->twig;
    }

    /**
     * Convert absolute path to relative.
     *
     * @param string $path Absolute path
     * @return string Relative path
     */
    private function toRelative(string $path): string
    {
        if (str_starts_with($path, $this->basePath)) {
            return ltrim(substr($path, strlen($this->basePath)), '/\\');
        }

        return $path;
    }
}
