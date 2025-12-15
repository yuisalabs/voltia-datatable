<?php

namespace Yuisalabs\VoltiaDatatable\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WithFilter
{
    /** @var array<string, Filter> */
    protected array $filters = [];

    protected function applyFilters(Builder $query): void
    {
        foreach ($this->filters as $filter) {
            $value = request("filters.{$filter->column}");
            $filter->apply($query, $value);
        }
    }

    protected function filtersMeta(): array
    {
        $meta = [];
        foreach ($this->filters as $filter) {
            $meta[$filter->column] = array_merge(
                ['value' => request("filters.{$filter->column}")],
                $filter->meta()
            );
        }

        return $meta;
    }
}