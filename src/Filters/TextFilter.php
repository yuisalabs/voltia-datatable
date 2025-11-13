<?php

namespace Yuisa\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisa\VoltiaDatatable\Filter;

class TextFilter extends Filter
{
    public function __construct(public string $column) {}
    
    public function apply(Builder $query, mixed $value): void
    {
        if ($value != null && $value !== '') {
            $query->where($this->column, 'like', "%$value%");
        }
    }

    public function meta(): array
    {
        return [
            'type' => 'text',
            'column' => $this->column,
            'placeholder' => 'Enter text...',
        ];
    }
}