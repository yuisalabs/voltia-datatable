<?php

namespace Yuisalabs\VoltiaDatatable\Concerns;

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
        
        $model = $query->getModel();
        $tableName = $model->getTable();
        
        if (str_contains($this->sortKey, '.')) {
            $this->applySortWithRelation($query, $this->sortKey);
        } else {
            $query->orderBy(
                $tableName . '.' . $this->sortKey,
                $this->sortDirection === 'desc' ? 'desc' : 'asc'
            );
        }
    }

    protected function applySortWithRelation(Builder $query, string $sortKey): void
    {
        [$relation, $column] = explode('.', $sortKey, 2);
        
        $model = $query->getModel();
        $relationInstance = $model->{$relation}();
        $relatedModel = $relationInstance->getRelated();
        $relationTable = $relatedModel->getTable();

        if (method_exists($relationInstance, 'getForeignKeyName')) {
            $foreignKey = $relationInstance->getForeignKeyName();
            $ownerKey = $relationInstance->getOwnerKeyName();
            
            $query->leftJoin(
                $relationTable,
                $model->getTable() . '.' . $foreignKey,
                '=',
                $relationTable . '.' . $ownerKey
            );
        }

        $query->orderBy(
            $relationTable . '.' . $column,
            $this->sortDirection === 'desc' ? 'desc' : 'asc'
        );

        $existingSelects = $query->getQuery()->columns;
        if (empty($existingSelects)) {
            $query->select($model->getTable() . '.*');
        }
    }
}