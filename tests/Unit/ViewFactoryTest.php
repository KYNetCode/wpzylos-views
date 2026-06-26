<?php

defined('ABSPATH') || exit;

declare(strict_types=1);

namespace WPZylos\Framework\Views\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Core\Contracts\ContextInterface;
use WPZylos\Framework\Views\ViewFactory;

/**
 * Tests for ViewFactory class.
 */
class ViewFactoryTest extends TestCase
{
    private string $viewsPath;
    private ViewFactory $factory;

    protected function setUp(): void
    {
        $this->viewsPath = sys_get_temp_dir() . '/wpzylos_views_test_' . uniqid();
        mkdir($this->viewsPath, 0755, true);

        $context = $this->createMock(ContextInterface::class);
        $context->method('path')
            ->willReturnCallback(fn($rel) => $this->viewsPath . '/' . $rel);

        // Pass explicit basePath to match where test files are created
        $this->factory = new ViewFactory($context, $this->viewsPath);
    }

    protected function tearDown(): void
    {
        // Clean up test views
        $files = glob($this->viewsPath . '/*');
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }
        if (is_dir($this->viewsPath))
            rmdir($this->viewsPath);
    }

    public function testRenderReturnsTemplateContent(): void
    {
        file_put_contents($this->viewsPath . '/test.php', 'Hello World');

        $result = $this->factory->render('test', []);

        $this->assertSame('Hello World', $result);
    }

    public function testRenderPassesDataToTemplate(): void
    {
        file_put_contents($this->viewsPath . '/greeting.php', 'Hello <?php echo $name; ?>');

        $result = $this->factory->render('greeting', ['name' => 'John']);

        $this->assertSame('Hello John', $result);
    }

    public function testExistsReturnsTrueForExistingView(): void
    {
        file_put_contents($this->viewsPath . '/exists.php', 'content');

        $this->assertTrue($this->factory->exists('exists'));
    }

    public function testExistsReturnsFalseForMissingView(): void
    {
        $this->assertFalse($this->factory->exists('missing'));
    }
}
