<?php

namespace Yuisa\VoltiaDatatable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Yuisa\VoltiaDatatable\VoltiaDatatable
 */
class VoltiaDatatable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Yuisa\VoltiaDatatable\VoltiaDatatable::class;
    }
}
