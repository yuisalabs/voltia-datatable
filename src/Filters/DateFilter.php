<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class DateFilter extends Filter
{
    public function __construct(
        public string $column,
        public ?string $label = null,
        public string $operator = '='
    ) {}

    public static function make(string $column, ?string $label = null, string $operator = '='): static
    {
        return new static($column, $label, $operator);
    }

    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') return;

        try {
            $date = Carbon::parse($value);
        } catch (\Exception $e) {
            return;
        }

        $operator = request("filters.{$this->column}_operator", '=');

        match ($operator) {
            'does_not_equal' => $query->whereDate($this->column, '!=', $date->format('Y-m-d')),
            'is_after' => $query->whereDate($this->column, '>', $date->format('Y-m-d')),
            'is_before' => $query->whereDate($this->column, '<', $date->format('Y-m-d')),
            'is_after_or_equal' => $query->whereDate($this->column, '>=', $date->format('Y-m-d')),
            'is_before_or_equal' => $query->whereDate($this->column, '<=', $date->format('Y-m-d')),
            'equals' => $query->whereDate($this->column, '=', $date->format('Y-m-d')),
            'is_set' => $query->whereNotNull($this->column),
            'is_not_set' => $query->whereNull($this->column),
            default => $query->whereDate($this->column, '=', $date->format('Y-m-d')),
        };
    }

    public function meta(): array
    {
        return [
            'type' => 'date',
            'column' => $this->column,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->column)),
            'operators' => [
                ['value' => 'equals', 'label' => 'Equals'],
                ['value' => 'does_not_equal', 'label' => 'Does Not Equal'],
                ['value' => 'is_before', 'label' => 'Is Before'],
                ['value' => 'is_after', 'label' => 'Is After'],
                ['value' => 'is_before_or_equal', 'label' => 'Equals Or Is Before'],
                ['value' => 'is_after_or_equal', 'label' => 'Equals Or Is After'],
                ['value' => 'is_set', 'label' => 'Is Set'],
                ['value' => 'is_not_set', 'label' => 'Is Not Set'],
            ],
            'defaultOperator' => 'equals',
        ];
    }
}
