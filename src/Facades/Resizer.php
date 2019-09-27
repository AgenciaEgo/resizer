<?php

namespace Ekersten\Resizer\Facades;

use Illuminate\Support\Facades\Facade;

class Resizer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'resizer';
    }
}
