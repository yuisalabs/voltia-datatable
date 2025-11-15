<?php

namespace Yuisalabs\VoltiaDatatable;

class Column
{
    protected string $type = 'text';

    protected array $config = [];

    public function __construct(
        public string $key,
        public ?string $label = null,
        public bool $sortable = false,
        public bool $searchable = false,
        public mixed $format = null,
        public ?string $align = 'left',
        public ?int $minWidth = null,
        public ?bool $hidden = false
    ) {
        $this->label ??= str($key)->headline()->toString();
    }

    public static function make(string $key, ?string $label = null): static
    {
        return new static(key: $key, label: $label);
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function format(mixed $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function align(string $value): static
    {
        $this->align = $value;
        return $this;
    }

    public function minWidth(int $value): static
    {
        $this->minWidth = $value;
        return $this;
    }

    public function hidden(bool $hidden = true): static
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function config (array $config): static
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'key' => $this->key,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'format' => $this->format,
            'align' => $this->align,
            'minWidth' => $this->minWidth,
            'hidden' => $this->hidden,
            'config' => $this->config,
        ];
    }
}