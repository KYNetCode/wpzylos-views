<?php

defined('ABSPATH') || exit;

declare(strict_types=1);

namespace WPZylos\Framework\Views\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Views\View;
use WPZylos\Framework\Views\ViewFactory;
use WPZylos\Framework\Views\ViewsServiceProvider;
use WPZylos\Framework\Views\EngineInterface;
use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * Tests for View and views infrastructure.
 */
class ViewTest extends TestCase
{
    private ViewFactory $factory;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/wpzylos-views-test-' . uniqid();
        mkdir($this->tmpDir, 0755, true);

        // Create a simple test template
        file_put_contents($this->tmpDir . '/greeting.php', 'Hello <?= $name ?>!');

        // Create a mock ContextInterface
        $context = $this->createMock(ContextInterface::class);
        $context->method('path')->willReturn($this->tmpDir);

        $this->factory = new ViewFactory($context, $this->tmpDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tmpDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
            }
            rmdir($this->tmpDir);
        }
    }

    public function testViewIsCreatable(): void
    {
        $view = new View($this->factory, 'greeting', ['name' => 'World']);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testViewRendersTemplate(): void
    {
        $view = new View($this->factory, 'greeting', ['name' => 'World']);
        $this->assertSame('Hello World!', $view->render());
    }

    public function testViewStringCasting(): void
    {
        $view = new View($this->factory, 'greeting', ['name' => 'PHP']);
        $this->assertSame('Hello PHP!', (string) $view);
    }

    public function testWithAddsSingleData(): void
    {
        $view = new View($this->factory, 'greeting');
        $result = $view->with('name', 'Test');

        $this->assertSame($view, $result); // fluent
        $this->assertSame('Hello Test!', $view->render());
    }

    public function testWithAddsArrayData(): void
    {
        $view = new View($this->factory, 'greeting');
        $view->with(['name' => 'Array']);

        $this->assertSame('Hello Array!', $view->render());
    }

    public function testEngineInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(EngineInterface::class));
    }

    public function testViewsServiceProviderIsInstantiable(): void
    {
        $provider = new ViewsServiceProvider();
        $this->assertInstanceOf(ViewsServiceProvider::class, $provider);
    }
}
