<?php

namespace Yuisalabs\VoltiaDatatable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Filter;

class TextFilter extends Filter
{
    public function __construct(public string $column, public ?string $label = null) {}

    public static function make(string $column, ?string $label = null): static
    {
        return new static($column, $label);
    }
    
    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') return;

        $operator = request("filters.{$this->column}_operator", 'contains');

        switch ($operator) {
            case 'does_not_contain':
                $query->where($this->column, 'not like', "%$value%");
                break;
            case 'equals':
                $query->where($this->column, '=', $value);
                break;
            case 'does_not_equal':
                $query->where($this->column, '!=', $value);
                break;
            case 'starts_with':
                $query->where($this->column, 'like', "$value%");
                break;
            case 'does_not_start_with':
                $query->where($this->column, 'not like', "$value%");
                break;
            case 'ends_with':
                $query->where($this->column, 'like', "%$value");
                break;
            case 'does_not_end_with':
                $query->where($this->column, 'not like', "%$value");
                break;
            case 'contains':
            default:
                $query->where($this->column, 'like', "%$value%");
                break;
        }
    }

    public function meta(): array
    {
        return [
            'type' => 'text',
            'column' => $this->column,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->column)),
            'placeholder' => 'Enter text...',
            'operators' => [
                ['value' => 'contains', 'label' => 'Contains'],
                ['value' => 'does_not_contain', 'label' => 'Does Not Contain'],
                ['value' => 'starts_with', 'label' => 'Starts With'],
                ['value' => 'ends_with', 'label' => 'Ends With'],
                ['value' => 'does_not_start_with', 'label' => 'Does Not Start With'],
                ['value' => 'does_not_end_with', 'label' => 'Does Not End With'],
                ['value' => 'equals', 'label' => 'Equals'],
                ['value' => 'does_not_equal', 'label' => 'Does Not Equal'],
            ],
            'defaultOperator' => 'contains',
        ];
    }
}