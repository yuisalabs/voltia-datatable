<?php

namespace Yuisa\VoltiaDatatable\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WithSort
{
    protected ?string $sortKey = null;

    protected ?string $sortDirection = 'asc';

    protected function applySort(Builder $query): void
    {
        if (!$this->sortKey) return;

        $columns = collect($this->columns)->filter->sortable->pluck('key')->all();
        if (!in_array($this->sortKey, $columns, true)) return;
        
        $query->orderBy(
            $this->sortKey,
            $this->sortDirection === 'desc' ? 'desc' : 'asc'
        );
    }
}