<?php

declare(strict_types=1);

namespace WPZylos\Framework\Views;

use WPZylos\Framework\Core\Contracts\ContextInterface;
use WPZylos\Framework\Views\Engines\PhpEngine;

/**
 * View factory.
 *
 * Resolves and renders views using registered engines.
 *
 * @package WPZylos\Framework\Views
 */
class ViewFactory
{
    /**
     * @var ContextInterface Plugin context
     */
    private ContextInterface $context;

    /**
     * @var string Base views path
     */
    private string $basePath;

    /**
     * @var EngineInterface[] Registered engines
     */
    private array $engines = [];

    /**
     * @var array<string, mixed> Shared data for all views
     */
    private array $shared = [];

    /**
     * Create view factory.
     *
     * @param ContextInterface $context Plugin context
     * @param string|null $basePath Base views path
     */
    public function __construct(ContextInterface $context, ?string $basePath = null)
    {
        $this->context = $context;
        $this->basePath = $basePath ?? $context->path('resources/views');

        // Register default PHP engine
        $this->engines[] = new PhpEngine();
    }

    /**
     * Render a view.
     *
     * @param string $view View name (dot notation: 'admin.settings')
     * @param array<string, mixed> $data View data
     * @return string Rendered content
     */
    public function render(string $view, array $data = []): string
    {
        $path = $this->resolvePath($view);
        $engine = $this->getEngineFor($path);

        $mergedData = array_merge($this->shared, $data);

        return $engine->render($path, $mergedData);
    }

    /**
     * Make a View instance (deferred rendering).
     *
     * @param string $view View name
     * @param array<string, mixed> $data View data
     * @return View
     */
    public function make(string $view, array $data = []): View
    {
        return new View($this, $view, array_merge($this->shared, $data));
    }

    /**
     * Check if view exists.
     *
     * @param string $view View name
     * @return bool
     */
    public function exists(string $view): bool
    {
        try {
            $path = $this->resolvePath($view);
            return file_exists($path);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Share data with all views.
     *
     * @param string|array<string, mixed> $key Key or array of key-value pairs
     * @param mixed $value Value (if key is string)
     * @return static
     */
    public function share(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
        } else {
            $this->shared[$key] = $value;
        }

        return $this;
    }

    /**
     * Register a template engine.
     *
     * @param EngineInterface $engine Engine to register
     * @return static
     */
    public function addEngine(EngineInterface $engine): static
    {
        // Prepend to check custom engines first
        array_unshift($this->engines, $engine);
        return $this;
    }

    /**
     * Resolve view name to file path.
     *
     * Converts dot notation to path and finds the file.
     *
     * @param string $view View name
     * @return string Full file path
     */
    private function resolvePath(string $view): string
    {
        // Convert dot notation to path
        $relativePath = str_replace('.', DIRECTORY_SEPARATOR, $view);
        $basePath = rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR;

        // Try different extensions
        $extensions = ['.php', '.html.php', '.twig', '.html.twig'];

        foreach ($extensions as $extension) {
            $fullPath = $basePath . $relativePath . $extension;
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        // Try without extension (if provided in view name)
        $fullPath = $basePath . $relativePath;
        if (file_exists($fullPath)) {
            return $fullPath;
        }

        throw new \InvalidArgumentException("View not found: {$view}");
    }

    /**
     * Get appropriate engine for a file.
     *
     * @param string $path File path
     * @return EngineInterface
     */
    private function getEngineFor(string $path): EngineInterface
    {
        foreach ($this->engines as $engine) {
            if ($engine->canRender($path)) {
                return $engine;
            }
        }

        // Fall back to PHP engine
        return $this->engines[array_key_last($this->engines)];
    }

    /**
     * Get base views path.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
