<?php

defined('ABSPATH') || exit;

declare(strict_types=1);

namespace WPZylos\Framework\Views\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Views\CssHelper;
use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * Tests for CssHelper class.
 */
class CssHelperTest extends TestCase
{
    /**
     * Create a CssHelper with a fake context that returns the given prefix from config.
     *
     * CssHelper calls $context->config()->get('ui.css_prefix', ...) and $context->name(),
     * which are not on ContextInterface. We use an anonymous class to provide them.
     */
    private function createHelper(string $prefix = 'test-', string $pluginName = 'Test Plugin'): CssHelper
    {
        $configStub = new class($prefix) {
            private string $prefix;

            public function __construct(string $prefix)
            {
                $this->prefix = $prefix;
            }

            public function get(string $key, $default = null)
            {
                if ($key === 'ui.css_prefix') {
                    return $this->prefix;
                }
                return $default;
            }
        };

        $context = new class($configStub, $pluginName) implements ContextInterface {
            private object $config;
            private string $pluginName;

            public function __construct(object $config, string $pluginName)
            {
                $this->config = $config;
                $this->pluginName = $pluginName;
            }

            public function config(): object
            {
                return $this->config;
            }

            public function name(): string
            {
                return $this->pluginName;
            }

            // ContextInterface stubs
            public function slug(): string { return 'test-plugin'; }
            public function prefix(): string { return 'test_'; }
            public function textDomain(): string { return 'test-plugin'; }
            public function version(): string { return '1.0.0'; }
            public function file(): string { return '/tmp/test.php'; }
            public function path(string $relativePath = ''): string { return '/tmp/' . $relativePath; }
            public function url(string $relativePath = ''): string { return 'http://example.com/' . $relativePath; }
            public function hook(string $name): string { return 'test_' . $name; }
            public function optionKey(string $key): string { return 'test_' . $key; }
            public function transientKey(string $key): string { return 'test_' . $key; }
            public function cronHook(string $name): string { return 'test_' . $name; }
            public function tableName(string $name, string $scope = 'site'): string { return 'wp_test_' . $name; }
            public function metaKey(string $key): string { return '_test_' . $key; }
            public function assetHandle(string $handle): string { return 'test-' . $handle; }
            public function namespace(): string { return 'TestPlugin'; }
        };

        return new CssHelper($context);
    }

    /**
     * Create a CssHelper that relies on derivePrefix fallback (no config value).
     */
    private function createHelperWithDerivedPrefix(string $pluginName): CssHelper
    {
        $configStub = new class {
            public function get(string $key, $default = null)
            {
                return $default; // Always return the default (derived prefix)
            }
        };

        $context = new class($configStub, $pluginName) implements ContextInterface {
            private object $config;
            private string $pluginName;

            public function __construct(object $config, string $pluginName)
            {
                $this->config = $config;
                $this->pluginName = $pluginName;
            }

            public function config(): object { return $this->config; }
            public function name(): string { return $this->pluginName; }

            // ContextInterface stubs
            public function slug(): string { return 'test'; }
            public function prefix(): string { return 'test_'; }
            public function textDomain(): string { return 'test'; }
            public function version(): string { return '1.0.0'; }
            public function file(): string { return '/tmp/test.php'; }
            public function path(string $relativePath = ''): string { return '/tmp/' . $relativePath; }
            public function url(string $relativePath = ''): string { return 'http://example.com/' . $relativePath; }
            public function hook(string $name): string { return 'test_' . $name; }
            public function optionKey(string $key): string { return 'test_' . $key; }
            public function transientKey(string $key): string { return 'test_' . $key; }
            public function cronHook(string $name): string { return 'test_' . $name; }
            public function tableName(string $name, string $scope = 'site'): string { return 'wp_test_' . $name; }
            public function metaKey(string $key): string { return '_test_' . $key; }
            public function assetHandle(string $handle): string { return 'test-' . $handle; }
            public function namespace(): string { return 'TestPlugin'; }
        };

        return new CssHelper($context);
    }

    public function testPrefixReturnsConfiguredValue(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame('fcds-', $css->prefix());
    }

    public function testPrefixCleanRemovesTrailingHyphen(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame('fcds', $css->prefixClean());
    }

    public function testPrefixCleanWithoutTrailingHyphen(): void
    {
        $css = $this->createHelper('abc');
        $this->assertSame('abc', $css->prefixClean());
    }

    public function testClsGeneratesSinglePrefixedClass(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame('fcds-btn', $css->cls('btn'));
    }

    public function testClsGeneratesMultiplePrefixedClasses(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame('fcds-btn fcds-btn-primary', $css->cls('btn', 'btn-primary'));
    }

    public function testClsGeneratesThreePrefixedClasses(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame(
            'fcds-btn fcds-btn-primary fcds-btn-lg',
            $css->cls('btn', 'btn-primary', 'btn-lg')
        );
    }

    public function testAdminRootIdUsesCleanPrefix(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame('fcds-admin', $css->adminRootId());
    }

    public function testAdminCloseReturnsClosingDiv(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame('</div>', $css->adminClose());
    }

    public function testDifferentPrefix(): void
    {
        $css = $this->createHelper('map-');
        $this->assertSame('map-', $css->prefix());
        $this->assertSame('map', $css->prefixClean());
        $this->assertSame('map-btn map-btn-primary', $css->cls('btn', 'btn-primary'));
        $this->assertSame('map-admin', $css->adminRootId());
    }

    public function testDerivePrefixFromPluginName(): void
    {
        $css = $this->createHelperWithDerivedPrefix('First Class Dress Clothing');
        $this->assertSame('fcdc-', $css->prefix());
        $this->assertSame('fcdc', $css->prefixClean());
    }

    public function testDerivePrefixFromTwoWordName(): void
    {
        $css = $this->createHelperWithDerivedPrefix('My Plugin');
        $this->assertSame('mp-', $css->prefix());
        $this->assertSame('mp', $css->prefixClean());
    }

    public function testDerivePrefixFromSingleWordName(): void
    {
        $css = $this->createHelperWithDerivedPrefix('Plugin');
        $this->assertSame('p-', $css->prefix());
    }

    public function testDerivePrefixStripsSpecialCharacters(): void
    {
        $css = $this->createHelperWithDerivedPrefix('My-Plugin 2.0 (Beta)');
        // Removing non-alpha chars: "My-Plugin 2.0 (Beta)" -> "MyPlugin  Beta" -> "mb-"
        $this->assertSame('mb-', $css->prefix());
    }

    public function testClsWithEmptyInput(): void
    {
        $css = $this->createHelper('fcds-');
        $this->assertSame('fcds-', $css->cls(''));
    }
}
