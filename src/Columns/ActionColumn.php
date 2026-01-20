<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Yuisalabs\VoltiaDatatable\Column;

class ActionColumn extends Column
{
    protected string $type = 'action';

    /** @var array<callable|array> */
    protected array $actions = [];

    protected bool $dropdown = false;

    protected ?string $dropdownLabel = null;

    public static function make(string $key = 'actions', ?string $label = null): static
    {
        $self = parent::make($key, $label);
        $self->align = 'center';
        
        return $self;
    }

    /**
     * Add an action to this column
     * 
     * @param string $name Action name/identifier
     * @param string $label Display label for the action
     * @param mixed $visible Callback to determine if action should be visible for a row
     * @param array $config Additional configuration (icon, color, url, method, etc.)
     * @return static
     */
    public function action(
        string $name,
        string $label,
        mixed $visible = null,
        array $config = []
    ): static {
        $this->actions[] = [
            'name' => $name,
            'label' => $label,
            'visible' => $visible,
            'config' => $config,
        ];

        return $this;
    }

    /**
     * Add multiple actions at once
     * 
     * @param array $actions Array of action definitions
     * @return static
     */
    public function actions(array $actions): static
    {
        foreach ($actions as $action) {
            $this->action(
                $action['name'] ?? '',
                $action['label'] ?? '',
                $action['visible'] ?? null,
                $action['config'] ?? []
            );
        }

        return $this;
    }

    /**
     * Display actions as a dropdown
     * 
     * @param string|null $label Label for the dropdown button
     * @return static
     */
    public function asDropdown(?string $label = null): static
    {
        $this->dropdown = true;
        $this->dropdownLabel = $label;
        
        return $this;
    }

    public function value(mixed $row, mixed $raw): mixed
    {
        $availableActions = [];

        foreach ($this->actions as $action) {
            $visible = $action['visible'];
            
            if ($visible === null || (is_callable($visible) && $visible($row))) {
                $availableActions[] = [
                    'name' => $action['name'],
                    'label' => $action['label'],
                    'config' => $action['config'],
                ];
            }
        }

        return $availableActions;
    }

    public function toArray(): array
    {
        $base = parent::toArray();
        
        return array_merge($base, [
            'actions' => collect($this->actions)->map(function ($action) {
                return [
                    'name' => $action['name'],
                    'label' => $action['label'],
                    'config' => $action['config'],
                ];
            })->all(),
            'dropdown' => $this->dropdown,
            'dropdownLabel' => $this->dropdownLabel,
        ]);
    }
}
