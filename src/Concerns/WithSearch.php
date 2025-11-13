<?php

namespace Yuisa\VoltiaDatatable\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WithSearch
{
    protected ?string $search = null;

    protected function applySearch(Builder $query): void
    {
        if (!$this->search) return;

        $columns = collect($this->columns)->filter->searchable->pluck('key');
        if ($columns->isEmpty()) return;

        $query->where(function (Builder $q) use ($columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', '%' . $this->search . '%');
            }
        });
    }
}