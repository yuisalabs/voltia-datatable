<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class NumericFilter extends Filter
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

        $operator = request("filters.{$this->column}_operator", 'equals');

        switch ($operator) {
            case 'does_not_equal':
                $numericValue = is_numeric($value) ? $value : null;
                if ($numericValue !== null) {
                    $query->where($this->column, '!=', $numericValue);
                }
                break;
            case 'is_greater_than':
                $numericValue = is_numeric($value) ? $value : null;
                if ($numericValue !== null) {
                    $query->where($this->column, '>', $numericValue);
                }
                break;
            case 'is_greater_than_or_equal_to':
                $numericValue = is_numeric($value) ? $value : null;
                if ($numericValue !== null) {
                    $query->where($this->column, '>=', $numericValue);
                }
                break;
            case 'is_less_than':
                $numericValue = is_numeric($value) ? $value : null;
                if ($numericValue !== null) {
                    $query->where($this->column, '<', $numericValue);
                }
                break;
            case 'is_less_than_or_equal_to':
                $numericValue = is_numeric($value) ? $value : null;
                if ($numericValue !== null) {
                    $query->where($this->column, '<=', $numericValue);
                }
                break;
            case 'is_between':
                if (is_array($value) && count($value) === 2) {
                    $min = is_numeric($value[0]) ? $value[0] : null;
                    $max = is_numeric($value[1]) ? $value[1] : null;
                    if ($min !== null && $max !== null) {
                        $query->whereBetween($this->column, [$min, $max]);
                    }
                }
                break;
            case 'is_not_between':
                if (is_array($value) && count($value) === 2) {
                    $min = is_numeric($value[0]) ? $value[0] : null;
                    $max = is_numeric($value[1]) ? $value[1] : null;
                    if ($min !== null && $max !== null) {
                        $query->whereNotBetween($this->column, [$min, $max]);
                    }
                }
                break;
            case 'equals':
            default:
                $numericValue = is_numeric($value) ? $value : null;
                if ($numericValue !== null) {
                    $query->where($this->column, '=', $numericValue);
                }
                break;
        }
    }

    public function meta(): array
    {
        return [
            'type' => 'numeric',
            'column' => $this->column,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->column)),
            'placeholder' => 'Enter number...',
            'operators' => [
                ['value' => 'equals', 'label' => 'Equals'],
                ['value' => 'does_not_equal', 'label' => 'Does Not Equal'],
                ['value' => 'is_greater_than', 'label' => 'Is Greater Than'],
                ['value' => 'is_greater_than_or_equal_to', 'label' => 'Is Greater Than Or Equal To'],
                ['value' => 'is_less_than', 'label' => 'Is Less Than'],
                ['value' => 'is_less_than_or_equal_to', 'label' => 'Is Less Than Or Equal To'],
                ['value' => 'is_between', 'label' => 'Is Between'],
                ['value' => 'is_not_between', 'label' => 'Is Not Between'],
            ],
            'defaultOperator' => 'equals',
        ];
    }
}
