<?php

namespace Yuisa\VoltiaDatatable\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Yuisa\VoltiaDatatable\Filter;

class DateRangeFilter extends Filter
{
    public function __construct(public string $column) {}

    public function apply(Builder $query, mixed $value): void
    {
        if (!is_array($value) || count($value) !== 2) return;

        [$start, $end] = $value;
        $dateFormat = config('voltia-datatable.date_format', 'Y-m-d');

        if ($start) $query->whereDate($this->column, '>=', Carbon::parse($start)->format($dateFormat));
        if ($end) $query->whereDate($this->column, '<=', Carbon::parse($end)->format($dateFormat));
    }

    public function meta(): array
    {
        return [
            'type' => 'daterange',
            'column' => $this->column,
        ];
    }
}