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
    use WithSort, WithFilter, WithSearch, WithPagination;

    /** @var Column[] */
    protected array $columns = [];

    /** @var array<int> */
    protected array $perPageOptions = [];

    /** @var int|null */
    protected ?int $defaultPerPage = null;

    /** @var array<string> */
    protected array $sortable = [];

    /** @var array<string> */
    protected array $searchable = [];

    /** @var string|null */
    protected ?string $defaultSortColumn = null;

    /** @var string */
    protected string $defaultSortDirection = 'asc';    

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
            return $table->build();
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $name
        ));
    }

    public function __call($name, $arguments)
    {
        if ($name === 'make') {
            return $this->build();
        }

        throw new BadMethodCallException(sprintf(
            'Method %s->%s() does not exist.', static::class, $name
        ));
    }

    public function mountFromRequest(): void
    {
        $request = request();

        $this->columns = $this->columns();
        $this->applySortable();
        $this->applySearchable();
        $this->filters = $this->filters();

        $this->search = $request->input('search', $this->search) ?: null;
        $this->sortKey = $request->input('sortBy', $this->defaultSortColumn) ?: null;
        $this->sortDirection = $request->input('sortDirection', $this->defaultSortDirection) === 'desc' ? 'desc' : 'asc';
        
        $maxPerPage = config('voltia-datatable.max_per_page', 100);
        $perPageOptions = $this->resolvePerPageOptions($maxPerPage);
        $defaultPerPage = $this->defaultPerPage ?? config('voltia-datatable.default_per_page', 15);

        $requestedPerPage = (int) $request->integer('perPage', $defaultPerPage);
        $this->perPage = $this->normalizePerPage($requestedPerPage, $perPageOptions, $defaultPerPage);
    }

    public function build(): array
    {
        $this->mountFromRequest();

        $query = $this->query();

        $this->applyEagerLoads($query);

        $this->applyFilters($query);
        $this->applySearch($query);
        $this->applySort($query);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $this->paginate($query);

        /** @var \Illuminate\Support\Collection $rows */
        $rows = collect($paginator->items())->map(function ($row) {
            return collect($this->columns)->mapWithKeys(function (Column $column) use ($row) {
                $rawData = data_get($row, $column->key);
                $value = $column->value($row, $rawData);

                return [$column->key => $value];
            })->all();
        })->values();

        return [
            'rows' => $rows,
            'columns' => collect($this->columns)->reject->hidden->map->toArray()->values(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'perPageOptions' => $this->resolvePerPageOptions((int) config('voltia-datatable.max_per_page', 100)),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'currentPage' => $paginator->currentPage(),
                'firstPage' => 1,
                'lastPage' => $paginator->lastPage()
            ],
            'sort' => [
                'sortBy' => $this->sortKey,
                'sortDirection' => $this->sortDirection,
            ],
            'search' => $this->search,
            'filters' => $this->getActiveFilters(),
            'filtersMeta' => $this->filtersMeta(),
        ];
    }

    protected function getActiveFilters(): array
    {
        $activeFilters = [];
        foreach ($this->filters as $filter) {
            $value = request("filters.{$filter->column}");
            if ($value !== null) {
                $activeFilters[$filter->column] = $value;

                $operator = request("filters.{$filter->column}_operator");
                if ($operator !== null && $operator !== '') {
                    $activeFilters["{$filter->column}_operator"] = $operator;
                }
            }
        }
        return $activeFilters;
    }

    protected function filtersMeta(): array
    {
        $out = [];
        foreach ($this->filters as $key => $filter) {
            $meta = $filter->meta();
            $out[$key] = array_merge(
                [
                    'key' => $meta['column'] ?? $key,
                    'label' => $meta['label'] ?? ucfirst(str_replace('_', ' ', $key)),
                ],
                $meta
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

    protected function resolvePerPageOptions(int $maxPerPage): array
    {
        $options = $this->perPageOptions ?: (array) config('voltia-datatable.per_page_options');

        $options = collect($options)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0 && $value <= $maxPerPage)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (empty($options)) {
            $options = [min(10, $maxPerPage)];
        }

        return $options;
    }

    protected function normalizePerPage(int $requested, array $options, int $default): int
    {
        return in_array($requested, $options, true)
            ? $requested
            : (in_array($default, $options, true) ? $default : $options[0]);
    }

    protected function applySortable()
    {
        foreach ($this->columns as $column) {
            if (in_array($column->key, $this->sortable, true)) {
                $column->sortable();
            }
        }
    }

    protected function applySearchable()
    {
        foreach ($this->columns as $column) {
            if (in_array($column->key, $this->searchable, true)) {
                $column->searchable();
            }
        }
    }
}