<?php

namespace Yuisalabs\VoltiaDatatable;

class Action
{
    public function __construct(
        public string $name,
        public string $label,
        public ?string $icon = null,
        public ?string $color = null,
        public ?string $variant = 'button',
        public mixed $visible = null,
        public array $config = []
    ) {}

    public static function make(string $name, string $label): static
    {
        return new static(
            name: $name,
            label: $label
        );
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    /**
     * Set a callback to determine if this action should be visible
     * 
     * @param callable $callback Function that returns boolean
     * @return static
     */
    public function visible(callable $callback): static
    {
        $this->visible = $callback;
        return $this;
    }

    /**
     * Add custom configuration to the action
     * 
     * @param array $config Additional configuration array
     * @return static
     */
    public function config(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Set URL configuration for the action
     * 
     * @param string|callable $url URL or callback that generates URL
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
     * @return static
     */
    public function url(string|callable $url, string $method = 'GET'): static
    {
        $this->config['url'] = $url;
        $this->config['method'] = $method;
        return $this;
    }

    /**
     * Mark this action as requiring confirmation
     * 
     * @param string|null $message Confirmation message
     * @return static
     */
    public function requireConfirmation(?string $message = null): static
    {
        $this->config['requireConfirmation'] = true;
        if ($message) {
            $this->config['confirmationMessage'] = $message;
        }
        return $this;
    }

    /**
     * Check if this action should be visible based on the callback
     * 
     * @return bool
     */
    public function isVisible(): bool
    {
        if ($this->visible === null) {
            return true;
        }

        return (bool) ($this->visible)();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'color' => $this->color,
            'variant' => $this->variant,
            'config' => $this->config,
        ];
    }
}
