<?php

namespace Yuisa\VoltiaDatatable;

if (! function_exists('datatable')) {
    /**
     * Syntactic sugar to make a datatable.
     */
    function datatable(Table $table): array
    {
        return $table->make();
    }
}