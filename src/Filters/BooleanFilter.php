<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class BooleanFilter extends Filter
{
    public function __construct(public string $column) {}
    
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
        ];
    }
}