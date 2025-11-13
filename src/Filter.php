<?php

namespace Yuisa\VoltiaDatatable;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    abstract public function apply(Builder $query, mixed $value): void;

    /**
     * Metadata about the filter for frontend use
     */
    abstract public function meta(): array;
}