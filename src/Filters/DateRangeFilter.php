<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class DateRangeFilter extends Filter
{
    public function __construct(public string $column, public ?string $label = null) {}

    public static function make(string $column, ?string $label = null): static
    {
        return new static($column, $label);
    }

    public function apply(Builder $query, mixed $value): void
    {
        if (!is_array($value) || count($value) !== 2) return;

        [$start, $end] = $value;
        $dateFormat = config('voltia-datatable.date_format', 'Y-m-d');

        $operator = request("filters.{$this->column}_operator", 'is_between');

        $column = $this->qualifyColumn($query, $this->column);

        match ($operator) {
            'is_not_between' => $query->whereDateBetween($column, [$start, $end], true),
            'is_between' => $query->whereDateBetween($column, [$start, $end], false),
            default => $query->whereDateBetween($column, [$start, $end], false),
        };
    }

    public function meta(): array
    {
        return [
            'type' => 'daterange',
            'column' => $this->column,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->column)),
            'operators' => [
                ['value' => 'is_between', 'label' => 'Is Between'],
                ['value' => 'is_not_between', 'label' => 'Is Not Between'],
            ],
            'defaultOperator' => 'is_between',
        ];
    }
}