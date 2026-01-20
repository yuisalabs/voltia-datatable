<?php

namespace Yuisalabs\VoltiaDatatable\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WithSearch
{
    protected ?string $search = null;

    protected function applySearch(Builder $query): void
    {
        if (!$this->search) return;

        $columns = collect($this->columns)->filter->searchable->pluck('key');
        if ($columns->isEmpty()) return;

        $model = $query->getModel();
        $tableName = $model->getTable();

        $query->where(function (Builder $q) use ($columns, $tableName) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function (Builder $subQuery) use ($relationColumn) {
                        $subQuery->where($relationColumn, 'like', '%' . $this->search . '%');
                    });
                } else {
                    $q->orWhere($tableName . '.' . $column, 'like', '%' . $this->search . '%');
                }
            }
        });
    }
}