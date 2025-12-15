<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class MultipleSelectFilter extends Filter
{
    /**
     * @param array<string, string> $options
     */
    public function __construct(public string $column, public ?string $label = null, public array $options = []) {}

    public static function make(string $column, ?string $label = null, array $options = []): static
    {
        return new static($column, $label, $options);
    }

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }
    
    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') return;

        if (!is_array($value)) {
            $value = [$value];
        }

        if (empty($value)) return;

        $operator = request("filters.{$this->column}_operator", 'is_in');

        switch ($operator) {
            case 'is_not_in':
                $query->whereNotIn($this->column, $value);
                break;
            case 'is_in':
            default:
                $query->whereIn($this->column, $value);
                break;
        }
    }

    public function meta(): array
    {
        return [
            'type' => 'multiple-select',
            'column' => $this->column,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->column)),
            'options' => $this->options,
            'placeholder' => 'Select options...',
            'operators' => [
                ['value' => 'is_in', 'label' => 'Is In'],
                ['value' => 'is_not_in', 'label' => 'Is Not In'],
            ],
            'defaultOperator' => 'is_in',
        ];
    }
}
