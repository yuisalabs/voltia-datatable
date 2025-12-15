<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class BooleanFilter extends Filter
{
    public function __construct(public string $column, public ?string $label = null) {}

    public static function make(string $column, ?string $label = null): static
    {
        return new static($column, $label);
    }
    
    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') return;
        $query->where($this->column, (bool) $value);
    }

    public function meta(): array
    {
        return [
            'type' => 'boolean',
            'column' => $this->column,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->column)),
        ];
    }
}