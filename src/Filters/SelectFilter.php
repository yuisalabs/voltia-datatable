<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class SelectFilter extends Filter
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

        $operator = request("filters.{$this->column}_operator", 'equals');

        $column = $this->qualifyColumn($query, $this->column);

        switch ($operator) {
            case 'does_not_equal':
                $query->where($column, '!=', $value);
                break;
            case 'equals':
            default:
                $query->where($column, '=', $value);
                break;
        }
    }

    public function meta(): array
    {
        return [
            'type' => 'select',
            'column' => $this->column,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->column)),
            'options' => $this->options,
            'placeholder' => 'Select an option...',
            'operators' => [
                ['value' => 'equals', 'label' => 'Equals'],
                ['value' => 'does_not_equal', 'label' => 'Does Not Equal'],
            ],
            'defaultOperator' => 'equals',
        ];
    }
}