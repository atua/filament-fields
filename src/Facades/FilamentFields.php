<?php

namespace Atua\FilamentFields\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Atua\FilamentFields\FilamentFields
 */
class FilamentFields extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Atua\FilamentFields\FilamentFields::class;
    }
}
