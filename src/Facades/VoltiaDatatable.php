<?php

namespace Yuisalabs\VoltiaDatatable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Yuisalabs\VoltiaDatatable\VoltiaDatatable
 */
class VoltiaDatatable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Yuisalabs\VoltiaDatatable\VoltiaDatatable::class;
    }
}
