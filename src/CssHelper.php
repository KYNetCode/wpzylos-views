<?php

declare(strict_types=1);

namespace WPZylos\Framework\Views;

use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * CSS prefix helper for DaisyUI component classes.
 *
 * Provides helper methods to generate prefixed CSS class names
 * for DaisyUI components, ensuring style isolation between plugins.
 *
 * @package WPZylos\Framework\Views
 */
class CssHelper
{
    /**
     * @var string CSS prefix (e.g., 'fcds-')
     */
    private string $prefix;

    /**
     * @var string CSS prefix without trailing hyphen (e.g., 'fcds')
     */
    private string $prefixClean;

    /**
     * Create CSS helper.
     *
     * @param ContextInterface $context Plugin context
     */
    public function __construct(ContextInterface $context)
    {
        $config = $context->config();
        $this->prefix = $config->get('ui.css_prefix', $this->derivePrefix($context->name()));
        $this->prefixClean = rtrim($this->prefix, '-');
    }

    /**
     * Get the CSS prefix.
     *
     * @return string e.g., 'fcds-'
     */
    public function prefix(): string
    {
        return $this->prefix;
    }

    /**
     * Get the CSS prefix without trailing hyphen.
     *
     * @return string e.g., 'fcds'
     */
    public function prefixClean(): string
    {
        return $this->prefixClean;
    }

    /**
     * Generate prefixed class name(s).
     *
     * @param string ...$classes DaisyUI class names (without prefix)
     * @return string Space-separated prefixed class names
     *
     * @example
     * $css->cls('btn', 'btn-primary')
     * // Returns: 'fcds-btn fcds-btn-primary'
     */
    public function cls(string ...$classes): string
    {
        return implode(' ', array_map(
            fn(string $class) => $this->prefix . $class,
            $classes
        ));
    }

    /**
     * Generate the admin root container ID.
     *
     * @return string e.g., 'fcds-admin'
     */
    public function adminRootId(): string
    {
        return $this->prefixClean . '-admin';
    }

    /**
     * Generate an opening admin root div tag.
     *
     * @param string $extraClasses Additional CSS classes
     * @return string HTML opening tag
     */
    public function adminOpen(string $extraClasses = 'wrap'): string
    {
        $id = $this->adminRootId();
        return sprintf('<div id="%s" class="%s">', esc_attr($id), esc_attr($extraClasses));
    }

    /**
     * Generate closing admin root div tag.
     *
     * @return string HTML closing tag
     */
    public function adminClose(): string
    {
        return '</div>';
    }

    /**
     * Derive CSS prefix from plugin name.
     *
     * Takes first letter of each word.
     * e.g., "First Class Dress Clothing" -> "fcds-"
     *
     * @param string $name Plugin name
     * @return string Derived prefix
     */
    private function derivePrefix(string $name): string
    {
        $cleaned = preg_replace('/[^a-zA-Z\s]/', '', $name);
        $words = preg_split('/\s+/', trim($cleaned));
        $prefix = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $prefix .= strtolower($word[0]);
            }
        }

        return $prefix . '-';
    }
}
