<?php

namespace Yuisalabs\VoltiaDatatable;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    abstract public function apply(Builder $query, mixed $value): void;

    /**
     * Metadata about the filter for frontend use
     */
    abstract public function meta(): array;

    /**
     * Qualify column name with table name to avoid ambiguity when joins are present
     */
    protected function qualifyColumn(Builder $query, string $column): string
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $query->getModel()->getTable() . '.' . $column;
    }
}