<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class SelectFilter extends Filter
{
    /**
     * @param array<string, string> $options
     */
    public function __construct(public string $column, public array $options) {}
    
    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') return;
        $query->where($this->column, $value);
    }

    public function meta(): array
    {
        return [
            'type' => 'select',
            'column' => $this->column,
            'options' => $this->options,
            'placeholder' => 'Select an option...',
        ];
    }
}