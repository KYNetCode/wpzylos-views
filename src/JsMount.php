<?php

declare(strict_types=1);

namespace WPZylos\Framework\Views;

use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * JavaScript framework mount helper.
 *
 * Provides methods to render mount points for Vue.js and React apps
 * with WordPress data passing via wp_localize_script.
 */
class JsMount
{
    private ContextInterface $context;
    private CssHelper $css;

    public function __construct(ContextInterface $context, CssHelper $css)
    {
        $this->context = $context;
        $this->css = $css;
    }

    /**
     * Render a Vue/React mount point wrapped in admin root scope.
     *
     * @param string $id Mount element ID
     * @param array $data Data to pass to the JS app via window variable
     * @param string $varName JavaScript global variable name
     * @return string HTML
     */
    public function adminMount(string $id, array $data = [], string $varName = ''): string
    {
        if ($varName === '') {
            $varName = str_replace('-', '', $this->context->slug()) . 'Data';
        }

        $rootId = $this->css->adminRootId();
        $jsonData = wp_json_encode($data, JSON_UNESCAPED_SLASHES);

        return sprintf(
            '<div id="%s" class="wrap" data-theme="light">' . "\n" .
            '  <div id="%s"></div>' . "\n" .
            '  <script>window.%s = %s;</script>' . "\n" .
            '</div>',
            esc_attr($rootId),
            esc_attr($id),
            esc_js($varName),
            $jsonData
        );
    }

    /**
     * Render a frontend mount point (no admin root scope).
     *
     * @param string $id Mount element ID
     * @param array $data Data to pass to the JS app
     * @param string $varName JavaScript global variable name
     * @return string HTML
     */
    public function frontendMount(string $id, array $data = [], string $varName = ''): string
    {
        if ($varName === '') {
            $varName = str_replace('-', '', $this->context->slug()) . 'Data';
        }

        $jsonData = wp_json_encode($data, JSON_UNESCAPED_SLASHES);

        $html = sprintf('<div id="%s"></div>', esc_attr($id));

        if (!empty($data)) {
            $html .= sprintf(
                "\n" . '<script>window.%s = %s;</script>',
                esc_js($varName),
                $jsonData
            );
        }

        return $html;
    }

    /**
     * Render a shortcode mount point.
     *
     * @param string $id Unique mount ID
     * @param array $data Data to pass
     * @return string HTML
     */
    public function shortcodeMount(string $id, array $data = []): string
    {
        return $this->frontendMount($id, $data);
    }
}
