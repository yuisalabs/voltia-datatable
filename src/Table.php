<?php

namespace Yuisalabs\VoltiaDatatable;

use BadMethodCallException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Yuisalabs\VoltiaDatatable\Concerns\WithFilter;
use Yuisalabs\VoltiaDatatable\Concerns\WithPagination;
use Yuisalabs\VoltiaDatatable\Concerns\WithSearch;
use Yuisalabs\VoltiaDatatable\Concerns\WithSort;

abstract class Table
{
    use WithSort,WithFilter, WithSearch, WithPagination;

    /** @var Column[] */
    protected array $columns = [];

    /** @return Builder */
    abstract public function query(): Builder;

    /** @return Column[] */
    abstract public function columns(): array;

    protected function filters(): array
    {
        return [];
    }

    public static function __callStatic($name, $arguments)
    {
        if ($name == 'make') {
            /** @var static $table */
            $table = app(static::class);
            return $table->make();
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $name
        ));
    }

    public function mountFromRequest(): void
    {
        $request = request();

        $this->columns = $this->columns();
        $this->filters = $this->filters();

        $this->search = $request->input('search', $this->search) ?: null;
        $this->sortKey = $request->input('sortBy', $this->sortKey) ?: null;
        $this->sortDirection = $request->input('sortDirection', $this->sortDirection) === 'desc' ? 'desc' : 'asc';
        
        $defaultPerPage = config('voltia-datatable.default_per_page', 15);
        $maxPerPage = config('voltia-datatable.max_per_page', 100);
        $this->perPage = (int) min(max((int) $request->integer('perPage', $defaultPerPage), 1), $maxPerPage);
    }

    public function make(): array
    {
        $this->mountFromRequest();

        $query = $this->query();

        $this->applyEagerLoads($query);

        $this->applyFilters($query);
        $this->applySearch($query);
        $this->applySort($query);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $this->paginate($query);

        $rows = $paginator->getCollection()->map(function ($row) {
            return collect($this->columns)->mapWithKeys(function (Column $column) use ($row) {
                $value = data_get($row, $column->key);
                if ($column->format) $value = ($column->format)($row, $value);
                return [$column->key => $value];
            })->all();
        })->values();

        return [
            'rows' => $rows,
            'columns' => collect($this->columns)->reject->hidden->map->toArray()->values(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'sort' => [
                'sortBy' => $this->sortKey,
                'sortDirection' => $this->sortDirection,
            ],
            'search' => $this->search,
            'filters' => $this->filtersMeta(),
        ];
    }

    protected function filtersMeta(): array
    {
        $out = [];
        foreach ($this->filters  as $key => $filter) {
            $out[$key] = array_merge(
                ['value' => request("filters.$key")],
                $filter->meta()
            );
        }
        return $out;
    }

    protected function applyEagerLoads(Builder $query): void
    {
        $relations = collect($this->columns)
            ->map->key
            ->filter(fn ($key) => str_contains($key, '.'))
            ->map(fn ($key) => explode('.', $key)[0])
            ->unique()
            ->values();

        if ($relations->isNotEmpty()) $query->with($relations->all());
    }
}