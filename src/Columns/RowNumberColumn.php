<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Yuisalabs\VoltiaDatatable\Column;

class RowNumberColumn extends Column
{
    protected string $type = 'row_number';

    protected int $startFrom = 1;

    public function __construct(
        string $key = '#',
        ?string $label = null,
        bool $sortable = false,
        bool $searchable = false,
        mixed $format = null,
        ?string $align = 'center',
        ?int $minWidth = 60,
        ?bool $hidden = false
    ) {
        parent::__construct(
            key: $key,
            label: $label ?? '#',
            sortable: $sortable,
            searchable: $searchable,
            format: $format,
            align: $align,
            minWidth: $minWidth,
            hidden: $hidden
        );
    }

    public static function make(string $key = '#', ?string $label = null): static
    {
        return new static(key: $key, label: $label);
    }

    public function startFrom(int $number): static
    {
        $this->startFrom = max(1, $number);
        $this->rowNumberConfig();

        return $this;
    }

    public function getStartFrom(): int
    {
        return $this->startFrom;
    }

    public function value(mixed $row, mixed $raw): mixed
    {
        if (is_callable($this->format)) {
            return ($this->format)($row, $raw);
        }

        return $raw;
    }

    protected function rowNumberConfig(): void
    {
        $this->config([
            'startFrom' => $this->startFrom,
        ]);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'config' => array_merge($this->config, [
                'startFrom' => $this->startFrom,
            ]),
        ]);
    }
}
