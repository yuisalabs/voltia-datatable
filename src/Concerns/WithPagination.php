<?php

namespace Yuisalabs\VoltiaDatatable\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait WithPagination
{
    protected int $perPage = 15;

    protected function paginate($query): LengthAwarePaginator
    {
        $paginator = $query->paginate($this->perPage);

        if (config('voltia-datatable.query_string')) {
            $paginator->appends(request()->query());
        }

        return $paginator;
    }
}