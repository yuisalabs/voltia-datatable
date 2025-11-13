<?php

namespace Yuisa\VoltiaDatatable\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WithFilter
{
    // ** @var array<string, Filter> */
    protected array $filters = [];

    protected function applyFilters(Builder $query): void
    {
        foreach ($this->filters as $key => $filter) {
            $value = request("filters.$key");
            $filter->apply($query, $value);
        }
    }

    protected function filtersMeta(): array
    {
        $meta = [];
        foreach ($this->filters as $key => $filter) {
            $meta[$key] = array_merge(
                ['value' => request("filters.$key")],
                $filter->meta()
            );
        }

        return $meta;
    }
}