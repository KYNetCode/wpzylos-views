<?php

declare(strict_types=1);

namespace WPZylos\Framework\Views\Engines;

use WPZylos\Framework\Views\EngineInterface;

/**
 * PHP template engine.
 *
 * Renders plain PHP templates with output buffering and safe extraction.
 *
 * @package WPZylos\Framework\Views
 */
class PhpEngine implements EngineInterface
{
    /**
     * {@inheritDoc}
     */
    public function render(string $path, array $data = []): string
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("View not found: {$path}");
        }

        // Start output buffering
        ob_start();

        // Extract data safely (EXTR_SKIP prevents overwriting existing vars)
        extract($data, EXTR_SKIP);

        // Include template
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() ?: '';
    }

    /**
     * {@inheritDoc}
     */
    public function canRender(string $path): bool
    {
        return str_ends_with($path, '.php');
    }
}
