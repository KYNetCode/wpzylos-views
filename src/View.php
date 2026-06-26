<?php

declare(strict_types=1);

namespace WPZylos\Framework\Views;

/**
 * View instance.
 *
 * Represents a renderable view with data.
 *
 * @package WPZylos\Framework\Views
 */
class View
{
    /**
     * @var ViewFactory Factory instance
     */
    private ViewFactory $factory;

    /**
     * @var string View name
     */
    private string $view;

    /**
     * @var array<string, mixed> View data
     */
    private array $data;

    /**
     * Create view.
     *
     * @param ViewFactory $factory Factory instance
     * @param string $view View name
     * @param array<string, mixed> $data View data
     */
    public function __construct(ViewFactory $factory, string $view, array $data = [])
    {
        $this->factory = $factory;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Add data to view.
     *
     * @param string|array<string, mixed> $key Key or array
     * @param mixed $value Value
     * @return static
     */
    public function with(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Render the view.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->factory->render($this->view, $this->data);
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
