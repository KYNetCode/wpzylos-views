<?php

declare(strict_types=1);

namespace WPZylos\Framework\Views;

/**
 * Engine interface.
 *
 * Template engines must implement this interface.
 *
 * @package WPZylos\Framework\Views
 */
interface EngineInterface
{
    /**
     * Render a template file.
     *
     * @param string $path Absolute path to template
     * @param array<string, mixed> $data Template data
     * @return string Rendered content
     */
    public function render(string $path, array $data = []): string;

    /**
     * Check if engine can render this file.
     *
     * @param string $path File path
     * @return bool
     */
    public function canRender(string $path): bool;
}
